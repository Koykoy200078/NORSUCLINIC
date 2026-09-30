<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * One place that knows how to dump and restore the clinic database, used by both the scheduled
 * `db:backup` command and the Backups screen. H-09.
 *
 * What it fixes compared with the two separate implementations it replaces:
 *  - flags are chosen for the server actually installed: MariaDB's mysqldump rejects the MySQL-8-only
 *    --column-statistics / --set-gtid-purged options (exit 7 and a 0-byte "backup");
 *  - dumps are consistent (--single-transaction, routines, triggers, events);
 *  - the dump is written to a temporary ".partial" file and only renamed to its final name once
 *    mysqldump succeeded AND the file ends with the "Dump completed" marker, so a failed or interrupted
 *    dump never appears in the list as a valid backup;
 *  - the database password is passed in the MYSQL_PWD environment variable, not on the command line
 *    (where any user on the machine could read it in the process list);
 *  - restore streams the file into the mysql client (no shell redirection).
 */
class DatabaseBackupService
{
    /**
     * Create a dump in $directory named "{$prefix}-YYYY-MM-DD_HH-MM-SS.sql" and return its full path.
     *
     * @throws RuntimeException with a message that is safe to show to the user
     */
    public function createDump(string $directory, string $prefix = 'backup'): string
    {
        $connection = $this->connection();
        $binary = $this->findBinary('mysqldump');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $finalPath = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $prefix . '-' . now()->format('Y-m-d_H-i-s') . '.sql';
        $partialPath = $finalPath . '.partial';

        $args = array_merge([$binary], $this->clientArguments($connection), [
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
        ], $this->flavourArguments($binary), [$connection['database']]);

        $handle = fopen($partialPath, 'wb');
        if (! $handle) {
            throw new RuntimeException('The backup file could not be created. Check that the backup folder is writable.');
        }

        $stderr = '';
        $process = new Process($args, null, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout(900);

        try {
            $process->run(function (string $type, string $buffer) use (&$stderr, $handle): void {
                if ($type === Process::ERR) {
                    $stderr .= $buffer;

                    return;
                }

                fwrite($handle, $buffer);
            });
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful() || ! $this->endsWithDumpCompleted($partialPath)) {
            @unlink($partialPath);

            throw new RuntimeException('The database dump failed: ' . $this->shorten($stderr ?: 'mysqldump did not finish.'));
        }

        if (! @rename($partialPath, $finalPath)) {
            @unlink($partialPath);

            throw new RuntimeException('The backup was created but could not be saved under its final name.');
        }

        return $finalPath;
    }

    /**
     * Load a .sql file into the clinic database (overwrites current data).
     *
     * @throws RuntimeException
     */
    public function restore(string $sqlPath): void
    {
        if (! is_file($sqlPath)) {
            throw new RuntimeException('The backup file was not found.');
        }

        $connection = $this->connection();
        $binary = $this->findBinary('mysql');

        $args = array_merge([$binary], $this->clientArguments($connection), [
            '--default-character-set=utf8mb4',
            $connection['database'],
        ]);

        $input = fopen($sqlPath, 'rb');
        if (! $input) {
            throw new RuntimeException('The backup file could not be read.');
        }

        $process = new Process($args, null, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout(1800);
        $process->setInput($input);

        try {
            $process->run();
        } finally {
            fclose($input);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('The restore failed: ' . $this->shorten($process->getErrorOutput() ?: 'the mysql client reported an error.'));
        }
    }

    /**
     * Whether a file looks like a dump created by mysqldump (first lines are the "-- MySQL dump" /
     * "-- MariaDB dump" comment header).
     */
    public function looksLikeDump(string $path): bool
    {
        $head = @file_get_contents($path, false, null, 0, 2048);

        return is_string($head) && preg_match('/^--\s+(MySQL|MariaDB) dump/mi', $head) === 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function connection(): array
    {
        $connection = config('database.connections.' . config('database.default'));

        if (! is_array($connection) || empty($connection['database']) || empty($connection['username'])) {
            throw new RuntimeException('The database connection is not configured (DB_DATABASE / DB_USERNAME).');
        }

        return $connection;
    }

    /**
     * @param  array<string, mixed>  $connection
     * @return array<int, string>
     */
    private function clientArguments(array $connection): array
    {
        return [
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? '3306'),
            '--user=' . $connection['username'],
        ];
    }

    /**
     * MySQL 8 clients need --column-statistics=0 / --set-gtid-purged=OFF to dump older servers cleanly;
     * MariaDB and MySQL 5.x do not understand them and abort.
     *
     * @return array<int, string>
     */
    private function flavourArguments(string $binary): array
    {
        $version = new Process([$binary, '--version']);
        $version->run();
        $text = $version->getOutput() . $version->getErrorOutput();

        if (stripos($text, 'mariadb') !== false) {
            return [];
        }

        if (preg_match('/Ver\s+(\d+)\./', $text, $match) === 1 && (int) $match[1] >= 8) {
            return ['--column-statistics=0', '--set-gtid-purged=OFF'];
        }

        return [];
    }

    /**
     * Locate mysqldump / mysql: explicit config first, then PATH, then the usual WAMP / XAMPP / Linux folders.
     */
    private function findBinary(string $name): string
    {
        $configured = config('database.' . ($name === 'mysqldump' ? 'mysqldump_path' : 'mysql_path'));
        if ($configured && is_file($configured)) {
            return $configured;
        }

        $locator = Process::fromShellCommandline(PHP_OS_FAMILY === 'Windows' ? "where {$name}" : "command -v {$name}");
        $locator->run();

        if ($locator->isSuccessful()) {
            $first = preg_split('/\r\n|\r|\n/', trim($locator->getOutput()))[0] ?? '';
            if ($first !== '' && is_file($first)) {
                return $first;
            }
        }

        $exe = PHP_OS_FAMILY === 'Windows' ? $name . '.exe' : $name;
        $candidates = [];
        foreach ([
            'C:\\wamp64\\bin\\mysql\\*\\bin\\',
            'C:\\wamp64\\bin\\mariadb\\*\\bin\\',
            'C:\\wamp\\bin\\mysql\\*\\bin\\',
            'C:\\wamp\\bin\\mariadb\\*\\bin\\',
            'C:\\xampp\\mysql\\bin\\',
            '/usr/bin/',
            '/usr/local/bin/',
            '/usr/local/mysql/bin/',
        ] as $pattern) {
            $candidates = array_merge($candidates, glob($pattern . $exe) ?: []);
        }

        if (! empty($candidates)) {
            rsort($candidates);

            return $candidates[0];
        }

        throw new RuntimeException(
            "{$name} was not found. Add its folder to the system PATH, or set "
            . ($name === 'mysqldump' ? 'MYSQLDUMP_PATH' : 'MYSQL_CLIENT_PATH') . ' in .env.'
        );
    }

    private function endsWithDumpCompleted(string $path): bool
    {
        $size = @filesize($path);
        if (! $size) {
            return false;
        }

        $tail = @file_get_contents($path, false, null, max(0, $size - 512));

        return is_string($tail) && str_contains($tail, 'Dump completed');
    }

    private function shorten(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return mb_strlen($text) > 300 ? mb_substr($text, 0, 300) . '...' : $text;
    }
}
