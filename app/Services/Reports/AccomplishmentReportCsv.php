<?php

namespace App\Services\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The ACCOMPLISHMENT REPORT as a flat CSV: one line per illness and per service, one column per college, so it can
 * be filtered and totalled in a spreadsheet. Same numbers as the screen, PDF and Excel (all from the builder).
 */
final class AccomplishmentReportCsv
{
    /**
     * @param  array<string, mixed>  $report  the result of AccomplishmentReportBuilder::build()
     * @return array<int, array<int, mixed>>  the header followed by the lines
     */
    public static function rows(array $report): array
    {
        $keys = array_column($report['columns'], 'key');
        $counts = fn (array $counts) => array_map(fn ($key) => (int) ($counts[$key] ?? 0), $keys);

        $rows = [array_merge(['Section', 'Body system / category', 'Group', 'Item'], array_column($report['columns'], 'label'), ['Total'])];

        foreach ($report['illness_sections'] as $section) {
            foreach ($section['groups'] as $group) {
                foreach ($group['rows'] as $row) {
                    $rows[] = array_merge(['Illness / diagnosis', $section['name'], $group['label'] ?? '', $row['name']], $counts($row['counts']), [$row['total']]);
                }
            }
        }
        $rows[] = array_merge(['Illness / diagnosis', 'TOTAL', '', ''], $counts($report['illness_total']['counts']), [$report['illness_total']['total']]);

        foreach ($report['service_sections'] as $section) {
            foreach ($section['rows'] as $row) {
                $rows[] = array_merge(['Other services', $section['name'], '', $row['name']], $counts($row['counts']), [$row['total']]);
            }
        }
        $rows[] = array_merge(['Other services', 'TOTAL', '', ''], $counts($report['service_total']['counts']), [$report['service_total']['total']]);

        $blank = array_fill(0, count($keys), '');
        $rows[] = array_merge(['Summary', 'Consultations in this report', '', ''], $blank, [$report['consultations']]);
        $rows[] = array_merge(['Summary', 'Unclassified consultations (no illness picked)', '', ''], $blank, [$report['unclassified']]);

        return $rows;
    }

    /** @param  array<string, mixed>  $report */
    public static function response(array $report): StreamedResponse
    {
        $filename = 'accomplishment_report_' . now()->format('Y-m-d_His') . '.csv';

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
