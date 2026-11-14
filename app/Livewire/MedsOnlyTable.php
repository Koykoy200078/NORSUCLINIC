<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\DispenseRecord;
use App\Services\Reports\ReportQueries;
use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;
use App\Support\Tables\Column;

/**
 * Meds Only: the patients who only came for medicine. Date, name, course, year, college and the drugs, as the clinic keeps
 * them on paper; the search finds a person, a college or course, or a drug by its name / generic / brand.
 *
 * The manual dispense records (the older "Add Dispense Record" pop-up of Dispense History) are listed too, tagged "Dispense
 * record" (the owner's decision, 2026-11-14). They keep no name, course, year or college of their own, so those come from the
 * patient's profile, also for the search and the sorting, and the doctor who gave the medicine is the one who recorded it.
 */
#[Lazy]
class MedsOnlyTable extends TableComponent
{
    protected $model = DispenseRecord::class;

    use ResetsPageOnEvent;

    /** The patient's profile value of a manual dispense record, as SQL: a user column, or a name column of a joined list. */
    private const PROFILE_SQL = '(SELECT %s FROM patients AS profile_patient JOIN users AS profile_user ON profile_user.id = profile_patient.user_id %s'
        . ' WHERE profile_patient.id = medicine_bills.patient_id)';

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('medicine_bills.bill_date', 'desc')
            ->setTableAttributes(['class' => 'table table-striped table-bordered'])
            // The list selects only the mapped columns; the label columns need these to load their relations and tell a
            // manual dispense record from a Meds Only one.
            ->setAdditionalSelects(['medicine_bills.id', 'medicine_bills.recorded_by', 'medicine_bills.patient_id', 'medicine_bills.doctor_id',
                'medicine_bills.model_type', 'medicine_bills.meds_only']);
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function builder(): Builder
    {
        // The page route checks this too; a Livewire request skips route middleware, so it is checked here as well.
        abort_unless(canUseModule('meds_only'), 403);

        return DispenseRecord::query()->inMedsOnlyList()->with([
            'dispenseItems.medicine:id,name,generic_name,brand_name', 'recorder', 'doctor.user',
            'patient.user.course', 'patient.user.college', 'patient.user.yearLevel',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::make('Date', 'bill_date')
                ->sortable()
                ->format(fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('M d, Y') : 'N/A'),
            // escaped here because of the tag (the list escapes a plain column itself)
            Column::make('Name', 'patient_name')
                ->sortable($this->sortWithProfile('patient_name', "CONCAT_WS(' ', profile_user.first_name, profile_user.last_name)"))
                ->searchable(fn (Builder $query, $word) => $query->where('medicine_bills.patient_name', 'like', SearchTerm::like($word))
                    ->orWhereHas('patient.user', fn ($user) => $user->where(fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS))))
                ->format(fn ($value, $row) => e($value ?: ($row->patient?->user?->full_name ?? '—'))
                    . ($row->isManual() ? ' <span class="badge bg-light-primary ms-1">Dispense record</span>' : ''))
                ->html(),
            Column::make('Course', 'course')
                ->sortable($this->sortWithProfile('course', 'profile_course.course_name', 'LEFT JOIN courses AS profile_course ON profile_course.id = profile_user.course_id'))
                ->searchable(fn (Builder $query, $word) => $query->where('medicine_bills.course', 'like', SearchTerm::like($word))
                    ->orWhereHas('patient.user.course', fn ($course) => $course->where('course_name', 'like', SearchTerm::like($word))))
                ->format(fn ($value, $row) => $value ?: ($row->isManual() ? $row->patient?->user?->course?->course_name : null) ?: '—'),
            Column::make('Year', 'year_level')
                ->sortable($this->sortWithProfile('year_level', 'profile_year.year_level_name', 'LEFT JOIN year_levels AS profile_year ON profile_year.id = profile_user.year_level_id'))
                ->format(fn ($value, $row) => $value ?: ($row->isManual() ? $row->patient?->user?->yearLevel?->year_level_name : null) ?: '—'),
            Column::make('College', 'college')
                ->sortable($this->sortWithProfile('college', 'profile_college.college_name', 'LEFT JOIN colleges AS profile_college ON profile_college.id = profile_user.college_id'))
                ->searchable(fn (Builder $query, $word) => $query->where('medicine_bills.college', 'like', SearchTerm::like($word))
                    ->orWhereHas('patient.user.college', fn ($college) => $college->where('college_name', 'like', SearchTerm::like($word))))
                ->format(fn ($value, $row) => $value ?: ($row->isManual() ? $row->patient?->user?->college?->college_name : null) ?: '—'),
            Column::make('Drugs')
                ->label(fn ($row) => e($row->medicineSummary()))
                ->html()
                ->searchable(function (Builder $query, $word) {
                    $query->whereHas('dispenseItems.medicine', fn ($medicine) => $medicine->where(
                        fn ($group) => SearchTerm::wordInColumns($group, $word, ReportQueries::medicineSearchColumns())
                    ));
                }),
            // plain text: the list escapes it (an e() here as well showed "D&#039;Souza"); a manual record names the doctor
            Column::make('Recorded by')
                ->label(fn ($row) => $row->recorder?->full_name ?? $row->doctor?->user?->full_name ?? 'N/A'),
            Column::make(__('messages.common.action'))
                ->label(fn ($row) => view('meds_only.columns.action', ['row' => $row]))
                ->html(),
        ];
    }

    /**
     * Sort a column by the record's own value, or for a manual dispense record (which keeps none) by the patient's profile.
     * $direction is 'asc' or 'desc' (the list base checks it).
     */
    private function sortWithProfile(string $column, string $profileValue, string $join = ''): callable
    {
        $profile = sprintf(self::PROFILE_SQL, $profileValue, $join);

        return fn (Builder $query, string $direction) => $query->orderByRaw("COALESCE(medicine_bills.{$column}, {$profile}) {$direction}");
    }
}
