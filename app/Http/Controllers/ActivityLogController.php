<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\Prescription;
use App\Models\PatientQueue;
use App\Models\UsedMedicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs and clinical reports
     */
    public function index(Request $request)
    {
        return view('activity_logs.index');
    }

    /**
     * Display the specified activity log
     */
    public function show($id)
    {
        $activityLog = ActivityLog::with('user')->findOrFail($id);

        return view('activity_logs.show', compact('activityLog'));
    }

    /**
     * Export activity logs or clinical reports to CSV
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'logs');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $filename = 'report_' . $tab . '_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tab, $search, $dateFrom, $dateTo, $request) {
            $file = fopen('php://output', 'w');

            switch ($tab) {
                case 'logs':
                    fputcsv($file, ['Date', 'Time', 'User', 'User Type', 'Action', 'Patient Name', 'Description']);
                    $query = ActivityLog::query()->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                    if ($request->filled('user_type') && $request->user_type !== 'all') $query->where('user_type', $request->user_type);
                    if ($request->filled('action') && $request->action !== 'all') $query->where('action', $request->action);
                    if ($dateFrom) $query->where('date', '>=', $dateFrom);
                    if ($dateTo) $query->where('date', '<=', $dateTo);
                    // The two search conditions must be grouped: a bare orWhere() made a search term
                    // override the date / user / action filters above. M-04.
                    if ($search) $query->where(function ($q) use ($search) {
                        $q->where('patient_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                    });

                    $query->chunk(100, function ($logs) use ($file) {
                        foreach ($logs as $log) {
                            $this->putCsvRow($file, [
                                $log->date ? $log->date->format('Y-m-d') : $log->created_at->format('Y-m-d'),
                                $log->created_at->format('H:i:s'),
                                $log->user_name,
                                $log->formatted_user_type,
                                $log->formatted_action,
                                $log->patient_name,
                                $log->description,
                            ]);
                        }
                    });
                    break;

                case 'visits':
                    fputcsv($file, ['Date', 'Patient Name', 'Age', 'Gender', 'Complaints', 'Assessment', 'Plan', 'Encoder']);
                    $query = DocumentIssuance::with('creator')->where('document_type', 'consultation_form')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                    if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
                    // Grouped, otherwise "OR complaints LIKE" also pulled in medical certificates and
                    // ignored the document-type and date filters. M-04.
                    if ($search) $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('complaints', 'like', "%{$search}%");
                    });

                    $query->chunk(100, function ($records) use ($file) {
                        foreach ($records as $r) {
                            $this->putCsvRow($file, [$r->created_at->format('Y-m-d H:i'), $r->name, $r->age, $r->gender, $r->complaints, $r->assessment, $r->plan, $r->creator->full_name ?? 'System']);
                        }
                    });
                    break;

                case 'inventory':
                    fputcsv($file, ['Medicine Name', 'Category', 'Generic', 'Current Quantity', 'Min Alert', 'Status']);
                    $query = Medicine::with(['category', 'generic'])->orderBy('name', 'asc')->orderBy('id', 'asc');
                    if ($search) $query->where('name', 'like', "%{$search}%");

                    $query->chunk(100, function ($medicines) use ($file) {
                        foreach ($medicines as $m) {
                            $status = $m->available_quantity <= 0 ? 'Out of Stock' : ($m->available_quantity <= $m->minimum_stock_alert ? 'Low Stock' : 'Healthy');
                            $this->putCsvRow($file, [$m->name, $m->category->name ?? 'N/A', $m->generic->name ?? 'N/A', $m->available_quantity, $m->minimum_stock_alert, $status]);
                        }
                    });
                    break;

                case 'dispensing':
                    fputcsv($file, ['Date', 'Medicine', 'Dosage', 'Batch', 'Quantity', 'Reference Type', 'Reference ID', 'Dispensed By']);
                    // Same source as the on-screen tab: the stock ledger, not the legacy used_medicines
                    // table that nothing writes to any more. M-04.
                    $query = MedicineTransaction::with(['batch.medicine', 'user'])
                        ->where('transaction_type', MedicineTransaction::TYPE_DISPENSE)
                        ->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                    if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
                    if ($search) $query->whereHas('batch.medicine', fn ($q) => $q->where('name', 'like', "%{$search}%"));

                    $query->chunk(100, function ($dispenses) use ($file) {
                        foreach ($dispenses as $d) {
                            $this->putCsvRow($file, [
                                $d->created_at->format('Y-m-d H:i'),
                                $d->batch?->medicine?->name ?? 'N/A',
                                $d->batch?->dosage ?? 'N/A',
                                $d->batch?->batch_number ?? 'N/A',
                                $d->quantity,
                                $d->reference_type ? class_basename($d->reference_type) : '',
                                $d->reference_id,
                                $d->user?->full_name ?? 'N/A',
                            ]);
                        }
                    });
                    break;

                case 'appointments':
                    fputcsv($file, ['Scheduled At', 'Patient Name', 'Added By', 'Status', 'Notes']);
                    $query = PatientQueue::with(['patient.user', 'addedBy'])->whereNotNull('scheduled_at')->orderBy('scheduled_at', 'asc')->orderBy('id', 'asc');
                    if ($dateFrom) $query->whereDate('scheduled_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('scheduled_at', '<=', $dateTo);
                    
                    $query->chunk(100, function ($apps) use ($file) {
                        foreach ($apps as $a) {
                            $this->putCsvRow($file, [$a->scheduled_at->format('Y-m-d H:i'), $a->patient->user->full_name ?? 'Unknown', $a->addedBy->full_name ?? 'System', $a->status, $a->notes]);
                        }
                    });
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Write one CSV row, neutralising spreadsheet formulas: a cell that starts with = + - @ (or a
     * tab / carriage return) is executed by Excel when the file is opened, and patient names, notes
     * and descriptions are free text. Prefixing an apostrophe makes Excel treat it as text. M-04.
     */
    private function putCsvRow($file, array $row): void
    {
        fputcsv($file, array_map(function ($cell) {
            if (is_string($cell) && $cell !== '' && in_array($cell[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                return "'" . $cell;
            }

            return $cell;
        }, $row));
    }
}
