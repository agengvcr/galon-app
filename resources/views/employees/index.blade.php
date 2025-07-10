@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Karyawan</h1>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        Tambah Karyawan
    </button>
    <div class="table-responsive">
        <table id="employeesTable" class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Tambah Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="{{ route('employees.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="position" name="position">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="addEmployeeForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Karyawan -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editEmployeeId" name="id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPhoneNumber" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="editPhoneNumber" name="phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Alamat</label>
                        <textarea class="form-control" id="editAddress" name="address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editPosition" class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="editPosition" name="position">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="saveEditEmployee">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Ensure CSRF token is available for AJAX
if (document.querySelector('meta[name="csrf-token"]') === null) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = "{{ csrf_token() }}";
    document.head.appendChild(meta);
}
$(document).ready(function() {
    var table = $('#employeesTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('employees.index') }}",
            type: "GET"
        },
        columns: [
            { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
            { data: 'name' },
            { data: 'phone_number' },
            { data: 'address' },
            { data: 'position' },
            {
                data: null,
                render: function(data, type, row) {
                    return '<button class="btn btn-warning btn-sm edit-employee" data-id="' + row.id + '">Edit</button> ' +
                           '<button class="btn btn-danger btn-sm delete-employee" data-id="' + row.id + '">Hapus</button>';
                }
            }
        ],
        order: [[1, "asc"]]
    });
    table.on('order.dt search.dt', function () {
        table.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
    // Edit employee
    $('#employeesTable').on('click', '.edit-employee', function() {
        const employeeId = $(this).data('id');
        fetch(`/employees/${employeeId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            $('#editEmployeeId').val(data.id);
            $('#editName').val(data.name);
            $('#editPhoneNumber').val(data.phone_number);
            $('#editAddress').val(data.address);
            $('#editPosition').val(data.position);
            $('#editEmployeeModal').modal('show');
        });
    });
    // Delete employee
    $('#employeesTable').on('click', '.delete-employee', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Yakin ingin menghapus karyawan ini?',
            text: 'Aksi ini tidak dapat dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/employees/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        table.ajax.reload();
                        Swal.fire('Berhasil!', 'Karyawan berhasil dihapus.', 'success');
                    } else {
                        Swal.fire('Gagal!', data.message || 'Gagal menghapus karyawan', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menghapus karyawan: ' + error.message, 'error');
                });
            }
        });
    });
    // Add employee
    $('#addEmployeeForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(form[0]);
        fetch("{{ route('employees.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#addEmployeeModal').modal('hide');
                table.ajax.reload();
                form[0].reset();
                Swal.fire('Berhasil!', 'Karyawan berhasil ditambahkan.', 'success');
            } else {
                Swal.fire('Gagal!', data.message || 'Gagal menambah karyawan', 'error');
            }
        });
    });
    // Save edit
    $('#saveEditEmployee').on('click', function() {
        const id = $('#editEmployeeId').val();
        const formData = {
            name: $('#editName').val(),
            phone_number: $('#editPhoneNumber').val(),
            address: $('#editAddress').val(),
            position: $('#editPosition').val(),
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT'
        };
        fetch(`/employees/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#editEmployeeModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Berhasil!', 'Karyawan berhasil diupdate.', 'success');
            } else {
                Swal.fire('Gagal!', data.message || 'Gagal update karyawan', 'error');
            }
        });
    });

    // Modal reset handlers
    $('#addEmployeeModal').on('show.bs.modal', function() {
        $('#addEmployeeForm')[0].reset();
    });

    $('#editEmployeeModal').on('show.bs.modal', function() {
        // Don't reset edit modal as it needs to be populated with data
    });
});
</script>
@endsection 