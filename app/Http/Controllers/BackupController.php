<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Laracasts\Flash\Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use RuntimeException;

class BackupController extends Controller
{
    protected $backupPath;

    /** Folder written by the scheduled `db:backup` command (listed alongside manual backups). */
    protected $scheduledPath;

    public function __construct(private DatabaseBackupService $backups)
    {
        $this->backupPath = storage_path('app/backups');
        $this->scheduledPath = $this->backupPath . DIRECTORY_SEPARATOR . 'scheduled';

        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $backups = [];

        // Manual backups (and the automatic "pre-restore" safety copies) and the scheduled ones. Only
        // finished .sql files are listed: an interrupted dump is a ".partial" file that never shows up.
        foreach ([false => $this->backupPath, true => $this->scheduledPath] as $scheduled => $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            foreach (File::files($directory) as $file) {
                if ($file->getExtension() !== 'sql') {
                    continue;
                }

                $backups[] = [
                    'name' => $file->getFilename(),
                    'scheduled' => (bool) $scheduled,
                    'size' => $this->formatBytes($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->toDayDateTimeString(),
                    'raw_date' => $file->getMTime()
                ];
            }
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return $b['raw_date'] <=> $a['raw_date'];
        });

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        try {
            $path = $this->backups->createDump($this->backupPath, 'backup');

            Flash::success('Backup created successfully: ' . basename($path));
        } catch (RuntimeException $e) {
            Log::error('Manual backup failed: ' . $e->getMessage());
            Flash::error('Failed to create backup: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function download(Request $request, $fileName)
    {
        $filePath = $this->resolveBackupPath($fileName, $request->boolean('scheduled'));

        if ($filePath && File::exists($filePath)) {
            return Response::download($filePath);
        }

        Flash::error('File not found.');
        return redirect()->back();
    }

    public function destroy(Request $request, $fileName)
    {
        $filePath = $this->resolveBackupPath($fileName, $request->boolean('scheduled'));

        if (! $filePath || ! File::exists($filePath)) {
            Flash::error('File not found.');
            return redirect()->back();
        }

        if (! File::delete($filePath)) {
            Flash::error('Failed to delete backup. Please try again.');
            return redirect()->back();
        }

        Flash::success('Backup deleted successfully.');

        return redirect()->back();
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt',
            'confirm_restore' => 'accepted',
        ], [
            'confirm_restore.accepted' => 'Please confirm that you want to overwrite the current database.',
        ]);

        try {
            $file = $request->file('backup_file');
            $filePath = $file->getRealPath();

            // Only genuine mysqldump / MariaDB dumps: an arbitrary SQL script must not be replayed
            // into the live database from this screen.
            if (! $this->backups->looksLikeDump($filePath)) {
                throw new RuntimeException('This file is not a database backup created by this system (it does not start with a mysqldump header).');
            }

            // Safety net: take a full backup of the CURRENT data first. If that fails nothing is restored.
            $safetyCopy = $this->backups->createDump($this->backupPath, 'pre-restore');

            $this->backups->restore($filePath);

            // Clear cache after restore
            Artisan::call('cache:clear');
            Artisan::call('view:clear');

            Flash::success('Database restored successfully from ' . $file->getClientOriginalName()
                . '. A copy of the previous data was saved as ' . basename($safetyCopy) . '.');
            return redirect()->back();
        } catch (RuntimeException $e) {
            Log::error('Database restore failed: ' . $e->getMessage());
            Flash::error('Failed to restore database: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function resolveBackupPath(string $fileName, bool $scheduled = false): ?string
    {
        $safeFileName = basename($fileName);

        if ($safeFileName !== $fileName || pathinfo($safeFileName, PATHINFO_EXTENSION) !== 'sql') {
            return null;
        }

        return ($scheduled ? $this->scheduledPath : $this->backupPath) . DIRECTORY_SEPARATOR . $safeFileName;
    }
}
