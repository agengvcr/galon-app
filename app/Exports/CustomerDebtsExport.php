<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomerDebtsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $customerId;
    protected $customerName;

    public function __construct($customerId, $customerName = null)
    {
        $this->customerId = $customerId;
        $this->customerName = $customerName;
    }

    public function collection()
    {
        $debts = DB::select(
            "SELECT 
                d.id,
                d.amount,
                d.status,
                d.notes,
                d.created_at,
                COALESCE(dp.total_paid, 0) as paid_amount,
                d.amount - COALESCE(dp.total_paid, 0) as remaining_amount
            FROM debts d
            LEFT JOIN (
                SELECT 
                    debt_id,
                    SUM(amount) as total_paid
                FROM debt_payments
                GROUP BY debt_id
            ) dp ON d.id = dp.debt_id
            WHERE d.customer_id = ? 
            AND d.is_active = true
            AND d.status <> 'PAID'
            ORDER BY d.created_at DESC",
            [$this->customerId]
        );
        
        return collect($debts);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Hutang',
            'Jumlah Hutang',
            'Dibayar',
            'Sisa Hutang',
            'Status',
            'Catatan'
        ];
    }

    public function map($debt): array
    {
        static $rowNumber = 1;
        
        return [
            $rowNumber++,
            date('d/m/Y', strtotime($debt->created_at)),
            $debt->amount,
            $debt->paid_amount,
           $debt->remaining_amount,
            $debt->status,
            $debt->notes ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders to all cells
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align number columns
        $sheet->getStyle("C2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add customer name as title
        if ($this->customerName) {
            $sheet->insertNewRowBefore(1, 2);
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'HUTANG PELANGGAN: ' . strtoupper($this->customerName));
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            
            // Adjust row numbers
            $sheet->getStyle("A3:G3")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // No
            'B' => 15, // Tanggal
            'C' => 15, // Jumlah Hutang
            'D' => 15, // Dibayar
            'E' => 15, // Sisa
            'F' => 15, // Status
            'G' => 30, // Catatan
        ];
    }
} 