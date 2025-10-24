<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user')->orderBy('created_at', 'desc');

        // Filter by user type
        if ($request->filled('user_type') && $request->user_type !== 'all') {
            $query->where('user_type', $request->user_type);
        }

        // Filter by action
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Search by patient name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $activityLogs = $query->paginate(20);

        // Get unique actions for filter dropdown (exclude 'created_patient')
        $actions = ActivityLog::select('action')
            ->distinct()
            ->whereNotIn('action', ['created_patient'])
            ->pluck('action')
            ->toArray();

        return view('activity_logs.index', compact('activityLogs', 'actions'));
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
     * Export activity logs to CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::query()->with('user')->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('user_type') && $request->user_type !== 'all') {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $activityLogs = $query->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($activityLogs) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Date',
                'Time',
                'User',
                'User Type',
                'Action',
                'Patient Name',
                'Age',
                'Gender',
                'College',
                'Course/Section',
                'Address',
                'Contact Number',
                'Complaints',
                'Diagnosis',
                'Informant',
                'Consult Mode',
                'Description',
            ]);

            // CSV Data
            foreach ($activityLogs as $log) {
                fputcsv($file, [
                    $log->date ? $log->date->format('Y-m-d') : $log->created_at->format('Y-m-d'),
                    $log->created_at->format('H:i:s'),
                    $log->user_name,
                    $log->formatted_user_type,
                    $log->formatted_action,
                    $log->patient_name,
                    $log->patient_age,
                    $log->patient_gender,
                    $log->college,
                    $log->course_section,
                    $log->address,
                    $log->contact_number,
                    $log->complaints,
                    $log->diagnosis,
                    $log->informant,
                    $log->consult_mode,
                    $log->description,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
