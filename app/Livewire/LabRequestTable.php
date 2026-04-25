<?php

namespace App\Livewire;

use App\Models\LabRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class LabRequestTable extends DataTableComponent
{
    protected $model = LabRequest::class;

    public bool $showFilterOnHeader = false;
    public bool $showButtonOnHeader = false;

    /** If set, show only this patient's requests (from patient history page) */
    public ?int $patientId = null;

    // Active status filter value synced with URL ?status=...
    #[Url(as: 'status')]
    public ?string $status = null;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setTableAttributes([
            'class' => 'table table-striped table-bordered',
        ]);
        $this->setColumnSelectStatus(false);
        $this->setAdditionalSelects([
            'lab_requests.id',
            'lab_requests.status',
            'lab_requests.patient_gender',
            'lab_requests.patient_age',
        ]);
    }

    public function placeholder()
    {
        return view('livewire.loading_skeleton');
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        $query = LabRequest::query()->with('items');

        $user = Auth::user();

        // Patients see only their own requests
        if ($user->type === User::PATIENT) {
            $query->where('patient_user_id', $user->id);
        } else {
            // Admin/Staff/Doctor can scope to a single patient (from patient history)
            if ($this->patientId) {
                $query->where('patient_user_id', $this->patientId);
            }
        }

        // Status filter
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        return $query->orderByDesc('requested_at')->orderByDesc('id');
    }

    public function filters(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [
            Column::make('Request #', 'request_number')
                ->sortable()
                ->searchable(),

            Column::make('Patient Name', 'patient_name')
                ->sortable()
                ->searchable(),

            Column::make('Tests Requested')
                ->label(function ($row) {
                    return e($row->getTestNamesSummary(3));
                }),

            Column::make('Date Requested', 'requested_at')
                ->sortable()
                ->format(fn($value) => $value ? $value->format('M d, Y') : '—'),

            Column::make('Status', 'status')
                ->sortable()
                ->label(function ($row) {
                    $label = ucfirst($row->status);
                    $class = $row->getStatusBadgeClass();
                    $icon  = $row->getStatusIcon();
                    return "<span class=\"badge bg-{$class}\"><i class=\"fas {$icon} me-1\"></i>{$label}</span>";
                })
                ->html(),

            Column::make('Actions')
                ->label(function ($row) {
                    return view('lab_requests.components.action-buttons', ['row' => $row]);
                })
                ->html(),
        ];
    }
}
