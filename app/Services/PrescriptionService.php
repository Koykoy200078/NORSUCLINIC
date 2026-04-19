<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionService
{
    /**
     * Create a new prescription with medicines
     *
     * @param array $data
     * @return Prescription
     */
    public function createPrescription(array $data): Prescription
    {
        return DB::transaction(function () use ($data) {
            $prescription = Prescription::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'appointment_id' => null,
                'health_checkup' => $data['health_checkup'] ?? null,
                'status' => $data['status'] ?? Prescription::DISPENSE_STATUS_PENDING,
                'is_active' => $data['is_active'] ?? true,
                'is_completed' => $data['is_completed'] ?? false,
            ]);

            if (isset($data['medicines']) && is_array($data['medicines'])) {
                $this->attachMedicines($prescription, $data['medicines']);
            }

            return $prescription->load(['patient.user', 'doctor.user', 'medicines']);
        });
    }

    /**
     * Update existing prescription
     *
     * @param Prescription $prescription
     * @param array $data
     * @return Prescription
     */
    public function updatePrescription(Prescription $prescription, array $data): Prescription
    {
        return DB::transaction(function () use ($prescription, $data) {
            $prescription->update([
                'health_checkup' => $data['health_checkup'] ?? $prescription->health_checkup,
                'status' => $data['status'] ?? $prescription->status,
                'is_active' => $data['is_active'] ?? $prescription->is_active,
                'is_completed' => $data['is_completed'] ?? $prescription->is_completed,
            ]);

            if (isset($data['medicines']) && is_array($data['medicines'])) {
                $prescription->medicines()->detach();
                $this->attachMedicines($prescription, $data['medicines']);
            }

            return $prescription->fresh(['patient.user', 'doctor.user', 'medicines']);
        });
    }

    /**
     * Attach medicines to prescription
     *
     * @param Prescription $prescription
     * @param array $medicines
     * @return void
     */
    private function attachMedicines(Prescription $prescription, array $medicines): void
    {
        foreach ($medicines as $medicine) {
            $prescription->medicines()->attach($medicine['medicine_id'], [
                'dosage' => $medicine['dosage'] ?? null,
                'day' => $medicine['day'] ?? null,
                'time' => $medicine['time'] ?? null,
                'comment' => $medicine['comment'] ?? null,
                'dose_interval' => $medicine['dose_interval'] ?? null,
                'dose_duration' => $medicine['dose_duration'] ?? null,
            ]);
        }
    }

    /**
     * Generate PDF for prescription
     *
     * @param Prescription $prescription
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Prescription $prescription)
    {
        $prescription->load([
            'patient.user',
            'doctor.user',
            'medicines',
        ]);

        $settings = SettingsService::getMultiple([
            'clinic_name',
            'logo',
            'address',
            'phone',
            'email'
        ]);

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription', 'settings'));

        return $pdf->download("prescription-{$prescription->id}.pdf");
    }

    /**
     * Get prescription with all related data for display
     *
     * @param int $prescriptionId
     * @return Prescription
     */
    public function getPrescriptionWithDetails(int $prescriptionId): Prescription
    {
        return Prescription::with([
            'patient.user',
            'doctor.user',
            'medicines.medicineCategory',
        ])->findOrFail($prescriptionId);
    }

    /**
     * Get prescriptions for a patient
     *
     * @param int $patientId
     * @return Collection
     */
    public function getPatientPrescriptions(int $patientId): Collection
    {
        return Prescription::with([
            'doctor.user',
            'medicines'
        ])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get prescriptions for a doctor
     *
     * @param int $doctorId
     * @return Collection
     */
    public function getDoctorPrescriptions(int $doctorId): Collection
    {
        return Prescription::with([
            'patient.user',
            'medicines'
        ])
            ->where('doctor_id', $doctorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Toggle prescription status
     *
     * @param Prescription $prescription
     * @return Prescription
     */
    public function toggleStatus(Prescription $prescription): Prescription
    {
        $prescription->update([
            'is_active' => ! (bool) $prescription->is_active,
        ]);

        return $prescription;
    }

    /**
     * Get available medicines for prescription
     *
     * @return Collection
     */
    public function getAvailableMedicines(): Collection
    {
        return Medicine::with('medicineCategory')
            ->whereHas('purchasedMedicine', function ($query) {
                $query->where('available_quantity', '>', 0);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Validate prescription data
     *
     * @param array $data
     * @return array
     */
    public function validatePrescriptionData(array $data): array
    {
        $errors = [];

        // Validate patient exists
        if (!Patient::find($data['patient_id'])) {
            $errors['patient_id'] = 'Patient not found.';
        }

        // Validate doctor exists
        if (!Doctor::find($data['doctor_id'])) {
            $errors['doctor_id'] = 'Doctor not found.';
        }

        // Validate medicines if provided
        if (isset($data['medicines']) && is_array($data['medicines'])) {
            foreach ($data['medicines'] as $index => $medicine) {
                if (!Medicine::find($medicine['medicine_id'])) {
                    $errors["medicines.{$index}.medicine_id"] = 'Medicine not found.';
                }
            }
        }

        return $errors;
    }
}
