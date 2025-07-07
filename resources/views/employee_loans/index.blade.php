@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Pinjaman Karyawan</h1>
    <a href="{{ route('employee-loans.create') }}" class="btn btn-primary mb-3">Tambah Pinjaman</a>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loans as $loan)
                <tr>
                    <td>{{ $loan->employee_name }}</td>
                    <td>{{ $loan->date }}</td>
                    <td>Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                    <td>{{ $loan->description }}</td>
                    <td>
                        <a href="{{ route('employee-loans.edit', $loan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('employee-loans.destroy', $loan->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pinjaman ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 