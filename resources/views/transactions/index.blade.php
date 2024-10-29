@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Transactions</h1>

    <button type="button" class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        Add New Transaction
    </button>   
    <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#batchTransactionModal">
        Add Batch Transactions
    </button>

    <div class="table-responsive">
        <table id="transactionsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Galon Out</th>
                    <th>Galon In</th>
                    <th>Date</th>
                    <th>Total Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTransactionModalLabel">Add New Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTransactionForm" action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id" required style="width: 100%;">
                            <!-- Options will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="galon_out" class="form-label">Galon Out</label>
                        <input type="number" class="form-control" id="galon_out" name="galon_out" required>
                    </div>
                    <div class="mb-3">
                        <label for="galon_in" class="form-label">Galon In</label>
                        <input type="number" class="form-control" id="galon_in" name="galon_in" required>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="total_price" class="form-label">Total Price</label>
                        <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="addTransactionForm" class="btn btn-primary">Save Transaction</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Transaction Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTransactionForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTransactionId" name="id">
                    <div class="mb-3">
                        <label for="editCustomerName" class="form-label">Customer</label>
                        <input type="text" class="form-control" id="editCustomerName" readonly>
                        <input type="hidden" id="editCustomerId" name="customer_id">
                    </div>
                    <div class="mb-3">
                        <label for="editGalonOut" class="form-label">Galon Out</label>
                        <input type="number" class="form-control" id="editGalonOut" name="galon_out" required>
                    </div>
                    <div class="mb-3">
                        <label for="editGalonIn" class="form-label">Galon In</label>
                        <input type="number" class="form-control" id="editGalonIn" name="galon_in" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTransactionDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="editTransactionDate" name="transaction_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTotalPrice" class="form-label">Total Price</label>
                        <input type="number" step="0.01" class="form-control" id="editTotalPrice" name="total_price" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditTransaction">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Transaction Modal -->
<div class="modal fade animate__animated animate__fadeIn" id="batchTransactionModal" tabindex="-1" aria-labelledby="batchTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchTransactionModalLabel">Add Batch Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="batchTransactionForm" action="{{ route('transactions.storeBatch') }}" method="POST">
                    @csrf
                    <div id="batchTransactions">
                        <!-- Batch transaction rows will be added here dynamically -->
                    </div>
                  
                    <button type="button" class="btn btn-secondary mt-3" id="addBatchRow">Add Row</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="batchTransactionForm" class="btn btn-primary">Save Batch Transactions</button>
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
                { "data": "id" },
                { "data": "customer_name" },
                { "data": "galon_out" },
                { "data": "galon_in" },
                { "data": "transaction_date" },
                { "data": "total_price" },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-warning btn-sm edit-transaction" data-id="' + row.id + '">Edit</button> ' +
                               '<button class="btn btn-danger btn-sm delete-transaction" data-id="' + row.id + '">Delete</button>';
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
                    $('#addTransactionModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: 'Transaction added successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            table.ajax.reload();
                        }
                    });
                    form[0].reset();
                } else {
                    Swal.fire('Error!', data.message || 'Failed to add transaction', 'error');
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
                        <input type="number" class="form-control" name="batch[${batchRowCount}][galon_out]" placeholder="Galon Out" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" class="form-control" name="batch[${batchRowCount}][galon_in]" placeholder="Galon In" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" class="form-control" name="batch[${batchRowCount}][transaction_date]" required>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" step="0.01" class="form-control" name="batch[${batchRowCount}][total_price]" placeholder="Total Price" required>
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
    });
</script>
@endsection
