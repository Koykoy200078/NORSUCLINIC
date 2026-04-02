<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceMonitor extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'performance:monitor {--slow-queries : Show slow queries}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor application performance and identify bottlenecks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== NORSUCLINIC Performance Monitor ===');
        $this->line('');

        // Check database performance
        $this->checkDatabasePerformance();

        // Check cache status
        $this->checkCacheStatus();

        // Check memory usage
        $this->checkMemoryUsage();

        if ($this->option('slow-queries')) {
            $this->showSlowQueries();
        }

        $this->line('');
        $this->info('Performance monitoring completed!');
    }

    private function checkDatabasePerformance()
    {
        $this->info('📊 Database Performance:');

        try {
            $start = microtime(true);
            $userCount = DB::table('users')->count();
            $userQueryTime = round((microtime(true) - $start) * 1000, 2);

            $start = microtime(true);
            $patientCount = DB::table('patients')->count();
            $patientQueryTime = round((microtime(true) - $start) * 1000, 2);

            $this->line("  Users table: {$userCount} records ({$userQueryTime}ms)");
            $this->line("  Patients table: {$patientCount} records ({$patientQueryTime}ms)");

            if ($userQueryTime > 100 || $patientQueryTime > 100) {
                $this->warn('  ⚠️  Some queries are slow. Consider adding indexes.');
            } else {
                $this->info('  ✅ Database queries are performing well.');
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Database connection failed: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function checkCacheStatus()
    {
        $this->info('🗄️  Cache Status:');

        try {
            $cacheDriver = config('cache.default');
            $this->line("  Cache driver: {$cacheDriver}");

            // Test cache performance
            $start = microtime(true);
            cache()->put('performance_test', 'test_value', 60);
            $value = cache()->get('performance_test');
            $cacheTime = round((microtime(true) - $start) * 1000, 2);
            cache()->forget('performance_test');

            $this->line("  Cache read/write: {$cacheTime}ms");

            if ($cacheTime > 10) {
                $this->warn('  ⚠️  Cache operations are slow. Consider using Redis.');
            } else {
                $this->info('  ✅ Cache is performing well.');
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Cache test failed: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function checkMemoryUsage()
    {
        $this->info('💾 Memory Usage:');

        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');

        $this->line('  Current: ' . $this->formatBytes($memoryUsage));
        $this->line('  Peak: ' . $this->formatBytes($memoryPeak));
        $this->line('  Limit: ' . $memoryLimit);

        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        if ($memoryLimitBytes > 0) {
            $usage = ($memoryPeak / $memoryLimitBytes) * 100;
            if ($usage > 80) {
                $this->warn('  ⚠️  High memory usage (' . round($usage, 1) . '%)');
            } else {
                $this->info('  ✅ Memory usage is normal (' . round($usage, 1) . '%)');
            }
        }

        $this->line('');
    }

    private function showSlowQueries()
    {
        $this->info('🐌 Potential Slow Query Patterns:');

        $suggestions = [
            'Consider adding index on users(type, status)',
            'Consider adding index on patients(created_at)',
            'Use eager loading for relationships',
            'Cache frequently accessed data',
        ];

        foreach ($suggestions as $suggestion) {
            $this->line('  • ' . $suggestion);
        }

        $this->line('');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function parseMemoryLimit($limit)
    {
        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            $value = $matches[1];
            $unit = strtoupper($matches[2]);

            switch ($unit) {
                case 'G':
                    return $value * 1024 * 1024 * 1024;
                case 'M':
                    return $value * 1024 * 1024;
                case 'K':
                    return $value * 1024;
                default:
                    return $value;
            }
        }

        return 0;
    }
}
