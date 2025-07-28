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

class CustomerDebtsExport implements FromCollection, WithHeadings, WithMapping, WithEvents
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
        // Use Query Builder for a cleaner and more secure approach
        return DB::table('debts as d')
            ->select([
                'd.id',
                'd.amount',
                'd.status',
                'd.notes',
                'd.created_at',
                DB::raw('COALESCE(SUM(dp.amount), 0) as paid_amount'),
                DB::raw('d.amount - COALESCE(SUM(dp.amount), 0) as remaining_amount')
            ])
            ->leftJoin('debt_payments as dp', 'd.id', '=', 'dp.debt_id')
            ->where('d.customer_id', $this->customerId)
            ->where('d.is_active', true)
            ->where('d.status', '<>', 'PAID')
            ->groupBy('d.id', 'd.amount', 'd.status', 'd.notes', 'd.created_at')
            ->orderBy('d.created_at', 'desc')
            ->get();
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Get the data to calculate totals
                $debts = $this->collection();
                $totalAmount = $debts->sum('amount');
                $totalPaid = $debts->sum('paid_amount');
                $totalRemaining = $debts->sum('remaining_amount');
                
                // Get the last row number
                $lastDataRow = $debts->count() + 1; // +1 for header row
                
                // Add total row
                $event->sheet->getDelegate()->setCellValue('A' . ($lastDataRow + 1), 'TOTAL');
                $event->sheet->getDelegate()->setCellValue('C' . ($lastDataRow + 1), $totalAmount);
                $event->sheet->getDelegate()->setCellValue('D' . ($lastDataRow + 1), $totalPaid);
                $event->sheet->getDelegate()->setCellValue('E' . ($lastDataRow + 1), $totalRemaining);
                
                // Style total row
                $event->sheet->getDelegate()->getStyle('A' . ($lastDataRow + 1) . ':G' . ($lastDataRow + 1))
                    ->getFont()->setBold(true);
                
                // Add borders to all data including total row
                $event->sheet->getDelegate()->getStyle('A1:G' . ($lastDataRow + 1))
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Auto-size columns
                foreach (range('A', 'G') as $column) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                }

                // Center align all cells
                $event->sheet->getDelegate()->getStyle('A1:G' . ($lastDataRow + 1))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}