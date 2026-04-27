<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\MedicineBatch;
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
                    $query = ActivityLog::query()->orderBy('created_at', 'desc');
                    if ($request->filled('user_type') && $request->user_type !== 'all') $query->where('user_type', $request->user_type);
                    if ($request->filled('action') && $request->action !== 'all') $query->where('action', $request->action);
                    if ($dateFrom) $query->where('date', '>=', $dateFrom);
                    if ($dateTo) $query->where('date', '<=', $dateTo);
                    if ($search) $query->where('patient_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                    
                    $query->chunk(100, function ($logs) use ($file) {
                        foreach ($logs as $log) {
                            fputcsv($file, [
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
                    $query = DocumentIssuance::where('document_type', 'consultation_form')->orderBy('created_at', 'desc');
                    if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
                    if ($search) $query->where('name', 'like', "%{$search}%")->orWhere('complaints', 'like', "%{$search}%");
                    
                    $query->chunk(100, function ($records) use ($file) {
                        foreach ($records as $r) {
                            fputcsv($file, [$r->created_at->format('Y-m-d H:i'), $r->name, $r->age, $r->gender, $r->complaints, $r->assessment, $r->plan, $r->creator->full_name ?? 'System']);
                        }
                    });
                    break;

                case 'inventory':
                    fputcsv($file, ['Medicine Name', 'Category', 'Generic', 'Current Quantity', 'Min Alert', 'Status']);
                    $query = Medicine::with(['category', 'generic'])->orderBy('name', 'asc');
                    if ($search) $query->where('name', 'like', "%{$search}%");
                    
                    $query->chunk(100, function ($medicines) use ($file) {
                        foreach ($medicines as $m) {
                            $status = $m->available_quantity <= 0 ? 'Out of Stock' : ($m->available_quantity <= $m->minimum_stock_alert ? 'Low Stock' : 'Healthy');
                            fputcsv($file, [$m->name, $m->category->name ?? 'N/A', $m->generic->name ?? 'N/A', $m->available_quantity, $m->minimum_stock_alert, $status]);
                        }
                    });
                    break;

                case 'dispensing':
                    fputcsv($file, ['Date', 'Medicine', 'Quantity Used', 'Reference Type', 'Reference ID']);
                    $query = UsedMedicine::with('medicine')->orderBy('created_at', 'desc');
                    if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
                    
                    $query->chunk(100, function ($dispenses) use ($file) {
                        foreach ($dispenses as $d) {
                            fputcsv($file, [$d->created_at->format('Y-m-d H:i'), $d->medicine->name ?? 'N/A', $d->stock_used, $d->model_type, $d->model_id]);
                        }
                    });
                    break;

                case 'appointments':
                    fputcsv($file, ['Scheduled At', 'Patient Name', 'Added By', 'Status', 'Notes']);
                    $query = PatientQueue::with(['patient', 'addedBy'])->whereNotNull('scheduled_at')->orderBy('scheduled_at', 'asc');
                    if ($dateFrom) $query->whereDate('scheduled_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('scheduled_at', '<=', $dateTo);
                    
                    $query->chunk(100, function ($apps) use ($file) {
                        foreach ($apps as $a) {
                            fputcsv($file, [$a->scheduled_at->format('Y-m-d H:i'), $a->patient->user->full_name ?? 'Unknown', $a->addedBy->full_name ?? 'System', $a->status, $a->notes]);
                        }
                    });
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
