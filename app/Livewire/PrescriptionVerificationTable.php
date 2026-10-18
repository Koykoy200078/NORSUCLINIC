<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Support\SearchTerm;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PrescriptionVerificationTable extends LivewireTableComponent
{
    protected $model = Prescription::class;

    public bool $showButtonOnHeader = false;

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('prescriptions.created_at', 'desc')
            ->setQueryStringStatus(false);
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function columns(): array
    {
        return [
            Column::make('RX #', 'id')
                ->sortable()
                ->searchable(fn (Builder $query, $word) => $query->where('prescriptions.id', 'like', SearchTerm::like($word))),
            Column::make('Patient', 'patient.user.first_name')
                ->label(fn($row) => optional(optional($row->patient)->user)->full_name ?: 'N/A')
                ->searchable(function (Builder $query, $word) {
                    $query->whereHas('patient.user', fn (Builder $q) => $q->where(fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)));
                }),
            Column::make('Doctor', 'doctor.user.first_name')
                ->label(fn($row) => optional(optional($row->doctor)->user)->full_name ?: 'N/A')
                ->searchable(function (Builder $query, $word) {
                    $query->whereHas('doctor.user', fn (Builder $q) => $q->where(fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)));
                }),
            Column::make('Consultation Date', 'consultation_date')
                ->format(fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : 'N/A')
                ->sortable(),
            Column::make('Requested Qty', 'id')
                ->label(fn($row) => number_format((float) ($row->requested_quantity ?? 0)))
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy('requested_quantity', $direction);
                }),
            Column::make('Dispense Status', 'status')
                ->view('prescriptions.columns.dispense_status')
                ->sortable(),
            Column::make(__('messages.common.action'), 'id')
                ->view('medicine-dispensing.columns.verify_action'),
        ];
    }

    public function builder(): Builder
    {
        $query = Prescription::query()
            ->select('prescriptions.*')
            ->selectSub(
                PrescriptionMedicine::query()
                    ->selectRaw('COALESCE(SUM(total_quantity), 0)')
                    ->whereColumn('prescriptions_medicines.prescription_id', 'prescriptions.id'),
                'requested_quantity'
            )
            ->with([
                'patient:id,user_id',
                'patient.user:id,first_name,last_name,email,gender',
                'doctor:id,user_id',
                'doctor.user:id,first_name,last_name,email,gender',
            ])
            ->where('prescriptions.status', Prescription::DISPENSE_STATUS_PENDING)
            // A deactivated prescription is stopped by its doctor; it must not wait in the pharmacy queue. R3-H3.
            ->where(function (Builder $active) {
                $active->where('prescriptions.is_active', true)->orWhereNull('prescriptions.is_active');
            });

        if (isRole('doctor')) {
            $doctor = Doctor::where('user_id', getLogInUserId())->first();
            // A doctor account without a doctor profile has no prescriptions (it used to be handed everybody's).
            $query->where('prescriptions.doctor_id', $doctor?->id ?? 0);
        }

        return $query;
    }
}
