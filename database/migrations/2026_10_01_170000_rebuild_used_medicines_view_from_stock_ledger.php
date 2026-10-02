<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * R3-M3 (2026-10-01 re-audit). The dispensing "Stock-out" tab read `used_medicines_view`, which listed every
 * line of `sale_medicines` - including the placeholder lines of a prescription that had not been dispensed -
 * looked the patient up with a model_type string that is never written (so the patient was always "N/A"), and
 * showed the earliest unexpired stock-in date instead of the batch that was handed out.
 *
 * The view is rebuilt from the stock ledger (`medicine_transactions`), which records exactly what left (or came
 * back to) which batch, for which consultation / prescription / dispense record. Only real movements appear;
 * units returned when a consultation or dispense record is edited or deleted show as negative rows, so the tab
 * always adds up to what is really out. The column names are unchanged.
 *
 * Names include the middle name, like the rest of the system (User::full_name), so a full name can be searched.
 *
 * The SQL is a nowdoc on purpose: the class names must reach MySQL with doubled backslashes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS used_medicines_view');

        DB::statement(<<<'SQL'
            CREATE VIEW used_medicines_view AS
            SELECT
                CONCAT('T', t.id) AS id,
                b.medicine_id AS medicine_id,
                m.name AS medicine_name,
                COALESCE(NULLIF(b.dosage, ''), NULLIF(m.dosage, ''), 'N/A') AS dosage,
                CASE WHEN t.transaction_type = 'dispense' THEN t.quantity ELSE -t.quantity END AS quantity,
                CASE
                    WHEN t.reference_type = 'App\\Models\\DocumentIssuance'
                        THEN IF(t.transaction_type = 'dispense', 'Consultation', 'Consultation (returned)')
                    WHEN t.reference_type = 'App\\Models\\Prescription'
                        THEN 'Prescription'
                    WHEN t.reference_type = 'App\\Models\\DispenseRecord'
                        THEN IF(t.transaction_type = 'dispense', 'Dispense Record', 'Dispense Record (returned)')
                    ELSE 'Other'
                END AS source,
                COALESCE(
                    di.name,
                    CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name),
                    IF(t.reference_type = 'App\\Models\\DocumentIssuance', 'Deleted consultation', 'N/A')
                ) AS patient_name,
                COALESCE(CONCAT_WS(' ', nurse.first_name, NULLIF(nurse.middle_name, ''), nurse.last_name), 'N/A') AS nurse_incharged,
                'N/A' AS used_for,
                b.expiration_date AS expiry_date,
                t.created_at AS created_at
            FROM medicine_transactions t
            INNER JOIN medicine_batches b ON b.id = t.batch_id
            INNER JOIN medicines m ON m.id = b.medicine_id
            LEFT JOIN document_issuances di
                ON t.reference_type = 'App\\Models\\DocumentIssuance' AND di.id = t.reference_id
            LEFT JOIN users nurse ON nurse.id = di.nursing_incharged_id
            LEFT JOIN prescriptions rx
                ON t.reference_type = 'App\\Models\\Prescription' AND rx.id = t.reference_id
            LEFT JOIN medicine_bills mb
                ON t.reference_type = 'App\\Models\\DispenseRecord' AND mb.id = t.reference_id
            LEFT JOIN patients p ON p.id = COALESCE(rx.patient_id, mb.patient_id)
            LEFT JOIN users pu ON pu.id = p.user_id
            WHERE t.transaction_type = 'dispense'
               OR (
                    t.transaction_type = 'adjustment'
                    AND t.reference_type IN ('App\\Models\\DocumentIssuance', 'App\\Models\\DispenseRecord')
               )
        SQL);
    }

    public function down(): void
    {
        // Back to the previous definition (dosage-aware view built from consultation_medicines + sale_medicines).
        (include __DIR__ . '/2026_05_05_230000_add_dosage_to_used_medicines_view.php')->up();
    }
};
