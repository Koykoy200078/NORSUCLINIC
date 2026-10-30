<?php

namespace App\Services\Reports;

use App\Models\ActivityLog;
use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\MedicineTransaction;
use App\Models\PatientQueue;
use App\Models\Prescription;
use App\Models\RequestDocuments;
use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * One query per report tab. The Report Generation screen pages these queries and the CSV export streams the very
 * same ones, so the two can never disagree about which rows match the filters.
 */
final class ReportQueries
{
    public const TABS = ['logs', 'visits', 'inventory', 'dispensing', 'appointments', 'meds_only'];

    /** The Patient Visits tab with a "Visit type" other than Consultations is a plain query on both kinds (VisitFeed), not a model query. */
    public static function forTab(string $tab, ReportFilters $filters): Builder|QueryBuilder|null
    {
        return match ($tab) {
            'logs' => self::logs($filters),
            'visits' => $filters->visitType === ReportFilters::VISIT_TYPES[0] ? self::visits($filters) : VisitFeed::query($filters),
            'inventory' => self::inventory($filters),
            'dispensing' => self::dispensing($filters),
            'appointments' => self::appointments($filters),
            'meds_only' => self::medsOnly($filters),
            default => null,
        };
    }

    public static function logs(ReportFilters $filters): Builder
    {
        $query = ActivityLog::query()->with('user')->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        if ($filters->userType !== 'all') {
            $query->where('user_type', $filters->userType);
        }
        if ($filters->action !== 'all') {
            $query->where('action', $filters->action);
        }
        if ($filters->dateFrom) {
            $query->where('date', '>=', $filters->dateFrom);
        }
        if ($filters->dateTo) {
            $query->where('date', '<=', $filters->dateTo);
        }

        return SearchTerm::whereAllWords($query, $filters->search, ['patient_name', 'description', 'user_name']);
    }

    public static function visits(ReportFilters $filters): Builder
    {
        return self::visitsSearched($filters)
            ->with(['creator', 'nursingInCharge', 'consultationMedicines.medicine', 'illnesses.system', 'services'])
            ->orderByRaw(ServiceRules::visitDate() . ' DESC')
            ->orderBy('document_issuances.id', 'desc');
    }

    /** The consultations of the filters and the word search, without the relations or the order (the mixed visit list builds on it too). */
    public static function visitsSearched(ReportFilters $filters): Builder
    {
        $query = ConsultationFilter::apply(DocumentIssuance::query(), $filters);

        return SearchTerm::whereAllWords($query, $filters->search, [
            'document_issuances.name',
            'document_issuances.complaints',
            'document_issuances.assessment',
            'document_issuances.plan',
            // The illness picked from the clinic list ("hypertension" finds every visit classified as such).
            fn ($inner, $word) => $inner->whereExists(
                fn ($sub) => $sub->selectRaw('1')->from('consultation_illnesses as ci_search')
                    ->join('illnesses as i_search', 'i_search.id', '=', 'ci_search.illness_id')
                    ->whereColumn('ci_search.document_issuance_id', 'document_issuances.id')
                    ->where('i_search.name', 'like', SearchTerm::like($word))
            ),
        ]);
    }

    public static function inventory(ReportFilters $filters): Builder
    {
        $query = Medicine::query()->with(['category', 'generic', 'batches'])->orderBy('name', 'asc')->orderBy('id', 'asc');

        if ($filters->status === 'low_stock') {
            $query->lowStock();
        }

        return SearchTerm::whereAllWords($query, $filters->search, [
            'name',
            fn ($inner, $word) => $inner->whereHas('category', fn ($q) => $q->where('name', 'like', SearchTerm::like($word))),
            fn ($inner, $word) => $inner->whereHas('generic', fn ($q) => $q->where('name', 'like', SearchTerm::like($word))),
        ]);
    }

