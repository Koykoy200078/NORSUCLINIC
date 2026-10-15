<?php

use App\Services\SettingsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plan Phase 4.1: the clinic is called "Medical University Clinic" (the clinic's name only: the university's name, its
 * address, e-mail address, logo and favicon stay).
 *
 *  - the `clinic_name` setting, but only while it still holds the old default "Norsu Clinic" (any letter case, extra
 *    spaces ignored); a name somebody typed in Settings is never replaced;
 *  - the landing page's default slider sentence, only while it is still the seeded text. The new sentence is exactly
 *    55 characters because the slider form refuses a longer one.
 *
 * Running it twice changes nothing. The settings cache is cleared, otherwise the old name would stay on every page
 * for up to an hour.
 */
return new class extends Migration
{
    private const OLD_SLIDER = 'Prioritizing your wellness at NORSU Clinic.';

    private const NEW_SLIDER = 'Prioritizing your wellness at Medical University Clinic';

    private const NAME = 'Medical University Clinic';

    private const OLD_NAME = 'Norsu Clinic';

    public function up(): void
    {
        DB::transaction(function () {
            if (Schema::hasTable('settings')) {
                DB::table('settings')->where('key', 'clinic_name')
                    ->whereRaw('LOWER(TRIM(value)) = ?', [strtolower(self::OLD_NAME)])
                    ->update(['value' => self::NAME]);
            }

            if (Schema::hasTable('sliders')) {
                DB::table('sliders')->where('short_description', self::OLD_SLIDER)->update(['short_description' => self::NEW_SLIDER]);
            }
        });

        SettingsService::clearCache();
    }

    public function down(): void
    {
        DB::transaction(function () {
            if (Schema::hasTable('settings')) {
                DB::table('settings')->where('key', 'clinic_name')->where('value', self::NAME)->update(['value' => self::OLD_NAME]);
            }

            if (Schema::hasTable('sliders')) {
                DB::table('sliders')->where('short_description', self::NEW_SLIDER)->update(['short_description' => self::OLD_SLIDER]);
            }
        });

        SettingsService::clearCache();
    }
};
