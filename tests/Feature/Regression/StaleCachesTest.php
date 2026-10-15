<?php

namespace Tests\Feature\Regression;

use App\Models\Category;
use App\Models\Medicine;
use App\Repositories\MedicineBillRepository;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 6 (stale caches). The pick-lists of the forms and the expiry badge are cached for 10 to 60
 * minutes. What changes them must drop them, otherwise a medicine, a doctor or a category added a minute ago is missing
 * from the form and a disposed batch still counts in the menu badge.
 */
class StaleCachesTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function warm(string ...$keys): void
    {
        foreach ($keys as $key) {
            Cache::put($key, 'stale', 600);
        }
    }

    public function test_a_new_medicine_is_in_the_dispense_form_list_at_once(): void
    {
        $this->makeMedicine('Paracetamol');
        $repository = app(MedicineBillRepository::class);
        $this->assertContains('Paracetamol', $repository->getMedicines()['medicines'], 'the list is cached now');

        $this->makeMedicine('Amoxicillin');

        $this->assertContains('Amoxicillin', $repository->getMedicines()['medicines']);
    }

    public function test_a_new_or_switched_off_category_is_in_the_list_at_once(): void
    {
        $category = Category::create(['name' => 'Antibiotics', 'is_active' => 1]);
        $repository = app(MedicineBillRepository::class);
        $this->assertContains('Antibiotics', $repository->getMedicinesCategoriesData()->all(), 'the list is cached now');

        $category->update(['is_active' => 0]);

        $this->assertNotContains('Antibiotics', $repository->getMedicinesCategoriesData()->all());
    }

    public function test_a_new_doctor_clears_the_doctor_lists(): void
    {
        $this->warm('doctors_list', 'active_doctors_prescription', 'active_doctors_medicine_bill');

        $this->makeDoctor();

        foreach (['doctors_list', 'active_doctors_prescription', 'active_doctors_medicine_bill'] as $key) {
            $this->assertFalse(Cache::has($key), "{$key} must be dropped when a doctor is added");
        }

        // A change of the doctor record alone (specialization, hours) drops them too, not only a change of the user.
        $doctor = \App\Models\Doctor::firstOrFail();
        $this->warm('doctors_list', 'active_doctors_prescription', 'active_doctors_medicine_bill');
        $doctor->update(['experience' => 9]);
        foreach (['doctors_list', 'active_doctors_prescription', 'active_doctors_medicine_bill'] as $key) {
            $this->assertFalse(Cache::has($key), "{$key} must be dropped when the doctor record changes");
        }
    }

    public function test_the_expiring_stock_badge_follows_stock_in_and_disposal_at_once(): void
    {
        $medicine = $this->makeMedicine('Soonmed');
        $this->assertSame(0, getExpiringMedicinesCount(), 'the count is cached now');

        $batch = $this->stockIn($medicine, 10, now()->addDays(10)->toDateString());
        $this->assertSame(1, getExpiringMedicinesCount(), 'a batch that expires within a month is counted at once');

        $batch->update(['quantity' => 0]);
        $this->assertSame(0, getExpiringMedicinesCount(), 'a batch with no stock left is not counted');
    }

    public function test_saving_the_settings_also_drops_the_copy_the_pdf_headers_use(): void
    {
        $this->warm('prescription_settings');

        SettingsService::clearCache();

        $this->assertFalse(Cache::has('prescription_settings'), 'the prescription / dispense PDFs would show the old clinic name for five minutes');
    }
}
