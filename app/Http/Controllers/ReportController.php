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
                DATE(transaction_date) as date,
                SUM(galon_in) as galon_in,
                SUM(galon_out) as galon_out
            FROM transactions
            WHERE YEAR(transaction_date) = ?
            AND MONTH(transaction_date) = ?
            AND is_active = TRUE
            GROUP BY DATE(transaction_date)
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
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(total_price) as total_price,
                COUNT(*) as transaction_count
            FROM transactions 
            WHERE transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
                AND is_active = TRUE
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
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
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(total_price) as total_price,
                COUNT(*) as transaction_count,
                SUM(galon_in) as total_galon_in,
                SUM(galon_out) as total_galon_out
            FROM transactions 
            WHERE transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
                AND is_active = TRUE
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month DESC
        ";

        $monthlyData = DB::select($sql);
        return view('reports.monthly-table', compact('monthlyData'));
    }

    public function getMonthlyTableData()
    {
        $sql = "
            SELECT 
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(total_price) as total_price,
                COUNT(*) as transaction_count,
                SUM(galon_in) as total_galon_in,
                SUM(galon_out) as total_galon_out
            FROM transactions 
            WHERE transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
                AND is_active = TRUE
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month DESC
        ";

        $monthlyData = DB::select($sql);
        
        // Format the month for display
        foreach ($monthlyData as $data) {
            $data->month = Carbon::createFromFormat('Y-m', $data->month)->format('F Y');
        }

        return response()->json($monthlyData);
    }
} 