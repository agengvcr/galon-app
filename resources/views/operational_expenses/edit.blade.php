@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Biaya Operasional</h1>
    <form action="{{ route('operational-expenses.update', $expense->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="date" class="form-label">Tanggal</label>
            <input type="date" name="date" id="date" class="form-control" required value="{{ $expense->date }}">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Keterangan</label>
            <input type="text" name="description" id="description" class="form-control" required value="{{ $expense->description }}">
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Jumlah (Rp)</label>
            <input type="number" name="amount" id="amount" class="form-control" required min="0" value="{{ $expense->amount }}">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('operational-expenses.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection 