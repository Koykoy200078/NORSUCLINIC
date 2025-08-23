<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLogo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:logo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the getAppLogo function';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing getAppLogo function...');

        try {
            $logo = getAppLogo();
            $this->info("✓ getAppLogo() result: {$logo}");

            // Test if file exists
            $logoPath = public_path($logo);
            if (file_exists($logoPath)) {
                $this->info("✓ Logo file exists at: {$logoPath}");
            } else {
                $this->warn("✗ Logo file NOT found at: {$logoPath}");
            }

            // Test getSettingValue for logo
            $logoSetting = getSettingValue('logo');
            $this->info("✓ getSettingValue('logo'): " . ($logoSetting ?: 'empty'));

            // Test if the setting exists in database
            $setting = \App\Models\Setting::where('key', 'logo')->first();
            if ($setting) {
                $this->info("✓ Logo setting found in database: {$setting->value}");
            } else {
                $this->warn("✗ No logo setting found in database");
            }
        } catch (\Exception $e) {
            $this->error("✗ Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}:{$e->getLine()}");
        }

        return 0;
    }
}
