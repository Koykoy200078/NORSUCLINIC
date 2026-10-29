<?php

namespace App\Support;

/**
 * The foreign keys a correctly installed database has: 57 on the current migrations (52 before the count sheets, 50 before patient merging).
 * The clinic's old MyISAM tables have none of them. The migration 2026_10_08_120000_restore_missing_foreign_keys adds
 * the missing ones from this list, and `php artisan db:integrity` reports the absent ones from it.
 *
 * ForeignKeyCatalogTest compares this list with a freshly migrated schema: when a migration adds, renames or changes a
 * foreign key, that test fails until the list below is updated.
 */
final class ForeignKeyCatalog
{
    /** table, constraint name, column, referenced table, referenced column, ON DELETE, ON UPDATE */
    private const KEYS = [
        ['patients', 'patients_merged_into_id_foreign', 'merged_into_id', 'patients', 'id', 'RESTRICT', 'NO ACTION'],
        ['addresses', 'addresses_merged_from_patient_id_foreign', 'merged_from_patient_id', 'patients', 'id', 'RESTRICT', 'NO ACTION'],
        ['categories', 'categories_merged_into_id_foreign', 'merged_into_id', 'categories', 'id', 'RESTRICT', 'NO ACTION'],
        ['generics', 'generics_merged_into_id_foreign', 'merged_into_id', 'generics', 'id', 'RESTRICT', 'NO ACTION'],
        ['medicines', 'medicines_merged_into_id_foreign', 'merged_into_id', 'medicines', 'id', 'RESTRICT', 'NO ACTION'],
        ['addresses', 'addresses_barangay_id_foreign', 'barangay_id', 'barangays', 'id', 'SET NULL', 'CASCADE'],
        ['addresses', 'addresses_city_id_foreign', 'city_id', 'cities', 'id', 'CASCADE', 'CASCADE'],
        ['addresses', 'addresses_country_id_foreign', 'country_id', 'countries', 'id', 'CASCADE', 'CASCADE'],
        ['addresses', 'addresses_state_id_foreign', 'state_id', 'states', 'id', 'CASCADE', 'CASCADE'],
        ['barangays', 'barangays_city_id_foreign', 'city_id', 'cities', 'id', 'CASCADE', 'CASCADE'],
        ['cities', 'cities_state_id_foreign', 'state_id', 'states', 'id', 'CASCADE', 'CASCADE'],
        ['consultation_illnesses', 'consultation_illnesses_document_issuance_id_foreign', 'document_issuance_id', 'document_issuances', 'id', 'CASCADE', 'NO ACTION'],
        ['consultation_illnesses', 'consultation_illnesses_illness_id_foreign', 'illness_id', 'illnesses', 'id', 'RESTRICT', 'NO ACTION'],
        ['consultation_medicines', 'consultation_medicines_medicine_id_foreign', 'medicine_id', 'medicines', 'id', 'RESTRICT', 'NO ACTION'],
        ['consultation_medicines', 'consultation_medicines_request_document_id_foreign', 'request_document_id', 'document_issuances', 'id', 'CASCADE', 'NO ACTION'],
        ['consultation_services', 'consultation_services_document_issuance_id_foreign', 'document_issuance_id', 'document_issuances', 'id', 'CASCADE', 'NO ACTION'],
        ['consultation_services', 'consultation_services_service_type_id_foreign', 'service_type_id', 'service_types', 'id', 'RESTRICT', 'NO ACTION'],
        ['doctor_specialization', 'doctor_specialization_doctor_id_foreign', 'doctor_id', 'doctors', 'id', 'CASCADE', 'CASCADE'],
        ['doctor_specialization', 'doctor_specialization_specialization_id_foreign', 'specialization_id', 'specializations', 'id', 'CASCADE', 'CASCADE'],
        ['doctors', 'doctors_user_id_foreign', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['illnesses', 'illnesses_illness_system_id_foreign', 'illness_system_id', 'illness_systems', 'id', 'CASCADE', 'NO ACTION'],
        ['lab_request_items', 'lab_request_items_lab_request_id_foreign', 'lab_request_id', 'lab_requests', 'id', 'CASCADE', 'NO ACTION'],
        ['medicine_batches', 'medicine_batches_medicine_id_foreign', 'medicine_id', 'medicines', 'id', 'RESTRICT', 'NO ACTION'],
        ['medicine_transactions', 'medicine_transactions_batch_id_foreign', 'batch_id', 'medicine_batches', 'id', 'RESTRICT', 'NO ACTION'],
        ['medicine_transactions', 'medicine_transactions_user_id_foreign', 'user_id', 'users', 'id', 'SET NULL', 'NO ACTION'],
        ['medicines', 'medicines_category_id_foreign', 'category_id', 'categories', 'id', 'SET NULL', 'CASCADE'],
        ['medicines', 'medicines_generic_id_foreign', 'generic_id', 'generics', 'id', 'SET NULL', 'NO ACTION'],
        ['model_has_permissions', 'model_has_permissions_permission_id_foreign', 'permission_id', 'permissions', 'id', 'CASCADE', 'NO ACTION'],
        ['model_has_roles', 'model_has_roles_role_id_foreign', 'role_id', 'roles', 'id', 'CASCADE', 'NO ACTION'],
        ['notifications', 'notifications_user_id_foreign', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['patient_queues', 'patient_queues_added_by_foreign', 'added_by', 'users', 'id', 'CASCADE', 'NO ACTION'],
        ['patient_queues', 'patient_queues_latest_consultation_id_foreign', 'latest_consultation_id', 'document_issuances', 'id', 'SET NULL', 'NO ACTION'],
        ['patient_queues', 'patient_queues_patient_id_foreign', 'patient_id', 'patients', 'id', 'CASCADE', 'NO ACTION'],
        ['patients', 'patients_insurance_provider_id_foreign', 'insurance_provider_id', 'insurance_providers', 'id', 'SET NULL', 'CASCADE'],
        ['patients', 'patients_patient_type_id_foreign', 'patient_type_id', 'patient_types', 'id', 'SET NULL', 'CASCADE'],
        ['patients', 'patients_user_id_foreign', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['prescriptions', 'fk_prescriptions_dispensed_by', 'dispensed_by', 'users', 'id', 'SET NULL', 'NO ACTION'],
        ['prescriptions', 'prescriptions_doctor_id_foreign', 'doctor_id', 'doctors', 'id', 'RESTRICT', 'CASCADE'],
        ['prescriptions', 'prescriptions_patient_id_foreign', 'patient_id', 'patients', 'id', 'RESTRICT', 'CASCADE'],
        ['prescriptions_medicines', 'prescriptions_medicines_medicine_foreign', 'medicine', 'medicines', 'id', 'RESTRICT', 'CASCADE'],
        ['prescriptions_medicines', 'prescriptions_medicines_prescription_id_foreign', 'prescription_id', 'prescriptions', 'id', 'CASCADE', 'CASCADE'],
        ['purchased_medicines', 'purchased_medicines_batch_id_foreign', 'batch_id', 'medicine_batches', 'id', 'SET NULL', 'NO ACTION'],
        ['purchased_medicines', 'purchased_medicines_medicine_id_foreign', 'medicine_id', 'medicines', 'id', 'SET NULL', 'CASCADE'],
        ['purchased_medicines', 'purchased_medicines_purchase_medicines_id_foreign', 'medicine_availabilities_id', 'medicine_availabilities', 'id', 'CASCADE', 'CASCADE'],
        ['qualifications', 'qualifications_user_id_foreign', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['role_has_permissions', 'role_has_permissions_permission_id_foreign', 'permission_id', 'permissions', 'id', 'CASCADE', 'NO ACTION'],
        ['role_has_permissions', 'role_has_permissions_role_id_foreign', 'role_id', 'roles', 'id', 'CASCADE', 'NO ACTION'],
        ['staff_profiles', 'staff_profiles_assigned_station_id_foreign', 'assigned_station_id', 'clinic_stations', 'id', 'SET NULL', 'CASCADE'],
        ['staff_profiles', 'staff_profiles_role_designation_id_foreign', 'role_designation_id', 'staff_designations', 'id', 'SET NULL', 'CASCADE'],
        ['staff_profiles', 'staff_profiles_user_id_foreign', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['states', 'states_country_id_foreign', 'country_id', 'countries', 'id', 'CASCADE', 'CASCADE'],
        ['used_medicines', 'used_medicines_medicine_id_foreign', 'medicine_id', 'medicines', 'id', 'RESTRICT', 'CASCADE'],
        ['inventory_counts', 'inventory_counts_created_by_foreign', 'created_by', 'users', 'id', 'SET NULL', 'NO ACTION'],
        ['inventory_counts', 'inventory_counts_finalized_by_foreign', 'finalized_by', 'users', 'id', 'SET NULL', 'NO ACTION'],
        ['inventory_count_lines', 'inventory_count_lines_inventory_count_id_foreign', 'inventory_count_id', 'inventory_counts', 'id', 'CASCADE', 'NO ACTION'],
        ['inventory_count_lines', 'inventory_count_lines_medicine_id_foreign', 'medicine_id', 'medicines', 'id', 'RESTRICT', 'NO ACTION'],
        ['inventory_count_lines', 'inventory_count_lines_adjusted_by_foreign', 'adjusted_by', 'users', 'id', 'SET NULL', 'NO ACTION'],
    ];

    /**
     * @return list<array{table: string, name: string, columns: list<string>, refTable: string, refColumns: list<string>, onDelete: string, onUpdate: string}>
     */
    public static function all(): array
    {
        return array_map(fn (array $key): array => [
            'table' => $key[0],
            'name' => $key[1],
            'columns' => [$key[2]],
            'refTable' => $key[3],
            'refColumns' => [$key[4]],
            'onDelete' => $key[5],
            'onUpdate' => $key[6],
        ], self::KEYS);
    }
}
