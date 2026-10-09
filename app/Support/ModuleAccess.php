<?php

namespace App\Support;

class ModuleAccess
{
    public const CLINICAL_PERMISSIONS = [
        'manage_patients', 'manage_request_documents', 'manage_medicines', 'manage_doctors', 'manage_specialties',
    ];

    public const PERMISSIONS = [
        'patients' => 'manage_patients', 'queue' => 'manage_patients',
        'consultations' => 'manage_request_documents', 'certificates' => 'manage_request_documents',
        'prescriptions' => 'manage_request_documents', 'lab_requests' => 'manage_request_documents',
        'document_issuances' => 'manage_request_documents',
        'inventory' => 'manage_medicines', 'dispensing' => 'manage_medicines',
        'doctors' => 'manage_doctors', 'specializations' => 'manage_specialties',
        'settings' => 'manage_settings', 'roles' => 'manage_roles', 'cms' => 'manage_front_cms',
        'countries' => 'manage_countries', 'states' => 'manage_states', 'cities' => 'manage_cities',
        'staffs' => 'manage_staff',
    ];

    public static function applicablePermissions(string $role): array
    {
        if (in_array($role, ['patient', 'nurse'], true)) {
            return [];
        }

        return in_array($role, ['staff', 'doctor'], true)
            ? self::CLINICAL_PERMISSIONS
            : array_values(array_unique(self::PERMISSIONS));
    }
}
