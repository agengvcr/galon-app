@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Absen Karyawan</h1>
    <form action="{{ route('employee-attendance.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="employee_id" class="form-label">Nama Karyawan</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Tanggal</label>
            <input type="date" name="date" id="date" class="form-control" required value="{{ date('Y-m-d') }}">
        </div>
        <div class="mb-3">
            <label for="check_in" class="form-label">Jam Masuk</label>
            <input type="time" name="check_in" id="check_in" class="form-control" value="07:00">
        </div>
        <div class="mb-3">
            <label for="check_out" class="form-label">Jam Pulang</label>
            <input type="time" name="check_out" id="check_out" class="form-control" value="{{ date('H:i') }}">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Keterangan</label>
            <input type="text" name="notes" id="notes" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('employee-attendance.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection 