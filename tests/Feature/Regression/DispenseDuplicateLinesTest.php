<?php

namespace Tests\Feature\Regression;

use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\MedicineBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * A dispense record has one line per medicine, strength and expiry date: two lines naming the same three are refused when
 * the record is saved and when it is edited, and nothing leaves the stock. (Pinned in the re-audit of 2026-11-13, when the
 * check written out twice became DispenseRecordLines::hasDuplicateLines.)
 */
class DispenseDuplicateLinesTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_two_lines_for_the_same_medicine_strength_and_expiry_are_refused_on_save_and_on_edit(): void
    {
        $patient = $this->makePatient();
        $category = Category::create(['name' => 'Zzdupcat', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Dupmed', ['category_id' => $category->id]);
        $this->stockIn($medicine, 10, now()->addYear()->toDateString());
        $this->stockIn($medicine, 10, now()->addYear()->toDateString(), '250mg');
        $this->actingAs($this->makeAdmin());
        $payload = fn (array $lines) => [
            'patient_id' => $patient->id, 'bill_date' => now()->toDateString(), 'note' => 'Note',
            'category_id' => array_fill(0, count($lines), $category->id),
            'medicine' => array_fill(0, count($lines), $medicine->id),
            'dosage' => array_column($lines, 0), 'quantity' => array_column($lines, 1),
        ];

        $this->post(route('dispense-records.store'), $payload([['500mg', 1], ['500mg', 2]]))
            ->assertRedirect(route('dispense-records.create'));
        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson(route('dispense-records.store'), $payload([['500mg', 1], ['500mg', 2]]))->assertStatus(422);
        $this->assertSame(0, DispenseRecord::count());
        $this->assertSame(20, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'));

        // the same medicine in another strength is a line of its own
        $this->post(route('dispense-records.store'), $payload([['500mg', 1], ['250mg', 2]]))->assertSessionHasNoErrors();
        $record = DispenseRecord::firstOrFail();
        $this->assertSame(17, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'));

        $this->putJson(route('dispense-records.update', $record), $payload([['250mg', 1], ['250mg', 1]]))->assertStatus(422);
        $this->assertSame(17, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'), 'a refused edit moves no stock');
    }
}
