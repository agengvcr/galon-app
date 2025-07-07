@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Biaya Operasional</h1>
    <a href="{{ route('operational-expenses.create') }}" class="btn btn-primary mb-3">Tambah Biaya</a>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->date }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('operational-expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('operational-expenses.destroy', $expense->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus biaya ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 