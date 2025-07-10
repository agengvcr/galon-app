@extends('layouts.app')

@section('styles')
<style>
    /* Select2 Flat UI Style */
    .select2-container--default .select2-selection--single {
        border: 1px solid #ced4da;
        border-radius: 4px;
        height: 38px;
        padding: 0.375rem 0.75rem;
        background-color: #fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px;
        padding-left: 0;
        color: #212529;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 4px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.375rem 0.75rem;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    /* Responsive form adjustments */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .card {
            margin-bottom: 1rem;
        }
    }

    /* Improve table responsiveness */
    .table-responsive {
        margin-bottom: 1rem;
        -webkit-overflow-scrolling: touch;
    }

    /* Card stats hover effect */
    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Action button styles */
    .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 1rem;
        line-height: 1;
        border-radius: 0.2rem;
    }

    .action-btn i {
        font-size: 0.875rem;
    }

    /* Tooltip custom styles */
    .tooltip-inner {
        background-color: #333;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1>Manajemen Hutang</h1>

    <!-- Action Buttons -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addDebtModal">
            Tambah Hutang
        </button>
        <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#debtSummaryModal">
            Lihat Ringkasan
        </button>
        <a href="{{ route('debts.index') }}" class="btn btn-secondary me-2" id="clearFilterBtn" style="display: none;">
            Hapus Filter
        </a>
    </div>

    <!-- Customer Filter Section -->
    <div class="alert alert-info" id="customerFilterSection" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Filter Pelanggan:</strong> <span id="filteredCustomerName"></span>
            </div>
            <a href="{{ route('debts.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i> Hapus Filter
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title fs-6">Total Belum Lunas</h5>
                    <h3 class="card-text fs-4" id="totalOutstanding">Loading...</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title fs-6">Total Terbayar</h5>
                    <h3 class="card-text fs-4" id="totalPaid">Loading...</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title fs-6">Hutang Aktif</h5>
                    <h3 class="card-text fs-4" id="activeDebts">Loading...</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Debts Table -->
    <div class="table-responsive">
        <table id="debtsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Jumlah</th>
                    <th>Terbayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add Debt Modal -->
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
                        <!-- Removed due date input -->
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

<!-- Edit Debt Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="editDebtModal" tabindex="-1">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Hutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editDebtForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editDebtId">
                    <div class="mb-3">
                        <label class="form-label">Pelanggan</label>
                        <input type="text" class="form-control" id="editCustomerName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editAmount" class="form-label">Jumlah</label>
                        <input type="number" step="0.01" class="form-control" id="editAmount" name="amount" required>
                    </div>
                    <!-- Removed due date input from edit form -->
                    <div class="mb-3">
                        <label for="editNotes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="saveEditDebt">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="recordPaymentForm">
                    <input type="hidden" id="paymentDebtId">
                    <div class="mb-3">
                        <label class="form-label">Pelanggan</label>
                        <input type="text" class="form-control" id="paymentCustomerName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sisa Jumlah</label>
                        <input type="text" class="form-control" id="remainingAmount" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Jumlah Pembayaran</label>
                        <input type="number" step="0.01" class="form-control" id="paymentAmount" name="payment_amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="recordPaymentDate" class="form-label">Tanggal Pembayaran</label>
                        <input type="date" class="form-control" id="recordPaymentDate" name="payment_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="savePaymentRecord">Catat Pembayaran</button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="debtSummaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Debt Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="summaryStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="summaryStartDate">
                    </div>
                    <div class="col-md-5">
                        <label for="summaryEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="summaryEndDate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" id="fetchSummary">Fetch</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="summaryTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Debts</th>
                                <th>Total Amount</th>
                                <th>Total Paid</th>
                                <th>Total Remaining</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Pembayaran Hutang -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Pembayaran Hutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    @csrf
                    <input type="hidden" id="paymentDebtId">
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Jumlah Pembayaran</label>
                        <input type="number" class="form-control" id="paymentAmount" name="payment_amount" min="1" required>
                        <div class="invalid-feedback" id="paymentAmountError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Tanggal Pembayaran</label>
                        <input type="date" class="form-control" id="paymentDate" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="paymentDescription" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="paymentDescription" name="description">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sisa Hutang</label>
                        <input type="text" class="form-control" id="remainingAmount" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="savePayment">Catat Pembayaran</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentHistoryModalLabel">Riwayat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="paymentHistoryTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#debtsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('debts.index') }}",
        columns: [
            { data: 'id' },
            { data: 'customer_name' },
            { 
                data: 'amount',
                render: function(data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data);
                }
            },
            { 
                data: 'paid_amount',
                render: function(data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data);
                }
            },
            { 
                data: 'remaining_amount',
                render: function(data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data);
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    const badges = {
                        'UNPAID': 'danger',
                        'PARTIALLY_PAID': 'warning',
                        'PAID': 'success'
                    };
                    return `<span class="badge bg-${badges[data]}">${data}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-warning btn-sm action-btn edit-debt" 
                                    data-id="${data.id}" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-info btn-sm action-btn record-payment" 
                                    data-id="${data.id}" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-title="Record Payment">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm action-btn payment-history" 
                                    data-id="${data.id}" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-title="Riwayat Pembayaran">
                                <i class="fas fa-list-ul"></i>
                            </button>
                            <button class="btn btn-danger btn-sm action-btn delete-debt" 
                                    data-id="${data.id}" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        drawCallback: function() {
            // Initialize tooltips after table draw
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });
        }
    });

    // Initialize Select2
    $('#customer_id').select2({
        dropdownParent: $('#addDebtModal'),
        ajax: {
            url: "{{ route('debts.customers') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        placeholder: 'Search customer by name or phone',
        minimumInputLength: 1,
        width: '100%',
        theme: 'default',
        language: {
            inputTooShort: function() {
                return 'Please enter at least 1 character';
            },
            searching: function() {
                return 'Searching...';
            },
            noResults: function() {
                return 'No customers found';
            }
        },
        templateResult: formatCustomer,
        templateSelection: formatCustomerSelection
    });

    // Custom formatting for dropdown items
    function formatCustomer(customer) {
        if (!customer.id) return customer.text;
        
        return $(`
            <div class="d-flex align-items-center">
                <div>
                    <div class="fw-bold">${customer.text.split('(')[0]}</div>
                    <small class="text-muted">${customer.text.split('(')[1].replace(')', '')}</small>
                </div>
            </div>
        `);
    }

    // Custom formatting for selected item
    function formatCustomerSelection(customer) {
        if (!customer.id) return customer.text;
        return customer.text.split('(')[0].trim();
    }

    // Load Statistics
    function loadStatistics() {
        fetch("{{ route('debts.statistics') }}")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.statistics;
                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
                    $('#totalOutstanding').text(formatter.format(stats.total_remaining));
                    $('#totalPaid').text(formatter.format(stats.total_paid_amount));
                    $('#activeDebts').text(stats.total_debts);
                }
            })
            .catch(error => console.error('Error loading statistics:', error));
    }

    // Add Debt Form Submission
    $('#addDebtForm').on('submit', function(e) {
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
                $('#addDebtModal').modal('hide');
                Swal.fire('Success!', data.message, 'success');
                table.ajax.reload();
                loadStatistics();
                form[0].reset();
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'An unexpected error occurred', 'error');
        });
    });

    // Edit Debt
    $('#debtsTable').on('click', '.edit-debt', function() {
        const debtId = $(this).data('id');
        fetch(`/debts/${debtId}/edit`)
            .then(response => response.json())
            .then(data => {
                $('#editDebtId').val(data.id);
                $('#editCustomerName').val(data.customer_text);
                $('#editAmount').val(data.amount);
                $('#editNotes').val(data.notes);
                $('#editDebtModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to load debt data', 'error');
            });
    });

    // Save Edit
    $('#saveEditDebt').on('click', function() {
        const debtId = $('#editDebtId').val();
        const formData = new FormData($('#editDebtForm')[0]);

        fetch(`/debts/${debtId}`, {
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
                $('#editDebtModal').modal('hide');
                Swal.fire('Success!', data.message, 'success');
                table.ajax.reload();
                loadStatistics();
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'An unexpected error occurred', 'error');
        });
    });

    // Record Payment
    $('#debtsTable').on('click', '.record-payment', function() {
        const debtId = $(this).data('id');
        fetch(`/debts/${debtId}/edit`)
            .then(response => response.json())
            .then(data => {
                $('#paymentDebtId').val(data.id);
                $('#paymentCustomerName').val(data.customer_text);
                $('#remainingAmount').val(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' })
                    .format(data.amount - data.paid_amount));
                $('#paymentAmount').val('').attr('max', data.amount - data.paid_amount);
                // Set tanggal pembayaran ke hari ini
                const today = new Date().toISOString().split('T')[0];
                $('#recordPaymentDate').val(today);
                $('#recordPaymentModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to load debt data', 'error');
            });
    });

    // Save Payment (untuk recordPaymentModal)
    $('#savePaymentRecord').on('click', function() {
        const debtId = $('#paymentDebtId').val();
        const formData = new FormData($('#recordPaymentForm')[0]);

        fetch(`/debts/${debtId}/payment`, {
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
                $('#recordPaymentModal').modal('hide');
                Swal.fire('Success!', data.message, 'success');
                table.ajax.reload();
                loadStatistics();
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'An unexpected error occurred', 'error');
        });
    });

    // Delete Debt
    $('#debtsTable').on('click', '.delete-debt', function() {
        const debtId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/debts/${debtId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        table.ajax.reload();
                        loadStatistics();
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An unexpected error occurred', 'error');
                });
            }
        });
    });

    // Fetch Summary
    $('#fetchSummary').on('click', function() {
        const startDate = $('#summaryStartDate').val();
        const endDate = $('#summaryEndDate').val();
        
        if (!startDate || !endDate) {
            Swal.fire('Error!', 'Please select both start and end dates', 'error');
            return;
        }

        fetch(`/debts/summary-by-date?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = $('#summaryTable tbody');
                    tbody.empty();
                    
                    if (data.summary.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center">No data available</td></tr>');
                        return;
                    }

                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
                    data.summary.forEach(row => {
                        tbody.append(`
                            <tr>
                                <td>${row.date}</td>
                                <td>${row.total_debts}</td>
                                <td>${formatter.format(row.total_amount)}</td>
                                <td>${formatter.format(row.total_paid_amount)}</td>
                                <td>${formatter.format(row.total_remaining)}</td>
                            </tr>
                        `);
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


    
    // Initial Load
    loadStatistics();

    // Tambah event untuk buka modal pembayaran hutang
    $(document).on('click', '.btn-payment', function() {
        const debtId = $(this).data('id');
        const remaining = $(this).data('remaining');
        $('#paymentDebtId').val(debtId);
        $('#remainingAmount').val(remaining);
        $('#paymentAmount').val('');
        $('#paymentAmountError').text('');
        // Set payment date to today
        const today = new Date().toISOString().split('T')[0];
        $('#paymentDate').val(today);
        $('#paymentModal').modal('show');
    });

    // Validasi jumlah pembayaran di sisi view
    $('#paymentAmount').on('input', function() {
        const max = parseFloat($('#remainingAmount').val());
        const val = parseFloat($(this).val());
        if (val > max) {
            $('#paymentAmountError').text('Jumlah pembayaran melebihi sisa hutang!').show();
            $(this).addClass('is-invalid');
        } else {
            $('#paymentAmountError').text('').hide();
            $(this).removeClass('is-invalid');
        }
    });

    // Submit pembayaran
    $('#savePayment').on('click', function() {
        const debtId = $('#paymentDebtId').val();
        const amount = parseFloat($('#paymentAmount').val());
        const max = parseFloat($('#remainingAmount').val());
        if (amount > max) {
            $('#paymentAmountError').text('Jumlah pembayaran melebihi sisa hutang!').show();
            $('#paymentAmount').addClass('is-invalid');
            return;
        }
        const data = {
            payment_amount: amount,
            payment_date: $('#paymentDate').val(),
            description: $('#paymentDescription').val(),
            _token: $('input[name="_token"]').val()
        };
        $.ajax({
            url: '/debts/' + debtId + '/payment',
            method: 'POST',
            data: data,
            success: function(res) {
                if (res.success) {
                    $('#paymentModal').modal('hide');
                    location.reload();
                } else {
                    $('#paymentAmountError').text(res.message).show();
                    $('#paymentAmount').addClass('is-invalid');
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#paymentAmountError').text(msg).show();
                $('#paymentAmount').addClass('is-invalid');
            }
        });
    });

    // Payment History
    $(document).on('click', '.payment-history', function() {
        const debtId = $(this).data('id');
        // Kosongkan tabel
        const tbody = $('#paymentHistoryTable tbody');
        tbody.empty();
        // Fetch data
        fetch(`/debts/${debtId}/payments`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payments.length > 0) {
                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
                    data.payments.forEach(payment => {
                        tbody.append(`
                            <tr>
                                <td>${new Date(payment.payment_date).toLocaleDateString('id-ID')}</td>
                                <td>${formatter.format(payment.amount)}</td>
                                <td>${payment.description ?? ''}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="3" class="text-center">Tidak ada riwayat pembayaran</td></tr>');
                }
                $('#paymentHistoryModal').modal('show');
            })
            .catch(error => {
                tbody.append('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data</td></tr>');
                $('#paymentHistoryModal').modal('show');
            });
    });

    // Handle URL parameters for customer filter
    const urlParams = new URLSearchParams(window.location.search);
    const customerId = urlParams.get('customer_id');
    const customerName = urlParams.get('customer_name');

    if (customerId) {
        $('#customer_id').val(customerId).trigger('change'); // Trigger change to load customer name
        $('#customerFilterSection').show();
        $('#filteredCustomerName').text(customerName);
        $('#clearFilterBtn').show();
        table.ajax.url(`{{ route('debts.index') }}?customer_id=${customerId}`).load(); // Reload table with filter
    }
});
</script>
@endsection 