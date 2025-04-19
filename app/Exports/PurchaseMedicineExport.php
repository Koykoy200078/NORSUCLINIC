<?php

namespace App\Exports;

use App\Models\PurchaseMedicine;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchaseMedicineExport implements WithMultipleSheets
{
    /**
     * Generate multiple sheets for the Excel file.
     */
    public function sheets(): array
    {
        $sheets = [];

        // Group data by `created_at` date
        $groupedData = PurchaseMedicine::all()->groupBy(function ($item) {
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
                    return view('purchase-medicines.purchase-medicine', [
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

class PurchaseMedicineSheet implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
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
        return view('purchase-medicines.purchase-medicine', [
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

    /**
     * Register events for styling, formatting, and page breaks.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Center-align all headers and data
                $rowCount = $sheet->getHighestRow();
                $sheet->getStyle('A1:G' . $rowCount)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Style the header row
                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], // White text
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4CAF50'], // Green background
                    ],
                ]);

                // Auto-fit rows
                foreach (range(1, $rowCount) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1); // Auto-fit row height
                }

                // Style the total row
                $sheet->getStyle('A' . $rowCount . ':G' . $rowCount)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFFC107'], // Yellow background
                    ],
                ]);
            },
        ];
    }
}
