@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Customers</h1>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        Add New Customer
    </button>   

    <div class="table-responsive">
        <table id="customersTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Actions</th>
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
                <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm" action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="addCustomerForm" class="btn btn-primary">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade animate__animated" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog animate__animated animate__slideInDown">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCustomerId" name="id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPhoneNumber" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditCustomer">Save changes</button>
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
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-warning btn-sm edit-customer" data-id="' + row.id + '">Edit</button> ' +
                               '<button class="btn btn-danger btn-sm delete-customer" data-id="' + row.id + '">Delete</button>';
                    }
                }
            ],
            "order": [[0, "desc"]]
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
    });
</script>
@endsection
