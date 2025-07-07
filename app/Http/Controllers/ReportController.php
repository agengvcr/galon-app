<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function transactionChart()
    {
        return view('reports.transaction-chart');
    }

    public function getChartData()
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $sql = "
            SELECT 
                transaction_date::date as date,
                SUM(galon_in) as galon_in,
                SUM(galon_out) as galon_out
            FROM transactions
            WHERE EXTRACT(YEAR FROM transaction_date) = ?
            AND EXTRACT(MONTH FROM transaction_date) = ?
            AND is_active = TRUE
            GROUP BY transaction_date::date
            ORDER BY date
        ";

        $transactions = DB::select($sql, [$currentYear, $currentMonth]);

        return response()->json([
            'labels' => array_column($transactions, 'date'),
            'galon_in' => array_column($transactions, 'galon_in'),
            'galon_out' => array_column($transactions, 'galon_out')
        ]);
    }

    public function priceChart()
    {
        return view('reports.price-chart');
    }

    public function getPriceChartData()
    {
        $sql = "
            SELECT 
                TO_CHAR(transaction_date, 'YYYY-MM') as month,
                SUM(total_price) as total_price,
                COUNT(*) as transaction_count
            FROM transactions 
            WHERE transaction_date >= (CURRENT_DATE - INTERVAL '3 months')
                AND is_active = TRUE
            GROUP BY TO_CHAR(transaction_date, 'YYYY-MM')
            ORDER BY month ASC
        ";

        $transactions = DB::select($sql);

        return response()->json([
            'labels' => array_map(function($item) {
                return Carbon::createFromFormat('Y-m', $item->month)->format('F Y');
            }, $transactions),
            'total_price' => array_column($transactions, 'total_price'),
            'transaction_count' => array_column($transactions, 'transaction_count')
        ]);
    }

    public function monthlyReport()
    {
        $sql = "
            SELECT 
                TO_CHAR(transaction_date, 'YYYY-MM') as month,
                SUM(total_price) as total_price,
                COUNT(*) as transaction_count,
                SUM(galon_in) as total_galon_in,
                SUM(galon_out) as total_galon_out
            FROM transactions 
            WHERE transaction_date >= (CURRENT_DATE - INTERVAL '3 months')
                AND is_active = TRUE
            GROUP BY TO_CHAR(transaction_date, 'YYYY-MM')
            ORDER BY month DESC
        ";

        $monthlyData = DB::select($sql);
        return view('reports.monthly-table', compact('monthlyData'));
    }

    public function getMonthlyTableData()
    {
        $sql = "
            SELECT 
                TO_CHAR(transaction_date, 'YYYY-MM') as month,
                SUM(total_price) as total_price,
                COUNT(*) as transaction_count,
                SUM(galon_in) as total_galon_in,
                SUM(galon_out) as total_galon_out
            FROM transactions 
            WHERE transaction_date >= (CURRENT_DATE - INTERVAL '3 months')
                AND is_active = TRUE
            GROUP BY TO_CHAR(transaction_date, 'YYYY-MM')
            ORDER BY month DESC
        ";

        $monthlyData = DB::select($sql);
        
        // Format the month for display
        foreach ($monthlyData as $data) {
            $data->month = Carbon::createFromFormat('Y-m', $data->month)->format('F Y');
        }

        return response()->json($monthlyData);
    }

    public function payrollReport(Request $request)
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $month = $request->input('month', date('Y-m'));
        if ($dateStart && $dateEnd) {
            $totalRevenue = DB::table('transactions')
                ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateStart, $dateEnd])
                ->where('is_active', true)
                ->sum('total_price');
            $totalGalonIn = DB::table('transactions')
                ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateStart, $dateEnd])
                ->where('is_active', true)
                ->sum('galon_in');
            $employeeIds = DB::table('employee_attendances')
                ->whereBetween('date', [$dateStart, $dateEnd])
                ->distinct('employee_id')
                ->pluck('employee_id');
            $employees = DB::table('employees')
                ->whereIn('id', $employeeIds)
                ->where('is_active', true)
                ->get();
            $periodeLabel = date('d F Y', strtotime($dateStart)) . ' s/d ' . date('d F Y', strtotime($dateEnd));
            $operationalExpenses = DB::table('operational_expenses')
                ->whereBetween('date', [$dateStart, $dateEnd])
                ->orderBy('date')
                ->get();
            $totalDebtPayment = DB::table('debt_payments')
                ->whereBetween('payment_date', [$dateStart, $dateEnd])
                ->sum('amount');
            $debtPayments = DB::table('debt_payments')
                ->join('debts', 'debt_payments.debt_id', '=', 'debts.id')
                ->join('customers', 'debts.customer_id', '=', 'customers.id')
                ->select('debt_payments.*', 'customers.name as customer_name')
                ->whereBetween('payment_date', [$dateStart, $dateEnd])
                ->orderBy('payment_date')
                ->get();
        } else {
            $totalRevenue = DB::table('transactions')
                ->whereRaw("to_char(transaction_date, 'YYYY-MM') = ?", [$month])
                ->where('is_active', true)
                ->sum('total_price');
            $totalGalonIn = DB::table('transactions')
                ->whereRaw("to_char(transaction_date, 'YYYY-MM') = ?", [$month])
                ->where('is_active', true)
                ->sum('galon_in');
            $employeeIds = DB::table('employee_attendances')
                ->whereRaw("to_char(date, 'YYYY-MM') = ?", [$month])
                ->distinct('employee_id')
                ->pluck('employee_id');
            $employees = DB::table('employees')
                ->whereIn('id', $employeeIds)
                ->where('is_active', true)
                ->get();
            $periodeLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
            $operationalExpenses = DB::table('operational_expenses')
                ->whereRaw("to_char(date, 'YYYY-MM') = ?", [$month])
                ->orderBy('date')
                ->get();
            $totalDebtPayment = DB::table('debt_payments')
                ->whereRaw("to_char(payment_date, 'YYYY-MM') = ?", [$month])
                ->sum('amount');
            $debtPayments = DB::table('debt_payments')
                ->join('debts', 'debt_payments.debt_id', '=', 'debts.id')
                ->join('customers', 'debts.customer_id', '=', 'customers.id')
                ->select('debt_payments.*', 'customers.name as customer_name')
                ->whereRaw("to_char(payment_date, 'YYYY-MM') = ?", [$month])
                ->orderBy('payment_date')
                ->get();
        }
        $totalRevenue = $totalRevenue + $totalDebtPayment;
        $totalInfak = $totalGalonIn * 1000;
        $totalOperational = $operationalExpenses->sum('amount');
        $totalRevenueSetelahInfak = $totalRevenue - $totalInfak - $totalOperational;
        $totalKaryawan = $employees->count();
        $karyawanShare = $totalRevenueSetelahInfak * 0.35;
        $pemilikShare = $totalRevenueSetelahInfak * 0.65;
        $gajiPerKaryawan = $totalKaryawan > 1 ? $karyawanShare / $totalKaryawan : $karyawanShare * 0.75;
        // Pinjaman karyawan pada periode
        $loans = DB::table('employee_loans')
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$dateStart ?? $month.'-01', $dateEnd ?? $month.'-31'])
            ->get();
        // Hitung gaji per karyawan setelah dipotong pinjaman
        $gajiKaryawan = [];
        foreach ($employees as $emp) {
            $pinjaman = $loans->where('employee_id', $emp->id)->sum('amount');
            $gajiBersih = max($gajiPerKaryawan - $pinjaman, 0);
            $gajiKaryawan[] = [
                'employee' => $emp,
                'gaji' => $gajiPerKaryawan,
                'pinjaman' => $pinjaman,
                'gaji_bersih' => $gajiBersih,
            ];
        }
        return view('reports.payroll-report', compact('month', 'dateStart', 'dateEnd', 'periodeLabel', 'totalRevenue', 'totalDebtPayment', 'debtPayments', 'totalGalonIn', 'totalInfak', 'totalOperational', 'totalRevenueSetelahInfak', 'karyawanShare', 'pemilikShare', 'gajiPerKaryawan', 'employees', 'operationalExpenses', 'loans', 'gajiKaryawan'));
    }

    /**
     * Laporan stok galon per customer
     */
    public function galonStockReport(Request $request)
    {
        $customers = DB::table('customers')
            ->where('is_active', true)
            ->get();
        $data = [];
        foreach ($customers as $customer) {
            $stok = DB::table('transactions')
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->selectRaw('COALESCE(SUM(galon_in),0) - COALESCE(SUM(galon_out),0) as stok')
                ->first();
            $data[] = [
                'customer_name' => $customer->name,
                'phone_number' => $customer->phone_number,
                'address' => $customer->address,
                'stok_galon' => $stok->stok ?? 0
            ];
        }
        return view('reports.galon-stock-report', ['data' => $data]);
    }
} 