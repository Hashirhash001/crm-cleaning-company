@extends('layouts.app')

@section('title', 'Jobs Management')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-assigned {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-in_progress {
            background-color: #0dcaf0;
            color: #000;
        }
        .badge-completed {
            background-color: #28a745;
            color: #fff;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }

        /* Prevent horizontal page scroll */
        body {
            overflow-x: hidden;
        }

        /* Main content area */
        .page-content {
            overflow-x: hidden;
        }

        /* Row and Column fixes */
        .row {
            margin-left: 0;
            margin-right: 0;
        }

        .row > * {
            padding-left: 12px;
            padding-right: 12px;
        }

        /* Card should not overflow */
        .card {
            overflow: hidden;
            max-width: 100%;
        }

        /* Remove card body padding that causes overflow */
        .jobs-card .card-body {
            padding: 0;
            overflow: hidden;
        }

        /* Filter card shouldn't overflow */
        .filter-card {
            overflow: hidden;
            max-width: 100%;
        }

        /* Card Header Styling */
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        /* Table Container - FIXED WIDTH */
        .table-container {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 600px;
            position: relative;
            width: 100%;
        }

        /* Custom Scrollbar Styling */
        .table-container::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Table styling */
        .table-container table {
            margin-bottom: 0;
            min-width: 100%;
        }

        /* Sticky Table Header */
        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            padding: 12px 15px;
            vertical-align: middle;
            font-weight: 600;
        }

        /* Table Body Styling */
        .table-container tbody td {
            white-space: nowrap;
            padding: 12px 15px;
            vertical-align: middle;
        }

        /* Table Hover Effect */
        .table-container tbody tr:hover td {
            background-color: #f8f9fa;
        }

        /* Clickable title */
        .job-title-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .job-title-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        /* Action column spacing */
        .action-icons {
            display: inline-flex;
            gap: 10px;
            align-items: center;
        }

        .action-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Filter Section Styling */
        .card-body form .row {
            margin: 0;
        }

        .card-body form .col-auto {
            padding-left: 8px;
            padding-right: 8px;
        }

        .card-body form .form-label {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .card-body form .form-select,
        .card-body form .form-control {
            font-size: 14px;
            padding: 0.47rem 0.75rem;
            height: auto;
        }

        .card-body form .btn {
            font-size: 14px;
            white-space: nowrap;
        }

        /* Consistent button heights */
        .card-body form .btn,
        .card-body form .form-control,
        .card-body form .form-select {
            height: 38px;
        }

        /* Remove default form spacing */
        .card-body form {
            margin: 0;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid" style="padding-left: 15px; padding-right: 15px;">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Jobs Management</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Jobs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card filter-card">
                <div class="card-body" style="padding: 20px;">
                    <form id="filterForm" method="GET" action="{{ route('jobs.index') }}">
                        <!-- First Row - Filters -->
                        <div class="row align-items-end g-3">
                            <!-- Status Filter -->
                            <div class="col-auto">
                                <label class="form-label fw-semibold mb-2">Filter by Status</label>
                                <select class="form-select" id="statusFilter" name="status" style="min-width: 140px;">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <!-- Branch Filter -->
                            <div class="col-auto">
                                <label class="form-label fw-semibold mb-2">Filter by Branch</label>
                                <select id="branchFilter" name="branch_id" class="form-select" style="min-width: 140px;">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assigned To Filter -->
                            <div class="col-auto">
                                <label class="form-label fw-semibold mb-2">Filter by Assigned To</label>
                                <select id="assignedToFilter" name="assigned_to" class="form-select" style="min-width: 150px;">
                                    <option value="">All Staff</option>
                                    <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                    @foreach(\App\Models\User::where('role', 'field_staff')->orderBy('name')->get() as $staff)
                                        <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date From -->
                            <div class="col-auto">
                                <label class="form-label fw-semibold mb-2">Scheduled Date From</label>
                                <input type="date" id="dateFrom" name="date_from" class="form-control" value="{{ request('date_from') }}" style="min-width: 150px;">
                            </div>

                            <!-- Date To -->
                            <div class="col-auto">
                                <label class="form-label fw-semibold mb-2">Scheduled Date To</label>
                                <input type="date" id="dateTo" name="date_to" class="form-control" value="{{ request('date_to') }}" style="min-width: 150px;">
                            </div>

                            <!-- Filter Button -->
                            <div class="col-auto ms-auto">
                                <button type="submit" class="btn btn-success">
                                    <i class="las la-filter me-1"></i> Filter
                                </button>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-auto">
                                <a href="{{ route('jobs.index') }}" class="btn btn-secondary">
                                    <i class="las la-redo-alt me-1"></i> Reset
                                </a>
                            </div>
                        </div>

                        <!-- Second Row - Search and Add Job -->
                        <div class="row align-items-end g-3">
                            <!-- Search -->
                            <div class="col">
                                <label class="form-label fw-semibold mb-2">Search</label>
                                <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search by title or job code..." value="{{ request('search') }}">
                            </div>

                            <!-- Add Job Button -->
                            @if(auth()->user()->role === 'super_admin')
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary" id="addJobBtn">
                                    <i class="las la-plus me-1"></i> Add Job
                                </button>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card jobs-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Jobs List (<span id="jobCount">{{ $jobs->total() }}</span> total)</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover mb-0" id="jobsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 100px;">Job Code</th>
                                    <th style="min-width: 200px;">Title</th>
                                    <th style="min-width: 150px;">Customer</th>
                                    <th style="min-width: 140px;">Service</th>
                                    <th style="min-width: 120px;">Amount</th>
                                    <th style="min-width: 120px;">Status</th>
                                    <th style="min-width: 120px;">Branch</th>
                                    @if(auth()->user()->role === 'super_admin')
                                        <th style="min-width: 150px;">Assigned To</th>
                                        <th style="min-width: 130px;">Scheduled Date</th>
                                    @endif
                                    <th style="min-width: 200px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                @include('jobs.partials.table-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="card-footer">
                    <div id="paginationContainer">
                        {{ $jobs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Add/Edit Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jobModalLabel">Add Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="jobForm">
                @csrf
                <input type="hidden" id="job_id" name="job_id">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <span class="error-text title_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text customer_id_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount (₹)</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" placeholder="0.00">
                            <span class="error-text amount_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="service_id" class="form-label">Service</label>
                            <select class="form-select" id="service_id" name="service_id">
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text service_id_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <span class="error-text description_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <!-- Customer Instructions Field -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="customer_instructions" class="form-label">Customer Instructions</label>
                            <textarea class="form-control" id="customer_instructions" name="customer_instructions" rows="2" placeholder="Special instructions or preferences for this job..."></textarea>
                            <small class="text-muted">E.g., "Use eco-friendly products", "Key under doormat", etc.</small>
                            <span class="error-text customer_instructions_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text branch_id_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                            <span class="error-text location_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="scheduled_date" class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date">
                            <span class="error-text scheduled_date_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="scheduled_time" class="form-label">Scheduled Time (HH:MM)</label>
                            <input type="time" class="form-control" id="scheduled_time" name="scheduled_time">
                            <span class="error-text scheduled_time_error text-danger d-block mt-1"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Job Modal -->
<div class="modal fade" id="assignJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Re-assign Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignJobForm">
                @csrf
                <input type="hidden" id="assign_job_id" name="assign_job_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Select Field Staff</option>
                            @foreach(\App\Models\User::where('role', 'field_staff')->get() as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <span class="error-text assigned_to_error text-danger d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@section('extra-scripts')
    <script src="{{ asset('assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Set minimum date for scheduled_date to today
            let today = new Date().toISOString().split('T')[0];
            $('#scheduled_date').attr('min', today);

            // AJAX Form Submit Function
            function loadJobs(url = null) {
                let formData = $('#filterForm').serialize();
                let requestUrl = url || '{{ route("jobs.index") }}';

                $.ajax({
                    url: requestUrl,
                    type: 'GET',
                    data: formData,
                    success: function(response) {
                        $('#jobsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#jobCount').text(response.total || 0);
                    },
                    error: function(xhr) {
                        console.error('Error loading jobs:', xhr);
                        Swal.fire('Error!', 'Failed to load jobs', 'error');
                    }
                });
            }

            // Form Submit - AJAX
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadJobs();
            });

            // Pagination Click Handler - AJAX
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                loadJobs(url);
            });

            // Search with debounce
            let searchTimeout;
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadJobs();
                }, 800);
            });

            // Add Job Button
            $('#addJobBtn').click(function() {
                $('#jobForm')[0].reset();
                $('#job_id').val('');
                $('#jobModalLabel').text('Add Job');
                $('.error-text').text('');
                $('#branch_id').val('');

                let today = new Date().toISOString().split('T')[0];
                $('#scheduled_date').attr('min', today);

                $('#jobModal').modal('show');
            });

            // Edit Job
            $(document).on('click', '.editJobBtn', function() {
                let jobId = $(this).data('id');

                $.ajax({
                    url: '/jobs/' + jobId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        $('#job_id').val(response.job.id);
                        $('#jobModalLabel').text('Edit Job');
                        $('#title').val(response.job.title || '');
                        $('#customer_id').val(response.job.customer_id || '');
                        $('#service_id').val(response.job.service_id || '');
                        $('#description').val(response.job.description || '');
                        $('#customer_instructions').val(response.job.customer_instructions || '');
                        $('#branch_id').val(response.job.branch_id || '');
                        $('#location').val(response.job.location || '');
                        $('#amount').val(response.job.amount || '');

                        let today = new Date().toISOString().split('T')[0];
                        $('#scheduled_date').attr('min', today);

                        if (response.job.scheduled_date) {
                            let dateValue = response.job.scheduled_date;
                            if (dateValue.includes(' ')) {
                                dateValue = dateValue.split(' ')[0];
                            }
                            if (dateValue.includes('T')) {
                                dateValue = dateValue.split('T')[0];
                            }
                            $('#scheduled_date').val(dateValue);
                        } else {
                            $('#scheduled_date').val('');
                        }

                        if (response.job.scheduled_time) {
                            let timeStr = response.job.scheduled_time;
                            if (timeStr && timeStr.includes(':')) {
                                let timeParts = timeStr.split(':');
                                if (timeParts.length >= 2) {
                                    let formattedTime = timeParts[0].padStart(2, '0') + ':' + timeParts[1].padStart(2, '0');
                                    $('#scheduled_time').val(formattedTime);
                                }
                            }
                        } else {
                            $('#scheduled_time').val('');
                        }

                        $('.error-text').text('');
                        $('#jobModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading job:', xhr);
                        Swal.fire('Error!', 'Failed to load job data', 'error');
                    }
                });
            });

            // Form Submit
            $('#jobForm').on('submit', function(e) {
                e.preventDefault();

                let jobId = $('#job_id').val();
                let url = jobId ? '/jobs/' + jobId : '/jobs';

                let formData = new FormData(this);

                if (jobId) {
                    formData.append('_method', 'PUT');
                }

                $('.error-text').text('');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#jobModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadJobs(); // Reload table without full page refresh
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong', 'error');
                        }
                    }
                });
            });

            // Assign Job
            $(document).on('click', '.assignJobBtn', function() {
                let jobId = $(this).data('id');
                $('#assign_job_id').val(jobId);

                $.ajax({
                    url: '/jobs/' + jobId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        $('#assigned_to').val('');

                        if (response.job.assigned_to) {
                            let assignedId = String(response.job.assigned_to);
                            setTimeout(function() {
                                $('#assigned_to').val(assignedId);
                            }, 100);
                        }

                        $('#assignJobModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading assignment:', xhr);
                        $('#assigned_to').val('');
                        $('#assignJobModal').modal('show');
                    }
                });
            });

            // Submit Assign Form
            $('#assignJobForm').on('submit', function(e) {
                e.preventDefault();

                let jobId = $('#assign_job_id').val();
                let formData = new FormData(this);

                $.ajax({
                    url: '/jobs/' + jobId + '/assign',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#assignJobModal').modal('hide');
                        Swal.fire('Assigned!', response.message, 'success').then(() => {
                            loadJobs(); // Reload table without full page refresh
                        });
                    },
                    error: function(xhr) {
                        console.error('Assign error:', xhr);
                        Swal.fire('Error!', 'Failed to assign job', 'error');
                    }
                });
            });

            // Delete Job
            $(document).on('click', '.deleteJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Delete Job?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/jobs/' + jobId,
                            type: 'DELETE',
                            success: function() {
                                Swal.fire('Deleted!', 'Job deleted successfully', 'success').then(() => {
                                    loadJobs(); // Reload table without full page refresh
                                });
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to delete job', 'error');
                            }
                        });
                    }
                });
            });

            // Start Job
            $(document).on('click', '.startJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Start Job?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Start',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/jobs/' + jobId + '/start',
                            type: 'POST',
                            success: function() {
                                Swal.fire('Started!', 'Job started successfully', 'success').then(() => {
                                    loadJobs(); // Reload table without full page refresh
                                });
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to start job', 'error');
                            }
                        });
                    }
                });
            });

            // Complete Job
            $(document).on('click', '.completeJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Complete Job?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Complete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/jobs/' + jobId + '/complete',
                            type: 'POST',
                            success: function() {
                                Swal.fire('Completed!', 'Job completed successfully', 'success').then(() => {
                                    loadJobs(); // Reload table without full page refresh
                                });
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to complete job', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>

@endsection
