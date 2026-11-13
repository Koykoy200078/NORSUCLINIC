<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Text that ends up in a CSV or Excel file the clinic opens in a spreadsheet. A cell that starts with = + - @ (or a tab or
 * a carriage return) is run as a formula when the file is opened, and patient names, notes and descriptions are typed by
 * people; an apostrophe in front makes the spreadsheet show it as text. Numbers stay numbers. (M-04; one copy since the
 * re-audit of 2026-11-13, DRY: the check was written out in eight places.)
 */
final class SpreadsheetText
{
    /** @var list<string> */
    private const FORMULA_STARTS = ['=', '+', '-', '@', "\t", "\r"];

    public static function safe(mixed $cell): mixed
    {
        return is_string($cell) && $cell !== '' && in_array($cell[0], self::FORMULA_STARTS, true) ? "'" . $cell : $cell;
    }

    /**
     * Every text in a (nested) report array made safe, for the Excel exports rendered from a Blade table.
     *
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    public static function safeAll(array $values): array
    {
        array_walk_recursive($values, function (&$value) {
            $value = self::safe($value);
        });

        return $values;
    }

    /**
     * @param  resource  $file
     * @param  array<int, mixed>  $row
     */
    public static function putCsvRow($file, array $row): void
    {
        fputcsv($file, array_map(fn ($cell) => self::safe($cell), $row), ',', '"', '\\');
    }

    /**
     * A CSV download that Excel opens as UTF-8 (byte order mark first). The rows are built while the file streams.
     *
     * @param  callable(): iterable<array<int, mixed>>  $rows
     */
    public static function csvDownload(string $filename, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($rows() as $row) {
                self::putCsvRow($file, $row);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
