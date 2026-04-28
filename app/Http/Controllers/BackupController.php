<?php

namespace App\Http\Controllers;

use Laracasts\Flash\Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use Exception;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $backups = [];
        $files = File::files($this->backupPath);

        foreach ($files as $file) {
            $backups[] = [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'created_at' => Carbon::createFromTimestamp($file->getMTime())->toDayDateTimeString(),
                'raw_date' => $file->getMTime()
            ];
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
            $connection = config('database.connections.mysql');
            $database = $connection['database'];
            $username = $connection['username'];
            $password = $connection['password'];
            $host = $connection['host'];
            $port = $connection['port'];

            $fileName = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
            $filePath = $this->backupPath . DIRECTORY_SEPARATOR . $fileName;

            // mysqldump command
            // Note: We use --column-statistics=0, --set-gtid-purged=OFF, and --skip-lock-tables for compatibility and reliability
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --column-statistics=0 --set-gtid-purged=OFF --skip-lock-tables --result-file=%s %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($filePath),
                escapeshellarg($database)
            );

            // Execute the command
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception("Error creating backup. Exit code: " . $returnVar);
            }

            Flash::success('Backup created successfully: ' . $fileName);
            return redirect()->back();
        } catch (Exception $e) {
            Flash::error('Failed to create backup: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function download($fileName)
    {
        $filePath = $this->resolveBackupPath($fileName);

        if ($filePath && File::exists($filePath)) {
            return Response::download($filePath);
        }

        Flash::error('File not found.');
        return redirect()->back();
    }

    public function destroy($fileName)
    {
        $filePath = $this->resolveBackupPath($fileName);

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
        ]);

        try {
            $file = $request->file('backup_file');
            $filePath = $file->getRealPath();

            $connection = config('database.connections.mysql');
            $database = $connection['database'];
            $username = $connection['username'];
            $password = $connection['password'];
            $host = $connection['host'];
            $port = $connection['port'];

            // mysql command to import
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filePath)
            );

            // Execute the command
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception("Error importing backup. Exit code: " . $returnVar);
            }

            // Clear cache after restore
            Artisan::call('cache:clear');
            Artisan::call('view:clear');

            Flash::success('Database restored successfully from ' . $file->getClientOriginalName());
            return redirect()->back();
        } catch (Exception $e) {
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

    protected function resolveBackupPath(string $fileName): ?string
    {
        $safeFileName = basename($fileName);

        if ($safeFileName !== $fileName) {
            return null;
        }

        return $this->backupPath . DIRECTORY_SEPARATOR . $safeFileName;
    }
}
