<?php

namespace App\Services\Reports;

use App\Models\ActivityLog;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\MedicineTransaction;
use App\Models\PatientQueue;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV download of a report tab. The rows come from the same ReportQueries the screen pages, with the same
 * filters, and every tab carries the names the screen shows. The file starts with a UTF-8 byte-order mark so
 * Excel reads names such as "Peña" correctly instead of showing garbled letters.
 */
final class ReportCsv
{
    public static function response(string $tab, ReportFilters $filters): StreamedResponse
    {
        $query = ReportQueries::forTab($tab, $filters);
        abort_if($query === null, 404, 'Unknown report.');

        $filename = 'report_' . $tab . '_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($tab, $query) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            self::put($file, self::headings($tab));

            $query->chunk(200, function ($records) use ($file, $tab) {
                foreach ($records as $record) {
                    self::put($file, self::row($tab, $record));
                }
            });

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<int, string> */
    public static function headings(string $tab): array
    {
        return match ($tab) {
            'logs' => ['Date', 'Time', 'User', 'User Type', 'Action', 'Patient Name', 'Description'],
            'visits' => [
                'Date', 'Patient Name', 'Address', 'Age', 'Gender', 'Patient Type', 'Campus', 'College', 'Course', 'Year Level',
                'Illness', 'Services', 'Complaints', 'Assessment', 'Plan', 'Consult Mode', 'Nurse in Charge', 'Encoder',
            ],
            'inventory' => ['Medicine Name', 'Category', 'Generic', 'Current Quantity', 'Reorder At', 'Nearest Expiry', 'Status'],
            'dispensing' => ['Date', 'Medicine', 'Dosage', 'Batch', 'Quantity', 'Associated Record', 'Patient Name', 'Dispensed By'],
            'appointments' => ['Scheduled At', 'Patient Name', 'Added By', 'Status', 'Notes'],
            default => [],
        };
    }

    /**
     * @return array<int, mixed>
     */
    public static function row(string $tab, $record): array
    {
        return match ($tab) {
            'logs' => self::logRow($record),
            'visits' => self::visitRow($record),
            'inventory' => self::inventoryRow($record),
            'dispensing' => self::dispensingRow($record),
            'appointments' => self::appointmentRow($record),
            default => [],
        };
    }

    /** @return array<int, mixed> */
    private static function logRow(ActivityLog $log): array
    {
        return [
            $log->date ? $log->date->format('Y-m-d') : $log->created_at->format('Y-m-d'),
            $log->created_at->format('H:i:s'),
            $log->user_name,
            $log->formatted_user_type,
            $log->formatted_action,
            $log->patient_name,
            $log->description,
        ];
    }

    /** @return array<int, mixed> */
    private static function visitRow(DocumentIssuance $visit): array
    {
        return [
            ($visit->requested_at ?? $visit->created_at)->format('Y-m-d'),
            $visit->name,
            $visit->address,
            $visit->age,
            $visit->gender,
            $visit->informant,
            $visit->campus,
            $visit->college,
            $visit->course,
            $visit->year_level,
            implode('; ', $visit->illnessLabels()),
            implode('; ', $visit->serviceLabels()),
            $visit->complaints,
            $visit->assessment,
            $visit->plan,
            match ($visit->consult_mode) {
                'physical' => 'Walk-in',
                'virtual' => 'Virtual',
                default => '',
            },
            $visit->nursingInCharge?->full_name,
            $visit->creator->full_name ?? 'System',
        ];
    }

    /** @return array<int, mixed> */
    private static function inventoryRow(Medicine $medicine): array
    {
        $nearest = $medicine->batches->where('quantity', '>', 0)->sortBy('expiration_date')->first();

        return [
            $medicine->name,
            $medicine->category->name ?? 'N/A',
            $medicine->generic->name ?? 'N/A',
            $medicine->available_quantity,
            $medicine->minimum_stock_alert,
            $nearest?->expiration_date?->format('Y-m-d') ?? 'No active batches',
            self::stockStatus($medicine),
        ];
    }

    public static function stockStatus(Medicine $medicine): string
    {
        return $medicine->available_quantity <= 0
            ? 'Out of Stock'
            : ($medicine->available_quantity <= $medicine->minimum_stock_alert ? 'Low Stock' : 'Healthy');
    }

    /** @return array<int, mixed> */
    private static function dispensingRow(MedicineTransaction $entry): array
    {
        return [
            $entry->created_at->format('Y-m-d H:i'),
            $entry->batch?->medicine?->name ?? 'Deleted Medicine',
            $entry->batch?->dosage ?: 'N/A',
            $entry->batch?->batch_number ?? 'N/A',
            $entry->quantity,
            ReportQueries::referenceLabel($entry),
            ReportQueries::referencePatientName($entry) ?? 'N/A',
            $entry->user?->full_name ?? 'N/A',
        ];
    }

    /** @return array<int, mixed> */
    private static function appointmentRow(PatientQueue $appointment): array
    {
        return [
            $appointment->scheduled_at->format('Y-m-d H:i'),
            $appointment->patient?->user?->full_name ?? 'Unknown',
            $appointment->addedBy?->full_name ?? 'System',
            $appointment->status,
            $appointment->notes,
        ];
    }

    /**
     * Write one CSV row, neutralising spreadsheet formulas: a cell that starts with = + - @ (or a tab / carriage
     * return) is executed by Excel when the file is opened, and patient names, notes and descriptions are free
     * text. Prefixing an apostrophe makes Excel treat it as text. M-04.
     *
     * @param  resource  $file
     * @param  array<int, mixed>  $row
     */
    private static function put($file, array $row): void
    {
        fputcsv($file, array_map(function ($cell) {
            if (is_string($cell) && $cell !== '' && in_array($cell[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                return "'" . $cell;
            }

            return $cell;
        }, $row));
    }
}
