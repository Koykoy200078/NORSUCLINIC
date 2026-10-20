<?php

namespace App\Services;

use App\Models\DocumentIssuance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentIssuanceNavigationService
{
    public function created(array $data, Request $request): RedirectResponse
    {
        // Check if we should redirect to patient history
        if ($request->has('redirect_to_patient') && $request->redirect_to_patient) {
            $user = User::find($data['user_id']);
            if ($user && $user->patient) {
                $patientId = $user->patient->id;

                // Determine success message based on document type
                $successMessage = $data['document_type'] === 'medical_certificate'
                    ? 'Medical certificate created successfully.'
                    : ($data['document_type'] === 'excuse_slip' ? 'Excuse slip created successfully.' : 'Consultation form created successfully.');

                // Role-based patient history redirect
                if (getLogInUser()?->hasAnyRole(['clinic_admin', 'staff', 'doctor'])) {
                    return redirectByRole('patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', $successMessage);
                }
            }
        }

        // Default: Return to request documents index with role-based redirect
        $documentModule = ($data['document_type'] ?? null) === 'consultation_form' ? 'consultation' : 'certificate';

        return redirectByRole('document-issuances.index', ['module' => $documentModule])
            ->with('success', 'Request document created successfully.');
    }

    public function changed(DocumentIssuance $requestDocument, Request $request, string $message): RedirectResponse
    {
        // Check if we have a redirect_patient_id (from patient history page)
        if ($request->has('redirect_patient_id')) {
            $patientId = $request->input('redirect_patient_id');

            // Redirect back to patient history
            if (getLogInUser()?->hasAnyRole(['clinic_admin', 'staff', 'doctor'])) {
                return redirectByRole('patients.showMyHistory', ['patient' => $patientId])
                    ->with('success', $message);
            }
        }

        // Default: Redirect to request documents index
        $documentModule = $request->input('redirect_module', $requestDocument->document_type === 'consultation_form' ? 'consultation' : 'certificate');

        return redirectByRole('document-issuances.index', ['module' => $documentModule])
            ->with('success', $message);
    }
}
