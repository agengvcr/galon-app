@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Biaya Operasional</h1>
    <form action="{{ route('operational-expenses.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="date" class="form-label">Tanggal</label>
            <input type="date" name="date" id="date" class="form-control" required value="{{ date('Y-m-d') }}">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Keterangan</label>
            <input type="text" name="description" id="description" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Jumlah (Rp)</label>
            <input type="number" name="amount" id="amount" class="form-control" required min="0">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('operational-expenses.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection 