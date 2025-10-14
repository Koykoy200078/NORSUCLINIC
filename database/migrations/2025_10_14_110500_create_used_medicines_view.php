<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW used_medicines_view AS
            SELECT 
                CONCAT('C', cm.id) as id,
                cm.medicine_id,
                m.name as medicine_name,
                cm.quantity,
                'Consultation' as source,
                rd.name as patient_name,
                COALESCE(CONCAT(nurse.first_name, ' ', nurse.last_name), 'N/A') as nurse_incharged,
                COALESCE(cm.used_for, 'N/A') as used_for,
                cm.created_at
            FROM consultation_medicines cm
            INNER JOIN medicines m ON cm.medicine_id = m.id
            INNER JOIN request_documents rd ON cm.request_document_id = rd.id
            LEFT JOIN users nurse ON rd.nursing_incharged_id = nurse.id
            
            UNION ALL
            
            SELECT 
                CONCAT('S', sm.id) as id,
                sm.medicine_id,
                m.name as medicine_name,
                sm.sale_quantity as quantity,
                'Medicine Bill' as source,
                COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'N/A') as patient_name,
                'N/A' as nurse_incharged,
                'Sale' as used_for,
                sm.created_at
            FROM sale_medicines sm
            INNER JOIN medicines m ON sm.medicine_id = m.id
            INNER JOIN medicine_bills mb ON sm.medicine_bill_id = mb.id
            LEFT JOIN patients p ON mb.model_id = p.id AND mb.model_type = 'App\\\\Models\\\\Patient'
            LEFT JOIN users u ON p.user_id = u.id
            WHERE mb.payment_status = 1
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS used_medicines_view");
    }
};
