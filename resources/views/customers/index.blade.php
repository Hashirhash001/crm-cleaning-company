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

    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Customers Management</h4>
            <div class="">
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
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Priority</label>
                        <select class="form-select" id="priorityFilter">
                            <option value="">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchCustomer" placeholder="Search by name or email...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sort By</label>
                        <select class="form-select" id="sortBy">
                            <option value="">Default (Latest First)</option>
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="priority-high">Priority (High First)</option>
                            <option value="priority-low">Priority (Low First)</option>
                            <option value="jobs-high">Most Jobs</option>
                            <option value="jobs-low">Least Jobs</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn btn-secondary w-100" id="resetFilters">
                            <i class="fas fa-redo me-2"></i> Reset Filters
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
            <div class="card-header">
                <h4 class="card-title">Customers List (<span id="customerCount">{{ $customers->total() }}</span> total)</h4>
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
                            <span class="error-text name_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <span class="error-text email_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                            <span class="error-text phone_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_priority" name="priority" required>
                                <option value="low">Low Priority</option>
                                <option value="medium">Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                            <span class="error-text priority_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                            <span class="error-text address_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_notes" class="form-label">General Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                            <span class="error-text notes_error text-danger d-block mt-1"></span>
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
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let currentSort = { column: null, direction: 'asc' };

            // AJAX Load Function
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
            $('#priorityFilter, #searchCustomer, #sortBy').on('change keyup', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    loadCustomers();
                }, 300);
            });

            // Reset Filters
            $('#resetFilters').click(function() {
                $('#priorityFilter, #searchCustomer, #sortBy').val('');
                $('.sortable').removeClass('asc desc');
                currentSort = { column: null, direction: 'asc' };
                window.location.href = '{{ route("customers.index") }}';
            });

            // Edit Customer
            $(document).on('click', '.editCustomerBtn', function() {
                let customerId = $(this).data('id');

                $.ajax({
                    url: '/customers/' + customerId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.customer) {
                            let customer = response.customer;
                            $('#customer_id').val(customer.id);
                            $('#edit_name').val(customer.name);
                            $('#edit_email').val(customer.email);
                            $('#edit_phone').val(customer.phone);
                            $('#edit_priority').val(customer.priority);
                            $('#edit_address').val(customer.address);
                            $('#edit_notes').val(customer.notes);
                            $('.error-text').text('');
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

            // Submit Edit Customer Form
            $('#editCustomerForm').on('submit', function(e) {
                e.preventDefault();

                let customerId = $('#customer_id').val();
                let formData = new FormData(this);
                formData.append('_method', 'PUT');

                $('.error-text').text('');

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
                            loadCustomers(); // Use AJAX reload instead of location.reload()
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
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
                            aVal = $(a).data('code');
                            bVal = $(b).data('code');
                            break;
                        case 'name':
                            aVal = $(a).data('name');
                            bVal = $(b).data('name');
                            break;
                        case 'email':
                            aVal = $(a).data('email');
                            bVal = $(b).data('email');
                            break;
                        case 'priority':
                            let priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
                            aVal = priorityOrder[$(a).data('priority')];
                            bVal = priorityOrder[$(b).data('priority')];
                            break;
                        case 'total-jobs':
                        case 'jobs':
                            aVal = parseInt($(a).data('total-jobs'));
                            bVal = parseInt($(b).data('total-jobs'));
                            break;
                        case 'completed-jobs':
                            aVal = parseInt($(a).data('completed-jobs'));
                            bVal = parseInt($(b).data('completed-jobs'));
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
