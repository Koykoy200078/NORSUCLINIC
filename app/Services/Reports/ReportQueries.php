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

/**
 * One query per report tab. The Report Generation screen pages these queries and the CSV export streams the very
 * same ones, so the two can never disagree about which rows match the filters.
 */
final class ReportQueries
{
    public const TABS = ['logs', 'visits', 'inventory', 'dispensing', 'appointments'];

    public static function forTab(string $tab, ReportFilters $filters): ?Builder
    {
        return match ($tab) {
            'logs' => self::logs($filters),
            'visits' => self::visits($filters),
            'inventory' => self::inventory($filters),
            'dispensing' => self::dispensing($filters),
            'appointments' => self::appointments($filters),
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
        $query = ConsultationFilter::apply(
            DocumentIssuance::query()->with(['creator', 'nursingInCharge', 'consultationMedicines.medicine', 'illnesses.system', 'services']),
            $filters
        )
            ->orderByRaw(ServiceRules::visitDate() . ' DESC')
            ->orderBy('document_issuances.id', 'desc');

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
            $query->whereRaw('available_quantity <= minimum_stock_alert');
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

        return SearchTerm::whereAllWords($query, $filters->search, [
            fn ($inner, $word) => $inner->whereHas('batch.medicine', fn ($q) => $q->where('name', 'like', SearchTerm::like($word))),
            fn ($inner, $word) => $inner->whereHas('batch', fn ($q) => $q->where('batch_number', 'like', SearchTerm::like($word))),
            // The patient the stock was issued to (consultation, prescription or dispense record): the Stock-out
            // view already resolves it for every ledger row.
            fn ($inner, $word) => $inner->whereExists(
                fn ($sub) => $sub->selectRaw('1')->from('used_medicines_view')
                    ->whereRaw("used_medicines_view.id = CONCAT('T', medicine_transactions.id)")
                    ->where('used_medicines_view.patient_name', 'like', SearchTerm::like($word))
            ),
        ]);
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
            DispenseRecord::class => 'Dispense record',
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

        if ($reference instanceof Prescription || $reference instanceof DispenseRecord) {
            return $reference->patient?->user?->full_name;
        }

        return null;
    }
}
