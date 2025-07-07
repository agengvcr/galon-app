<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\DatatableHelper;

class DebtController extends Controller
{
    /**
     * Display a listing of debts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $table = 'debts';
            $columns = ['id', 'customer_id', 'amount', 'paid_amount', 'due_date', 'status', 'notes'];
            $whereConditions = [['is_active', '=', true]];

            $response = DatatableHelper::getServerSideProcessingData($request, $table, $columns, $whereConditions);
            
            // Fetch customer names
            foreach ($response['aaData'] as &$row) {
                $customer = DB::table('customers')->where('id', $row->customer_id)->first();
                $row->customer_name = $customer ? $customer->name : 'Unknown';
                $row->remaining_amount = $row->amount - $row->paid_amount;
            }

            return response()->json($response);
        }

        return view('debts.index');
    }

    /**
     * Store a newly created debt record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::insert(
                "INSERT INTO debts (customer_id, amount, paid_amount, due_date, status, notes, is_active, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $validatedData['customer_id'],
                    $validatedData['amount'],
                    0, // Initial paid_amount is 0
                    $validatedData['due_date'],
                    'UNPAID',
                    $validatedData['notes'] ?? null,
                    true
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Debt record created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create debt record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update debt payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePayment(Request $request, $id)
    {
        $debt = DB::table('debts')->where('id', $id)->first();
        if (!$debt) {
            return response()->json(['success' => false, 'message' => 'Hutang tidak ditemukan.'], 404);
        }
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);
        $remaining = $debt->amount - $debt->paid_amount;
        if ($validated['payment_amount'] > $remaining) {
            return response()->json(['success' => false, 'message' => 'Jumlah pembayaran melebihi sisa hutang!'], 422);
        }
        // Tambahkan ke debt_payments
        DB::table('debt_payments')->insert([
            'debt_id' => $debt->id,
            'amount' => $validated['payment_amount'],
            'payment_date' => $validated['payment_date'],
            'description' => $validated['description'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Update paid_amount di debts
        DB::table('debts')->where('id', $debt->id)->update([
            'paid_amount' => $debt->paid_amount + $validated['payment_amount'],
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Pembayaran hutang berhasil dicatat.']);
    }

    /**
     * Show the debt record for editing.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = "SELECT d.*, c.name as customer_name, c.phone_number as customer_phone 
                  FROM debts d 
                  JOIN customers c ON d.customer_id = c.id 
                  WHERE d.id = ? AND d.is_active = true";
        
        $debt = DB::selectOne($query, [$id]);

        if (!$debt) {
            return response()->json(['error' => 'Debt record not found'], 404);
        }

        return response()->json([
            'id' => $debt->id,
            'customer_id' => $debt->customer_id,
            'customer_text' => $debt->customer_name . ' (' . $debt->customer_phone . ')',
            'amount' => $debt->amount,
            'paid_amount' => $debt->paid_amount,
            'due_date' => $debt->due_date,
            'status' => $debt->status,
            'notes' => $debt->notes
        ]);
    }

    /**
     * Update the debt record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $updated = DB::update(
                "UPDATE debts 
                 SET amount = ?,
                     due_date = ?,
                     notes = ?,
                     updated_at = NOW() 
                 WHERE id = ? AND is_active = true",
                [
                    $validatedData['amount'],
                    $validatedData['due_date'],
                    $validatedData['notes'] ?? null,
                    $id
                ]
            );

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Debt record updated successfully'
                ]);
            } else {
                throw new \Exception('Debt record not found or no changes made');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update debt record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the debt record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::update(
                "UPDATE debts SET is_active = false WHERE id = ?",
                [$id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Debt record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete debt record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer's debt summary.
     *
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function getCustomerDebtSummary($customerId)
    {
        try {
            $summary = DB::selectOne(
                "SELECT 
                    COUNT(*) as total_debts,
                    SUM(amount) as total_amount,
                    SUM(paid_amount) as total_paid,
                    SUM(amount - paid_amount) as total_remaining
                FROM debts 
                WHERE customer_id = ? 
                AND is_active = true",
                [$customerId]
            );

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch debt summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new debt record.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = DB::table('customers')
            ->where('is_active', true)
            ->select('id', 'name', 'phone_number')
            ->get();
        return view('debts.create', compact('customers'));
    }

    /**
     * Get customers for select2 dropdown.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = "SELECT id, name, address 
                  FROM customers 
                  WHERE (
                    LOWER(name) LIKE LOWER(?) OR 
                    LOWER(address) LIKE LOWER(?)
                  )
                  AND is_active = true 
                  ORDER BY 
                    CASE 
                        WHEN LOWER(name) LIKE LOWER(?) THEN 1
                        WHEN LOWER(address) LIKE LOWER(?) THEN 2
                    END
                  LIMIT ? OFFSET ?";

        $offset = ($page - 1) * $perPage;
        $searchTerm = "%$search%";
        $customers = DB::select($query, [
            $searchTerm, 
            $searchTerm,
            $searchTerm,
            $searchTerm, 
            $perPage, 
            $offset
        ]);

        $formattedCustomers = array_map(function ($customer) {
            return [
                'id' => $customer->id,
                'text' => $customer->name . ' (' . $customer->address . ')'
            ];
        }, $customers);

        $totalQuery = "SELECT COUNT(*) as total 
                       FROM customers 
                       WHERE (
                         LOWER(name) LIKE LOWER(?) OR 
                         LOWER(address) LIKE LOWER(?)
                       )
                       AND is_active = true";
        $totalResult = DB::selectOne($totalQuery, [$searchTerm, $searchTerm]);
        $total = $totalResult->total;

        return response()->json([
            'results' => $formattedCustomers,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Get debt summary by date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSummaryByDate(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $summary = DB::table('debts')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_debts'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('SUM(paid_amount) as total_paid_amount'),
                    DB::raw('SUM(amount - paid_amount) as total_remaining')
                )
                ->where('is_active', true)
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            // Format the date and numbers
            $summary = $summary->map(function($row) {
                $date = \Carbon\Carbon::parse($row->date)->format('d-m-Y');
                return [
                    'date' => $date,
                    'total_debts' => $row->total_debts,
                    'total_amount' => $row->total_amount,
                    'total_paid_amount' => $row->total_paid_amount,
                    'total_remaining' => $row->total_remaining
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
     * Get overdue debts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getOverdueDebts()
    {
        try {
            $overdueDebts = DB::table('debts as d')
                ->join('customers as c', 'd.customer_id', '=', 'c.id')
                ->select(
                    'd.id',
                    'c.name as customer_name',
                    'c.phone_number',
                    'd.amount',
                    'd.paid_amount',
                    DB::raw('d.amount - d.paid_amount as remaining_amount'),
                    'd.due_date',
                    DB::raw('DATEDIFF(CURRENT_DATE, d.due_date) as days_overdue')
                )
                ->where('d.is_active', true)
                ->where('d.status', '!=', 'PAID')
                ->where('d.due_date', '<', DB::raw('CURRENT_DATE'))
                ->orderBy('d.due_date')
                ->get();

            return response()->json([
                'success' => true,
                'overdue_debts' => $overdueDebts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overdue debts: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get debt statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStatistics()
    {
        try {
            $statistics = DB::selectOne(
                "SELECT 
                    COUNT(*) as total_debts,
                    SUM(CASE WHEN status = 'UNPAID' THEN 1 ELSE 0 END) as unpaid_debts,
                    SUM(CASE WHEN status = 'PARTIALLY_PAID' THEN 1 ELSE 0 END) as partially_paid_debts,
                    SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) as paid_debts,
                    SUM(amount) as total_amount,
                    SUM(paid_amount) as total_paid_amount,
                    SUM(amount - paid_amount) as total_remaining,
                    SUM(CASE WHEN due_date < CURRENT_DATE AND status != 'PAID' THEN 1 ELSE 0 END) as overdue_debts
                FROM debts 
                WHERE is_active = true"
            );

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch debt statistics: ' . $e->getMessage()
            ]);
        }
    }
} 