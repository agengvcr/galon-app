<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = DB::table('employee_attendances')
            ->join('employees', 'employee_attendances.employee_id', '=', 'employees.id')
            ->select('employee_attendances.*', 'employees.name as employee_name')
            ->orderBy('date', 'desc')
            ->get();
        return view('employee_attendance.index', compact('attendances'));
    }

    public function create()
    {
        $employees = DB::table('employees')->where('is_active', true)->get();
        return view('employee_attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:255',
        ]);
        DB::table('employee_attendances')->insert([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'check_in' => $validated['check_in'] ?? null,
            'check_out' => $validated['check_out'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('employee-attendance.index')->with('success', 'Absen karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $attendance = DB::table('employee_attendances')->where('id', $id)->first();
        $employees = DB::table('employees')->where('is_active', true)->get();
        if (!$attendance) {
            abort(404);
        }
        return view('employee_attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:255',
        ]);
        DB::table('employee_attendances')->where('id', $id)->update([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'check_in' => $validated['check_in'] ?? null,
            'check_out' => $validated['check_out'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_at' => now(),
        ]);
        return redirect()->route('employee-attendance.index')->with('success', 'Absen karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('employee_attendances')->where('id', $id)->delete();
        return redirect()->route('employee-attendance.index')->with('success', 'Absen karyawan berhasil dihapus.');
    }
} 