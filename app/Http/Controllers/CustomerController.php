<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DatatableHelper;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $table = 'customers';
            $columns = ['id', 'name', 'phone_number', 'address'];
            $whereConditions = [['is_active', '=', true]];
            $searchColumns = ['id', 'name', 'phone_number', 'address'];

            $response = DatatableHelper::getServerSideProcessingData($request, $table, $columns, $whereConditions, [], $searchColumns);

            // Tambahkan tanggal transaksi terakhir untuk setiap pelanggan
            foreach ($response['aaData'] as &$row) {
                $lastTransaction = DB::table('transactions')
                    ->where('customer_id', $row->id)
                    ->where('is_active', true)
                    ->orderByDesc('transaction_date')
                    ->value('transaction_date');
                $row->last_transaction = $lastTransaction ? \Carbon\Carbon::parse($lastTransaction)->translatedFormat('d F Y') : '-';

                // Tambahkan sisa hutang (jumlah hutang - jumlah pembayaran)
                $debt = DB::table('debts')
                    ->where('customer_id', $row->id)
                    ->where('is_active', true)
                    ->sum(DB::raw('amount - paid_amount'));
                $row->outstanding_debt = $debt > 0 ? 'Rp ' . number_format($debt, 0, ',', '.') : '-';

                // Tambahkan stok galon (galon kirim - galon tarik)
                $stok = DB::table('transactions')
                    ->where('customer_id', $row->id)
                    ->where('is_active', true)
                    ->selectRaw('COALESCE(SUM(galon_in),0) - COALESCE(SUM(galon_out),0) as stok')
                    ->value('stok');
                $row->stok_galon = $stok ?? 0;
            }

            return response()->json($response);
        }

        return view('customers.index');
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        $query = "INSERT INTO customers (name, phone_number, address, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
        DB::insert($query, [
            $validatedData['name'],
            $validatedData['phone_number'],
            $validatedData['address'],
            true
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Customer added successfully']);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $query = "UPDATE customers SET is_active = ? WHERE id = ?";
        DB::update($query, [false, $id]);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = "SELECT * FROM customers WHERE id = ? AND is_active = ?";
        $customer = DB::selectOne($query, [$id, true]);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        $query = "UPDATE customers SET name = ?, phone_number = ?, address = ?, updated_at = NOW() WHERE id = ?";
        $updated = DB::update($query, [
            $validatedData['name'],
            $validatedData['phone_number'],
            $validatedData['address'],
            $id
        ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Customer updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to add/update customer']);
        }
    }
}
