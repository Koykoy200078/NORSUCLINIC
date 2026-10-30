<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * The MEDS ONLY report as an Excel sheet, rendered from the same Blade tables as the screen and the PDF
 * (activity_logs.reports.meds_only_table / meds_only_totals), so the three always show the same figures.
 */
class MedsOnlyReportExport implements FromView, ShouldAutoSize, WithTitle
{
    /** @var array<string, mixed> */
    private array $report;

    /** @param  array<string, mixed>  $report  the result of MedsOnlyReportBuilder::build() */
    public function __construct(array $report)
    {
        // Names and notes are typed by people: a text that starts with = + - @ would be run as a formula when the sheet is
        // opened, so it is made plain text, as in the CSV. (Only text: the numbers of the report stay numbers.)
        array_walk_recursive($report, function (&$value) {
            if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                $value = "'" . $value;
            }
        });

        $this->report = $report;
    }

    public function view(): View
    {
        return view('activity_logs.reports.meds_only_sheet', ['report' => $this->report]);
    }

    public function title(): string
    {
        return 'Meds Only';
    }
}
