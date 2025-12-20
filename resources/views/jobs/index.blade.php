@extends('layouts.app')

@section('title', 'Jobs Management')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-confirmed {
            background-color: #8b5cf6;
            color: #fff;
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

        #serviceFilter {
            max-height: 300px;
            overflow-y: auto;
        }

        .select2-container .select2-selection--single .select2-selection__rendered{
            padding-left: 8px !important;
            padding-right: 20px !important;
        }

        /* Services Badges */
        .services-section {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .service-badge {
            background: var(--primary-blue);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .service-checkbox-item {
            padding: 8px 10px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
        }

        .service-checkbox-item:hover {
            background: #f8f9fa;
        }

        .service-checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }

        .service-checkbox-item label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

        .service-select-box {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            min-height: 150px;
            max-height: 200px;
            overflow-y: auto;
        }

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

        /* Loading overlay */
        .table-loading {
            position: relative;
            opacity: 0.6;
            pointer-events: none;
        }

        .job-name-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .job-name-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        .job-name-link h6 {
            color: inherit;
            font-weight: 600;
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
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Filter by Status</label>
                                <select class="form-select" id="statusFilter" name="status" style="min-width: 140px;">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <!-- Branch Filter -->
                            <div class="col-3">
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

                            <!-- Date From -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Scheduled Date From</label>
                                <input type="date" id="dateFrom" name="date_from" class="form-control" value="{{ request('date_from') }}" style="min-width: 150px;">
                            </div>

                            <!-- Date To -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Scheduled Date To</label>
                                <input type="date" id="dateTo" name="date_to" class="form-control" value="{{ request('date_to') }}" style="min-width: 150px;">
                            </div>
                        </div>

                        <!-- Second Row - Search and Add Job -->
                        <div class="row align-items-end g-3 mt-2">
                            <!-- Service Filter -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Filter by Service</label>
                                <select id="serviceFilter" name="service_id" class="form-select" style="min-width: 160px;">
                                    <option value="">All Services</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Search -->
                            <div class="col">
                                <label class="form-label fw-semibold mb-2">Search</label>
                                <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search by title or job code..." value="{{ request('search') }}">
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
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card jobs-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Jobs List (<span id="jobCount">{{ $jobs->total() }}</span> total)</h4>
                    <!-- Add Job Button -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" id="addJobBtn">
                            <i class="las la-plus me-1"></i> Add Job
                        </button>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover mb-0" id="jobsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-column="code" style="min-width: 100px;">Job Code</th>
                                    <th class="sortable" data-column="title" style="min-width: 200px;">Title</th>
                                    <th class="sortable" data-column="customer" style="min-width: 150px;">Customer</th>
                                    <th class="sortable" data-column="service" style="min-width: 140px;">Service</th>
                                    <th class="sortable" data-column="amount" style="min-width: 120px;">Amount</th>
                                    <th class="sortable" data-column="amount_paid" style="min-width: 120px;">Amount Paid</th>
                                    <th class="sortable" data-column="status" style="min-width: 120px;">Status</th>
                                    <th class="sortable" data-column="branch" style="min-width: 120px;">Branch</th>
                                    <th class="sortable" data-column="assigned" style="min-width: 150px;">Assigned To</th>
                                    <th class="sortable" data-column="scheduled_date" style="min-width: 130px;">Scheduled Date</th>
                                    <th class="sortable" data-column="action" style="min-width: 200px; text-align: center;">Action</th>
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
                            <select name="customer_id" id="customerId" class="form-select select2-customer">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-code="{{ $customer->customer_code }}"
                                            data-phone="{{ $customer->phone }}">
                                        {{ $customer->customer_code }} - {{ $customer->name }}
                                        @if($customer->phone)
                                            ({{ $customer->phone }})
                                        @endif
                                    </option>
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
                        <!-- Amount Paid -->
                        <div class="col-md-6">
                            <label for="amountPaid" class="form-label">Amount Paid (₹)</label>
                            <input type="number"
                                name="amount_paid"
                                id="amountPaid"
                                class="form-control"
                                step="0.01"
                                min="0"
                                value="0"
                                placeholder="Enter amount paid">
                            <small class="text-muted">Balance will be calculated automatically</small>
                        </div>

                        <!-- Balance Display (Optional - Read-only) -->
                        <div class="col-md-6">
                            <label class="form-label">Balance Amount</label>
                            <input type="text"
                                id="balanceAmount"
                                class="form-control"
                                readonly
                                value="₹0.00">
                        </div>

                        <div class="col-md-6">
                            <label for="service_type" class="form-label">Service Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="service_type" name="service_type" required>
                                <option value="">Select Service Type</option>
                                <option value="cleaning">Cleaning</option>
                                <option value="pest_control">Pest Control</option>
                                <option value="other">Other</option>
                            </select>
                            <span class="error-text service_type_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Select Services (Multiple Selection Allowed) <span class="text-danger">*</span></label>
                            <div class="service-select-box" id="servicesContainer">
                                <p class="text-muted text-center my-3">
                                    <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                                    Please select a service type first
                                </p>
                            </div>
                            <small class="text-muted">Check all services that apply to this job</small>
                            <span class="error-text service_ids_error text-danger d-block mt-1"></span>
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
                    <!-- Job Information -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="las la-info-circle fs-20 me-2"></i>
                            <div>
                                <strong>Job Code:</strong> <span id="assign_job_code"></span><br>
                                <strong>Title:</strong> <span id="assign_job_title"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Assign To Dropdown with grouped options -->
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label fw-semibold">
                            <i class="las la-user-check me-1"></i>Assign To
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Select Staff Member</option>

                            <!-- Telecallers Group -->
                            @if($telecallers->count() > 0)
                            <optgroup label="📞 Telecallers">
                                @foreach($telecallers as $telecaller)
                                    <option value="{{ $telecaller->id }}">
                                        {{ $telecaller->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif

                            <!-- Field Staff Group -->
                            @if($field_staff->count() > 0)
                            <optgroup label="🔧 Field Staff">
                                @foreach($field_staff as $staff)
                                    <option value="{{ $staff->id }}">
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i> Select a staff member to assign this job to
                        </small>
                        <span class="error-text assigned_to_error text-danger d-block mt-1"></span>
                    </div>

                    <!-- Notes (Optional) -->
                    <div class="mb-3">
                        <label for="assign_notes" class="form-label">Assignment Notes (Optional)</label>
                        <textarea class="form-control" id="assign_notes" name="assign_notes" rows="2"
                                  placeholder="Add any notes about this assignment..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-check me-1"></i>Assign Job
                    </button>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Select2 on service filter
            $('#serviceFilter').select2({
                theme: 'bootstrap-5',
                placeholder: 'search and select service',
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 on assign dropdown
            $('#assigned_to').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search and select staff member',
                allowClear: true,
                dropdownParent: $('#assignJobModal'),
                width: '100%'
            });

            // ============================================
            // INITIALIZE SELECT2 FOR CUSTOMER DROPDOWN
            // ============================================

            function initializeCustomerSelect2() {
                // Destroy existing instance if any
                if ($('.select2-customer').data('select2')) {
                    $('.select2-customer').select2('destroy');
                }

                $('.select2-customer').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search by name, code, or phone',
                    allowClear: true,
                    dropdownParent: $('#jobModal'), // IMPORTANT: Set modal as parent
                    width: '100%',
                    matcher: function(params, data) {
                        // If there are no search terms, return all data
                        if ($.trim(params.term) === '') {
                            return data;
                        }

                        // Search in text, customer code, and phone
                        const searchTerm = params.term.toLowerCase();
                        const text = data.text.toLowerCase();
                        const code = $(data.element).data('code')?.toString().toLowerCase() || '';
                        const phone = $(data.element).data('phone')?.toString() || '';

                        if (text.indexOf(searchTerm) > -1 ||
                            code.indexOf(searchTerm) > -1 ||
                            phone.indexOf(searchTerm) > -1) {
                            return data;
                        }

                        return null;
                    }
                });
            }

            // Initialize customer select2 when modal is fully shown
            $('#jobModal').on('shown.bs.modal', function() {
                initializeCustomerSelect2();
            });

            // ============================================
            // CALCULATE BALANCE AMOUNT
            // ============================================

            function calculateBalance() {
                const totalAmount = parseFloat($('#amount').val()) || 0;
                const amountPaid = parseFloat($('#amountPaid').val()) || 0;
                const balance = totalAmount - amountPaid;

                $('#balanceAmount').val('₹' + balance.toFixed(2));

                // Change color based on payment status
                if (balance <= 0) {
                    $('#balanceAmount').removeClass('text-danger text-warning').addClass('text-success');
                } else if (amountPaid > 0) {
                    $('#balanceAmount').removeClass('text-danger text-success').addClass('text-warning');
                } else {
                    $('#balanceAmount').removeClass('text-warning text-success').addClass('text-danger');
                }
            }

            // Calculate balance on amount change
            $(document).on('input', '#amount, #amountPaid', function() {
                calculateBalance();
            });

            // Store all telecallers and field staff data
            const allTelecallers = @json($telecallers);
            const allFieldStaff = @json($field_staff);

            let currentJobServiceIds = [];

            // Set minimum date for scheduled_date to today
            let today = new Date().toISOString().split('T')[0];
            $('#scheduled_date').attr('min', today);

            // Load services when service type is selected
            function loadServices(serviceType, preselectedIds = []) {
                let container = $('#servicesContainer');

                if (!serviceType) {
                    container.html(`
                        <p class="text-muted text-center my-3">
                            <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                            Please select a service type first
                        </p>
                    `);
                    return;
                }

                container.html('<p class="text-center my-3"><i class="las la-spinner la-spin"></i> Loading services...</p>');

                $.ajax({
                    url: '{{ route("leads.servicesByType") }}',
                    type: 'GET',
                    data: { service_type: serviceType },
                    success: function(services) {
                        if (services.length === 0) {
                            container.html('<p class="text-muted text-center my-3">No services available for this type</p>');
                            return;
                        }

                        let html = '';
                        services.forEach(function(service) {
                            let isChecked = preselectedIds.includes(service.id);
                            html += `
                                <div class="service-checkbox-item">
                                    <input type="checkbox"
                                        name="service_ids[]"
                                        value="${service.id}"
                                        id="service_${service.id}"
                                        class="service-checkbox"
                                        ${isChecked ? 'checked' : ''}>
                                    <label for="service_${service.id}">${service.name}</label>
                                </div>
                            `;
                        });

                        container.html(html);
                    },
                    error: function() {
                        container.html('<p class="text-danger text-center my-3">Error loading services. Please try again.</p>');
                    }
                });
            }

            $('#service_type').on('change', function() {
                loadServices($(this).val(), currentJobServiceIds);
            });

            // ============================================
            // SORTING STATE - FIXED
            // ============================================
            let currentSort = {
                column: 'created_at',
                direction: 'desc'
            };

            // ============================================
            // LOAD JOBS FUNCTION - FIXED
            // ============================================
            function loadJobs(page = 1) {
                const searchTerm = $('#searchInput').val(); // FIXED: was #jobSearch
                const status = $('#statusFilter').val();
                const branchId = $('#branchFilter').val();
                const serviceId = $('#serviceFilter').val();
                const dateFrom = $('#dateFrom').val(); // FIXED: was #dateFromFilter
                const dateTo = $('#dateTo').val(); // FIXED: was #dateToFilter

                console.log('🔍 Loading jobs with:', {
                    search: searchTerm,
                    sort: currentSort.column,
                    direction: currentSort.direction
                });

                $.ajax({
                    url: '{{ route("jobs.index") }}',
                    type: 'GET',
                    data: {
                        search: searchTerm,
                        status: status,
                        branch_id: branchId,
                        service_id: serviceId,
                        date_from: dateFrom,
                        date_to: dateTo,
                        sort_column: currentSort.column, // Use currentSort object
                        sort_direction: currentSort.direction,
                        page: page
                    },
                    beforeSend: function() {
                        $('#jobsTableBody').addClass('table-loading');
                    },
                    success: function(response) {
                        console.log('✅ Jobs loaded:', response.total, 'results');

                        $('#jobsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#jobCount').text(response.total);

                        // Update sort indicators
                        updateSortIndicators(currentSort.column, currentSort.direction);

                        $('#jobsTableBody').removeClass('table-loading');
                    },
                    error: function(xhr) {
                        console.error('❌ Error loading jobs:', xhr);
                        $('#jobsTableBody').removeClass('table-loading');
                        Swal.fire('Error!', 'Failed to load jobs', 'error');
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
            // SORT COLUMN CLICK HANDLER - FIXED
            // ============================================
            $(document).on('click', '.sortable', function() {
                let column = $(this).data('column');

                console.log('🔀 Sorting column:', column);

                // Toggle direction if same column, otherwise reset to asc
                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                // Load jobs with new sorting
                loadJobs();
            });

            // Search and filter handlers
            $(document).on('submit', '#filterForm', function(e) {
                e.preventDefault();
                loadJobs();
            });

            $(document).on('keyup', '#searchInput', function() {
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(function() {
                    loadJobs();
                }, 500);
            });

            $(document).on('change', '#statusFilter, #branchFilter, #serviceFilter, #dateFrom, #dateTo', function() {
                loadJobs();
            });

            // Pagination click handler
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const page = url.split('page=')[1];
                if (page) {
                    loadJobs(page);
                }
            });

            // Add Job Button
            $('#addJobBtn').click(function() {
                $('#jobForm')[0].reset();
                $('#job_id').val('');
                $('#jobModalLabel').text('Add Job');
                $('.error-text').text('');
                $('#branch_id').val('');
                $('#amount').val('');
                $('#amountPaid').val('0');
                $('#balanceAmount').val('₹0.00');
                currentJobServiceIds = [];

                let today = new Date().toISOString().split('T')[0];
                $('#scheduled_date').attr('min', today);

                // Reset services container
                $('#servicesContainer').html(`
                    <p class="text-muted text-center my-3">
                        <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                        Please select a service type first
                    </p>
                `);

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
                        $('#customerId').val(response.job.customer_id || '').trigger('change');
                        $('#description').val(response.job.description || '');
                        $('#customer_instructions').val(response.job.customer_instructions || '');
                        $('#branch_id').val(response.job.branch_id || '');
                        $('#location').val(response.job.location || '');
                        $('#amount').val(response.job.amount || '');
                        $('#amountPaid').val(response.job.amount_paid || '0');

                        // Calculate balance
                        calculateBalance();

                        // Set service type
                        if (response.job.service_type) {
                            $('#service_type').val(response.job.service_type);
                        }

                        // Store current service IDs
                        currentJobServiceIds = response.job.service_ids || [];

                        // Load services with preselected ones
                        if (response.job.service_type) {
                            loadServices(response.job.service_type, currentJobServiceIds);
                        }

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

                // Validate at least one service is selected
                if ($('.service-checkbox:checked').length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select at least one service'
                    });
                    return;
                }

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
                            loadJobs();
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

                $.ajax({
                    url: `/jobs/${jobId}/edit`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#assign_job_id').val(response.job.id);
                            $('#assign_job_code').text(response.job.job_code);
                            $('#assign_job_title').text(response.job.title);

                            let assignedToSelect = $('#assigned_to');
                            assignedToSelect.html('<option value="">Select Staff Member</option>');

                            // Add Telecallers group
                            if (allTelecallers.length > 0) {
                                let telecallersOptgroup = $('<optgroup label="📞 Telecallers"></optgroup>');
                                allTelecallers.forEach(function(telecaller) {
                                    let isSelected = response.job.assigned_to == telecaller.id;
                                    telecallersOptgroup.append(
                                        $('<option>', {
                                            value: telecaller.id,
                                            text: telecaller.name,
                                            selected: isSelected
                                        })
                                    );
                                });
                                assignedToSelect.append(telecallersOptgroup);
                            }

                            // Add Field Staff group
                            if (allFieldStaff.length > 0) {
                                let fieldStaffOptgroup = $('<optgroup label="🔧 Field Staff"></optgroup>');
                                allFieldStaff.forEach(function(staff) {
                                    let isSelected = response.job.assigned_to == staff.id;
                                    fieldStaffOptgroup.append(
                                        $('<option>', {
                                            value: staff.id,
                                            text: staff.name,
                                            selected: isSelected
                                        })
                                    );
                                });
                                assignedToSelect.append(fieldStaffOptgroup);
                            }

                            $('#assign_notes').val('');
                            $('#assignJobModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading assignment:', xhr);
                        Swal.fire('Error!', 'Failed to load job data', 'error');
                    }
                });
            });

            // Submit Assign Form
            $('#assignJobForm').on('submit', function(e) {
                e.preventDefault();

                let jobId = $('#assign_job_id').val();
                let formData = new FormData(this);

                $.ajax({
                    url: `/jobs/${jobId}/assign`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#assignJobModal').modal('hide');
                        Swal.fire('Assigned!', response.message, 'success').then(() => {
                            loadJobs();
                        });
                    },
                    error: function(xhr) {
                        console.error('Assign error:', xhr);
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to assign job', 'error');
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
                                    loadJobs();
                                });
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to delete job', 'error');
                            }
                        });
                    }
                });
            });

            // Confirm Job Status
            $(document).on('click', '.confirmJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Confirm Job Status?',
                    text: 'This will change the job status to confirmed.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/jobs/${jobId}/confirm-status`,
                            type: 'POST',
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Confirmed!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        loadJobs();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Could not confirm job.', 'error');
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
                                    loadJobs();
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
                                    loadJobs();
                                });
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to complete job', 'error');
                            }
                        });
                    }
                });
            });

            console.log('✅ Jobs management system initialized');
        });
        </script>

@endsection
