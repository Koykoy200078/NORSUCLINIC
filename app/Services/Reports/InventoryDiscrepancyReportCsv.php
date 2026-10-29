<?php

namespace App\Services\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The MEDICINES WITH NOTES report as a flat CSV: one line per counted medicine of a finalized sheet, the same columns in the same
 * order as the screen, the PDF and the Excel sheet (all from InventoryDiscrepancyReportBuilder).
 */
final class InventoryDiscrepancyReportCsv
{
    /**
     * @param  array<string, mixed>  $report  the result of InventoryDiscrepancyReportBuilder::build()
     * @return array<int, array<int, mixed>>  the header followed by the lines
     */
    public static function rows(array $report): array
    {
        $rows = [['Sheet', 'Day counted', 'Medicine', 'Strength', 'Unit', 'System', 'Counted', 'Difference', 'Reason', 'Note', 'Stock corrected']];

        foreach ($report['rows'] as $row) {
            $rows[] = [$row['sheet'], $row['counted_on'], $row['name'], $row['strength'], $row['unit'], $row['system'], $row['counted'], $row['difference'], $row['reason'], $row['note'], $row['corrected']];
        }

        return $rows;
    }

    /** "medicines_with_notes_2026_2027-02-15_091500.csv" */
    public static function fileName(array $report, string $extension): string
    {
        return 'medicines_with_notes_' . ($report['year'] ?? 'all') . '_' . now()->format('Y-m-d_His') . $extension;
    }

    /** @param  array<string, mixed>  $report */
    public static function response(array $report): StreamedResponse
    {
        $filename = self::fileName($report, '.csv');

        return response()->streamDownload(function () use ($report) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            foreach (self::rows($report) as $row) {
                fputcsv($file, array_map(fn ($cell) => self::safe($cell), $row));
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** A text cell that starts with = + - @ is read as a formula by Excel; an apostrophe makes it text (a number stays a number). */
    private static function safe(mixed $cell): mixed
    {
        return is_string($cell) && $cell !== '' && in_array($cell[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $cell : $cell;
    }
}
