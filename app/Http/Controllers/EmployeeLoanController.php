<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeLoanController extends Controller
{
    public function index()
    {
        $loans = DB::table('employee_loans')
            ->join('employees', 'employee_loans.employee_id', '=', 'employees.id')
            ->select('employee_loans.*', 'employees.name as employee_name')
            ->orderBy('date', 'desc')
            ->get();
        return view('employee_loans.index', compact('loans'));
    }

    public function create()
    {
        $employees = DB::table('employees')->where('is_active', true)->get();
        return view('employee_loans.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);
        DB::table('employee_loans')->insert([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('employee-loans.index')->with('success', 'Pinjaman karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $loan = DB::table('employee_loans')->where('id', $id)->first();
        $employees = DB::table('employees')->where('is_active', true)->get();
        if (!$loan) abort(404);
        return view('employee_loans.edit', compact('loan', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);
        DB::table('employee_loans')->where('id', $id)->update([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'updated_at' => now(),
        ]);
        return redirect()->route('employee-loans.index')->with('success', 'Pinjaman karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('employee_loans')->where('id', $id)->delete();
        return redirect()->route('employee-loans.index')->with('success', 'Pinjaman karyawan berhasil dihapus.');
    }
} 