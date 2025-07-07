<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalExpenseController extends Controller
{
    public function index()
    {
        $expenses = DB::table('operational_expenses')->orderBy('date', 'desc')->get();
        return view('operational_expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('operational_expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        DB::table('operational_expenses')->insert([
            'date' => $validated['date'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('operational-expenses.index')->with('success', 'Biaya operasional berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $expense = DB::table('operational_expenses')->where('id', $id)->first();
        if (!$expense) abort(404);
        return view('operational_expenses.edit', compact('expense'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        DB::table('operational_expenses')->where('id', $id)->update([
            'date' => $validated['date'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'updated_at' => now(),
        ]);
        return redirect()->route('operational-expenses.index')->with('success', 'Biaya operasional berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('operational_expenses')->where('id', $id)->delete();
        return redirect()->route('operational-expenses.index')->with('success', 'Biaya operasional berhasil dihapus.');
    }
} 