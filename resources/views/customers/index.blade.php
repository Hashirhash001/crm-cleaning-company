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
            font-size: 14px;
        }
        .sortable.asc::after {
            content: '↑';
            opacity: 1;
            color: #0d6efd;
        }
        .sortable.desc::after {
            content: '↓';
            opacity: 1;
            color: #0d6efd;
        }

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

        /* Loading overlay */
        .table-loading {
            position: relative;
            opacity: 0.6;
            pointer-events: none;
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

<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card border-success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h3 class="mb-1 text-success fw-bold" id="totalRevenueDisplay">
                    ₹{{ number_format($totalRevenue ?? 0) }}
                </h3>
                <small class="text-muted">Total Revenue</small>
                <small class="d-block text-muted" style="font-size: 0.75rem;">
                    (All Completed/Approved Jobs)
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h3 class="mb-1 text-primary" id="totalCustomersDisplay">
                    {{ $totalCustomers ?? 0 }}
                </h3>
                <small class="text-muted">Total Customers</small>
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
                    <!-- Add to filters section -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Branch</label>
                        <select class="form-select" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchCustomer" placeholder="Search by name, email, phone or code...">
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

                <div class="d-flex gap-2">
                    <!-- Add this button next to other action buttons -->
                    <a href="#" class="btn btn-success" id="exportCustomersBtn">
                        <i class="las la-file-download me-1"></i> Export CSV
                    </a>

                    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']))
                    <a href="{{ route('customers.create') }}" class="btn btn-success">
                        <i class="las la-plus me-1"></i> Create Customer
                    </a>
                    @endif
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="customersTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-column="code">Customer Code</th>
                                <th class="sortable" data-column="name">Name</th>
                                <th>Phone</th>
                                @if(auth()->user()->role === 'super_admin')
                                    <th class="sortable" data-column="branch">Branch</th>
                                @endif
                                <th class="sortable" data-column="priority">Priority</th>
                                {{-- <th class="sortable" data-column="total-jobs">Total Work Orders</th> --}}
                                <th class="sortable" data-column="completed-jobs">Completed Work Orders</th>
                                <th class="sortable" data-column="customer_value">
                                    Customer Value
                                </th>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="las la-sticky-note me-2"></i>Add Customer Note
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <input type="hidden" id="note_customer_id" name="note_customer_id">
                <div class="modal-body">
                    <!-- ===== FIX: Customer Info Display ===== -->
                    <div class="alert alert-light border border-primary mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="las la-user text-primary" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong class="d-block text-dark">Customer:</strong>
                                <span id="note_customer_name" class="text-primary fw-semibold">Loading...</span>
                                <span id="note_customer_code" class="badge bg-secondary ms-2"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Note Text -->
                    <div class="mb-3">
                        <label for="note" class="form-label fw-semibold">
                            Note <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                                  id="note"
                                  name="note"
                                  rows="4"
                                  required
                                  placeholder="Enter customer note or instruction..."></textarea>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i>
                            Add important information about customer preferences, issues, or instructions
                        </small>
                    </div>

                    <!-- Link to Job (Optional) -->
                    <div class="mb-3">
                        <label for="job_id" class="form-label fw-semibold">
                            Link to Job (Optional)
                        </label>
                        <select class="form-select" id="job_id" name="job_id">
                            <option value="">Loading jobs...</option>
                        </select>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i>
                            Link this note to a specific job if relevant
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i> Add Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notes Modal -->
<div class="modal fade" id="viewNotesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="las la-list me-2"></i>Customer Notes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Customer Info -->
                <div class="alert alert-light border-start border-info border-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="las la-user"></i> Customer:</strong>
                            <span id="view_customer_name" class="text-primary"></span>
                        </div>
                        <span id="view_customer_code" class="badge bg-secondary"></span>
                    </div>
                </div>

                <!-- Notes List -->
                <div id="notesListContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Loading notes...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="las la-times me-1"></i> Close
                </button>
            </div>
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

            // ============================================
            // SORTING STATE
            // ============================================
            let currentSort = {
                column: 'completed-jobs',
                direction: 'desc'
            };

            updateSortIndicators(currentSort.column, currentSort.direction);

            // Store current customer data for modals
            let currentCustomer = {
                id: null,
                name: null,
                code: null,
                jobs: []
            };

            // ============================================
            // AJAX LOAD FUNCTION WITH SORTING
            // ============================================
            function loadCustomers(url = null) {
                let requestUrl = url || '{{ route("customers.index") }}';

                let params = {
                    priority: $('#priorityFilter').val(),
                    search: $('#searchCustomer').val(),
                    branch_id: $('#branchFilter').val(),
                    sort_column: currentSort.column,
                    sort_direction: currentSort.direction
                };

                $('#customersTable').addClass('table-loading');

                $.ajax({
                    url: requestUrl,
                    type: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#customersTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#customerCount').text(response.total);

                        if (response.stats) {
                            $('#totalRevenueDisplay').text('₹' + response.stats.total_revenue);
                            $('#totalCustomersDisplay').text(response.stats.total_customers);
                            $('#activeCustomersDisplay').text(response.stats.active_customers);
                        }

                        if (response.current_sort) {
                            currentSort.column = response.current_sort.column;
                            currentSort.direction = response.current_sort.direction;

                            updateSortIndicators(currentSort.column, currentSort.direction);
                        } else {
                            updateSortIndicators(currentSort.column, currentSort.direction);
                        }


                        $('#customersTable').removeClass('table-loading');
                    },
                    error: function(xhr) {
                        console.error('Error loading customers:', xhr);
                        $('#customersTable').removeClass('table-loading');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load customers'
                        });
                    }
                });
            }

            // ============================================
            // UPDATE SORT INDICATORS
            // ============================================
            function updateSortIndicators(column, direction) {
                $('.sortable').removeClass('asc desc');
                $(`.sortable[data-column="${column}"]`).addClass(direction);
            }

            // ============================================
            // SORT COLUMN CLICK HANDLER
            // ============================================
            $(document).on('click', '.sortable', function() {
                let column = $(this).data('column');

                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                loadCustomers();
            });

            // ============================================
            // PAGINATION CLICK HANDLER
            // ============================================
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                if (url) {
                    loadCustomers(url);
                }
            });

            // ============================================
            // FILTERS WITH DEBOUNCE
            // ============================================
            let filterTimeout;

            $('#priorityFilter').on('change', function() {
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

            // Branch filter (for super admin)
            $('#branchFilter').on('change', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    loadCustomers();
                }, 300);
            });

            // ============================================
            // RESET FILTERS
            // ============================================
            $('#resetFilters').click(function() {
                $('#priorityFilter').val('');
                $('#searchCustomer').val('');
                $('#branchFilter').val('');

                currentSort = { column: 'completed-jobs', direction: 'desc' };
                updateSortIndicators(currentSort.column, currentSort.direction);

                loadCustomers();
            });

            // ============================================
            // EDIT CUSTOMER
            // ============================================
            $(document).on('click', '.editCustomerBtn', function() {
                let customerId = $(this).data('id');

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

            $('#editCustomerModal input, #editCustomerModal select, #editCustomerModal textarea').on('input change', function() {
                $(this).removeClass('is-invalid');
                let fieldName = $(this).attr('name');
                $('.' + fieldName + '_error').text('').hide();
            });

            // ============================================
            // SUBMIT EDIT CUSTOMER FORM
            // ============================================
            $('#editCustomerForm').on('submit', function(e) {
                e.preventDefault();

                let customerId = $('#customer_id').val();
                let formData = new FormData(this);
                formData.append('_method', 'PUT');

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

            // ============================================
            // ADD NOTE - LOAD CUSTOMER JOBS
            // ============================================
            $(document).on('click', '.addNoteBtn', function() {
                let customerId = $(this).data('id');
                let customerName = $(this).data('name');
                let customerCode = $(this).data('code');

                console.log('Add Note Button Clicked:', {
                    id: customerId,
                    name: customerName,
                    code: customerCode
                });

                // Store current customer data
                currentCustomer.id = customerId;
                currentCustomer.name = customerName;
                currentCustomer.code = customerCode;

                // ===== UPDATE MODAL DISPLAY =====
                $('#note_customer_id').val(customerId);
                $('#note_customer_name').text(customerName || 'Unknown Customer');
                $('#note_customer_code').text(customerCode || 'N/A');
                $('#note').val('');

                // Reset and show loading in job dropdown
                $('#job_id').html('<option value="">Loading jobs...</option>');

                // Show modal immediately
                $('#addNoteModal').modal('show');

                // Load customer jobs for dropdown
                loadCustomerJobs(customerId);
            });

            // Helper function to load customer jobs
            function loadCustomerJobs(customerId) {
                $.ajax({
                    url: `/api/customers/${customerId}/jobs`,
                    type: 'GET',
                    success: function(jobs) {
                        let options = '<option value="">General Note (Not linked to any job)</option>';

                        if (jobs && jobs.length > 0) {
                            jobs.forEach(function(job) {
                                options += `<option value="${job.id}">
                                    ${job.job_code} - ${job.title || job.service_name} (${job.status_label})
                                </option>`;
                            });
                        } else {
                            options = '<option value="">General Note (No jobs available)</option>';
                        }

                        $('#job_id').html(options);
                    },
                    error: function(xhr) {
                        console.error('Failed to load jobs:', xhr);
                        $('#job_id').html('<option value="">General Note (Not linked to any job)</option>');
                    }
                });
            }

            // ============================================
            // SUBMIT ADD NOTE FORM
            // ============================================
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

            // ============================================
            // VIEW NOTES
            // ============================================
            $(document).on('click', '.viewNotesBtn', function() {
                let customerId = $(this).data('id');
                let customerName = $(this).data('name');
                let customerCode = $(this).data('code');

                $('#view_customer_name').text(customerName);
                $('#view_customer_code').text(customerCode);

                // Show loading
                $('#notesListContainer').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Loading notes...</p>
                    </div>
                `);

                $('#viewNotesModal').modal('show');

                // Load notes
                loadCustomerNotes(customerId);
            });

            // Helper function to load and display notes
            function loadCustomerNotes(customerId) {
                $.ajax({
                    url: '/customers/' + customerId,
                    type: 'GET',
                    success: function(htmlResponse) {
                        // Since the show method returns HTML, we need a JSON endpoint
                        // For now, let's create a workaround
                        fetchNotesAsJSON(customerId);
                    },
                    error: function() {
                        $('#notesListContainer').html(`
                            <div class="alert alert-danger">
                                <i class="las la-exclamation-triangle"></i>
                                Failed to load notes
                            </div>
                        `);
                    }
                });
            }

            // Fetch notes as JSON (you'll need to add this endpoint)
            function fetchNotesAsJSON(customerId) {
                $.ajax({
                    url: '/api/customers/' + customerId + '/notes',
                    type: 'GET',
                    success: function(notes) {
                        displayNotes(notes, customerId);
                    },
                    error: function() {
                        $('#notesListContainer').html(`
                            <div class="text-center py-5">
                                <i class="las la-sticky-note" style="font-size: 4rem; opacity: 0.2;"></i>
                                <p class="text-muted mt-3">No notes found</p>
                            </div>
                        `);
                    }
                });
            }

            // Display notes in modal
            function displayNotes(notes, customerId) {
                if (!notes || notes.length === 0) {
                    $('#notesListContainer').html(`
                        <div class="text-center py-5">
                            <i class="las la-sticky-note" style="font-size: 4rem; opacity: 0.2;"></i>
                            <p class="text-muted mt-3 mb-0">No notes added yet</p>
                            <small class="text-muted">Add your first note to keep track of customer interactions</small>
                        </div>
                    `);
                    return;
                }

                let notesHtml = '';

                notes.forEach(function(note) {
                    let canDelete = {{ auth()->user()->role === 'super_admin' ? 'true' : 'false' }} ||
                                   note.created_by === {{ auth()->id() }};

                    notesHtml += `
                        <div class="card mb-2 note-card" id="note-${note.id}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>${note.created_by_name || 'Unknown'}</strong>
                                        <small class="text-muted ms-2">${note.created_at_human || ''}</small>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        ${note.job ? `
                                            <a href="/jobs/${note.job.id}" class="badge bg-info text-decoration-none"
                                               title="View Related Job: ${note.job.title || ''}">
                                                <i class="las la-briefcase"></i> ${note.job.job_code}
                                            </a>
                                        ` : '<span class="badge bg-secondary">General Note</span>'}

                                        ${canDelete ? `
                                            <button type="button"
                                                    class="deleteNoteBtn"
                                                    data-customer-id="${customerId}"
                                                    data-note-id="${note.id}"
                                                    title="Delete Note">
                                                <i class="las la-trash text-danger fs-5"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>

                                ${note.job ? `
                                    <div class="alert alert-light border-start border-info border-3 py-2 px-3 mb-2">
                                        <small class="d-block">
                                            <i class="las la-briefcase text-info"></i>
                                            <strong>Related to:</strong> ${note.job.title || 'N/A'}
                                        </small>
                                        <small class="text-muted d-block">
                                            Status: <span class="badge badge-${note.job.status} badge-sm">
                                                ${note.job.status ? note.job.status.replace('_', ' ') : 'N/A'}
                                            </span>
                                            ${note.job.amount ? `| Amount: ₹${parseFloat(note.job.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}` : ''}
                                        </small>
                                    </div>
                                ` : ''}

                                <p class="mb-0">${note.note || ''}</p>
                            </div>
                        </div>
                    `;
                });

                $('#notesListContainer').html(notesHtml);
            }

            // ============================================
            // DELETE NOTE
            // ============================================
            $(document).on('click', '.deleteNoteBtn', function() {
                let noteId = $(this).data('note-id');
                let customerId = $(this).data('customer-id');
                let noteCard = $('#note-' + noteId);

                Swal.fire({
                    title: 'Delete Note?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/customers/' + customerId + '/notes/' + noteId,
                            type: 'DELETE',
                            success: function(response) {
                                noteCard.fadeOut(300, function() {
                                    $(this).remove();

                                    if ($('.note-card').length === 0) {
                                        fetchNotesAsJSON(customerId);
                                    }
                                });

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'Failed to delete note',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
                    }
                });
            });

            // Export Button Handler
            $('#exportCustomersBtn').on('click', function(e) {
                e.preventDefault();

                let totalCustomers = parseInt($('#customerCount').text()) || 0;
                let exportLimit = 10000;
                let willExport = Math.min(totalCustomers, exportLimit);

                // Show warning if exceeding limit
                if (totalCustomers > exportLimit) {
                    Swal.fire({
                        title: 'Export Limit Warning',
                        html: `
                            <div class="text-start">
                                <p><strong>Total Customers:</strong> ${totalCustomers}</p>
                                <p><strong>Export Limit:</strong> ${exportLimit}</p>
                                <p class="text-warning mb-0">
                                    <i class="las la-exclamation-triangle"></i>
                                    Only the first ${exportLimit} customers will be exported.
                                </p>
                                <p class="text-muted mt-2" style="font-size: 13px;">
                                    Please apply additional filters to reduce the result set.
                                </p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Export Anyway',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            startExport();
                        }
                    });
                } else {
                    startExport();
                }

                function startExport() {
                    // Show loading
                    let timerInterval;
                    Swal.fire({
                        title: 'Exporting Customers...',
                        html: `
                            <div style="text-align: center;">
                                <p>Processing <strong>${willExport}</strong> customers</p>
                                <div class="progress mt-3" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 100%">
                                        Generating CSV...
                                    </div>
                                </div>
                                <p class="text-muted mt-3" style="font-size: 13px;">
                                    <i class="las la-clock"></i> <span id="exportTimer">0</span>s
                                </p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            let seconds = 0;
                            timerInterval = setInterval(() => {
                                seconds++;
                                $('#exportTimer').text(seconds);
                            }, 1000);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                        }
                    });

                    // Build params
                    let params = new URLSearchParams();
                    let priority = $('#priorityFilter').val();
                    let branchId = $('#branchFilter').val();
                    let isActive = $('#statusFilter').val();
                    let search = $('#searchInput').val();

                    // Add non-empty filters to params
                    if (priority && priority !== '') params.append('priority', priority);
                    if (branchId && branchId !== '') params.append('branch_id', branchId);
                    if (isActive && isActive !== '') params.append('is_active', isActive);
                    if (search) params.append('search', search);

                    // Download with iframe
                    let exportUrl = "{{ route('customers.export') }}" + '?' + params.toString();
                    let iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = exportUrl;
                    document.body.appendChild(iframe);

                    // Close loader after delay
                    setTimeout(() => {
                        clearInterval(timerInterval);
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Complete!',
                            html: `<p><strong>${willExport}</strong> customers exported successfully</p>
                                <small class="text-muted">Check your Downloads folder</small>`,
                            timer: 3000,
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#198754'
                        });

                        setTimeout(() => {
                            document.body.removeChild(iframe);
                        }, 1000);
                    }, 2000);
                }
            });
        });
    </script>
@endsection