    public static function dispensing(ReportFilters $filters): Builder
    {
        $query = MedicineTransaction::query()
            ->with([
                'batch.medicine',
                'user',
                // The record the stock was issued for, with the patient it belonged to (so the report can name them).
                'reference' => function (MorphTo $morph) {
                    $morph->morphWith([
                        Prescription::class => ['patient.user'],
                        DispenseRecord::class => ['patient.user'],
                    ]);
                },
            ])
            ->where('transaction_type', MedicineTransaction::TYPE_DISPENSE)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($filters->dateFrom) {
            $query->whereDate('created_at', '>=', $filters->dateFrom);
        }
        if ($filters->dateTo) {
            $query->whereDate('created_at', '<=', $filters->dateTo);
        }

        // Every word must be found in at least one of these: the medicine (see medicineSearchColumns()), the batch, the person
        // the stock was issued to, who handed it out, and the kind of record.
        return SearchTerm::whereAllWords($query, $filters->search, [
            fn ($inner, $word) => $inner->whereHas('batch.medicine', fn ($medicine) => $medicine->where(
                fn ($group) => SearchTerm::wordInColumns($group, $word, self::medicineSearchColumns())
            )),
            // Strength and batch number of the batch that was handed out (a medicine can have several strengths).
            fn ($inner, $word) => $inner->whereHas('batch', fn ($q) => $q->where('batch_number', 'like', SearchTerm::like($word))
                ->orWhere('dosage', 'like', SearchTerm::like($word))),
            fn ($inner, $word) => $inner->whereHas('user', fn ($user) => $user->where(
                fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)
            )),
            // The patient the stock was issued to and the kind of record ("Consultation", "Prescription", "Dispense Record"):
            // the Stock-out view already resolves both for every ledger row.
            fn ($inner, $word) => $inner->whereExists(
                fn ($sub) => $sub->selectRaw('1')->from('used_medicines_view')
                    ->whereRaw("used_medicines_view.id = CONCAT('T', medicine_transactions.id)")
                    ->where(fn ($view) => $view->where('used_medicines_view.patient_name', 'like', SearchTerm::like($word))
                        ->orWhere('used_medicines_view.source', 'like', SearchTerm::like($word)))
            ),
        ]);
    }

    /**
     * What a word can match on a medicine: its own text columns, and the name of its generic and of its category (both are
     * lists; the plain `generic_name` / `category` / `category_name` columns hold the older free text).
     *
     * @return array<int, string|\Closure>
     */
    public static function medicineSearchColumns(): array
    {
        return [
            'name', 'generic_name', 'brand_name', 'category', 'category_name', 'dosage', 'salt_composition', 'sku',
            fn ($inner, $word) => $inner->whereHas('generic', fn ($q) => $q->where('name', 'like', SearchTerm::like($word))),
            fn ($inner, $word) => $inner->whereHas('category', fn ($q) => $q->where('name', 'like', SearchTerm::like($word))),
        ];
    }

    /**
     * Meds Only: the patients who only came for medicine, newest first. The date range is on the day of the record; the id filters
     * (campus, college, course, year level, patient type) are the ones saved with the record; the medicine filter keeps the records
     * that include that drug; a word is found in the person, the college / course / year / campus text, the note, who recorded it and
     * a drug (see medicineSearchColumns()). A manual dispense record or a prescription's record is never in it.
     */
    public static function medsOnly(ReportFilters $filters): Builder
    {
        return self::medsOnlySearched($filters)
            ->with(['dispenseItems.medicine:id,name,dosage,uom', 'recorder', 'patientType'])
            ->orderByDesc('medicine_bills.bill_date')->orderByDesc('medicine_bills.id');
    }

    /** The Meds Only records of the filters and the word search, without the relations or the order (the mixed visit list builds on it too). */
    public static function medsOnlySearched(ReportFilters $filters): Builder
    {
        $query = self::medsOnlyFiltered($filters);

        return SearchTerm::whereAllWords($query, $filters->search, [
            'medicine_bills.patient_name', 'medicine_bills.campus', 'medicine_bills.college', 'medicine_bills.course', 'medicine_bills.year_level', 'medicine_bills.note',
            fn ($inner, $word) => $inner->whereHas('dispenseItems.medicine', fn ($medicine) => $medicine->where(
                fn ($group) => SearchTerm::wordInColumns($group, $word, self::medicineSearchColumns())
            )),
            fn ($inner, $word) => $inner->whereHas('recorder', fn ($user) => $user->where(
                fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS)
            )),
        ]);
    }

    /**
     * The Meds Only records of the dates, campus, college, course, year level, patient type and medicine filters - without the word
     * search, the order and the relations. The Meds Only tab builds on it, and so does the "Medicine only (no consultation)" line of
     * the Accomplishment Report, so both always count the same records.
     */
    public static function medsOnlyFiltered(ReportFilters $filters): Builder
    {
        $query = DispenseRecord::query()->medsOnly();

        if ($filters->dateFrom) {
            $query->whereDate('medicine_bills.bill_date', '>=', $filters->dateFrom);
        }
        if ($filters->dateTo) {
            $query->whereDate('medicine_bills.bill_date', '<=', $filters->dateTo);
        }
        foreach (['campus_id' => $filters->campusId, 'college_id' => $filters->collegeId, 'course_id' => $filters->courseId,
            'year_level_id' => $filters->yearLevelId, 'patient_type_id' => $filters->patientTypeId] as $column => $value) {
            if ($value) {
                $query->where('medicine_bills.' . $column, $value);
            }
        }
        if ($filters->medicineId) {
            $query->whereHas('dispenseItems', fn ($items) => $items->where('medicine_id', $filters->medicineId));
        }

        return $query;
    }

    public static function appointments(ReportFilters $filters): Builder
    {
        $query = PatientQueue::query()
            ->with(['patient.user', 'addedBy'])
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at', 'asc')
            ->orderBy('id', 'asc');

        if ($filters->dateFrom) {
            $query->whereDate('scheduled_at', '>=', $filters->dateFrom);
        }
        if ($filters->dateTo) {
            $query->whereDate('scheduled_at', '<=', $filters->dateTo);
        }

        return SearchTerm::whereAllWords($query, $filters->search, [
            fn ($inner, $word) => $inner->whereHas('patient.user', fn ($q) => $q->where(fn ($person) => SearchTerm::wordInColumns($person, $word, SearchTerm::PERSON_COLUMNS))),
            'notes',
        ]);
    }

    /**
     * "Consultation #12", "Prescription #7", ... - what a stock-out was issued for.
     */
    public static function referenceLabel(MedicineTransaction $transaction): string
    {
        $label = match ($transaction->reference_type) {
            DocumentIssuance::class, RequestDocuments::class => 'Consultation',
            Prescription::class => 'Prescription',
            DispenseRecord::class => $transaction->reference?->meds_only ? 'Meds only' : 'Dispense record',
            default => $transaction->reference_type ? class_basename($transaction->reference_type) : 'Manual entry',
        };

        return $label . ($transaction->reference_id ? ' #' . $transaction->reference_id : '');
    }

    /**
     * Name of the patient a stock-out belongs to (consultation, prescription or dispense record), if any.
     */
    public static function referencePatientName(MedicineTransaction $transaction): ?string
    {
        $reference = $transaction->reference;

        if ($reference instanceof DocumentIssuance) {
            return $reference->name ?: null;
        }

        if ($reference instanceof Prescription) {
            return $reference->patient?->user?->full_name;
        }

        // A Meds Only record of a person who is not registered has only the typed name.
        if ($reference instanceof DispenseRecord) {
            return $reference->patient?->user?->full_name ?? ($reference->patient_name ?: null);
        }

        return null;
    }
}
