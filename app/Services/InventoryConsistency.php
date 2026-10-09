<?php

namespace App\Services;

use App\Models\DocumentIssuance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only questions about whether the medicine stock agrees with itself: the total on each medicine, the quantity of
 * each batch, the stock ledger (medicine_transactions) and the medicines given in consultations. Shared by
 * `php artisan inventory:reconcile` and `php artisan db:integrity`. It never changes data, and it reads through the
 * write connection so a check made right after an upgrade sees the upgrade.
 */
final class InventoryConsistency
{
    /**
     * Medicines whose recorded total differs from the sum of their batches.
     *
     * @return list<array{id: int, name: string, recorded: int, batches: int}>
     */
    public function totalMismatches(): array
    {
        $rows = DB::select(
            'SELECT m.id, m.name, m.quantity AS recorded, COALESCE(SUM(b.quantity), 0) AS batches
             FROM medicines m LEFT JOIN medicine_batches b ON b.medicine_id = m.id
             GROUP BY m.id, m.name, m.quantity
             HAVING recorded <> batches
             ORDER BY m.id',
            [],
            false
        );

        return array_map(fn ($row): array => [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'recorded' => (int) $row->recorded,
            'batches' => (int) $row->batches,
        ], $rows);
    }

    /**
     * Batches whose quantity differs from the balance after their last ledger entry.
     *
     * @return list<array{id: int, medicine: string, batch_number: ?string, quantity: int, ledger: int}>
     */
    public function ledgerMismatches(): array
    {
        $rows = DB::select(
            'SELECT b.id, b.medicine_id, m.name AS medicine, b.batch_number, b.quantity, t.balance_after AS ledger
             FROM medicine_batches b
             JOIN medicine_transactions t ON t.id = (SELECT MAX(t2.id) FROM medicine_transactions t2 WHERE t2.batch_id = b.id)
             LEFT JOIN medicines m ON m.id = b.medicine_id
             WHERE t.balance_after <> b.quantity
             ORDER BY b.id',
            [],
            false
        );

        return array_map(fn ($row): array => [
            'id' => (int) $row->id,
            'medicine' => $row->medicine ?? '#'.$row->medicine_id,
            'batch_number' => $row->batch_number,
            'quantity' => (int) $row->quantity,
            'ledger' => (int) $row->ledger,
        ], $rows);
    }

    /**
     * Consultation medicine lines against the ledger: for every (consultation, medicine) the units on the lines must
     * equal the units the ledger deducted net of returns (dispenses minus adjustments that reference the consultation).
     * A deleted consultation must have returned everything, so its expected units are 0.
     *
     * @return list<array{consultation: int, medicine_id: int, medicine: string, lines: int, ledger: int}>
     */
    public function consultationMismatches(): array
    {
        $deleted = Schema::hasColumn('document_issuances', 'deleted_at') ? 'd.deleted_at IS NOT NULL' : '0';

        $expected = [];
        foreach (DB::select(
            "SELECT d.id AS consultation, cm.medicine_id, SUM(cm.quantity) AS units, MAX({$deleted}) AS gone
             FROM consultation_medicines cm JOIN document_issuances d ON d.id = cm.request_document_id
             GROUP BY d.id, cm.medicine_id",
            [],
            false
        ) as $row) {
            $expected[$row->consultation.':'.$row->medicine_id] = (int) $row->gone === 1 ? 0 : (int) $row->units;
        }

        $actual = [];
        foreach (DB::select(
            "SELECT t.reference_id AS consultation, b.medicine_id,
                    SUM(CASE t.transaction_type WHEN 'dispense' THEN t.quantity WHEN 'adjustment' THEN -t.quantity ELSE 0 END) AS net
             FROM medicine_transactions t JOIN medicine_batches b ON b.id = t.batch_id
             WHERE t.reference_type = ?
             GROUP BY t.reference_id, b.medicine_id",
            [DocumentIssuance::class],
            false
        ) as $row) {
            $actual[$row->consultation.':'.$row->medicine_id] = (int) $row->net;
        }

        $names = DB::table('medicines')->pluck('name', 'id');
        $keys = array_unique([...array_keys($expected), ...array_keys($actual)]);
        sort($keys, SORT_NATURAL);

        $mismatches = [];
        foreach ($keys as $key) {
            if (($expected[$key] ?? 0) === ($actual[$key] ?? 0)) {
                continue;
            }

            [$consultation, $medicineId] = array_map('intval', explode(':', $key));
            $mismatches[] = [
                'consultation' => $consultation,
                'medicine_id' => $medicineId,
                'medicine' => (string) ($names[$medicineId] ?? '#'.$medicineId),
                'lines' => $expected[$key] ?? 0,
                'ledger' => $actual[$key] ?? 0,
            ];
        }

        return $mismatches;
    }

    /**
     * Medicines with units on the shelf in batches that have no ledger row at all (stock that was entered before the
     * ledger existed). The yearly inventory report needs a ledger row to know when such stock arrived.
     *
     * @return list<array{id: int, name: string, units: int}>
     */
    public function stockWithoutLedger(): array
    {
        $rows = DB::select(
            'SELECT m.id, m.name, SUM(b.quantity) AS units
             FROM medicine_batches b JOIN medicines m ON m.id = b.medicine_id
             WHERE b.quantity > 0 AND NOT EXISTS (SELECT 1 FROM medicine_transactions t WHERE t.batch_id = b.id)
             GROUP BY m.id, m.name
             ORDER BY m.id',
            [],
            false
        );

        return array_map(fn ($row): array => ['id' => (int) $row->id, 'name' => (string) $row->name, 'units' => (int) $row->units], $rows);
    }
}
