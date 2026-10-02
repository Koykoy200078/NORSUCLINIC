<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Support\SearchTerm;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PrescriptionTable extends LivewireTableComponent
{
    protected $model = Prescription::class;

    public bool $showButtonOnHeader = true;

    public bool $showFilterOnHeader = false;

    public string $buttonComponent = 'prescriptions.add-button';

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public $doctor;

    public $patient;

    public function mount()
    {
        $this->doctor = getLogInUser()->hasRole('doctor') ? 1 : 0;
        $this->patient = getLogInUser()->hasRole('patient') ? 1 : 0;
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('prescriptions.created_at', 'desc')
            ->setQueryStringStatus(false);
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.patients'), 'patient.user.first_name')
                ->view('prescriptions.columns.patient_name')
                ->sortable()
                ->searchable(function (Builder $query, $word) {
                    $query->whereHas('patient.user', fn (Builder $q) => $q->where(fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)));
                })->hideIf($this->patient),
            Column::make(__('messages.prescription.patient'), 'patient_id')->hideIf(1),
            Column::make(__('messages.doctors'), 'doctor.user.first_name')
                ->view('prescriptions.columns.doctor_name')
                ->sortable()
                ->searchable(function (Builder $query, $word) {
                    $query->whereHas('doctor.user', fn (Builder $q) => $q->where(fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)));
                })->hideIf($this->doctor),
            Column::make(__('messages.doctor_opd_charge.doctor'), 'doctor_id')->hideIf(1),
            Column::make('Consultation Date', 'consultation_date')
                ->format(fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : 'N/A')
                ->sortable(),
            Column::make('Dispense Status', 'status')
                ->view('prescriptions.columns.dispense_status')
                ->sortable()
                ->searchable(),
            Column::make('ICD-10', 'diagnosis.diagnoses')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.common.action'), 'id')
                ->view('prescriptions.action'),
        ];
    }

    public function builder(): Builder
    {
        /** @var Prescription $query */
        if (! getLoggedinDoctor()) {
            $query = Prescription::query()->select('prescriptions.*')->with([
                'patient:id,user_id',
                'patient.user:id,first_name,last_name,email,gender',
                'doctor:id,user_id',
                'doctor.user:id,first_name,last_name,email,gender',
                'diagnosis:id,diagnoses',
            ]);
        } else {
            $doctorId = Doctor::where('user_id', getLogInUserId())->first();
            $query = Prescription::query()->select('prescriptions.*')->with([
                'patient:id,user_id',
                'patient.user:id,first_name,last_name,email,gender',
                'doctor:id,user_id',
                'doctor.user:id,first_name,last_name,email,gender',
                'diagnosis:id,diagnoses',
            ])->where('doctor_id', $doctorId->id);
        }

        return $query;
    }
}
