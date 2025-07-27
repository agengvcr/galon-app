@extends('layouts.app')

@section('styles')
<style>
    /* Responsive table styles */
    .table-responsive {
        overflow-x: auto;
    }
    
    /* Compact button group for actions */
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.2rem;
    }
    
    /* Ensure buttons stay in one line */
    .btn-group {
        white-space: nowrap;
        flex-wrap: nowrap;
    }
    
    /* Responsive adjustments for small screens */
    @media (max-width: 768px) {
        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        /* Hide less important columns on mobile */
        .table-responsive .table th:nth-child(4),
        .table-responsive .table td:nth-child(4) {
            display: none;
        }
    }
    
    @media (max-width: 576px) {
        .btn-group-sm .btn {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
        }
        
        /* Hide more columns on very small screens */
        .table-responsive .table th:nth-child(5),
        .table-responsive .table td:nth-child(5) {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1>Pelanggan</h1>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        Tambah Pelanggan
    </button>   

    <div class="table-responsive">
        <table id="customersTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Nomor Telepon</th>
                    <th>Alamat</th>
                    <th>Transaksi Terakhir</th>
                    <th>Sisa Hutang</th>
                    <th>Stok Galon</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">Tambah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm" action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="addCustomerForm" class="btn btn-primary">Simpan Pelanggan</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade animate__animated" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">Edit Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCustomerId" name="id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPhoneNumber" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Alamat</label>
                        <textarea class="form-control" id="editAddress" name="address" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="saveEditCustomer">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTransactionModalLabel">Tambah Transaksi untuk <span id="transactionCustomerName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addTransactionForm">
        <div class="modal-body">
          <input type="hidden" name="customer_id" id="transactionCustomerId">
          <div class="mb-2 text-info" id="customer-galon-info" style="display:none;">
            Total galon pelanggan: <span id="customer-galon-value">0</span>
          </div>
          <div class="mb-3">
            <label for="galon_in" class="form-label">Galon Kirim</label>
            <input type="number" class="form-control" name="galon_in" id="galon_in" required>
          </div>
          <div class="mb-3">
            <label for="galon_out" class="form-label">Galon Tarik</label>
            <input type="number" class="form-control" name="galon_out" id="galon_out" required>
          </div>          
          <div class="mb-3">
            <label for="transaction_date" class="form-label">Tanggal Transaksi</label>
            <input type="date" class="form-control" name="transaction_date" id="transaction_date" required>
          </div>
          <div class="mb-3">
            <label for="total_price" class="form-label">Total Harga</label>
            <input type="number" class="form-control" name="total_price" id="total_price" required>
          </div>
          <div class="mb-3">
            <label for="debt_amount" class="form-label">Hutang (Opsional)</label>
            <input type="number" step="0.01" class="form-control" name="debt_amount" id="debt_amount" placeholder="Kosongkan jika tidak ada hutang">
            <small class="form-text text-muted">Jika diisi, akan otomatis menambahkan ke daftar hutang pelanggan</small>
          </div>
          <div class="mb-3">
            <label for="debt_notes" class="form-label">Catatan Hutang (Opsional)</label>
            <textarea class="form-control" name="debt_notes" id="debt_notes" rows="2" placeholder="Catatan untuk hutang (opsional)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Debt Modal (copy from debts/index.blade.php) -->
<div class="modal fade animate__animated animate__fadeIn" id="addDebtModal" tabindex="-1">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Hutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDebtForm" action="{{ route('debts.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="customer_id" class="form-label">Pelanggan</label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <!-- Options will be loaded via AJAX -->
                            </select>
                            <div class="invalid-feedback">
                                Please select a customer
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Jumlah</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            <div class="invalid-feedback">
                                Please enter a valid amount
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="addDebtForm" class="btn btn-primary">Simpan Hutang</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var table = $('#customersTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('customers.index') }}",
                "type": "GET"
            },
            "columns": [
                { "data": "id" },
                { "data": "name" },
                { "data": "phone_number" },
                { "data": "address" },
                { "data": "last_transaction", "defaultContent": "-" },
                { "data": "outstanding_debt", "defaultContent": "-" },
                { "data": "stok_galon", "defaultContent": "0"},
                {
                    "data": null,
                    "className": "text-end",
                    "render": function(data, type, row) {
                        let buttons = '<div class="btn-group btn-group-sm" role="group">';
                        buttons += '<button class="btn btn-success add-transaction" data-id="' + row.id + '" data-name="' + row.name + '" title="Tambah Transaksi"><i class="fas fa-plus"></i></button>';
                        buttons += '<button class="btn btn-warning edit-customer" data-id="' + row.id + '" title="Edit"><i class="fas fa-edit"></i></button>';
                        
                        // Add debt payment button if customer has outstanding debt
                        if (row.outstanding_debt && row.outstanding_debt !== '-' && parseFloat(row.outstanding_debt.replace(/[^\d.-]/g, '')) > 0) {
                            buttons += '<button class="btn btn-info pay-debt" data-id="' + row.id + '" data-name="' + row.name + '" title="Bayar Hutang"><i class="fas fa-money-bill-wave"></i></button>';
                        }
                        
                        buttons += '<button class="btn btn-danger delete-customer" data-id="' + row.id + '" title="Hapus"><i class="fas fa-trash"></i></button>';
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ],
            "order": [[0, "desc"]],
            "createdRow": function(row, data, dataIndex) {
                if (data.last_transaction && data.last_transaction !== '-') {
                    // Parse tanggal format 'd F Y' (misal: 10 Juni 2024)
                    var parts = data.last_transaction.split(' ');
                    var bulan = {
                        'Januari': 0, 'Februari': 1, 'Maret': 2, 'April': 3, 'Mei': 4, 'Juni': 5,
                        'Juli': 6, 'Agustus': 7, 'September': 8, 'Oktober': 9, 'November': 10, 'Desember': 11
                    };
                    var tgl = parseInt(parts[0]);
                    var bln = bulan[parts[1]];
                    var thn = parseInt(parts[2]);
                    var lastDate = new Date(thn, bln, tgl);
                    var now = new Date();
                    var diffTime = now - lastDate;
                    var diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    if (diffDays > 10) {
                        $(row).addClass('table-danger');
                    } else if (diffDays > 5) {
                        $(row).addClass('table-warning');
                    }
                }
            }
        });

        // Add row numbers
        table.on('order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        // Updated delete functionality
        $('#customersTable').on('click', '.delete-customer', function() {
            const button = $(this);
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const customerId = button.data('id');
                    fetch(`/customers/${customerId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'The customer has been deleted.',
                                'success'
                            ).then(() => {
                                // Remove the row from DataTable
                                $('#customersTable').DataTable().row(button.parents('tr')).remove().draw();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete the customer.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An unexpected error occurred',
                            'error'
                        );
                    });
                }
            });
        });

        // Edit customer
        $('#customersTable').on('click', '.edit-customer', function() {
            const customerId = $(this).data('id');
            fetch(`/customers/${customerId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                $('#editCustomerId').val(data.id);
                $('#editName').val(data.name);
                $('#editPhoneNumber').val(data.phone_number);
                $('#editAddress').val(data.address);
                showModalSmooth('#editCustomerModal');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to load customer data', 'error');
            });
        });

        // Function to reset the add customer form
        function resetAddCustomerForm() {
            $('#addCustomerForm')[0].reset();
        }

        // Reset form when add customer modal is opened
        $('#addCustomerModal').on('show.bs.modal', function (e) {
            resetAddCustomerForm();
        });

        // Add Customer Form Submission
        $('#addCustomerForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(form[0]);

            fetch(form.attr('action'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideModalSmooth('#addCustomerModal');
                    Swal.fire({
                        title: 'Success!',
                        text: 'Customer added successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            table.ajax.reload();
                        }
                    });
                    resetAddCustomerForm();
                } else {
                    Swal.fire('Error!', data.message || 'Failed to add customer', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred', 'error');
            });
        });

        // Update save edited customer
        $('#saveEditCustomer').on('click', function() {
            const customerId = $('#editCustomerId').val();
            const formData = new FormData($('#editCustomerForm')[0]);

            fetch(`/customers/${customerId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideModalSmooth('#editCustomerModal');
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            table.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred', 'error');
            });
        });

        // Refresh the table after adding or editing a customer
        function refreshTable() {
            table.ajax.reload();
        }

        // Smooth transition for modals
        function showModalSmooth(modalId) {
            const modal = $(modalId);
            const backdrop = $('<div class="modal-backdrop fade"></div>');
            $('body').append(backdrop);
            modal.css('display', 'block');
            setTimeout(() => {
                backdrop.addClass('show');
                modal.addClass('show');
                modal.find('.modal-dialog')
                    .removeClass('animate__animated animate__fadeOutUp')
                    .addClass('animate__animated animate__fadeInDown');
            }, 50);
        }

        function hideModalSmooth(modalId) {
            const modal = $(modalId);
            const backdrop = $('.modal-backdrop');
            modal.find('.modal-dialog')
                .removeClass('animate__animated animate__fadeInDown')
                .addClass('animate__animated animate__fadeOutUp');
            setTimeout(() => {
                modal.removeClass('show');
                backdrop.removeClass('show');
                setTimeout(() => {
                    modal.css('display', 'none');
                    backdrop.remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                }, 300);
            }, 300);
        }

        // Use smooth transition for add customer modal
        $('[data-bs-toggle="modal"][data-bs-target="#addCustomerModal"]').on('click', function(e) {
            e.preventDefault();
            // Reset form to default
            $('#addCustomerForm')[0].reset();
            showModalSmooth('#addCustomerModal');
        });

        $('#addCustomerModal .btn-close, #addCustomerModal .btn-secondary').on('click', function(e) {
            e.preventDefault();
            hideModalSmooth('#addCustomerModal');
        });

        // Use smooth transition for edit customer modal
        $('#customersTable').on('click', '.edit-customer', function() {
            const customerId = $(this).data('id');
            fetch(`/customers/${customerId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                $('#editCustomerId').val(data.id);
                $('#editName').val(data.name);
                $('#editPhoneNumber').val(data.phone_number);
                $('#editAddress').val(data.address);
                showModalSmooth('#editCustomerModal');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to load customer data', 'error');
            });
        });

        // Use smooth transition for edit customer modal closing
        $('#editCustomerModal .btn-close, #editCustomerModal .btn-secondary').on('click', function(e) {
            e.preventDefault();
            hideModalSmooth('#editCustomerModal');
        });

        // Add animation classes when opening modals
        // $('#addCustomerModal, #editCustomerModal').on('show.bs.modal', function (e) { ... });
        // $('#addCustomerModal, #editCustomerModal').on('hide.bs.modal', function (e) { ... });

        $('#customersTable').on('click', '.add-transaction', function() {
            const customerId = $(this).data('id');
            const customerName = $(this).data('name');
            $('#transactionCustomerId').val(customerId);
            $('#transactionCustomerName').text(customerName);
            $('#addTransactionForm')[0].reset();
            // Set default tanggal transaksi ke hari ini (tanpa jam)
            const now = new Date();
            const pad = n => n.toString().padStart(2, '0');
            const localDate = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate());
            $('#transaction_date').val(localDate);
            // Reset debt fields
            $('#debt_amount').val('');
            $('#debt_notes').val('');
            // Ambil total galon pelanggan
            $.ajax({
                url: '/transactions/customer-galon',
                data: { customer_id: customerId },
                success: function(res) {
                    if (res.success) {
                        $('#customer-galon-value').text(res.total_galon);
                        $('#customer-galon-info').show();
                    } else {
                        $('#customer-galon-info').hide();
                    }
                },
                error: function() {
                    $('#customer-galon-info').hide();
                }
            });
            $('#addTransactionModal').modal('show');
        });

        $('#addTransactionForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData($(this)[0]);
            
            // Check if debt amount is provided
            const debtAmount = parseFloat($('#debt_amount').val()) || 0;
            const debtNotes = $('#debt_notes').val();
            
            fetch('/transactions', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // If debt amount is provided, create debt record
                    if (debtAmount > 0) {
                        const debtData = new FormData();
                        debtData.append('customer_id', $('#transactionCustomerId').val());
                        debtData.append('amount', debtAmount);
                        debtData.append('notes', debtNotes || 'Hutang dari transaksi');
                        debtData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        
                        return fetch('/debts', {
                            method: 'POST',
                            body: debtData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                    } else {
                        return Promise.resolve({ success: true });
                    }
                } else {
                    throw new Error(data.message || 'Gagal menambah transaksi');
                }
            })
            .then(response => {
                if (response.success !== undefined) {
                    // This is from the first transaction response
                    return response;
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    $('#addTransactionModal').modal('hide');
                    let message = 'Transaksi berhasil ditambahkan';
                    if (debtAmount > 0) {
                        message += ' dan hutang berhasil dicatat';
                    }
                    Swal.fire('Sukses!', message, 'success');
                    $('#customersTable').DataTable().ajax.reload();
                } else {
                    Swal.fire('Error!', data.message || 'Gagal menambah hutang', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Terjadi kesalahan', 'error');
            });
        });

        // Pay debt button handler
        $('#customersTable').on('click', '.pay-debt', function() {
            const customerId = $(this).data('id');
            const customerName = $(this).data('name');
            
            // Redirect to debts page with customer filter
            window.location.href = `/debts?customer_id=${customerId}&customer_name=${encodeURIComponent(customerName)}`;
        });

        // Add debt modal reset
        $('#addDebtModal').on('show.bs.modal', function() {
            $('#addDebtForm')[0].reset();
            $('#customer_id').val('').trigger('change');
        });

        // Hapus seluruh script terkait add-debt, select2 hutang, dan submit form hutang
    });
</script>
@endsection
