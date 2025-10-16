<?php

if (!function_exists('parseExaminedOnDate')) {
    /**
     * Parse the examined_on date field which can contain:
     * - Single date: "2025-10-16"
     * - Date range: "2025-10-12|2025-10-16|range"
     * - Multiple dates: "2025-10-12,2025-10-14,2025-10-16|multiple"
     *
     * @param string|null $dateString
     * @return array
     */
    function parseExaminedOnDate($dateString)
    {
        if (empty($dateString)) {
            return [
                'type' => 'none',
                'display' => 'N/A',
                'dates' => []
            ];
        }

        // Check for date range format
        if (strpos($dateString, '|range') !== false) {
            $parts = explode('|', $dateString);
            $startDate = $parts[0] ?? '';
            $endDate = $parts[1] ?? '';

            return [
                'type' => 'range',
                'start' => $startDate,
                'end' => $endDate,
                'display' => formatDateForDisplay($startDate) . ' - ' . formatDateForDisplay($endDate),
                'dates' => [$startDate, $endDate]
            ];
        }

        // Check for multiple dates format
        if (strpos($dateString, '|multiple') !== false) {
            $parts = explode('|', $dateString);
            $datesString = $parts[0] ?? '';
            $dates = explode(',', $datesString);

            $formattedDates = array_map(function ($date) {
                return formatDateForDisplay($date);
            }, $dates);

            return [
                'type' => 'multiple',
                'dates' => $dates,
                'display' => implode(', ', $formattedDates),
                'count' => count($dates)
            ];
        }

        // Single date format
        return [
            'type' => 'single',
            'date' => $dateString,
            'display' => formatDateForDisplay($dateString),
            'dates' => [$dateString]
        ];
    }
}

if (!function_exists('formatDateForDisplay')) {
    /**
     * Format a date string (Y-m-d) to display format (m/d/Y)
     *
     * @param string $dateString
     * @return string
     */
    function formatDateForDisplay($dateString)
    {
        if (empty($dateString)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($dateString)->format('m/d/Y');
        } catch (\Exception $e) {
            return $dateString;
        }
    }
}

if (!function_exists('formatExaminedOnForPDF')) {
    /**
     * Format examined_on date for PDF display
     *
     * @param string|null $dateString
     * @return string
     */
    function formatExaminedOnForPDF($dateString)
    {
        $parsed = parseExaminedOnDate($dateString);

        if ($parsed['type'] === 'range') {
            return \Carbon\Carbon::parse($parsed['start'])->format('F j, Y') . ' - ' .
                \Carbon\Carbon::parse($parsed['end'])->format('F j, Y');
        }

        if ($parsed['type'] === 'multiple') {
            $formattedDates = array_map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('F j, Y');
            }, $parsed['dates']);
            return implode(', ', $formattedDates);
        }

        if ($parsed['type'] === 'single' && !empty($parsed['date'])) {
            return \Carbon\Carbon::parse($parsed['date'])->format('F j, Y');
        }

        return 'N/A';
    }
}
