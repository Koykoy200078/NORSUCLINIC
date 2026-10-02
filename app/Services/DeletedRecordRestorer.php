<?php

namespace App\Services;

use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\MedicineTransaction;
use App\Models\PatientQueue;
use App\Support\AuditLog;
use Illuminate\Support\Facades\DB;

/**
 * Brings back a consultation, certificate, excuse slip or lab request that was deleted from a screen (they are only
 * hidden, see the `deleted_at` columns). A consultation took its medicines out of stock when it was saved and gave
 * them back when it was deleted, so restoring it takes them out again - from the batches that have them now. If the
 * clinic no longer has enough, nothing is restored and the reason is returned so the stock can be fixed first.
 */
class DeletedRecordRestorer
{
    public function __construct(private readonly MedicineInventoryService $inventory)
    {
    }

    /**
     * @return string a sentence for the administrator
     *
     * @throws \RuntimeException when the medicines of a consultation cannot be taken out of stock again
     */
    public function restoreDocument(int $id): string
    {
        $document = DocumentIssuance::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($document) {
            $medicineLines = 0;

            if ($document->document_type === 'consultation_form') {
                foreach ($document->consultationMedicines()->get() as $line) {
                    $quantity = (int) $line->quantity;
                    if ($quantity <= 0) {
                        continue;
                    }

                    $dosage = trim((string) $line->dosage);

                    $this->inventory->deductStockFefo(
                        (int) $line->medicine_id,
                        $quantity,
                        auth()->id(),
                        $document,
                        'Consultation #' . $document->id . ' restored: medicine taken out of stock again',
                        MedicineTransaction::TYPE_DISPENSE,
                        $dosage !== '' ? $dosage : null
                    );

                    cache()->forget('medicine_' . (int) $line->medicine_id);
                    $medicineLines++;
                }

                cache()->forget('medicines_list');
            }

            $document->restore();

            // A restored consultation shows on the doctor's queue screen again if the patient is still waiting.
            if ($document->document_type === 'consultation_form' && $document->user_id) {
                PatientQueue::syncAttachment((int) $document->user_id);
            }

            AuditLog::record('document_restored', $this->label($document) . ' #' . $document->id . ' restored (' . $document->name . ')', [
                'patient_name' => $document->name,
                'subject_type' => 'RequestDocuments',
                'subject_id' => $document->id,
                'properties' => ['document_type' => $document->document_type, 'medicine_lines_taken_out_again' => $medicineLines],
            ]);
        });

        return $this->label($document) . ' of ' . $document->name . ' was restored.';
    }

    public function restoreLabRequest(int $id): string
    {
        $labRequest = LabRequest::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($labRequest) {
            $labRequest->restore();

            AuditLog::record('lab_request_restored', "Lab request #{$labRequest->request_number} restored ({$labRequest->patient_name})", [
                'patient_name' => $labRequest->patient_name,
                'subject_type' => 'LabRequest',
                'subject_id' => $labRequest->id,
                'properties' => ['request_number' => $labRequest->request_number, 'status' => $labRequest->status],
            ]);
        });

        return "Lab request #{$labRequest->request_number} of {$labRequest->patient_name} was restored.";
    }

    private function label(DocumentIssuance $document): string
    {
        return match ($document->document_type) {
            'consultation_form' => 'Consultation',
            'excuse_slip' => 'Excuse slip',
            'medical_certificate' => 'Medical certificate',
            default => 'Document',
        };
    }
}
