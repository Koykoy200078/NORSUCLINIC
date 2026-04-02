<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Doctor;
use App\Services\SettingsService;

class WarmUpCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:warmup';

    /**
     * The console command description.
     */
    protected $description = 'Warm up application cache for better performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Warming up application cache...');

        // Warm up settings cache
        $this->info('Caching settings...');
        SettingsService::refresh();

        // Warm up doctors list cache  
        $this->info('Caching doctors list...');
        Cache::put('doctors_list', Doctor::with('user:id,first_name,last_name')->get()
            ->pluck('user.full_name', 'id')->toArray(), 600);

        $this->info('Cache warming completed successfully!');
    }
}
