<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\RequestDocuments;
use Illuminate\Support\Facades\Auth;

class RequestDocumentTable extends DataTableComponent
{
    protected $model = RequestDocuments::class;
    public bool $showFilterOnHeader = false;
    public bool $showButtonOnHeader = true;
    public string $buttonComponent = 'requests.components.table-buttons';
    public ?int $patientId = null; // Add patient ID filter

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setTableAttributes([
            'class' => 'table table-striped table-bordered',
        ]);
        $this->setColumnSelectStatus(false);
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        $query = RequestDocuments::query();

        // Check the user's role and filter data accordingly
        $user = Auth::user();

        if ($user->type == 3) { // Patient
            $query->where('user_id', $user->id);
        } elseif ($user->type == 1 || $user->type == 4) { // Admin or Staff
            // If patient_id is set, filter by that patient's consultation forms only
            if ($this->patientId) {
                $query->where('user_id', $this->patientId)
                    ->where('document_type', 'consultation_form');
            } else {
                // For Admin/Staff viewing "Patient Data", show only consultation forms
                $query->where('document_type', 'consultation_form');
            }
        }

        return $query;
    }

    public function columns(): array
    {
        return [
            Column::make("ID", "id")
                ->sortable(),
            Column::make("Patient Name", "name")
                ->sortable(),
            Column::make("Document Type", "document_type")
                ->sortable()
                ->searchable()
                ->format(function ($value) {
                    // Map the document_type to a user-friendly label
                    $documentTypes = [
                        'medical_certificate' => 'Medical Certificate',
                        'referral_letter' => 'Referral Letter',
                        'clearance' => 'Clearance',
                        'consultation_form' => 'Consultation Form',
                        'medical_history' => 'Medical History',
                        'medical_report' => 'Medical Report',
                        'other' => 'Other',
                    ];

                    return $documentTypes[$value] ?? ucfirst(str_replace('_', ' ', $value));
                }),
            Column::make("Requested At", "requested_at")
                ->sortable()
                ->format(fn($value) => $value ? $value->format('Y-m-d') : 'N/A'),

            Column::make("Actions")
                ->label(
                    fn($row) => view('requests.components.action-buttons', ['id' => $row->id])
                ),
        ];
    }
}
