<?php

namespace Tests\Feature\Regression;

use App\Models\InventoryCount;
use App\Models\InventoryCountLine;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Quarterly count sheets (Phase 11 D, 2026-10-29): `inventory_counts` + `inventory_count_lines`. The database itself keeps
 * a sheet from holding one medicine twice, a line from outliving its sheet or pointing at nothing, and a medicine that was
 * counted from being deleted (the count is a record of what was found on the shelf).
 */
class InventoryCountStorageTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function sheet(array $over = []): InventoryCount
    {
        return InventoryCount::create($over + ['year' => 2026, 'quarter' => 2, 'scope' => 'all', 'counted_on' => '2026-06-30', 'status' => 'draft', 'created_by' => $this->makeAdmin()->id]);
    }

    private function line(InventoryCount $count, int $medicineId, array $over = []): InventoryCountLine
    {
        return InventoryCountLine::create($over + ['inventory_count_id' => $count->id, 'medicine_id' => $medicineId, 'medicine_name' => 'Biogesic',
            'strength' => '500mg', 'unit' => 'tablet', 'system_quantity' => 120]);
    }

    public function test_the_tables_and_columns_exist_and_a_new_sheet_is_a_draft_that_counts_nothing_yet(): void
    {
        foreach (['year', 'quarter', 'scope', 'counted_on', 'status', 'notes', 'created_by', 'finalized_by', 'finalized_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('inventory_counts', $column), "inventory_counts.$column");
        }
        foreach (['inventory_count_id', 'medicine_id', 'medicine_name', 'strength', 'unit', 'system_quantity', 'counted_quantity', 'difference', 'reason', 'note', 'adjusted_at', 'adjusted_by', 'adjusted_audit_id'] as $column) {
            $this->assertTrue(Schema::hasColumn('inventory_count_lines', $column), "inventory_count_lines.$column");
        }

        $line = $this->line($this->sheet(['status' => 'draft']), $this->makeMedicine('Biogesic')->id);
        $fresh = $line->fresh();
        $this->assertNull($fresh->counted_quantity);
        $this->assertNull($fresh->difference);
        $this->assertNull($fresh->adjusted_at);
        $this->assertSame('draft', $fresh->sheet->status);
    }

    public function test_a_sheet_holds_one_line_per_medicine(): void
    {
        $sheet = $this->sheet();
        $medicine = $this->makeMedicine('Biogesic');
        $this->line($sheet, $medicine->id);

        $this->expectException(QueryException::class);
        $this->line($sheet, $medicine->id);
    }

    public function test_a_line_needs_a_real_sheet_and_a_real_medicine(): void
    {
        $sheet = $this->sheet();

        try {
            $this->line($sheet, 999999);
            $this->fail('a line of a medicine that does not exist was accepted');
        } catch (QueryException) {
            $this->assertSame(0, InventoryCountLine::count());
        }

        $this->expectException(QueryException::class);
        $this->line(InventoryCount::make(['id' => 999999]), $this->makeMedicine('Biogesic')->id);
    }

    public function test_deleting_a_sheet_takes_its_lines_but_a_counted_medicine_cannot_be_deleted(): void
    {
        $medicine = $this->makeMedicine('Biogesic');
        $sheet = $this->sheet();
        $this->line($sheet, $medicine->id);

        try {
            DB::table('medicines')->where('id', $medicine->id)->delete();
            $this->fail('a medicine that was counted could be deleted');
        } catch (QueryException) {
            $this->assertSame(1, InventoryCountLine::count());
        }

        $sheet->delete();
        $this->assertSame(0, InventoryCountLine::count());
        $this->assertNotNull(DB::table('medicines')->where('id', $medicine->id)->first());
    }

    public function test_the_people_who_made_and_finalized_a_sheet_are_kept_by_id_and_cleared_when_the_account_is_removed(): void
    {
        $maker = $this->makeAdmin(['email' => 'maker@example.test']);
        $sheet = $this->sheet(['created_by' => $maker->id, 'finalized_by' => $maker->id]);
        $this->assertSame($maker->id, $sheet->creator->id);

        DB::table('users')->where('id', $maker->id)->delete();

        $fresh = $sheet->fresh();
        $this->assertNull($fresh->created_by);
        $this->assertNull($fresh->finalized_by);
        $this->assertInstanceOf(User::class, $this->makeAdmin(['email' => 'other@example.test']));
    }

    public function test_a_reason_and_a_difference_are_stored_as_given_and_the_label_reads_like_the_report_period(): void
    {
        $quarter = $this->sheet(['quarter' => 3, 'year' => 2026]);
        $all = $this->sheet(['quarter' => null, 'counted_on' => '2026-10-29']);

        $this->assertSame('2026 Q3', $quarter->label());
        $this->assertSame('2026, all medicines counted 2026-10-29', $all->label());

        $line = $this->line($quarter, $this->makeMedicine('Biogesic')->id, ['counted_quantity' => 118, 'difference' => -2, 'reason' => 'damaged', 'note' => 'two blister packs wet']);
        $this->assertSame(-2, $line->fresh()->difference);
        $this->assertSame('Damaged', $line->fresh()->reasonLabel());
    }
}
