<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class PatientService
{
    /**
     * Create a new patient with user account
     *
     * @param array $data
     * @return Patient
     */
    public function createPatient(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            // If is_employee is checked, set course_id to null
            if (isset($data['is_employee']) && $data['is_employee']) {
                $data['course_id'] = null;
            }

            // Create user first
            $user = User::create([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_no' => $data['emergency_contact_no'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'],
                'blood_type' => $data['blood_type'] ?? null,
                'password' => isset($data['password']) ? Hash::make($data['password']) : null,
                'status' => $data['status'] ?? true,
                'type' => User::PATIENT,
                'campus_id' => $data['campus_id'] ?? null,
                'college_id' => $data['college_id'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'year_level_id' => $data['year_level_id'] ?? null,
                'vaccination_id' => $data['vaccination_id'] ?? null,
                'country_code' => $data['country_code'] ?? null,
            ]);

            // Assign patient role
            $user->assignRole('patient');

            // Handle profile image upload
            if (isset($data['profile']) && $data['profile']) {
                $user->addMediaFromRequest('profile')
                    ->toMediaCollection(User::PROFILE);
            }

            // Create patient
            $patient = Patient::create([
                'user_id' => $user->id,
                'patient_unique_id' => $this->generateUniqueId(),
            ]);

            // Handle address if provided
            if (isset($data['address']) && is_array($data['address'])) {
                $patient->address()->create($data['address']);
            }

            return $patient->load('user', 'address');
        });
    }

    /**
     * Update existing patient
     *
     * @param Patient $patient
     * @param array $data
     * @return Patient
     */
    public function updatePatient(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            // If is_employee is checked, set course_id to null
            if (isset($data['is_employee']) && $data['is_employee']) {
                $data['course_id'] = null;
            }

            // Update user data
            $userData = [
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? $patient->user->middle_name,
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? $patient->user->email,
                'contact' => $data['contact'] ?? $patient->user->contact,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? $patient->user->emergency_contact_name,
                'emergency_contact_no' => $data['emergency_contact_no'] ?? $patient->user->emergency_contact_no,
                'dob' => $data['dob'] ?? $patient->user->dob,
                'gender' => $data['gender'] ?? $patient->user->gender,
                'blood_type' => $data['blood_type'] ?? $patient->user->blood_type,
                'status' => $data['status'] ?? $patient->user->status,
                'campus_id' => $data['campus_id'] ?? $patient->user->campus_id,
                'college_id' => $data['college_id'] ?? $patient->user->college_id,
                'course_id' => $data['course_id'] ?? $patient->user->course_id,
                'year_level_id' => $data['year_level_id'] ?? $patient->user->year_level_id,
                'vaccination_id' => $data['vaccination_id'] ?? $patient->user->vaccination_id,
            ];

            // Update password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $patient->user->update($userData);

            // Handle profile image upload
            if (isset($data['profile']) && $data['profile']) {
                $patient->user->clearMediaCollection(User::PROFILE);
                $patient->user->addMediaFromRequest('profile')
                    ->toMediaCollection(User::PROFILE);
            }

            // Handle address update
            if (isset($data['address']) && is_array($data['address'])) {
                if ($patient->address) {
                    $patient->address->update($data['address']);
                } else {
                    $patient->address()->create($data['address']);
                }
            }

            return $patient->fresh(['user', 'address']);
        });
    }

    /**
     * Generate unique patient ID
     *
     * @return string
     */
    private function generateUniqueId(): string
    {
        do {
            $id = 'PT' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Patient::where('patient_unique_id', $id)->exists());

        return $id;
    }

    /**
     * Get patient with complete medical history
     *
     * @param int $patientId
     * @return Patient
     */
    public function getPatientWithHistory(int $patientId): Patient
    {
        return Patient::with([
            'user',
            'address',
            'prescriptions' => function ($query) {
                $query->with(['medicines', 'doctor.user'])
                    ->orderBy('created_at', 'desc');
            },
            'medicineBills' => function ($query) {
                $query->with(['saleMedicine.medicine'])
                    ->orderBy('created_at', 'desc');
            },
            'visits' => function ($query) {
                $query->with(['doctor.user'])
                    ->orderBy('created_at', 'desc');
            },
            'requestDocuments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($patientId);
    }

    /**
     * Search patients by multiple criteria
     *
     * @param array $filters
     * @return Collection
     */
    public function searchPatients(array $filters): Collection
    {
        $query = Patient::with(['user']);

        // Search by name
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by campus
        if (isset($filters['campus_id']) && !empty($filters['campus_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('campus_id', $filters['campus_id']);
            });
        }

        // Filter by college
        if (isset($filters['college_id']) && !empty($filters['college_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('college_id', $filters['college_id']);
            });
        }

        // Filter by course
        if (isset($filters['course_id']) && !empty($filters['course_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('course_id', $filters['course_id']);
            });
        }

        // Filter by year level
        if (isset($filters['year_level_id']) && !empty($filters['year_level_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('year_level_id', $filters['year_level_id']);
            });
        }

        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get patients for dropdown/select options
     *
     * @return Collection
     */
    public function getPatientsForSelect(): Collection
    {
        return Patient::with('user:id,first_name,last_name')
            ->whereHas('user', function ($query) {
                $query->where('status', true);
            })
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->user->full_name,
                    'unique_id' => $patient->patient_unique_id,
                ];
            });
    }

    /**
     * Delete patient and associated data
     *
     * @param Patient $patient
     * @return bool
     */
    public function deletePatient(Patient $patient): bool
    {
        return DB::transaction(function () use ($patient) {
            // Delete associated records (if cascade is not set in database)
            $patient->prescriptions()->delete();
            $patient->medicineBills()->delete();
            $patient->visits()->delete();
            $patient->requestDocuments()->delete();

            // Delete address
            if ($patient->address) {
                $patient->address->delete();
            }

            // Delete patient record
            $patient->delete();

            // Delete user record
            $patient->user->delete();

            return true;
        });
    }

    /**
     * Get patient statistics
     *
     * @param int $patientId
     * @return array
     */
    public function getPatientStatistics(int $patientId): array
    {
        $patient = Patient::findOrFail($patientId);

        return [
            'total_appointments' => 0,
            'completed_appointments' => 0,
            'pending_appointments' => 0,
            'total_prescriptions' => $patient->prescriptions()->count(),
            'active_prescriptions' => $patient->prescriptions()->where('status', 1)->count(),
            'total_visits' => $patient->visits()->count(),
            'last_visit' => $patient->visits()->latest()->first()?->created_at,
            'last_appointment' => null,
        ];
    }

    /**
     * Check if patient has any medical records
     *
     * @param int $patientId
     * @return bool
     */
    public function hasMedicalRecords(int $patientId): bool
    {
        $patient = Patient::findOrFail($patientId);

        return $patient->prescriptions()->exists() ||
            $patient->visits()->exists() ||
            $patient->medicineBills()->exists();
    }

    /**
     * Get recent patients (last 30 days)
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentPatients(int $limit = 10): Collection
    {
        return Patient::with('user')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
