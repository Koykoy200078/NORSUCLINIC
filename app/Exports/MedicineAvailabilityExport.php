<?php

namespace App\Exports;

use App\Models\StockIn;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class MedicineAvailabilityExport implements WithMultipleSheets
{
    /**
     * Generate multiple sheets for the Excel file.
     */
    public function sheets(): array
    {
        $sheets = [];

        // Group data by `created_at` date
        $groupedData = StockIn::all()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d'); // Group by date
        });

        // Create a sheet for each date
        foreach ($groupedData as $date => $data) {
            $sheets[] = new class($date, $data) implements FromView, WithTitle, ShouldAutoSize {
                protected $date;
                protected $data;

                /**
                 * Constructor to pass the date and data for the sheet.
                 */
                public function __construct($date, $data)
                {
                    $this->date = $date;
                    $this->data = $data;
                }

                /**
                 * Fetch the data and pass it to the view.
                 */
                public function view(): View
                {
                    return view('medicine-availabilities.medicine-availability', [
                        'purchaseMedicines' => $this->data,
                    ]);
                }

                /**
                 * Set the title of the sheet.
                 */
                public function title(): string
                {
                    return 'Report - ' . $this->date;
                }
            };
        }

        return $sheets;
    }
}
