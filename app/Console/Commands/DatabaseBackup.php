<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
        {--keep-all-days=2 : Keep every backup made in the last N days}
        {--keep-daily-days=30 : After that keep only the newest backup of each day, for N days in total}
        {--force : Save the backup even when nothing changed since the previous one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a consistent MySQL/MariaDB backup in storage/app/backups/scheduled (skipped when nothing changed)';

    public function handle(DatabaseBackupService $backups): int
    {
        $directory = storage_path('app/backups/scheduled');

        try {
            $path = $backups->createDump($directory, 'norsuclinic_backup');
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        // "Only if changes detected": compare this dump with the previous scheduled one, ignoring the
        // "-- Dump completed on ..." style comment lines that always differ. An unchanged database
        // would otherwise pile up identical copies (24 a day).
        if (! $this->option('force')) {
            $previous = $this->previousBackup($directory, $path);

            if ($previous !== null && $this->fingerprint($previous) === $this->fingerprint($path)) {
                File::delete($path);
                $this->info('No changes since ' . basename($previous) . '; nothing saved.');

                return self::SUCCESS;
            }
        }

        $this->info('Backup created: ' . basename($path) . ' (' . round(filesize($path) / 1024, 1) . ' KB)');

        $this->applyRetention($directory, max(1, (int) $this->option('keep-all-days')), max(1, (int) $this->option('keep-daily-days')));

        return self::SUCCESS;
    }

    private function previousBackup(string $directory, string $current): ?string
    {
        $files = collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'sql' && $file->getPathname() !== $current)
            ->sortByDesc(fn ($file) => $file->getMTime());

        return $files->first()?->getPathname();
    }

    private function fingerprint(string $path): string
    {
        $context = hash_init('sha1');
        $handle = fopen($path, 'rb');

        while (($line = fgets($handle)) !== false) {
            if (str_starts_with($line, '--')) {
                continue;
            }

            hash_update($context, $line);
        }

        fclose($handle);

        return hash_final($context);
    }

    /**
     * Keep every backup of the last $keepAllDays days, then only the newest backup of each day up to
     * $keepDailyDays days old; delete the rest. (It used to keep everything for 30 days: 720 full dumps.)
     */
    private function applyRetention(string $directory, int $keepAllDays, int $keepDailyDays): void
    {
        $allCutoff = now()->subDays($keepAllDays)->getTimestamp();
        $dailyCutoff = now()->subDays(max($keepDailyDays, $keepAllDays))->getTimestamp();

        $files = collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->sortByDesc(fn ($file) => $file->getMTime());

        $seenDays = [];
        $removed = 0;

        foreach ($files as $file) {
            $mtime = $file->getMTime();

            if ($mtime >= $allCutoff) {
                continue;
            }

            $day = date('Y-m-d', $mtime);

            if ($mtime < $dailyCutoff || isset($seenDays[$day])) {
                File::delete($file->getPathname());
                $removed++;

                continue;
            }

            $seenDays[$day] = true;
        }

        if ($removed > 0) {
            $this->info("Removed {$removed} old backup file(s).");
        }
    }
}
