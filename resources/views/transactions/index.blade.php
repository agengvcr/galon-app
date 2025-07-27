@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Transaksi</h1>

    <button type="button" class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        Tambah Transaksi
    </button>   
    <button type="button" class="btn btn-success mb-3 me-2" data-bs-toggle="modal" data-bs-target="#batchTransactionModal">
        Tambah Transaksi Massal
    </button>
    <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#summaryModal">
        Lihat Ringkasan Tanggal
    </button>
    <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#customerByDateModal">
        Customer Transaksi per Tanggal
    </button>

    <div class="table-responsive">
        <table id="transactionsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Galon Kirim</th>
                    <th>Galon Tarik</th>                  
                    <th>Tanggal</th>
                    <th>Total Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="mt-4">
        <h4>Ringkasan Transaksi</h4>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th>Total Galon Kirim</th>
                    <td>{{ $totalGalonIn }}</td>
                </tr>
                <tr>
                    <th>Total Galon Tarik</th>
                    <td>{{ $totalGalonOut }}</td>
                </tr>
                <tr>
                    <th>Total Jumlah Uang</th>
                    <td>{{ number_format($totalPrice, 2, ',', '.') }}</td>
                </tr>               
            </tbody>
        </table>
    </div>

</div>

<!-- Add Transaction Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTransactionModalLabel">Tambah Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTransactionForm" action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Pelanggan</label>
                        <select class="form-select" id="customer_id" name="customer_id" required style="width: 100%;">
                            <!-- Options will be loaded via AJAX -->
                        </select>
                        <div id="customer-galon-info" class="mt-2 text-info" style="display:none;">
                            Total galon pelanggan: <span id="customer-galon-value">0</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="galon_out" class="form-label">Galon Tarik</label>
                        <input type="number" class="form-control" id="galon_out" name="galon_out" required>
                    </div>
                    <div class="mb-3">
                        <label for="galon_in" class="form-label">Galon Kirim</label>
                        <input type="number" class="form-control" id="galon_in" name="galon_in" required>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="total_price" class="form-label">Total Harga</label>
                        <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" required>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="addTransactionForm" class="btn btn-primary">Simpan Transaksi</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Transaction Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTransactionForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTransactionId" name="id">
                    <div class="mb-3">
                        <label for="editCustomerName" class="form-label">Pelanggan</label>
                        <input type="text" class="form-control" id="editCustomerName" readonly>
                        <input type="hidden" id="editCustomerId" name="customer_id">
                    </div>
                    <div class="mb-3">
                        <label for="editGalonOut" class="form-label">Galon Tarik</label>
                        <input type="number" class="form-control" id="editGalonOut" name="galon_out" required>
                    </div>
                    <div class="mb-3">
                        <label for="editGalonIn" class="form-label">Galon Kirim</label>
                        <input type="number" class="form-control" id="editGalonIn" name="galon_in" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTransactionDate" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="editTransactionDate" name="transaction_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTotalPrice" class="form-label">Total Harga</label>
                        <input type="number" step="0.01" class="form-control" id="editTotalPrice" name="total_price" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="saveEditTransaction">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Transaction Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="batchTransactionModal" tabindex="-1" aria-labelledby="batchTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchTransactionModalLabel">Tambah Transaksi Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="batchTransactionForm" action="{{ route('transactions.storeBatch') }}" method="POST">
                    @csrf
                    <div id="batchTransactions">
                        <!-- Batch transaction rows will be added here dynamically -->
                    </div>
                  
                    <button type="button" class="btn btn-secondary mt-3" id="addBatchRow">Tambah Baris</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="batchTransactionForm" class="btn btn-primary">Simpan Transaksi Massal</button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="summaryModalLabel">Ringkasan Transaksi per Tanggal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="startDate" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="col-md-5">
                        <label for="endDate" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" id="fetchSummary">Ambil</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="summaryTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Total Transaksi</th>
                                <th>Total Galon Tarik</th>
                                <th>Total Galon Kirim</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Summary data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Customer By Date Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="customerByDateModal" tabindex="-1" aria-labelledby="customerByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerByDateModalLabel">Daftar Customer yang Transaksi pada Range Tanggal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="fromDate" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="fromDate">
                    </div>
                    <div class="col-md-5">
                        <label for="toDate" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="toDate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" id="fetchCustomerByDate">Cari</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="customerByDateTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th>Jumlah Transaksi</th>
                                <th>Galon Kirim</th> 
                                <th>Galon Tarik</th>                                                               
                                <th>Jumlah Uang</th>
                                <th>Hutang</th>
                                <th>Bayar Utang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan diisi via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <h4>Ringkasan</h4>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Total Galon Kirim</th>
                                <td id="totalGalonIn"></td>
                            </tr>
                            <tr>
                                <th>Total Galon Tarik</th>
                                <td id="totalGalonOut"></td>
                            </tr>
                            <tr>
                                <th>Total Jumlah Uang</th>
                                <td id="totalPrice"></td>
                            </tr>
                            <tr>
                                <th>Total Bayar Utang</th>
                                <td id="totalDebtPayment"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var table = $('#transactionsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "{{ route('transactions.index') }}",
            "columns": [
                { "data": "transaction_id" },
                { "data": "customer_name" },
                { "data": "galon_in" },
                { "data": "galon_out" },
                { 
                    "data": "transaction_date",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            let date = new Date(data);
                            return date.getDate().toString().padStart(2,'0') + '-' + 
                                   (date.getMonth() + 1).toString().padStart(2,'0') + '-' + 
                                   date.getFullYear();
                        }
                        return data;
                    }
                },
                { "data": "total_price" },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-warning btn-sm edit-transaction" data-id="' + row.transaction_id + '">Edit</button> ' +
                               '<button class="btn btn-danger btn-sm delete-transaction" data-id="' + row.transaction_id + '">Delete</button>';
                    }
                }
            ]
        });

        // Update Select2 initialization
        $('#customer_id').select2({
            dropdownParent: $('#addTransactionModal'),
            ajax: {
                url: "{{ route('transactions.customers') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            placeholder: 'Select a customer',
            minimumInputLength: 1,
            width: '100%'
        });

        // Add Transaction Form Submission
        $('#addTransactionForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(form[0]);
            
            // Check if debt amount is provided
            const debtAmount = parseFloat($('#debt_amount').val()) || 0;
            const debtNotes = $('#debt_notes').val();

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
                    // If debt amount is provided, create debt record
                    if (debtAmount > 0) {
                        const debtData = new FormData();
                        debtData.append('customer_id', $('#customer_id').val());
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
                    throw new Error(data.message || 'Failed to add transaction');
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
                    let message = 'Transaction added successfully';
                    if (debtAmount > 0) {
                        message += ' and debt recorded';
                    }
                    Swal.fire({
                        title: 'Success!',
                        text: message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            table.ajax.reload();
                        }
                    });
                    form[0].reset();
                } else {
                    Swal.fire('Error!', data.message || 'Failed to add debt', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred', 'error');
            });
        });

        // Edit Transaction
        $('#transactionsTable').on('click', '.edit-transaction', function() {
            const transactionId = $(this).data('id');
            fetch(`/transactions/${transactionId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                $('#editTransactionId').val(data.id);
                
                // Set the customer name in the read-only input field
                $('#editCustomerName').val(data.customer_text);
                // Set the customer ID in the hidden input
                $('#editCustomerId').val(data.customer_id);
                
                $('#editGalonOut').val(data.galon_out);
                $('#editGalonIn').val(data.galon_in);
                $('#editTransactionDate').val(data.transaction_date.split(' ')[0]); // Only take the date part
                $('#editTotalPrice').val(data.total_price);
                showModalSmooth('#editTransactionModal');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to load transaction data', 'error');
            });
        });

        // Save Edited Transaction
        $('#saveEditTransaction').on('click', function() {
            const transactionId = $('#editTransactionId').val();
            const formData = new FormData($('#editTransactionForm')[0]);

            fetch(`/transactions/${transactionId}`, {
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
                    hideModalSmooth('#editTransactionModal');
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

        // Delete Transaction
        $('#transactionsTable').on('click', '.delete-transaction', function() {
            const transactionId = $(this).data('id');
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
                    fetch(`/transactions/${transactionId}`, {
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
                                'The transaction has been deleted.',
                                'success'
                            ).then(() => {
                                table.ajax.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to delete the transaction.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An unexpected error occurred', 'error');
                    });
                }
            });
        });

        // Batch Transaction
        let batchRowCount = 0;

        function addBatchRow() {
            const rowHtml = `
                <div class="row mb-3 batch-row g-2">
                    <div class="col-md-3 col-sm-12">
                        <select class="form-select batch-customer" name="batch[${batchRowCount}][customer_id]" required>
                            <option value="">Select Customer</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" class="form-control" name="batch[${batchRowCount}][galon_out]" placeholder="Galon Tarik" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" class="form-control" name="batch[${batchRowCount}][galon_in]" placeholder="Galon Kirim" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" class="form-control" name="batch[${batchRowCount}][transaction_date]" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" step="0.01" class="form-control" name="batch[${batchRowCount}][total_price]" placeholder="Total Price" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" step="0.01" class="form-control" name="batch[${batchRowCount}][debt_amount]" placeholder="Hutang (Opsional)">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="text" class="form-control" name="batch[${batchRowCount}][debt_notes]" placeholder="Catatan Hutang">
                    </div>
                    <div class="col-md-1 col-sm-12 text-end">
                        <button type="button" class="btn btn-danger btn-sm w-100 remove-batch-row">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#batchTransactions').append(rowHtml);
            initializeSelect2ForBatchRow(batchRowCount);
            batchRowCount++;
        }

        function initializeSelect2ForBatchRow(rowIndex) {
            $(`.batch-row:last .batch-customer`).select2({
                dropdownParent: $('#batchTransactionModal'),
                ajax: {
                    url: "{{ route('transactions.customers') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                placeholder: 'Select a customer',
                minimumInputLength: 1,
                width: '100%'
            });
        }

        $('#addBatchRow').on('click', addBatchRow);

        $('#batchTransactions').on('click', '.remove-batch-row', function() {
            $(this).closest('.batch-row').remove();
        });

        function showModalSmooth(modalId) {
            $(modalId).modal('show');
            setTimeout(function() {
                $(modalId + ' .modal-dialog').addClass('animate__animated animate__slideInDown');
            }, 100);
        }

        function hideModalSmooth(modalId) {
            $(modalId + ' .modal-dialog').addClass('animate__animated animate__slideOutUp');
            setTimeout(function() {
                $(modalId).modal('hide');
                $(modalId + ' .modal-dialog').removeClass('animate__animated animate__slideInDown animate__slideOutUp');
            }, 300);
        }

        $('#batchTransactionForm').on('submit', function(e) {
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
                    hideModalSmooth('#batchTransactionModal');
                    Swal.fire({
                        title: 'Success!',
                        text: 'Batch transactions added successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            table.ajax.reload();
                        }
                    });
                    form[0].reset();
                    $('#batchTransactions').empty();
                    batchRowCount = 0;
                } else {
                    Swal.fire('Error!', data.message || 'Failed to add batch transactions', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred', 'error');
            });
        });

        // Add initial batch row
        addBatchRow();

        $('#fetchSummary').on('click', function() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            
            if (!startDate || !endDate) {
                Swal.fire('Error!', 'Please select both start and end dates', 'error');
                return;
            }

            fetch(`/transactions/summary?start_date=${startDate}&end_date=${endDate}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = $('#summaryTable tbody');
                    tbody.empty();
                    
                    let totalTransactions = 0;
                    let totalGalonOut = 0;
                    let totalGalonIn = 0;
                    let totalRevenue = 0;

                    data.summary.forEach(row => {
                        tbody.append(`
                            <tr>
                                <td>${row.date}</td>
                                <td>${row.total_transactions}</td>
                                <td>${row.total_galon_out}</td>
                                <td>${row.total_galon_in}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.total_revenue)}</td>
                            </tr>
                        `);

                        // Calculate totals
                        totalTransactions += parseInt(row.total_transactions);
                        totalGalonOut += parseInt(row.total_galon_out);
                        totalGalonIn += parseInt(row.total_galon_in);
                        totalRevenue += parseFloat(row.total_revenue);
                    });

                    // Add total row
                    if (data.summary.length > 0) {
                        tbody.append(`
                            <tr class="table-info fw-bold">
                                <td>Total</td>
                                <td>${totalTransactions}</td>
                                <td>${totalGalonOut}</td>
                                <td>${totalGalonIn}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalRevenue)}</td>
                            </tr>
                        `);
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="5" class="text-center">No data available for the selected date range</td>
                            </tr>
                        `);
                    }
                } else {
                    Swal.fire('Error!', data.message || 'Failed to fetch summary', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred', 'error');
            });
        });

        $('#customer_id').on('change', function() {
            var customerId = $(this).val();
            if (customerId) {
                $.ajax({
                    url: '{{ route('transactions.customer-galon') }}',
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
            } else {
                $('#customer-galon-info').hide();
            }
        });

        // Modal reset handlers
        $('#addTransactionModal').on('show.bs.modal', function() {
            $('#addTransactionForm')[0].reset();
            $('#customer_id').val('').trigger('change');
            $('#debt_amount').val('');
            $('#debt_notes').val('');
            // Set default date to today
            const now = new Date();
            const pad = n => n.toString().padStart(2,'0');
            const localDate = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate());
            $('#transaction_date').val(localDate);
        });

        $('#batchTransactionModal').on('show.bs.modal', function() {
            $('#batchTransactionForm')[0].reset();
            $('#batchTransactions').empty();
            batchRowCount = 0;
            addBatchRow();
        });

        $('#summaryModal').on('show.bs.modal', function() {
            $('#startDate').val('');
            $('#endDate').val('');
            $('#summaryTable tbody').empty();
        });

        // Customer By Date Modal
        $('#fetchCustomerByDate').on('click', function() {
            const fromDate = $('#fromDate').val();
            const toDate = $('#toDate').val();
            if (!fromDate || !toDate) {
                Swal.fire('Error!', 'Pilih tanggal from dan to', 'error');
                return;
            }
            fetch(`/transactions/customers-by-date?from=${fromDate}&to=${toDate}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const tbody = $('#customerByDateTable tbody');
                tbody.empty();
                if (data.success && data.customers.length > 0) {
                    let totalGalonIn = 0;
                    let totalGalonOut = 0;
                    let totalPrice = 0;
                    let totalDebtPayment = 0;
                    data.customers.forEach(function(row, i) {
                        tbody.append(`
                            <tr>
                                <td>${i+1}</td>
                                <td>${new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(row.transaction_date))}</td>
                                <td>${row.name}</td>
                                <td>${row.phone_number || '-'}</td>
                                <td>${row.address || '-'}</td>
                                <td>${row.transaction_count}</td>
                                <td>${row.total_galon_in}</td>
                                <td>${row.total_galon_out}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.total_price)}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.total_debt_amount)}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.total_debt_payment)}</td>
                            </tr>
                        `);
                        totalGalonIn += parseInt(row.total_galon_in);
                        totalGalonOut += parseInt(row.total_galon_out);
                        totalPrice += parseFloat(row.total_price);
                        totalDebtPayment += parseFloat(row.total_debt_payment);
                    });
                    $('#totalGalonIn').text(totalGalonIn);
                    $('#totalGalonOut').text(totalGalonOut);
                    $('#totalPrice').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalPrice));
                    $('#totalDebtPayment').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalDebtPayment));
                } else {
                    tbody.append('<tr><td colspan="9" class="text-center">Tidak ada customer transaksi di range tanggal tersebut</td></tr>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Terjadi kesalahan', 'error');
            });
        });
    });
</script>
@endsection
