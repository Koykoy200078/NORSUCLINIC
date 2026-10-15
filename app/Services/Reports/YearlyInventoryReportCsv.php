<?php

namespace App\Services\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The YEARLY MEDICINE INVENTORY as a flat CSV: one line per medicine, the same columns in the same order as the
 * screen, the PDF and the Excel sheet (all from YearlyInventoryReportBuilder). A quarter that has not started is empty.
 */
final class YearlyInventoryReportCsv
{
    /**
     * @param  array<string, mixed>  $report  the result of YearlyInventoryReportBuilder::build()
     * @return array<int, array<int, mixed>>  the header followed by the lines
     */
    public static function rows(array $report): array
    {
        $quarters = $report['quarters'];

        $rows = [array_merge(
            ['Medicine', 'Generic', 'Strength', 'Unit', 'Left from last year', 'Additional'],
            array_map(fn (array $quarter) => $quarter['label'] . ' remaining' . ($quarter['to_date'] ? ' (to date)' : ''), $quarters),
            ['Notes']
        )];

        foreach ($report['rows'] as $row) {
            $rows[] = array_merge(
                [$row['name'], $row['generic'], $row['strength'], $row['unit'], $row['left'], $row['additional']],
                array_map(fn (array $quarter) => $row['quarters'][$quarter['key']]['remaining'] ?? '', $quarters),
                [$row['notes']]
            );
        }

        return $rows;
    }

    /** "medicine_inventory_2026_2027-02-15_091500.csv" */
    public static function fileName(array $report, string $extension): string
    {
        return 'medicine_inventory_' . $report['year'] . '_' . now()->format('Y-m-d_His') . $extension;
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

    /** A cell that starts with = + - @ is read as a formula by Excel; an apostrophe makes it text. */
    private static function safe(mixed $cell): mixed
    {
        return is_string($cell) && $cell !== '' && in_array($cell[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $cell : $cell;
    }
}
