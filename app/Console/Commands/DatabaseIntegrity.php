<?php

namespace App\Console\Commands;

use App\Services\DatabaseIntegrityChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Health check of the clinic database. Run it after an upgrade and whenever the numbers on screen look wrong. It only
 * reads (--compare-fresh also builds, and then drops, a throwaway schema of its own), prints one line per check, and
 * exits with 1 when any check found an error, so a script can stop on it.
 *
 *   php artisan db:integrity
 *   php artisan db:integrity --compare-fresh
 */
class DatabaseIntegrity extends Command
{
    protected $signature = 'db:integrity
        {--compare-fresh : Also build a fresh install in a throwaway schema and compare its structure with this database (about a minute; needs permission to create a schema)}';

    protected $description = 'Check that the clinic database is what a correct installation looks like (exit code 1 on an error)';

    /** Detail lines printed per check before "... and N more". */
    private const MAX_LINES = 25;

    public function handle(DatabaseIntegrityChecker $checker): int
    {
        $this->line('<options=bold>Database integrity - '.DB::connection()->getDatabaseName().'</>');

        $errors = 0;
        $warnings = 0;

        foreach ($checker->run() as $check) {
            $this->report($check['severity'], $check['title'], $check['errors'], $check['warnings']);
            $errors += count($check['errors']);
            $warnings += count($check['warnings']);
        }

        if ($this->option('compare-fresh')) {
            $this->line('Building a fresh install in a throwaway schema to compare with ...');
            $differences = $checker->compareWithFreshInstall();
            $this->report($differences === [] ? 'ok' : 'error', 'Structure is identical to a fresh install', $differences, []);
            $errors += count($differences);
        }

        $this->newLine();
        $this->line("{$errors} error(s), {$warnings} warning(s).".($errors > 0 ? ' Errors must be fixed; nothing was changed.' : ' Warnings do not stop the clinic working.'));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     */
    private function report(string $severity, string $title, array $errors, array $warnings): void
    {
        $label = match ($severity) {
            'error' => '<fg=red;options=bold>ERROR</>',
            'warning' => '<fg=yellow;options=bold>WARN </>',
            default => '<fg=green>OK   </>',
        };
        $this->line("{$label} {$title}");

        $lines = [...array_map(fn (string $line): string => $line, $errors), ...array_map(fn (string $line): string => "(warning) {$line}", $warnings)];
        foreach (array_slice($lines, 0, self::MAX_LINES) as $line) {
            $this->line("        - {$line}");
        }
        if (count($lines) > self::MAX_LINES) {
            $this->line('        ... and '.(count($lines) - self::MAX_LINES).' more');
        }
    }
}
