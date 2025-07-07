@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Absen Karyawan</h1>
    <form action="{{ route('employee-attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="employee_id" class="form-label">Nama Karyawan</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @if($attendance->employee_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Tanggal</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ $attendance->date }}" required>
        </div>
        <div class="mb-3">
            <label for="check_in" class="form-label">Jam Masuk</label>
            <input type="time" name="check_in" id="check_in" class="form-control" value="{{ $attendance->check_in }}">
        </div>
        <div class="mb-3">
            <label for="check_out" class="form-label">Jam Pulang</label>
            <input type="time" name="check_out" id="check_out" class="form-control" value="{{ $attendance->check_out }}">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Keterangan</label>
            <input type="text" name="notes" id="notes" class="form-control" value="{{ $attendance->notes }}">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('employee-attendance.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection 