<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Meds Only (2026-10-29): the Dispense History view (`dispense_history_view`) and the Stock-out view (`used_medicines_view`)
 * learn the new dispense records:
 *
 *  - source "Meds Only" (and "Meds Only (returned)" in Stock Out) for `medicine_bills.meds_only = 1`;
 *  - the person is the registered patient when there is one, otherwise the typed `patient_name`;
 *  - Dispense History's "given by" falls back to `recorded_by` (a manual dispense record has no author, a Meds Only one has);
 *    Stock Out's nurse column does the same for Meds Only rows.
 *
 * Every other row reads exactly as before: the changes only apply where `meds_only = 1` or where the patient name used to be
 * empty. The definitions are those of 2026_10_09_140000 and 2026_10_01_170000 plus these lines. The SQL is a nowdoc on purpose:
 * the class names must reach MySQL with doubled backslashes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('medicine_bills', 'meds_only')) {
            return;
        }

        $this->dispenseHistory(true);
        $this->usedMedicines(true);
    }

    public function down(): void
    {
        $this->dispenseHistory(false);
        $this->usedMedicines(false);
    }

    private function dispenseHistory(bool $withMedsOnly): void
    {
        DB::statement('DROP VIEW IF EXISTS dispense_history_view');

        $source = $withMedsOnly
            ? "IF(mb.meds_only = 1, 'Meds Only', IF(mb.model_type = 'App\\\\Models\\\\Prescription', 'Prescription', 'Dispense Record'))"
            : "IF(mb.model_type = 'App\\\\Models\\\\Prescription', 'Prescription', 'Dispense Record')";
        $patientName = $withMedsOnly
            ? "COALESCE(NULLIF(CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), ''), mb.patient_name)"
            : "NULLIF(CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), '')";
        $givenBy = $withMedsOnly
            ? "COALESCE(NULLIF(CONCAT_WS(' ', du.first_name, NULLIF(du.middle_name, ''), du.last_name), ''), NULLIF(CONCAT_WS(' ', ru.first_name, NULLIF(ru.middle_name, ''), ru.last_name), ''))"
            : "NULLIF(CONCAT_WS(' ', du.first_name, NULLIF(du.middle_name, ''), du.last_name), '')";
        $givenByEmail = $withMedsOnly ? 'COALESCE(du.email, ru.email)' : 'du.email';
        $recorderJoin = $withMedsOnly ? 'LEFT JOIN users ru ON ru.id = mb.recorded_by' : '';

        DB::statement(<<<SQL
            CREATE VIEW dispense_history_view AS
            SELECT
                CONCAT('B', mb.id) AS id,
                {$source} AS source,
                mb.id AS record_id,
                mb.history_number AS history_number,
                mb.bill_date AS dispensed_at,
                mb.patient_id AS patient_id,
                {$patientName} AS patient_name,
                pu.email AS patient_email,
                mb.doctor_id AS doctor_id,
                {$givenBy} AS given_by,
                {$givenByEmail} AS given_by_email,
                CAST(COALESCE(bill_lines.quantity, 0) AS SIGNED) AS quantity,
                NULL AS used_for
            FROM medicine_bills mb
            LEFT JOIN patients p ON p.id = mb.patient_id
            LEFT JOIN users pu ON pu.id = p.user_id
            LEFT JOIN doctors dr ON dr.id = mb.doctor_id
            LEFT JOIN users du ON du.id = dr.user_id
            {$recorderJoin}
            LEFT JOIN (
                SELECT medicine_bill_id, SUM(sale_quantity) AS quantity
                FROM sale_medicines
                GROUP BY medicine_bill_id
            ) bill_lines ON bill_lines.medicine_bill_id = mb.id
            WHERE mb.model_type IN ('App\\\\Models\\\\DispenseRecord', 'App\\\\Models\\\\MedicineBill')
               OR (
                    mb.model_type = 'App\\\\Models\\\\Prescription'
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

    private function usedMedicines(bool $withMedsOnly): void
    {
        DB::statement('DROP VIEW IF EXISTS used_medicines_view');

        $source = $withMedsOnly
            ? "IF(mb.meds_only = 1, IF(t.transaction_type = 'dispense', 'Meds Only', 'Meds Only (returned)'), IF(t.transaction_type = 'dispense', 'Dispense Record', 'Dispense Record (returned)'))"
            : "IF(t.transaction_type = 'dispense', 'Dispense Record', 'Dispense Record (returned)')";
        $patientName = $withMedsOnly
            ? "COALESCE(di.name, IF(mb.meds_only = 1, COALESCE(NULLIF(CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), ''), mb.patient_name), NULL), CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), IF(t.reference_type = 'App\\\\Models\\\\DocumentIssuance', 'Deleted consultation', 'N/A'))"
            : "COALESCE(di.name, CONCAT_WS(' ', pu.first_name, NULLIF(pu.middle_name, ''), pu.last_name), IF(t.reference_type = 'App\\\\Models\\\\DocumentIssuance', 'Deleted consultation', 'N/A'))";
        $nurse = $withMedsOnly
            ? "IF(mb.meds_only = 1, COALESCE(NULLIF(CONCAT_WS(' ', rb.first_name, NULLIF(rb.middle_name, ''), rb.last_name), ''), 'N/A'), COALESCE(CONCAT_WS(' ', nurse.first_name, NULLIF(nurse.middle_name, ''), nurse.last_name), 'N/A'))"
            : "COALESCE(CONCAT_WS(' ', nurse.first_name, NULLIF(nurse.middle_name, ''), nurse.last_name), 'N/A')";
        $recorderJoin = $withMedsOnly ? 'LEFT JOIN users rb ON rb.id = mb.recorded_by' : '';

        DB::statement(<<<SQL
            CREATE VIEW used_medicines_view AS
            SELECT
                CONCAT('T', t.id) AS id,
                b.medicine_id AS medicine_id,
                m.name AS medicine_name,
                COALESCE(NULLIF(b.dosage, ''), NULLIF(m.dosage, ''), 'N/A') AS dosage,
                CASE WHEN t.transaction_type = 'dispense' THEN t.quantity ELSE -t.quantity END AS quantity,
                CASE
                    WHEN t.reference_type = 'App\\\\Models\\\\DocumentIssuance'
                        THEN IF(t.transaction_type = 'dispense', 'Consultation', 'Consultation (returned)')
                    WHEN t.reference_type = 'App\\\\Models\\\\Prescription'
                        THEN 'Prescription'
                    WHEN t.reference_type = 'App\\\\Models\\\\DispenseRecord'
                        THEN {$source}
                    ELSE 'Other'
                END AS source,
                {$patientName} AS patient_name,
                {$nurse} AS nurse_incharged,
                'N/A' AS used_for,
                b.expiration_date AS expiry_date,
                t.created_at AS created_at
            FROM medicine_transactions t
            INNER JOIN medicine_batches b ON b.id = t.batch_id
            INNER JOIN medicines m ON m.id = b.medicine_id
            LEFT JOIN document_issuances di
                ON t.reference_type = 'App\\\\Models\\\\DocumentIssuance' AND di.id = t.reference_id
            LEFT JOIN users nurse ON nurse.id = di.nursing_incharged_id
            LEFT JOIN prescriptions rx
                ON t.reference_type = 'App\\\\Models\\\\Prescription' AND rx.id = t.reference_id
            LEFT JOIN medicine_bills mb
                ON t.reference_type = 'App\\\\Models\\\\DispenseRecord' AND mb.id = t.reference_id
            {$recorderJoin}
            LEFT JOIN patients p ON p.id = COALESCE(rx.patient_id, mb.patient_id)
            LEFT JOIN users pu ON pu.id = p.user_id
            WHERE t.transaction_type = 'dispense'
               OR (
                    t.transaction_type = 'adjustment'
                    AND t.reference_type IN ('App\\\\Models\\\\DocumentIssuance', 'App\\\\Models\\\\DispenseRecord')
               )
        SQL);
    }
};
