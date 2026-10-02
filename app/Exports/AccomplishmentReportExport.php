<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * The ACCOMPLISHMENT REPORT as an Excel sheet. It is rendered from the same Blade tables as the screen and the PDF
 * (activity_logs.reports.accomplishment_sheet), so the three always show the same numbers.
 */
class AccomplishmentReportExport implements FromView, ShouldAutoSize, WithTitle
{
    /** @var array<string, mixed> */
    private array $report;

    /** @param  array<string, mixed>  $report  the result of AccomplishmentReportBuilder::build() */
    public function __construct(array $report)
    {
        // Names (the signatories, list lines) are typed by people: a text that starts with = + - @ would be run as a
        // formula when the sheet is opened, so it is made plain text, as in the CSV.
        array_walk_recursive($report, function (&$value) {
            if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                $value = "'" . $value;
            }
        });

        $this->report = $report;
    }

    public function view(): View
    {
        return view('activity_logs.reports.accomplishment_sheet', ['report' => $this->report]);
    }

    public function title(): string
    {
        return 'Accomplishment Report';
    }
}
