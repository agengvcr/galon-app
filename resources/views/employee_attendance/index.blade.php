@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Absen Karyawan</h1>
    <a href="{{ route('employee-attendance.create') }}" class="btn btn-primary mb-3">Tambah Absen</a>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->employee_name ?? '-' }}</td>
                    <td>{{ $attendance->date }}</td>
                    <td>{{ $attendance->check_in ?? '-' }}</td>
                    <td>{{ $attendance->check_out ?? '-' }}</td>
                    <td>{{ $attendance->notes ?? '-' }}</td>
                    <td>
                        <a href="{{ route('employee-attendance.edit', $attendance->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('employee-attendance.destroy', $attendance->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attach swal to all delete buttons
    document.querySelectorAll('form[action*="employee-attendance"] .btn-danger').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Yakin ingin menghapus absen ini?',
                text: 'Aksi ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    // Flash message swal
    @if(session('success'))
        Swal.fire('Berhasil!', @json(session('success')), 'success');
    @elseif(session('error'))
        Swal.fire('Gagal!', @json(session('error')), 'error');
    @endif
});
</script>
@endsection 