<?php

namespace App\Livewire;

use App\Livewire\Concerns\SearchesByWords;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\DocumentIssuance;
use App\Models\User;
use App\Support\SearchTerm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;

class DocumentIssuanceTable extends DataTableComponent
{
    use SearchesByWords;

    protected $model = DocumentIssuance::class;
    public bool $showFilterOnHeader = false;
    public bool $showButtonOnHeader = false;
    public string $buttonComponent = 'document_issuances.components.table-buttons';

    // Fixed when the component is mounted. They used to be client-writable, so a front-desk account that
    // may only open certificates could switch the table to "consultation" (or to another patient) from
    // the browser. R3-H6.
    #[Locked]
    public ?int $patientId = null;

    #[Locked]
    public string $module = 'consultation';

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setTableAttributes([
            'class' => 'table table-striped table-bordered',
        ]);
        $this->setColumnSelectStatus(false);
        // Required so ->label() callbacks can access these fields (Rappasoft v3 only
        // SELECTs columns mapped in Column::make(), label columns are excluded)
        $this->setAdditionalSelects(['document_issuances.assessment', 'document_issuances.plan']);
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        $query = DocumentIssuance::query();
        $isConsultationModule = ! in_array(strtolower($this->module), ['certificate', 'certificates'], true);

        // Check the user's role and filter data accordingly
        $user = Auth::user();

        // Staff/nurse see a document list only if their designation + station has that module; the page
        // route checks it too, but the list is also served by Livewire requests that skip route middleware.
        abort_unless(
            canStaffAccessModule($isConsultationModule ? 'consultations' : 'certificates', $user),
            403,
            'You are not allowed to access this request document module for your designation/station assignment.'
        );

        if ($user->type === User::PATIENT) { // Patient sees only own documents
            $query->where('user_id', $user->id);
        } elseif ($user->type === User::ADMIN || $user->type === User::STAFF || $user->type === User::DOCTOR) {
            // Admin, Staff, and Doctor can optionally scope records to a selected patient.
            if ($this->patientId) {
                $query->where('user_id', $this->patientId);
            }
        }

        if ($isConsultationModule) {
            $query->where('document_type', 'consultation_form');
        } else {
            $query->where('document_type', '!=', 'consultation_form');
        }

        return $query;
    }

    public function columns(): array
    {
        $columns = [
            Column::make("ID", "id")
                ->sortable(),
            // The patient's name was not searchable at all, so typing a name in the search box showed nothing.
            Column::make("Patient Name", "name")
                ->sortable()
                ->searchable(function ($query, $word) {
                    $query->where('document_issuances.name', 'like', SearchTerm::like($word))
                        ->orWhere('document_issuances.request_of', 'like', SearchTerm::like($word))
                        ->orWhere('document_issuances.complaints', 'like', SearchTerm::like($word));
                }),
            Column::make("Document Type", "document_type")
                ->sortable()
                ->searchable()
                ->format(function ($value) {
                    // Map the document_type to a user-friendly label
                    $documentTypes = [
                        'medical_certificate' => 'Medical Certificate',
                        'excuse_slip' => 'Excuse Slip',
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

            Column::make("Completion Status")
                ->label(function ($row, Column $column) {
                    if ($row->document_type === 'medical_certificate' || $row->document_type === 'excuse_slip') {
                        return '<span class="badge bg-success text-white">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>';
                    }

                    if ($row->document_type !== 'consultation_form') {
                        return '<span class="badge bg-secondary text-white">N/A</span>';
                    }

                    $hasAssessment = !empty(trim((string)$row->assessment));
                    $hasPlan = !empty(trim((string)$row->plan));

                    if ($hasAssessment && $hasPlan) {
                        return '<span class="badge bg-success text-white">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>';
                    }

                    $missing = [];
                    if (!$hasAssessment) $missing[] = 'Assessment';
                    if (!$hasPlan) $missing[] = 'Plan';

                    return '<span class="badge bg-warning text-dark" data-bs-toggle="tooltip" title="Missing: ' . implode(', ', $missing) . '">
                                <i class="fas fa-exclamation-triangle"></i> Incomplete
                            </span>';
                })
                ->html(),
        ];

        $columns[] = Column::make("Actions")
            ->label(
                fn($row) => view('document_issuances.components.action-buttons', ['id' => $row->id, 'row' => $row])
            );

        return $columns;
    }
}
