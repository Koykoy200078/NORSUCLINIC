<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Plan Phase 2. The Dispense History tab listed only `medicine_bills` (dispense records and dispensed
 * prescriptions), so the medicines a doctor or nurse recorded inside a consultation - which leave the stock just
 * the same - never appeared there, only in the Stock-out tab.
 *
 * `dispense_history_view` puts the three sources side by side, one row each:
 *   - Dispense Record : a manual dispense record (`medicine_bills`, not tied to a prescription);
 *   - Prescription    : a prescription that has been dispensed (a pending / cancelled one moved no stock);
 *   - Consultation    : a live consultation that has medicine lines (`consultation_medicines`, summed per visit).
 *     A deleted consultation gave its stock back, so it is not listed; restoring it lists it again.
 *
 * The view carries flat names (patient, doctor / who recorded it) so the table can search and sort on them with
 * plain columns. `id` is prefixed ('B' bill, 'C' consultation) because the two sources number their rows
 * independently. `doctor_id` is set only when the person is a doctor (a nurse who recorded a consultation has
 * none). The "Prescription" and "dispensed" literals mirror Prescription::DISPENSE_STATUS_DISPENSED.
 *
 * The SQL is a nowdoc on purpose: the class names must reach MySQL with doubled backslashes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS dispense_history_view');

        DB::statement(<<<'SQL'
            CREATE VIEW dispense_history_view AS
            SELECT
                CONCAT('B', mb.id) AS id,
                IF(mb.model_type = 'App\\Models\\Prescription', 'Prescription', 'Dispense Record') AS source,
                mb.id AS record_id,
                mb.history_number AS history_number,
                mb.bill_date AS dispensed_at,
                mb.patient_id AS patient_id,
                NULLIF(CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), '') AS patient_name,
                pu.email AS patient_email,
                mb.doctor_id AS doctor_id,
                NULLIF(CONCAT_WS(' ', du.first_name, NULLIF(du.middle_name, ''), du.last_name), '') AS given_by,
                du.email AS given_by_email,
                CAST(COALESCE(bill_lines.quantity, 0) AS SIGNED) AS quantity,
                NULL AS used_for
            FROM medicine_bills mb
            LEFT JOIN patients p ON p.id = mb.patient_id
            LEFT JOIN users pu ON pu.id = p.user_id
            LEFT JOIN doctors dr ON dr.id = mb.doctor_id
            LEFT JOIN users du ON du.id = dr.user_id
            LEFT JOIN (
                SELECT medicine_bill_id, SUM(sale_quantity) AS quantity
                FROM sale_medicines
                GROUP BY medicine_bill_id
            ) bill_lines ON bill_lines.medicine_bill_id = mb.id
            WHERE mb.model_type IN ('App\\Models\\DispenseRecord', 'App\\Models\\MedicineBill')
               OR (
                    mb.model_type = 'App\\Models\\Prescription'
                    AND EXISTS (
                        SELECT 1 FROM prescriptions rx
                        WHERE rx.id = mb.model_id AND rx.status = 'dispensed'
                    )
               )

            UNION ALL

            SELECT
                CONCAT('C', d.id) AS id,
                'Consultation' AS source,
                d.id AS record_id,
                CONCAT('CONS-', d.id) AS history_number,
                d.created_at AS dispensed_at,
                p.id AS patient_id,
                COALESCE(NULLIF(CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), ''), d.name) AS patient_name,
                pu.email AS patient_email,
                dr.id AS doctor_id,
                NULLIF(CONCAT_WS(' ', cu.first_name, NULLIF(cu.middle_name, ''), cu.last_name), '') AS given_by,
                cu.email AS given_by_email,
                CAST(cm.quantity AS SIGNED) AS quantity,
                cm.used_for AS used_for
            FROM document_issuances d
            INNER JOIN (
                SELECT request_document_id,
                       SUM(quantity) AS quantity,
                       GROUP_CONCAT(DISTINCT used_for ORDER BY used_for SEPARATOR ',') AS used_for
                FROM consultation_medicines
                GROUP BY request_document_id
            ) cm ON cm.request_document_id = d.id
            LEFT JOIN users pu ON pu.id = d.user_id
            LEFT JOIN patients p ON p.user_id = d.user_id
            LEFT JOIN users cu ON cu.id = d.document_creator_id
            LEFT JOIN doctors dr ON dr.user_id = d.document_creator_id
            WHERE d.deleted_at IS NULL
              AND d.document_type = 'consultation_form'
              AND cm.quantity > 0
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS dispense_history_view');
    }
};
