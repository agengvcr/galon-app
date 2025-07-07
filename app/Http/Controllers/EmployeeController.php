<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $table = 'employees';
            $columns = ['name', 'phone_number', 'address', 'position'];
            $whereConditions = [['is_active', '=', true]];

            $response = [
                'data' => DB::table($table)->where('is_active', true)->get($columns)
            ]; // Sederhana, bisa diubah ke datatable helper jika perlu

            return response()->json($response);
        }
        return view('employees.index');
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:50',
        ]);
        DB::table('employees')->insert([
            'name' => $validatedData['name'],
            'phone_number' => $validatedData['phone_number'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'position' => $validatedData['position'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Employee added successfully']);
        }
        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    public function edit($id)
    {
        $employee = DB::table('employees')->where('id', $id)->where('is_active', true)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:50',
        ]);
        $updated = DB::table('employees')->where('id', $id)->update([
            'name' => $validatedData['name'],
            'phone_number' => $validatedData['phone_number'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'position' => $validatedData['position'] ?? null,
            'updated_at' => now(),
        ]);
        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Employee updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update employee']);
        }
    }

    public function destroy($id)
    {
        DB::table('employees')->where('id', $id)->update(['is_active' => false]);
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }
} 