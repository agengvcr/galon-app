<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DatatableHelper;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Transaction;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $table = 'transactions';
            $joins = [
                [
                    'table' => 'customers',
                    'first' => 'transactions.customer_id',
                    'operator' => '=',
                    'second' => 'customers.id',
                    // 'type' => 'inner' // opsional, default 'inner'
                ]
            ];
            $columns = [
                'transactions.id as transaction_id',
                'transactions.customer_id',
                'transactions.galon_out',
                'transactions.galon_in',
                'transactions.transaction_date',
                'transactions.total_price',
                'customers.name as customer_name'
            ];
            $searchColumns = [
                'transactions.id',
                'transactions.customer_id',
                'transactions.galon_out',
                'transactions.galon_in',
                'transactions.transaction_date',
                'transactions.total_price',
                'customers.name'
            ];
            $whereConditions = [['transactions.is_active', '=', true]];

            $response = DatatableHelper::getServerSideProcessingData($request, $table, $columns, $whereConditions, $joins, $searchColumns);
            // Tidak perlu lagi fetch customer_name manual karena sudah di-join dan di-alias
            // foreach ($response['aaData'] as &$row) {
            //     $customer = DB::table('customers')->where('id', $row->customer_id)->first();
            //     $row->customer_name = $customer ? $customer->name : 'Unknown';
            // }

            return response()->json($response);
        }

        $customers = DB::table('customers')->where('is_active', true)->get();
        return view('transactions.index', compact('customers'));
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'galon_out' => 'required|integer|min:0',
            'galon_in' => 'required|integer|min:0',
            'transaction_date' => 'required|date',
            'total_price' => 'required|numeric|min:0',
        ]);

        $query = "INSERT INTO transactions (customer_id, galon_out, galon_in, transaction_date, total_price, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        DB::insert($query, [
            $validatedData['customer_id'],
            $validatedData['galon_out'],
            $validatedData['galon_in'],
            $validatedData['transaction_date'],
            $validatedData['total_price'],
            true
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Transaction added successfully']);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully');
    }

    /**
     * Remove the specified transaction from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $query = "UPDATE transactions SET is_active = ? WHERE id = ?";
        DB::update($query, [false, $id]);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully');
    }

    /**
     * Show the form for editing the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = "SELECT t.*, c.name as customer_name, c.phone_number as customer_phone 
                  FROM transactions t 
                  JOIN customers c ON t.customer_id = c.id 
                  WHERE t.id = ? AND t.is_active = ?";
        $transaction = DB::selectOne($query, [$id, true]);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'id' => $transaction->id,
            'customer_id' => $transaction->customer_id,
            'customer_text' => $transaction->customer_name . ' (' . $transaction->customer_phone . ')',
            'galon_out' => $transaction->galon_out,
            'galon_in' => $transaction->galon_in,
            'transaction_date' => $transaction->transaction_date,
            'total_price' => $transaction->total_price
        ]);
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'galon_out' => 'required|integer|min:0',
            'galon_in' => 'required|integer|min:0',
            'transaction_date' => 'required|date',
            'total_price' => 'required|numeric|min:0',
        ]);

        $query = "UPDATE transactions 
                  SET customer_id = ?, galon_out = ?, galon_in = ?, transaction_date = ?, total_price = ?, updated_at = NOW() 
                  WHERE id = ? AND is_active = ?";
        $updated = DB::update($query, [
            $validatedData['customer_id'],
            $validatedData['galon_out'],
            $validatedData['galon_in'],
            $validatedData['transaction_date'],
            $validatedData['total_price'],
            $id,
            true
        ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Transaction updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update transaction'], 500);
        }
    }

    public function getCustomers(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = "SELECT id, name, address 
                  FROM customers 
                  WHERE (name LIKE ? OR address LIKE ?) 
                  AND is_active = true 
                  LIMIT ? OFFSET ?";
        $offset = ($page - 1) * $perPage;
        $customers = DB::select($query, ["%$search%", "%$search%", $perPage, $offset]);

        $formattedCustomers = array_map(function ($customer) {
            return [
                'id' => $customer->id,
                'text' => $customer->name . ' (' . $customer->address . ')'
            ];
        }, $customers);

        $totalQuery = "SELECT COUNT(*) as total 
                       FROM customers 
                       WHERE (name LIKE ? OR phone_number LIKE ?) 
                       AND is_active = true";
        $totalResult = DB::selectOne($totalQuery, ["%$search%", "%$search%"]);
        $total = $totalResult->total;

        return response()->json([
            'results' => $formattedCustomers,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    public function storeBatch(Request $request)
    {
        $batchData = $request->input('batch');
        
        DB::beginTransaction();
        
        try {
            foreach ($batchData as $transaction) {
                $query = "INSERT INTO transactions (customer_id, galon_out, galon_in, transaction_date, total_price, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                DB::insert($query, [
                    $transaction['customer_id'],
                    $transaction['galon_out'],
                    $transaction['galon_in'],
                    $transaction['transaction_date'],
                    $transaction['total_price']
                ]);
                
                // If debt amount is provided, create debt record
                if (isset($transaction['debt_amount']) && !empty($transaction['debt_amount']) && $transaction['debt_amount'] > 0) {
                    $debtQuery = "INSERT INTO debts (customer_id, amount, notes, created_at, updated_at) 
                                  VALUES (?, ?, ?, NOW(), NOW())";
                    DB::insert($debtQuery, [
                        $transaction['customer_id'],
                        $transaction['debt_amount'],
                        $transaction['debt_notes'] ?? 'Hutang dari transaksi massal'
                    ]);
                }
            }
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Batch transactions added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to add batch transactions: ' . $e->getMessage()]);
        }
    }

    public function getSummary(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $summary = DB::table('transactions')
                ->select(
                    DB::raw('DATE(transaction_date) as date'),
                    DB::raw('COUNT(*) as total_transactions'),
                    DB::raw('SUM(galon_out) as total_galon_out'),
                    DB::raw('SUM(galon_in) as total_galon_in'),
                    DB::raw('SUM(total_price) as total_revenue')
                )
                ->where('is_active', true)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(transaction_date)'))
                ->orderBy('date')
                ->get();

            $summary = $summary->map(function($row) {
                $date = \Carbon\Carbon::parse($row->date)->format('d-m-Y');
                return [
                    'date' => $date,
                    'total_transactions' => $row->total_transactions,
                    'total_galon_out' => $row->total_galon_out,
                    'total_galon_in' => $row->total_galon_in,
                    'total_revenue' => $row->total_revenue,
                ];
            });

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get total galon for a specific customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCustomerGalon(Request $request)
    {
        $customerId = $request->input('customer_id');
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'customer_id is required'], 400);
        }
        $result = DB::table('transactions')
            ->select(
                DB::raw('COALESCE(SUM(galon_out),0) as total_galon_out'),
                DB::raw('COALESCE(SUM(galon_in),0) as total_galon_in')
            )
            ->where('customer_id', $customerId)
            ->where('is_active', true)
            ->first();
        $total_galon = ($result->total_galon_in ?? 0) -($result->total_galon_out ?? 0) ;
        return response()->json([
            'success' => true,
            'total_galon' => $total_galon,
            'total_galon_out' => $result->total_galon_out ?? 0,
            'total_galon_in' => $result->total_galon_in ?? 0,
        ]);
    }

    public function customersByDate(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        if (!$from || !$to) {
            return response()->json(['success' => false, 'message' => 'Tanggal from dan to wajib diisi', 'customers' => []]);
        }

        $customers = DB::table('transactions')
            ->join('customers', 'transactions.customer_id', '=', 'customers.id')
            ->leftJoin(DB::raw(
                "(SELECT customer_id, SUM(debt_payments.amount) as total_debt_payment
                  FROM debt_payments
                  JOIN debts ON debts.id = debt_payments.debt_id
                  WHERE DATE(payment_date) BETWEEN '" . $from . "' AND '" . $to . "'
                  GROUP BY customer_id) as dp"
            ), 'customers.id', '=', 'dp.customer_id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone_number',
                'customers.address',
                DB::raw('COUNT(transactions.id) as transaction_count'),
                DB::raw('COALESCE(SUM(transactions.galon_in),0) as total_galon_in'),
                DB::raw('COALESCE(SUM(transactions.galon_out),0) as total_galon_out'),
                DB::raw('COALESCE(SUM(transactions.total_price),0) as total_price'),
                DB::raw('COALESCE(dp.total_debt_payment,0) as total_debt_payment')
            )
            ->where('transactions.is_active', true)
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->groupBy('customers.id', 'customers.name', 'customers.phone_number', 'customers.address', 'dp.total_debt_payment')
            ->orderBy('transaction_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }
}
