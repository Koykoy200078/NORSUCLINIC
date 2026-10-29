<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedsOnlyRequest;
use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\DispenseRecord;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\MedsOnlyService;
use App\Support\SearchTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

/**
 * Meds Only: patients who only come for medicine (no consultation). The work is MedsOnlyService; this class opens the
 * pages, finds a registered patient and answers where to go next. Everything sits behind the `manage_medicines` permission
 * (routes); a doctor only has the list and the record page.
 */
class MedsOnlyController extends AppBaseController
{
    /** At most this many patients come back from the search box. */
    private const PATIENT_RESULTS = 15;

    public function __construct(private MedsOnlyService $service)
    {
    }

    public function index(): View
    {
        return view('meds_only.index');
    }

    public function create(): View
    {
        return view('meds_only.form', $this->formData(null));
    }

    public function store(MedsOnlyRequest $request): RedirectResponse
    {
        $record = $this->service->create($request->validated(), $request->user());

        Flash::success('Meds only recorded for ' . $record->patient_name . '.');

        return redirectByRole('meds-only.index');
    }

    public function show(DispenseRecord $record): View
    {
        $this->mustBeMedsOnly($record);

        return view('meds_only.show', ['record' => $record->load(['dispenseItems.medicine', 'recorder', 'patient.user'])]);
    }

    public function edit(DispenseRecord $record): View
    {
        $this->mustBeMedsOnly($record);

        return view('meds_only.form', $this->formData($record->load(['dispenseItems.medicine', 'patient.user'])));
    }

    public function update(MedsOnlyRequest $request, DispenseRecord $record): RedirectResponse
    {
        $this->mustBeMedsOnly($record);

        $record = $this->service->update($record, $request->validated(), $request->user());

        Flash::success('Meds only record of ' . $record->patient_name . ' saved.');

        return redirectByRole('meds-only.index');
    }

    public function destroy(DispenseRecord $record): RedirectResponse
    {
        $this->mustBeMedsOnly($record);
        $name = $record->patient_name;

        $this->service->delete($record, request()->user());

        Flash::success('Meds only record of ' . $name . ' deleted; the medicine is back in stock.');

        return redirectByRole('meds-only.index');
    }

    /**
     * The search box of the form: registered, not archived patients whose name (or university id) holds every word typed.
     * Only what the form fills in comes back: the name, the university id and the college / course / year.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('term', ''));
        if ($term === '') {
            return response()->json(['results' => []]);
        }

        $patients = SearchTerm::whereAllWords(Patient::query()->with(['user.college', 'user.course', 'user.yearLevel']), $term, [
            fn ($inner, $word) => $inner->whereHas('user', fn ($user) => $user->where(
                fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)
            )),
            'patient_unique_id',
        ])->whereHas('user')->limit(self::PATIENT_RESULTS)->get();

        $results = $patients->map(function (Patient $patient) {
            $user = $patient->user;

            return [
                'id' => $patient->id,
                'text' => $user->full_name . ($user->university_id_number ? ' (' . $user->university_id_number . ')' : ''),
                'university_id' => $user->university_id_number,
                'campus_id' => $user->campus_id,
                'college_id' => $user->college_id,
                'course_id' => $user->course_id,
                'year_level_id' => $user->year_level_id,
                'patient_type_id' => $patient->patient_type_id,
                'college' => $user->college?->college_name,
                'course' => $user->course?->course_name,
                'year_level' => $user->yearLevel?->year_level_name,
            ];
        })->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return response()->json(['results' => $results]);
    }

    /** A manual dispense record or a prescription's record is not this module's: it has its own screens. */
    private function mustBeMedsOnly(DispenseRecord $record): void
    {
        abort_unless($record->meds_only, 404);
    }

    /**
     * What the form needs: the clinic's own lists, and the record and its drugs when it is edited. The drugs of a record that
     * the stock engine split over several batches come back as one line per medicine + strength.
     *
     * @return array<string, mixed>
     */
    private function formData(?DispenseRecord $record): array
    {
        $lines = $record ? $record->dispenseItems->groupBy(fn ($item) => $item->medicine_id . '|' . mb_strtolower(trim((string) $item->dosage)))
            ->map(fn ($items) => [
                'medicine_id' => (int) $items->first()->medicine_id,
                'dosage' => trim((string) $items->first()->dosage),
                'quantity' => (int) $items->sum('quantity'),
            ])->values()->all() : [];

        return [
            'record' => $record,
            'lines' => $lines,
            'colleges' => College::orderBy('college_name')->pluck('college_name', 'id'),
            'courses' => Course::orderBy('course_name')->pluck('course_name', 'id'),
            'yearLevels' => YearLevel::orderBy('id')->pluck('year_level_name', 'id'),
            'campuses' => Campus::orderBy('campus_name')->pluck('campus_name', 'id'),
            'patientTypes' => PatientType::orderBy('id')->pluck('name', 'id'),
        ];
    }
}
