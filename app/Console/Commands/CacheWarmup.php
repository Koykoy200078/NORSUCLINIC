<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheWarmup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application cache with frequently accessed data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cache warmup...');

        // Warm up doctors list (for dashboards)
        $this->warmupCache('doctors_list', function () {
            return Doctor::with('user:id,first_name,last_name')->get()
                ->pluck('user.full_name', 'id')->toArray();
        }, 'Doctors List (Dashboard)');

        // Warm up active doctors list (for appointments)
        $this->warmupCache('active_doctors_list', function () {
            return Doctor::with('user:id,first_name,last_name,status')
                ->whereHas('user', function ($query) {
                    $query->where('status', User::ACTIVE);
                })
                ->get()
                ->pluck('user.full_name', 'id');
        }, 'Active Doctors List');

        // Warm up patients list
        $this->warmupCache('patients_list', function () {
            return Patient::with('patientUser:id,first_name,last_name')
                ->get()
                ->pluck('patientUser.full_name', 'id');
        }, 'Patients List');

        // Warm up application settings
        $this->warmupCache('app_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        }, 'Application Settings', 3600);

        // Warm up medicines list
        $this->warmupCache('medicines_list', function () {
            return Medicine::pluck('name', 'id')->toArray();
        }, 'Medicines List');

        // Warm up active medicine categories
        $this->warmupCache('active_medicine_categories', function () {
            return Category::where('is_active', '=', 1)->pluck('name', 'id');
        }, 'Active Medicine Categories');

        // Warm up HTMLPurifier instance
        $this->warmupCache('htmlpurifier_instance', function () {
            return new \HTMLPurifier(\HTMLPurifier_Config::createDefault());
        }, 'HTMLPurifier Instance', 3600);

        $this->info('✓ Cache warmup completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Warm up a specific cache key
     *
     * @param string $key
     * @param \Closure $callback
     * @param string $description
     * @param int $ttl Time to live in seconds (default: 600)
     * @return void
     */
    private function warmupCache(string $key, \Closure $callback, string $description, int $ttl = 600): void
    {
        $this->info("Warming up: {$description}...");

        try {
            Cache::forget($key);
            Cache::remember($key, $ttl, $callback);
            $this->line("  ✓ {$description} cached successfully");
        } catch (\Exception $e) {
            $this->error("  ✗ Failed to cache {$description}: " . $e->getMessage());
        }
    }
}
