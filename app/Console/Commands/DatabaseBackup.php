<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--prune-days=30 : Delete backup files older than this number of days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a MySQL backup file in storage/app/backups/scheduled';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = config('database.connections.mysql');

        if (! is_array($connection)) {
            $this->error('MySQL connection is not configured.');

            return self::FAILURE;
        }

        $database = $connection['database'] ?? null;
        $username = $connection['username'] ?? null;
        $password = (string) ($connection['password'] ?? '');
        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '3306');

        if (empty($database) || empty($username)) {
            $this->error('DB_DATABASE and DB_USERNAME must be configured.');

            return self::FAILURE;
        }

        $mysqldump = $this->resolveMySqlDumpBinary();
        if (! $mysqldump) {
            $this->error('mysqldump was not found in PATH or expected WAMP locations.');

            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups/scheduled');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFile = $backupDir . DIRECTORY_SEPARATOR . "norsuclinic_backup_{$timestamp}.sql";

        $args = [
            $mysqldump,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            $database,
        ];

        if ($password !== '') {
            $args[] = "--password={$password}";
        }

        $errorOutput = '';
        $dumpHandle = fopen($sqlFile, 'wb');

        if (! $dumpHandle) {
            $this->error("Could not create backup file at {$sqlFile}.");

            return self::FAILURE;
        }

        $process = new Process($args);
        $process->setTimeout(600);

        $process->run(function (string $type, string $buffer) use (&$errorOutput, $dumpHandle): void {
            if ($type === Process::ERR) {
                $errorOutput .= $buffer;

                return;
            }

            fwrite($dumpHandle, $buffer);
        });

        fclose($dumpHandle);

        if (! $process->isSuccessful()) {
            File::delete($sqlFile);
            $message = trim($errorOutput);
            $this->error($message !== '' ? $message : 'mysqldump failed.');

            return self::FAILURE;
        }

        $sizeKb = round(filesize($sqlFile) / 1024, 1);
        $this->info('Backup created: ' . basename($sqlFile) . " ({$sizeKb} KB)");

        $this->pruneOldBackups($backupDir, (int) $this->option('prune-days'));

        return self::SUCCESS;
    }

    private function resolveMySqlDumpBinary(): ?string
    {
        $command = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump' : 'which mysqldump';
        $locator = Process::fromShellCommandline($command);
        $locator->run();

        if ($locator->isSuccessful()) {
            $lines = preg_split('/\r\n|\r|\n/', trim($locator->getOutput()));
            if (! empty($lines[0])) {
                return trim($lines[0]);
            }
        }

        $wampCandidates = glob('C:\\wamp64\\bin\\mysql\\*\\bin\\mysqldump.exe');
        if (is_array($wampCandidates) && count($wampCandidates) > 0) {
            rsort($wampCandidates);

            return $wampCandidates[0];
        }

        return null;
    }

    private function pruneOldBackups(string $backupDir, int $days): void
    {
        $days = max(1, $days);
        $cutoffTimestamp = now()->subDays($days)->getTimestamp();
        $removed = 0;

        foreach (File::files($backupDir) as $file) {
            if ($file->getMTime() < $cutoffTimestamp) {
                File::delete($file->getPathname());
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->info("Pruned {$removed} backup file(s) older than {$days} day(s).");
        }
    }
}
