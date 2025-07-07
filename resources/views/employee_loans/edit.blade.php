@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Pinjaman Karyawan</h1>
    <form action="{{ route('employee-loans.update', $loan->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="employee_id" class="form-label">Nama Karyawan</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @if($loan->employee_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Tanggal</label>
            <input type="date" name="date" id="date" class="form-control" required value="{{ $loan->date }}">
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Jumlah (Rp)</label>
            <input type="number" name="amount" id="amount" class="form-control" required min="0" value="{{ $loan->amount }}">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Keterangan</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ $loan->description }}">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('employee-loans.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection 