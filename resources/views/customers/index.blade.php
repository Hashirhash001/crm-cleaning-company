@extends('layouts.app')

@section('title', 'Customers Management')

@section('extra-css')
    <style>
        /* Sortable headers */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 20px !important;
        }
        .sortable:hover {
            background-color: #e9ecef;
        }
        .sortable::after {
            content: '⇅';
            position: absolute;
            right: 8px;
            opacity: 0.3;
        }
        .sortable.asc::after {
            content: '↑';
            opacity: 1;
        }
        .sortable.desc::after {
            content: '↓';
            opacity: 1;
        }

        /* Clickable customer name styling */
        .customer-name-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .customer-name-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        .customer-name-link h6 {
            color: inherit;
            font-weight: 600;
            margin: 0;
        }

        .error-text {
            display: none;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Customers Management</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Customers</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body" style="padding: 1.5rem; border-radius: 0.4rem;">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Priority</label>
                        <select class="form-select" id="priorityFilter">
                            <option value="">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchCustomer" placeholder="Search by name or email...">
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn btn-secondary w-100" id="resetFilters">
                            <i class="las la-redo me-2"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customers Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    Customers List (<span id="customerCount">{{ $customers->total() }}</span> total)
                </h4>

                @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']))
                <a href="{{ route('customers.create') }}" class="btn btn-success">
                    <i class="las la-plus me-1"></i> Create Customer
                </a>
                @endif
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="customersTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-column="code">Customer Code</th>
                                <th class="sortable" data-column="name">Name</th>
                                <th class="sortable" data-column="email">Email</th>
                                <th>Phone</th>
                                <th class="sortable" data-column="priority">Priority</th>
                                <th class="sortable" data-column="total-jobs">Total Jobs</th>
                                <th class="sortable" data-column="completed-jobs">Completed Jobs</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="customersTableBody">
                            @include('customers.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCustomerForm">
                @csrf
                <input type="hidden" id="customer_id" name="customer_id">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <span class="error-text name_error text-danger"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                            <span class="error-text email_error text-danger"></span>
                            <small class="text-muted">Email is optional</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                            <span class="error-text phone_error text-danger"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_priority" name="priority" required>
                                <option value="low">Low Priority</option>
                                <option value="medium">Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                            <span class="error-text priority_error text-danger"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                            <span class="error-text address_error text-danger"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_notes" class="form-label">General Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                            <span class="error-text notes_error text-danger"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <input type="hidden" id="note_customer_id" name="note_customer_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="note" name="note" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        @if(session('success'))
            let successData = @json(json_decode(session('success'), true));
            Swal.fire({
                icon: 'success',
                title: successData.title,
                html: `<p>${successData.message}</p>
                    ${successData.customer_code ? `<p><strong>Customer Code:</strong> <span class="badge bg-primary">${successData.customer_code}</span></p>` : ''}
                    ${successData.name ? `<p><strong>Name:</strong> ${successData.name}</p>` : ''}`,
                timer: 4000,
                showConfirmButton: true,
                confirmButtonColor: '#28a745'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session("error") }}',
            });
        @endif

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let currentSort = { column: null, direction: 'asc' };

            // AJAX Load Function - FIXED
            function loadCustomers(url = null) {
                let requestUrl = url || '{{ route("customers.index") }}';
                let params = {
                    priority: $('#priorityFilter').val(),
                    search: $('#searchCustomer').val(),
                    sort_by: $('#sortBy').val()
                };

                $.ajax({
                    url: requestUrl,
                    type: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'  // This is crucial!
                    },
                    success: function(response) {
                        $('#customersTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#customerCount').text(response.total);
                    },
                    error: function(xhr) {
                        console.error('Error loading customers:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load customers'
                        });
                    }
                });
            }

            // Pagination Click Handler
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                loadCustomers(url);
            });

            // Filters with Debounce
            let filterTimeout;
            $('#priorityFilter, #sortBy').on('change', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    loadCustomers();
                }, 300);
            });

            $('#searchCustomer').on('keyup', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    loadCustomers();
                }, 500);
            });

            // Reset Filters
            $('#resetFilters').click(function() {
                $('#priorityFilter').val('');
                $('#searchCustomer').val('');
                $('#sortBy').val('');
                $('.sortable').removeClass('asc desc');
                currentSort = { column: null, direction: 'asc' };
                loadCustomers();
            });

            // Edit Customer
            $(document).on('click', '.editCustomerBtn', function() {
                let customerId = $(this).data('id');

                // Clear previous errors
                $('.error-text').text('').hide();
                $('input, select, textarea').removeClass('is-invalid');

                $.ajax({
                    url: '/customers/' + customerId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.customer) {
                            let customer = response.customer;
                            $('#customer_id').val(customer.id);
                            $('#edit_name').val(customer.name);
                            $('#edit_email').val(customer.email || '');
                            $('#edit_phone').val(customer.phone);
                            $('#edit_priority').val(customer.priority);
                            $('#edit_address').val(customer.address || '');
                            $('#edit_notes').val(customer.notes || '');
                            $('#editCustomerModal').modal('show');
                        } else {
                            Swal.fire('Error!', 'Failed to load customer data', 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Edit customer error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load customer data',
                        });
                    }
                });
            });

            // Remove error styling when user types in edit modal
            $('#editCustomerModal input, #editCustomerModal select, #editCustomerModal textarea').on('input change', function() {
                $(this).removeClass('is-invalid');
                let fieldName = $(this).attr('name');
                $('.' + fieldName + '_error').text('').hide();
            });

            // Submit Edit Customer Form
            $('#editCustomerForm').on('submit', function(e) {
                e.preventDefault();

                let customerId = $('#customer_id').val();
                let formData = new FormData(this);
                formData.append('_method', 'PUT');

                // Clear previous errors
                $('.error-text').text('').hide();
                $('input, select, textarea').removeClass('is-invalid');

                $.ajax({
                    url: '/customers/' + customerId,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#editCustomerModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadCustomers();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#edit_' + key).addClass('is-invalid');
                                $('.' + key + '_error').text(value[0]).show();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to update customer'
                            });
                        }
                    }
                });
            });

            // Add Note
            $(document).on('click', '.addNoteBtn', function() {
                let customerId = $(this).data('id');
                $('#note_customer_id').val(customerId);
                $('#note').val('');
                $('#addNoteModal').modal('show');
            });

            // Submit Add Note Form
            $('#addNoteForm').on('submit', function(e) {
                e.preventDefault();

                let customerId = $('#note_customer_id').val();
                let formData = new FormData(this);

                $.ajax({
                    url: '/customers/' + customerId + '/notes',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addNoteModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Note Added!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to add note'
                        });
                    }
                });
            });

            // Table Header Sorting (Client-side for current page)
            $(document).on('click', '.sortable', function() {
                let column = $(this).data('column');

                // Toggle direction
                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                // Update header indicators
                $('.sortable').removeClass('asc desc');
                $(this).addClass(currentSort.direction);

                // Sort the table
                sortTable(column, currentSort.direction);
            });

            function sortTable(column, direction) {
                let rows = $('#customersTableBody tr').get();

                rows.sort(function(a, b) {
                    let aVal, bVal;

                    switch(column) {
                        case 'code':
                            aVal = $(a).data('code') || '';
                            bVal = $(b).data('code') || '';
                            break;
                        case 'name':
                            aVal = $(a).data('name') || '';
                            bVal = $(b).data('name') || '';
                            break;
                        case 'email':
                            aVal = $(a).data('email') || '';
                            bVal = $(b).data('email') || '';
                            break;
                        case 'priority':
                            let priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
                            aVal = priorityOrder[$(a).data('priority')] || 0;
                            bVal = priorityOrder[$(b).data('priority')] || 0;
                            break;
                        case 'total-jobs':
                        case 'jobs':
                            aVal = parseInt($(a).data('total-jobs')) || 0;
                            bVal = parseInt($(b).data('total-jobs')) || 0;
                            break;
                        case 'completed-jobs':
                            aVal = parseInt($(a).data('completed-jobs')) || 0;
                            bVal = parseInt($(b).data('completed-jobs')) || 0;
                            break;
                    }

                    if (direction === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });

                $.each(rows, function(index, row) {
                    $('#customersTableBody').append(row);
                });
            }
        });
    </script>
@endsection
