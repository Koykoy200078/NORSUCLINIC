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
use App\Exports\AccomplishmentReportExport;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\AccomplishmentReportCsv;
use App\Services\Reports\ReportCsv;
use App\Services\Reports\ReportFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

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
        abort_unless(canViewActivityLogTab('logs'), 403, 'You are not allowed to view the activity log.');

        $activityLog = ActivityLog::with('user')->findOrFail($id);

        return view('activity_logs.show', compact('activityLog'));
    }

    /**
     * Export activity logs or clinical reports to CSV.
     *
     * The rows come from the same queries, with the same filters, as the Report Generation screen
     * (App\Services\Reports\ReportQueries), so the file always holds what the screen shows, patient names included.
     */
    public function export(Request $request)
    {
        $tab = (string) $request->get('tab', 'logs');
        abort_unless(canViewActivityLogTab($tab), 403, 'You are not allowed to export this report.');

        return ReportCsv::response($tab, ReportFilters::fromRequest($request));
    }

    /**
     * The ACCOMPLISHMENT REPORT as a PDF, an Excel sheet or a CSV. All three are built from the same
     * AccomplishmentReportBuilder result, with the filters of the screen, so they hold the same numbers.
     * "Prepared by" is the signed-in user; "Noted by" is the University Physician saved in Settings.
     */
    public function accomplishment(Request $request, string $format)
    {
        abort_unless(canViewActivityLogTab('accomplishment'), 403, 'You are not allowed to export this report.');
        abort_unless(in_array($format, ['pdf', 'xlsx', 'csv'], true), 404, 'Unknown format.');

        $report = app(AccomplishmentReportBuilder::class)->build(ReportFilters::fromRequest($request), $request->user());
        $name = 'accomplishment_report_' . now()->format('Y-m-d_His');

        return match ($format) {
            'csv' => AccomplishmentReportCsv::response($report),
            'xlsx' => Excel::download(new AccomplishmentReportExport($report), $name . '.xlsx'),
            // 8.5" x 13" (long bond) portrait, the paper the clinic prints its other forms on.
            'pdf' => Pdf::loadView('activity_logs.reports.accomplishment_pdf', ['report' => $report])
                ->setPaper([0, 0, 612, 936], 'portrait')
                ->setWarnings(false)
                ->stream($name . '.pdf'),
        };
    }
}
