<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TotalDebtSummaryExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $customerDebts;
    protected $totalRemaining;

    public function __construct($customerDebts, $totalRemaining)
    {
        $this->customerDebts = $customerDebts;
        $this->totalRemaining = $totalRemaining;
    }

    public function collection()
    {
        // Convert the customer debts to a collection
        return collect($this->customerDebts);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Customer',
            'Total Hutang'
        ];
    }

    public function map($customerDebt): array
    {
        static $rowNumber = 1;
        
        return [
            $rowNumber++,
            $customerDebt->customer_name,
            $customerDebt->total_remaining
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Auto-size columns
                foreach (range('A', 'C') as $column) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                }

                // Style the header row
                $event->sheet->getDelegate()->getStyle('A1:C1')
                    ->getFont()->setBold(true);

                // Center align all cells
                $event->sheet->getDelegate()->getStyle('A1:C' . (count($this->customerDebts) + 2))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add total row
                $lastRow = count($this->customerDebts) + 2;
                $event->sheet->getDelegate()->setCellValue('A' . $lastRow, 'TOTAL');
                $event->sheet->getDelegate()->setCellValue('C' . $lastRow, $this->totalRemaining);
                
                // Style total row
                $event->sheet->getDelegate()->getStyle('A' . $lastRow . ':C' . $lastRow)
                    ->getFont()->setBold(true);
                
                // Add borders
                $event->sheet->getDelegate()->getStyle('A1:C' . $lastRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        ];
    }
}
