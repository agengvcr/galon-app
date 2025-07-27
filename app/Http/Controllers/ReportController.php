<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{


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

    public function payrollReport(Request $request)
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $month = $request->input('month', date('Y-m'));
        if ($dateStart && $dateEnd) {
            $periodeLabel = date('d F Y', strtotime($dateStart)) . ' s/d ' . date('d F Y', strtotime($dateEnd));
        } else {
            $dateStart = $month.'-01';
            $dateEnd = $month.'-31';
            $periodeLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
        }

        // 1. Hitung total pemasukan (transaksi + pembayaran hutang)
        $totalRevenue = DB::table('transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateStart, $dateEnd])
            ->where('is_active', true)
            ->sum('total_price');
        $totalDebtPayment = DB::table('debt_payments')
            ->whereBetween('payment_date', [$dateStart, $dateEnd])
            ->sum('amount');
        $totalRevenue += $totalDebtPayment;

        // 2. Hitung total galon masuk (untuk infak)
        $totalGalonIn = DB::table('transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateStart, $dateEnd])
            ->where('is_active', true)
            ->sum('galon_in');
        $totalInfak = $totalGalonIn * 1000;

        // 3. Hitung total biaya operasional
        $operationalExpenses = DB::table('operational_expenses')
            ->whereBetween('date', [$dateStart, $dateEnd])
            ->orderBy('date')
            ->get();
        $totalOperational = $operationalExpenses->sum('amount');

        // 4. Hitung pendapatan bersih
        $totalRevenueSetelahInfak = $totalRevenue - $totalInfak - $totalOperational;

        // 5. Ambil semua tanggal aktif (ada transaksi atau pembayaran hutang)
        $tanggalTransaksi = DB::table('transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateStart, $dateEnd])
            ->where('is_active', true)
            ->select(DB::raw('DATE(transaction_date) as tanggal'))
            ->distinct()
            ->pluck('tanggal')->toArray();
        $tanggalHutang = DB::table('debt_payments')
            ->whereBetween('payment_date', [$dateStart, $dateEnd])
            ->select(DB::raw('DATE(payment_date) as tanggal'))
            ->distinct()
            ->pluck('tanggal')->toArray();
        $tanggalAktif = array_unique(array_merge($tanggalTransaksi, $tanggalHutang));
        sort($tanggalAktif);

        // 6. Hitung gaji harian per pegawai
        $gajiHarianPegawai = [];
        foreach ($tanggalAktif as $tanggal) {
            // Pendapatan hari itu (transaksi + pembayaran hutang)
            $pendapatanHari = DB::table('transactions')
                ->whereDate('transaction_date', $tanggal)
                ->where('is_active', true)
                ->sum('total_price');
            $pembayaranHutangHari = DB::table('debt_payments')
                ->whereDate('payment_date', $tanggal)
                ->sum('amount');
            $pendapatanHari += $pembayaranHutangHari;
            // Infak dan operasional hari itu
            $galonInHari = DB::table('transactions')
                ->whereDate('transaction_date', $tanggal)
                ->where('is_active', true)
                ->sum('galon_in');
            $infakHari = $galonInHari * 1000;
            $operasionalHari = DB::table('operational_expenses')
                ->whereDate('date', $tanggal)
                ->sum('amount');
            $pendapatanBersihHari = $pendapatanHari - $infakHari - $operasionalHari;
            $shareKaryawanHari = $pendapatanBersihHari * 0.35;
            // Pegawai yang hadir hari itu
            $absensiHari = DB::table('employee_attendances')
                ->whereDate('date', $tanggal)
                ->pluck('employee_id')->toArray();
            $jumlahHadir = count($absensiHari);
            if ($jumlahHadir === 1) {
                $gajiHariIni = $shareKaryawanHari * 0.70;
                $gajiHarianPegawai[$absensiHari[0]][$tanggal] = $gajiHariIni;
            } elseif ($jumlahHadir > 1) {
                $gajiHariIni = $shareKaryawanHari / $jumlahHadir;
                foreach ($absensiHari as $empId) {
                    $gajiHarianPegawai[$empId][$tanggal] = $gajiHariIni;
                }
            }
        }
        // 7. Akumulasi gaji pegawai selama periode
        $gajiKaryawan = [];
        $employeeIds = array_keys($gajiHarianPegawai);
        $employees = DB::table('employees')
            ->whereIn('id', $employeeIds)
            ->where('is_active', true)
            ->get();
        $loans = DB::table('employee_loans')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$dateStart, $dateEnd])
            ->get();
        foreach ($employees as $emp) {
            $gajiKotor = isset($gajiHarianPegawai[$emp->id]) ? array_sum($gajiHarianPegawai[$emp->id]) : 0;
            $jmlHadir = isset($gajiHarianPegawai[$emp->id]) ? count($gajiHarianPegawai[$emp->id]) : 0;
            $pinjaman = $loans->where('employee_id', $emp->id)->sum('amount');
            $gajiBersih = max($gajiKotor - $pinjaman, 0);
            $gajiKaryawan[] = [
                'employee' => $emp,
                'gaji' => $gajiKotor,
                'pinjaman' => $pinjaman,
                'gaji_bersih' => $gajiBersih,
                'jumlah_kehadiran' => $jmlHadir,
            ];
        }

        // 11. Ambil data hutang dan pembayaran hutang untuk laporan
        $debtPayments = DB::table('debt_payments')
            ->join('debts', 'debt_payments.debt_id', '=', 'debts.id')
            ->join('customers', 'debts.customer_id', '=', 'customers.id')
            ->select('debt_payments.*', 'customers.name as customer_name')
            ->whereBetween('payment_date', [$dateStart, $dateEnd])
            ->orderBy('payment_date')
            ->get();

        return view('reports.payroll-report', compact(
            'month', 'dateStart', 'dateEnd', 'periodeLabel',
            'totalRevenue', 'totalDebtPayment', 'debtPayments',
            'totalGalonIn', 'totalInfak', 'totalOperational',
            'totalRevenueSetelahInfak',
            'employees', 'operationalExpenses',
            'loans', 'gajiKaryawan', 'tanggalAktif'
        ));
    }

    public function payrollDetail($employee, $start, $end)
    {
        // Ambil semua tanggal aktif (ada transaksi atau pembayaran hutang)
        $tanggalTransaksi = DB::table('transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$start, $end])
            ->where('is_active', true)
            ->select(DB::raw('DATE(transaction_date) as tanggal'))
            ->distinct()
            ->pluck('tanggal')->toArray();
        $tanggalHutang = DB::table('debt_payments')
            ->whereBetween('payment_date', [$start, $end])
            ->select(DB::raw('DATE(payment_date) as tanggal'))
            ->distinct()
            ->pluck('tanggal')->toArray();
        $tanggalAktif = array_unique(array_merge($tanggalTransaksi, $tanggalHutang));
        sort($tanggalAktif);

        $detail = [];
        foreach ($tanggalAktif as $tanggal) {
            // Pegawai hadir?
            $hadir = DB::table('employee_attendances')
                ->where('employee_id', $employee)
                ->whereDate('date', $tanggal)
                ->exists();
            if (!$hadir) continue;
            // Data hari itu
            $pendapatanHari = DB::table('transactions')
                ->whereDate('transaction_date', $tanggal)
                ->where('is_active', true)
                ->sum('total_price');
            $pembayaranHutangHari = DB::table('debt_payments')
                ->whereDate('payment_date', $tanggal)
                ->sum('amount');
            $pendapatanHari += $pembayaranHutangHari;
            $galonInHari = DB::table('transactions')
                ->whereDate('transaction_date', $tanggal)
                ->where('is_active', true)
                ->sum('galon_in');
            $infakHari = $galonInHari * 1000;
            $operasionalHari = DB::table('operational_expenses')
                ->whereDate('date', $tanggal)
                ->sum('amount');
            $pendapatanBersihHari = $pendapatanHari - $infakHari - $operasionalHari;
            $shareKaryawanHari = $pendapatanBersihHari * 0.35;
            // Jumlah pegawai hadir hari itu
            $jumlahHadir = DB::table('employee_attendances')
                ->whereDate('date', $tanggal)
                ->count();
            if ($jumlahHadir === 1) {
                $gajiHariIni = $shareKaryawanHari * 0.75;
            } elseif ($jumlahHadir > 1) {
                $gajiHariIni = $shareKaryawanHari / $jumlahHadir;
            } else {
                $gajiHariIni = 0;
            }
            $detail[] = [
                'tanggal' => $tanggal,
                'gaji' => $gajiHariIni,
                'galon_in' => $galonInHari,
                'total_transaksi' => $pendapatanHari - $pembayaranHutangHari,
                'total_pembayaran_hutang' => $pembayaranHutangHari,
                'total_infak' => $infakHari,
                'total_operasional' => $operasionalHari,
                'total_pendapatan' => $pendapatanHari,
                'total_bersih' => $pendapatanBersihHari,
            ];
        }
        return response()->json($detail);
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

    /**
     * Pelanggan 10 hari tidak dikirim galon
     */
    public function getInactiveCustomers()
    {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.phone_number,
                c.address,
                DATE_FORMAT(MAX(t.transaction_date), '%d %M %Y') as last_transaction,
                COALESCE(SUM(t.galon_in), 0) - COALESCE(SUM(t.galon_out), 0) as stok_galon,
                DATEDIFF(CURDATE(), MAX(t.transaction_date)) as days_inactive
            FROM customers c
            LEFT JOIN transactions t ON c.id = t.customer_id AND t.is_active = true
            WHERE c.is_active = true
            GROUP BY c.id, c.name, c.phone_number, c.address
            HAVING MAX(t.transaction_date) IS NULL 
                OR DATEDIFF(CURDATE(), MAX(t.transaction_date)) >= 10
            ORDER BY days_inactive ASC
        ";

        $customers = DB::select($sql);

        // Format the data
        foreach ($customers as $customer) {
            if ($customer->last_transaction) {
                $customer->last_transaction = Carbon::parse($customer->last_transaction)->format('d F Y');
            }
            $customer->days_inactive = (int) $customer->days_inactive;
            $customer->stok_galon = (int) $customer->stok_galon;
        }

        return response()->json($customers);
    }


} 