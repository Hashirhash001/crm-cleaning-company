@extends('layouts.app')

@section('title', 'Work Orders Management')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* Status Badge Colors */
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-confirmed {
            background-color: #8b5cf6; /* Purple for confirmed/awaiting approval */
            color: #fff;
        }

        .badge-approved {
            background-color: #28a745; /* Green for approved */
            color: #fff;
        }

        .badge-in_progress {
            background-color: #0dcaf0;
            color: #000;
        }

        .badge-completed {
            background-color: #198754;
            color: #fff;
        }

        .badge-work_on_hold {
            background-color: #fd7e14; /* Orange */
            color: #fff;
        }

        .badge-postponed {
            background-color: #6f42c1; /* Purple */
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

        .row>* {
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
            /* max-height: 600px; */
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

        .select2-container .select2-selection--single .select2-selection__rendered {
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
            justify-content: space-between;
        }

        .service-checkbox-wrapper {
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

        .service-quantity-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .service-quantity-input {
            width: 80px;
            padding: 4px 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-align: center;
            font-size: 0.875rem;
        }

        .service-quantity-input:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .quantity-label {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
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

        .badge-confirmed {
            background-color: #8b5cf6;
            color: #fff;
        }

        .badge-approved {
            background-color: #28a745;
            color: #fff;
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        /* Quick filter button active states */
        #viewModeToggle .btn-outline-info.active {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #000;
        }

        /* Status Badge Colors */
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-confirmed {
            background-color: #8b5cf6; /* Purple for confirmed/awaiting approval */
            color: #fff;
        }

        .badge-approved {
            background-color: #28a745; /* Green for approved */
            color: #fff;
        }

        .badge-in_progress {
            background-color: #0dcaf0;
            color: #000;
        }

        .badge-completed {
            background-color: #198754;
            color: #fff;
        }

        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }

        /* Purple text color for confirm button */
        .text-purple {
            color: #8b5cf6 !important;
        }

        .text-purple:hover {
            color: #7c3aed !important;
        }

         /* Quick filter button active states */
        #viewModeToggle .btn-outline-primary.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        #viewModeToggle .btn-outline-info.active {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #000;
        }

        #viewModeToggle .btn-outline-success.active {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        #viewModeToggle .btn-outline-secondary.active {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        /* Confirm on creation checkbox styling */
        #confirmOnCreation {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
            border: 2px solid #000;
        }

        .form-check-label {
            cursor: pointer;
        }

        .confirm-info-message {
            font-size: 0.875rem;
            border-left: 3px solid #8b5cf6;
        }

    </style>
@endsection

@section('content')
    <div class="container-fluid" style="padding-left: 15px; padding-right: 15px;">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Work Orders Management</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Work Orders</li>
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
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="work_on_hold" {{ request('status') == 'work_on_hold' ? 'selected' : '' }}>Work on Hold</option>
                                        <option value="postponed" {{ request('status') == 'postponed' ? 'selected' : '' }}>Postponed</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>

                                <!-- Branch Filter -->
                                <div class="col-3">
                                    <label class="form-label fw-semibold mb-2">Filter by Branch</label>
                                    <select id="branchFilter" name="branch_id" class="form-select"
                                        style="min-width: 140px;">
                                        <option value="">All Branches</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div class="col-3">
                                    <label class="form-label fw-semibold mb-2">Scheduled Date From</label>
                                    <input type="date" id="dateFrom" name="date_from" class="form-control"
                                        value="{{ request('date_from') }}" style="min-width: 150px;">
                                </div>

                                <!-- Date To -->
                                <div class="col-3">
                                    <label class="form-label fw-semibold mb-2">Scheduled Date To</label>
                                    <input type="date" id="dateTo" name="date_to" class="form-control"
                                        value="{{ request('date_to') }}" style="min-width: 150px;">
                                </div>
                            </div>

                            <!-- Second Row - Search and Add Job -->
                            <div class="row align-items-end g-3 mt-2">
                                <!-- Service Filter -->
                                <div class="col-3">
                                    <label class="form-label fw-semibold mb-2">Filter by Service</label>
                                    <select id="serviceFilter" name="service_id" class="form-select"
                                        style="min-width: 160px;">
                                        <option value="">All Services</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}"
                                                {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Search -->
                                <div class="col">
                                    <label class="form-label fw-semibold mb-2">Search</label>
                                    <input type="text" id="searchInput" name="search" class="form-control"
                                        placeholder="Search by title or job code..." value="{{ request('search') }}">
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
                        <h4 class="card-title mb-0">
                            Work Orders List
                            <span id="jobCount">{{ $jobs->total() }}</span> total
                            @if(auth()->user()->role === 'super_admin' && $pendingApproval > 0)
                                <span class="badge bg-warning ms-2">{{ $pendingApproval }} Pending Approval</span>
                            @endif
                            @if(auth()->user()->role === 'telecallers' && $pendingJobs > 0)
                                <span class="badge bg-warning ms-2">{{ $pendingJobs }} Pending Jobs</span>
                            @endif
                        </h4>
                        <div class="d-flex gap-2">
                            <!-- Export Button -->
                            <a href="#" class="btn btn-success" id="exportJobsBtn">
                                <i class="las la-file-download me-1"></i> Export CSV
                            </a>

                            <!-- Add Job Button -->
                            @if (auth()->user()->role === 'super_admin' ||
                                    auth()->user()->role === 'lead_manager' ||
                                    auth()->user()->role === 'telecallers')
                                <button type="button" class="btn btn-primary" id="addJobBtn">
                                    <i class="las la-plus me-1"></i> Add Work Order
                                </button>
                            @endif
                        </div>
                    </div>
                    {{-- VIEW MODE TOGGLE - Updated filters --}}
                    <div class="btn-group w-100" role="group" id="viewModeToggle">
                        <button type="button"
                                class="btn btn-outline-primary {{ !request('status') ? 'active' : '' }}"
                                data-status=""
                                data-mode="">
                            All Work Orders
                        </button>

                        {{-- Pending Approval (confirmed status) --}}
                        <button type="button"
                                class="btn btn-outline-info {{ request('status') == 'confirmed' ? 'active' : '' }}"
                                data-status="confirmed"
                                data-mode="">
                            <i class="las la-hourglass-half me-1"></i> Pending Approval
                        </button>

                        {{-- Approved --}}
                        <button type="button"
                                class="btn btn-outline-success {{ request('status') == 'approved' ? 'active' : '' }}"
                                data-status="approved"
                                data-mode="">
                            <i class="las la-check-circle me-1"></i> Approved
                        </button>

                        {{-- NEW: Completed --}}
                        <button type="button"
                                class="btn btn-outline-secondary {{ request('status') == 'completed' ? 'active' : '' }}"
                                data-status="completed"
                                data-mode="">
                            <i class="las la-flag-checkered me-1"></i> Completed
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-container">
                            <table class="table table-hover mb-0" id="jobsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-column="code" style="min-width: 100px;">Job Code</th>
                                        <th class="sortable" data-column="title" style="min-width: 200px;">Title</th>
                                        <th class="sortable" data-column="customer" style="min-width: 150px;">Customer
                                        </th>
                                        <th class="sortable" data-column="service" style="min-width: 140px;">Service</th>
                                        <th class="sortable" data-column="amount" style="min-width: 120px;">Amount</th>
                                        <th class="sortable" data-column="amount_paid" style="min-width: 120px;">Amount
                                            Paid</th>
                                        <th class="sortable" data-column="status" style="min-width: 120px;">Status</th>
                                        <th class="sortable" data-column="branch" style="min-width: 120px;">Branch</th>
                                        <th class="sortable" data-column="assigned" style="min-width: 150px;">Assigned To
                                        </th>
                                        <th class="sortable" data-column="scheduled_date" style="min-width: 130px;">
                                            Scheduled Date</th>
                                        <th class="sortable" data-column="action"
                                            style="min-width: 200px; text-align: center;">Action</th>
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
    <div class="modal fade" id="jobModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="jobModalLabel">Add Work Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="jobForm">
                    @csrf
                    <input type="hidden" id="job_id" name="jobid">

                    <div class="modal-body">

                        {{-- Row 1 --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label required-field">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                                <span class="error-text titleerror text-danger d-block mt-1"></span>
                            </div>

                            @if (auth()->user()->role === 'super_admin')
                                <div class="col-md-6">
                                    <label class="form-label required-field">Branch</label>
                                    <select id="branchid" name="branch_id" class="form-select" required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label class="form-label">Branch</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ auth()->user()->branch->name ?? 'N/A' }}" readonly
                                        style="cursor: not-allowed;">
                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" id="branchid" name="branch_id"
                                        value="{{ auth()->user()->branch_id }}">
                                </div>
                            @endif

                        </div>

                        {{-- Row 2 --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customerId" class="form-label required-field">Customer</label>
                                <select name="customer_id" id="customerId" class="form-select select2-customer" required>
                                    <option value="">Select Customer</option>
                                    {{-- Options are loaded dynamically by branch --}}
                                </select>
                                <span class="error-text customeriderror text-danger d-block mt-1"></span>
                                <small class="text-muted">Select branch first to load customers.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="servicetype" class="form-label">Service Type (Filter)</label>
                                <select class="form-select" id="servicetype" name="servicetype">
                                    <option value="">All Services</option>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="pest_control">Pest Control</option>
                                    <option value="other">Other</option>
                                </select>
                                <small class="text-muted">Filter services by type or view all services</small>
                            </div>

                        </div>

                        {{-- Services with Quantity --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label required-field">Select Services <span class="badge bg-info">Multiple Selection from Any Type</span></label>

                                <!-- Search Box for Services -->
                                <div class="mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="las la-search"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control"
                                            id="serviceSearchInput"
                                            placeholder="Search services by name...">
                                        <button class="btn btn-outline-secondary" type="button" id="clearServiceSearch">
                                            <i class="las la-times"></i> Clear
                                        </button>
                                    </div>
                                </div>

                                <div class="service-select-box @error('service_ids') is-invalid @enderror" id="servicesContainer">
                                    <p class="text-muted text-center my-5">
                                        <i class="las la-spinner la-spin" style="font-size: 2rem;"></i><br>
                                        Loading services...
                                    </p>
                                </div>
                                <small class="text-muted">
                                    <i class="las la-info-circle"></i>
                                    You can select services from different types. Use the "Service Type" filter or search box above.
                                </small>
                                @error('service_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Amounts --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01"
                                    min="0" placeholder="0.00">
                                <span class="error-text amounterror text-danger d-block mt-1"></span>
                            </div>

                            <div class="col-md-6">
                                <label for="amountPaid" class="form-label">Amount Paid</label>
                                <input type="number" name="amount_paid" id="amountPaid" class="form-control"
                                    step="0.01" min="0" value="0" placeholder="Enter amount paid">
                                <small class="text-muted">Balance will be calculated automatically</small>
                                <span class="error-text amountpaiderror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Add-on Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="addonPrice" name="addon_price" placeholder="0.00">
                                <span class="error-text addon_priceerror text-danger d-block mt-1"></span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Add-on Price Comments</label>
                                <textarea class="form-control" id="addonPriceComments" name="addon_price_comments" rows="2"></textarea>
                                <span class="error-text addon_price_commentserror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Balance Amount</label>
                                <input type="text" id="balanceAmount" class="form-control" readonly value="0.00">
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location">
                                <span class="error-text locationerror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- Schedule --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="scheduleddate" class="form-label">Scheduled Date</label>
                                <input type="date" class="form-control" id="scheduleddate" name="scheduled_date">
                                <span class="error-text scheduleddateerror text-danger d-block mt-1"></span>
                            </div>

                            <div class="col-md-6">
                                <label for="scheduledtime" class="form-label">Scheduled Time</label>
                                <input type="time" class="form-control" id="scheduledtime" name="scheduled_time">
                                <span class="error-text scheduledtimeerror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                <span class="error-text descriptionerror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- Customer Instructions -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="customerinstructions" class="form-label">Customer Instructions</label>
                                <textarea class="form-control" id="customerinstructions" name="customerinstructions" rows="2" placeholder="Special instructions or preferences for this job..."></textarea>
                                <small class="text-muted">E.g., Key under doormat, call before arriving, etc.</small>
                                <span class="error-text customerinstructionserror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- Status Selection - Role-based access --}}
                        @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager' || auth()->user()->role === 'telecallers')
                            <div class="row mb-3" id="statusDropdownRow">
                                <div class="col-12">
                                    <label for="jobstatus" class="form-label">
                                        <i class="las la-info-circle me-1"></i> Status
                                    </label>
                                    <select class="form-select" id="jobstatus" name="status">
                                        <option value="pending" selected>Pending</option>
                                        <option value="work_on_hold">Work on Hold</option>
                                        <option value="postponed">Postponed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="las la-shield-alt"></i> You can manually set the status of this work order.
                                    </small>
                                    <span class="error-text statuserror text-danger d-block mt-1"></span>
                                </div>
                            </div>
                        @endif

                        {{-- Confirm on Creation - Only for Telecallers --}}
                        @if(auth()->user()->role === 'telecallers')
                            <div class="row mb-3" id="confirmCheckboxRow">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirmOnCreation" name="confirm_on_creation" value="1">
                                        <label class="form-check-label" for="confirmOnCreation">
                                            <strong>Confirm this work order immediately</strong>
                                            <small class="d-block text-muted">
                                                <i class="las la-info-circle"></i> Check this box to mark the job as "Confirmed" and send it directly for admin approval.
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Work Order</button>
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
                                    <strong>Work Order Code:</strong> <span id="assign_job_code"></span><br>
                                    <strong>Title:</strong> <span id="assign_job_title"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Branch restriction note -->
                        <div class="alert alert-warning py-2 mb-3">
                            <div class="d-flex align-items-start">
                            <i class="las la-shield-alt fs-18 me-2 mt-1"></i>
                            <div>
                                <strong>Branch restriction:</strong>
                                Only staff members who belong to this Work Order’s branch will appear in this list.
                                Assigning a user from another branch can cause reporting/assignment conflicts, so it is blocked by the system.
                                <br>
                                <small class="text-muted">
                                If the correct staff member is not listed, please check the staff member’s Branch in Users/Staff settings.
                                </small>
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
                                @if ($telecallers->count() > 0)
                                    <optgroup label="📞 Telecallers">
                                        @foreach ($telecallers as $telecaller)
                                            <option value="{{ $telecaller->id }}">
                                                {{ $telecaller->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                <!-- Field Staff Group -->
                                @if ($field_staff->count() > 0)
                                    <optgroup label="🔧 Field Staff">
                                        @foreach ($field_staff as $staff)
                                            <option value="{{ $staff->id }}">
                                                {{ $staff->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <small class="text-muted">
                                <i class="las la-info-circle"></i> Select a staff member to assign this Work Order to
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
                            <i class="las la-check me-1"></i>Assign Work Order
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
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let selectedServices = {};

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
                    dropdownParent: $('#jobModal'),
                    width: '100%',
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') return data;

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

            function resetCustomerDropdown() {
                $('#customerId').html('<option value="">Select Customer</option>').val('').trigger('change');
            }

            function loadCustomersByBranch(branchId, preselectCustomerId = null) {
                const customerSelect = $('#customerId');

                resetCustomerDropdown();

                if (!branchId) return;

                customerSelect.html('<option value="">Loading customers...</option>');

                $.ajax({
                    url: '{{ route('customers.byBranch') }}',
                    type: 'GET',
                    data: {
                        branch_id: branchId
                    },
                    success: function(customers) {
                        let html = '<option value="">Select Customer</option>';
                        customers.forEach(c => {
                            const code = c.customer_code ?? '';
                            const phone = c.phone ?? '';
                            const text =
                                `${code ? code + ' - ' : ''}${c.name}${phone ? ' - ' + phone : ''}`;
                            html +=
                                `<option value="${c.id}" data-code="${code}" data-phone="${phone}">${text}</option>`;
                        });

                        customerSelect.html(html);

                        // Reinitialize Select2 after adding options
                        initializeCustomerSelect2();

                        // Then set the value
                        if (preselectCustomerId) {
                            customerSelect.val(preselectCustomerId).trigger('change');
                        }
                    },
                    error: function() {
                        resetCustomerDropdown();
                    }
                });
            }

            // When branch changes, load customers for that branch
            $('#branchid').on('change', function() {
                const branchId = $(this).val();
                if (branchId) {
                    loadCustomersByBranch(branchId);
                } else {
                    resetCustomerDropdown();
                }
            });

            // Initialize customer select2 when modal is shown
            $('#jobModal').on('shown.bs.modal', function() {
                const modalTitle = $('#jobModalLabel').text();

                if (modalTitle === 'Add Work Order') {
                    // ADDING NEW JOB
                    const branchId = $('#branchid').val();
                    console.log('Add Job Modal - Branch ID:', branchId); // Debug

                    if (branchId) {
                        // Load customers for telecaller's branch
                        loadCustomersByBranch(branchId);
                    } else {
                        // Initialize empty for super admin
                        initializeCustomerSelect2();
                    }
                } else {
                    // EDITING EXISTING JOB
                    // Customer loading is handled in the edit AJAX success callback
                    // Just ensure Select2 is initialized
                    if (!$('.select2-customer').data('select2')) {
                        initializeCustomerSelect2();
                    }
                }
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

            // Save only VISIBLE selections
            function saveCurrentSelections() {
                console.log('Saving selections... Current DOM checkboxes:', $('.service-checkbox').length);

                // Only update services that are currently visible in DOM
                let visibleServiceIds = [];
                $('.service-checkbox').each(function() {
                    let serviceId = $(this).data('service-id');
                    visibleServiceIds.push(serviceId);
                    let isChecked = $(this).is(':checked');

                    if (isChecked) {
                        let quantity = parseInt($(`#quantity${serviceId}`).val()) || 1;
                        let serviceName = $(this).data('service-name');
                        let serviceType = $(this).data('service-type');

                        selectedServices[serviceId] = {
                            name: serviceName,
                            quantity: quantity,
                            type: serviceType
                        };
                        console.log('Saved service:', serviceId, selectedServices[serviceId]);
                    } else {
                        // Only remove if this service is visible AND unchecked
                        if (selectedServices.hasOwnProperty(serviceId)) {
                            delete selectedServices[serviceId];
                            console.log('Removed service (unchecked):', serviceId);
                        }
                    }
                });

                console.log('Visible service IDs:', visibleServiceIds);
                console.log('Total selected services:', Object.keys(selectedServices).length, selectedServices);
            }

            // NEW: Inject all selected services as hidden inputs
            function injectSelectedServicesIntoForm() {
                // Remove any previously injected hidden inputs
                $('#jobForm').find('.injected-service-input').remove();

                console.log('Injecting selected services into form:', selectedServices);

                // Inject hidden inputs for all selected services
                Object.entries(selectedServices).forEach(([serviceId, data]) => {
                    // Add service ID (checkbox checked)
                    $('#jobForm').append(`<input type="checkbox" name="service_ids[]" value="${serviceId}" checked class="injected-service-input" style="display:none">`);

                    // Add quantity input
                    $('#jobForm').append(`<input type="number" name="service_quantities[${serviceId}]" value="${data.quantity}" class="injected-service-input" style="display:none">`);

                    console.log(`Injected service ${serviceId} (${data.name}) qty: ${data.quantity}`);
                });

                console.log('Total injected inputs:', $('#jobForm').find('.injected-service-input').length);
            }

            // ✅ Load all services from controller (NO AJAX)
            const allServices = @json($services);
            console.log('All services loaded:', allServices.length);

            // ✅ NEW: Client-side filtering (like Leads page)
            function loadServices(serviceType = '', preselectedIds = [], preselectedQty = {}) {
                console.log('Loading services... Type:', serviceType || 'ALL');
                console.log('Preselected IDs:', preselectedIds);
                console.log('Preselected quantities:', preselectedQty);
                console.log('Current selections before load:', selectedServices);

                const container = $('#servicesContainer');

                // ✅ Filter services client-side
                let servicesToShow = serviceType
                    ? allServices.filter(s => s.service_type === serviceType)
                    : allServices;

                console.log('Services to show:', servicesToShow.length);

                if (servicesToShow.length === 0) {
                    container.html('<p class="text-muted text-center my-3">No services available</p>');
                    return;
                }

                // Group services by type
                let grouped = {};
                servicesToShow.forEach(service => {
                    let type = service.service_type || 'other';
                    if (!grouped[type]) {
                        grouped[type] = [];
                    }
                    grouped[type].push(service);
                });

                let html = '';

                // Display grouped services with headers
                Object.keys(grouped).sort().forEach(type => {
                    let typeName = type === 'cleaning' ? '🧹 Cleaning Services' :
                                type === 'pest_control' ? '🐛 Pest Control Services' :
                                type === 'pestcontrol' ? '🐛 Pest Control Services' :
                                '🔧 Other Services';
                    let typeColor = type === 'cleaning' ? '#3b82f6' :
                                    (type === 'pest_control' || type === 'pestcontrol') ? '#10b981' :
                                    '#6b7280';

                    html += `
                        <div style="margin-top: ${html ? '15px' : '0'}; padding: 8px 10px; background: ${typeColor}15; border-left: 3px solid ${typeColor}; border-radius: 4px;">
                            <strong style="color: ${typeColor}; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                ${typeName}
                                <span style="font-size: 0.8rem; font-weight: 400;">(${grouped[type].length})</span>
                            </strong>
                        </div>
                    `;

                    // Add services for this type
                    grouped[type].forEach(service => {
                        // ✅ Check if this service should be pre-selected
                        const isChecked = selectedServices.hasOwnProperty(service.id) ||
                                        preselectedIds.includes(service.id);

                        const qtyValue = isChecked
                            ? (selectedServices[service.id]?.quantity || preselectedQty[service.id] || 1)
                            : 1;

                        console.log(`Service ${service.id} (${service.name}) - checked:${isChecked}, qty:${qtyValue}`);

                        html += `
                            <div class="service-checkbox-item">
                                <div class="service-checkbox-wrapper">
                                    <input type="checkbox"
                                        name="service_ids[]"
                                        value="${service.id}"
                                        id="service${service.id}"
                                        class="service-checkbox"
                                        data-service-id="${service.id}"
                                        data-service-name="${service.name}"
                                        data-service-type="${service.service_type}"
                                        ${isChecked ? 'checked' : ''}>
                                    <label for="service${service.id}">${service.name}</label>
                                </div>
                                <div class="service-quantity-wrapper">
                                    <span class="quantity-label">Qty:</span>
                                    <input type="number"
                                        name="service_quantities[${service.id}]"
                                        id="quantity${service.id}"
                                        class="service-quantity-input"
                                        min="1"
                                        value="${qtyValue}"
                                        ${!isChecked ? 'disabled' : ''}>
                                </div>
                            </div>
                        `;
                    });
                });

                container.html(html);

                console.log('DOM rendered. Checkboxes found:', $('.service-checkbox').length);
                console.log('Checked checkboxes:', $('.service-checkbox:checked').length);

                // Re-bind event handlers
                bindServiceEvents();

                // Update display
                updateSelectedServicesDisplay();

                // Apply search filter if active
                if (currentSearchTerm) {
                    filterServicesInDOM(currentSearchTerm);
                }
            }

            // BIND EVENT HANDLERS TO SERVICE CHECKBOXES
            function bindServiceEvents() {
                // Enable/disable quantity input based on checkbox
                $('.service-checkbox').off('change').on('change', function() {
                    let serviceId = $(this).data('service-id');
                    let serviceName = $(this).data('service-name');
                    let serviceType = $(this).data('service-type');
                    let quantityInput = $(`#quantity${serviceId}`);

                    if ($(this).is(':checked')) {
                        quantityInput.prop('disabled', false);
                        if (!quantityInput.val()) {
                            quantityInput.val(1);
                        }
                        // Add to persistent state
                        selectedServices[serviceId] = {
                            name: serviceName,
                            quantity: parseInt(quantityInput.val()),
                            type: serviceType
                        };
                        console.log('Checkbox checked - added to selection:', serviceId, selectedServices[serviceId]);
                    } else {
                        quantityInput.prop('disabled', true);
                        // Remove from persistent state
                        delete selectedServices[serviceId];
                        console.log('Checkbox unchecked - removed from selection:', serviceId);
                    }

                    updateSelectedServicesDisplay(); // ✅ ADD THIS LINE
                });

                // Update quantity in persistent state when changed
                $('.service-quantity-input').off('change').on('change', function() {
                    let serviceId = $(this).attr('id').replace('quantity', '');
                    if (selectedServices.hasOwnProperty(serviceId)) {
                        selectedServices[serviceId].quantity = parseInt($(this).val()) || 1;
                        console.log('Quantity updated:', serviceId, selectedServices[serviceId].quantity);
                        updateSelectedServicesDisplay(); // ✅ ADD THIS LINE
                    }
                });
            }

            // DISPLAY SELECTED SERVICES WITH NAMES
            function updateSelectedServicesDisplay() {
                let selectedCount = Object.keys(selectedServices).length;
                let existingBadge = $('#selectedServicesBadge');

                console.log('Updating display. Selected count:', selectedCount);

                if (selectedCount > 0) {
                    // Build list of selected service names grouped by type
                    // ✅ FIXED: Handle both 'pest_control' and 'pestcontrol'
                    let byType = { cleaning: [], pest_control: [], pestcontrol: [], other: [] };

                    Object.entries(selectedServices).forEach(([id, data]) => {
                        let type = data.type || 'other';

                        // ✅ FIXED: Initialize array if key doesn't exist
                        if (!byType[type]) {
                            byType[type] = [];
                        }

                        byType[type].push(`${data.name} x${data.quantity}`);
                    });

                    let servicesList = [];

                    // Cleaning services
                    if (byType.cleaning && byType.cleaning.length > 0) {
                        servicesList.push(`<span style="color: #3b82f6">${byType.cleaning.join(', ')}</span>`);
                    }

                    // Pest Control services - handle both formats
                    let pestControlServices = [
                        ...(byType.pest_control || []),
                        ...(byType.pestcontrol || [])
                    ];
                    if (pestControlServices.length > 0) {
                        servicesList.push(`<span style="color: #10b981">${pestControlServices.join(', ')}</span>`);
                    }

                    // Other services
                    if (byType.other && byType.other.length > 0) {
                        servicesList.push(`<span style="color: #6b7280">${byType.other.join(', ')}</span>`);
                    }

                    let displayList = servicesList.join(' | ');

                    let badgeHtml = `
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <i class="las la-check-circle"></i> <strong>${selectedCount}</strong> service${selectedCount != 1 ? 's' : ''} selected
                                <br><small style="font-size: 0.75rem">${displayList}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearAllSelections" style="min-width: 100px;">
                                <i class="las la-times"></i> Clear All
                            </button>
                        </div>
                    `;

                    if (existingBadge.length === 0) {
                        $('#servicesContainer').before(`<div id="selectedServicesBadge" class="alert alert-success py-2 px-3 mb-2" style="font-size: 0.85rem">${badgeHtml}</div>`);
                    } else {
                        existingBadge.html(badgeHtml);
                    }

                    // Bind clear all button
                    $('#clearAllSelections').off('click').on('click', function() {
                        if (confirm('Are you sure you want to clear all selected services?')) {
                            selectedServices = {};
                            console.log('All selections cleared');
                            loadServices($('#servicetype').val());
                        }
                    });
                } else {
                    existingBadge.remove();
                }
            }

            // Service Search Handler
            let currentSearchTerm = '';

            $('#serviceSearchInput').on('input', function() {
                currentSearchTerm = $(this).val().trim();
                console.log('Search changed to:', currentSearchTerm);

                // Save current visible selections before searching
                saveCurrentSelections();

                // Filter visible services
                filterServicesInDOM(currentSearchTerm);
            });

            // Filter services in DOM without reloading
            function filterServicesInDOM(searchTerm) {
                if (!searchTerm) {
                    $('.service-checkbox-item').show();
                    updateSelectedServicesDisplay();
                    return;
                }

                const search = searchTerm.toLowerCase();
                let visibleCount = 0;

                $('.service-checkbox-item').each(function() {
                    const serviceName = $(this).find('.service-checkbox').data('service-name').toLowerCase();
                    if (serviceName.includes(search)) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Show message if no results
                if (visibleCount === 0) {
                    if ($('#noSearchResults').length === 0) {
                        $('#servicesContainer').append(`
                            <p id="noSearchResults" class="text-muted text-center my-3">
                                No services found matching "<strong>${searchTerm}</strong>"
                            </p>
                        `);
                    }
                } else {
                    $('#noSearchResults').remove();
                }

                updateSelectedServicesDisplay();
            }

            // Clear search button
            $('#clearServiceSearch').on('click', function() {
                $('#serviceSearchInput').val('');
                currentSearchTerm = '';
                saveCurrentSelections();
                filterServicesInDOM('');
                $('#serviceSearchInput').focus();
            });

            // Enter key to search
            $('#serviceSearchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    currentSearchTerm = $(this).val().trim();
                    saveCurrentSelections();
                    filterServicesInDOM(currentSearchTerm);
                }
            });

            // On service type change (create flow)
            $('#servicetype').on('change', function() {
                // Save current selections before switching
                saveCurrentSelections();

                // Clear search
                $('#serviceSearchInput').val('');
                currentSearchTerm = '';

                loadServices($(this).val());
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
                const searchTerm = $('#searchInput').val();
                const status = $('#statusFilter').val();
                const branchId = $('#branchFilter').val();
                const serviceId = $('#serviceFilter').val();
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();

                console.log('Loading jobs with:', {
                    search: searchTerm,
                    status: status,
                    branchId: branchId,
                    sort: currentSort.column,
                    direction: currentSort.direction
                });

                $.ajax({
                    url: '{{ route("jobs.index") }}',
                    type: 'GET',
                    data: {
                        search: searchTerm,
                        status: status, // Make sure status is sent
                        branchid: branchId,
                        serviceid: serviceId,
                        datefrom: dateFrom,
                        dateto: dateTo,
                        sortcolumn: currentSort.column,
                        sortdirection: currentSort.direction,
                        page: page
                    },
                    beforeSend: function() {
                        $('#jobsTableBody').addClass('table-loading');
                    },
                    success: function(response) {
                        console.log('Jobs loaded:', response.total, 'results');
                        $('#jobsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#jobCount').text(response.total);

                        // Update sort indicators
                        updateSortIndicators(currentSort.column, currentSort.direction);

                        $('#jobsTableBody').removeClass('table-loading');
                    },
                    error: function(xhr) {
                        console.error('Error loading jobs:', xhr);
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

            $(document).on('change', '#statusFilter, #branchFilter, #serviceFilter, #dateFrom, #dateTo',
                function() {
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

            // Show/hide confirmation message based on checkbox
            $('#confirmOnCreation').on('change', function() {
                if ($(this).is(':checked')) {
                    // Optional: Show a small info message
                    if ($('.confirm-info-message').length === 0) {
                        $(this).closest('.form-check').after(
                            '<div class="alert alert-info py-2 mt-2 confirm-info-message">' +
                            '<i class="las la-check-circle"></i> ' +
                            'This job will be marked as <strong>Confirmed</strong> and sent directly to admin for approval.' +
                            '</div>'
                        );
                    }

                    // Disable status dropdown and reset to pending
                    $('#jobstatus').val('pending').prop('disabled', true).addClass('bg-light');
                } else {
                    $('.confirm-info-message').remove();
                    // Re-enable status dropdown
                    $('#jobstatus').prop('disabled', false).removeClass('bg-light');
                }
            });

            // Add Job Button
            $('#addJobBtn').click(function() {
                $('#jobForm')[0].reset();
                $('#job_id').val('');
                $('#jobModalLabel').text('Add Work Order');
                $('.error-text').text('');

                // Clear the confirm checkbox
                $('#confirmOnCreation').prop('checked', false);
                $('.confirm-info-message').remove();

                // Reset and enable status dropdown
                $('#jobstatus').val('pending').prop('disabled', false).removeClass('bg-light');

                // Show both rows for new jobs
                $('#statusDropdownRow').show();
                $('#confirmCheckboxRow').show();

                // Reset amounts
                $('#amount').val('');
                $('#amountPaid').val(0);
                $('#balanceAmount').val('0.00');

                // Clear selected services
                selectedServices = {};

                let today = new Date().toISOString().split('T')[0];
                $('#scheduleddate').attr('min', today);

                // Reset services container
                $('#servicesContainer').html('<p class="text-muted text-center my-3"><i class="las la-arrow-up" style="font-size: 2rem"></i><br>Please select a service type first</p>');

                // ✅ FIX: Get branch ID properly
                const branchId = $('#branchid').val();
                console.log('Add Job - Branch ID:', branchId);

                if (branchId) {
                    loadCustomersByBranch(branchId);
                } else {
                    // For super admin, check if there's a default branch or show all
                    resetCustomerDropdown();
                }

                $('#servicetype').val('');
                loadServices('');

                $('#jobModal').modal('show');
            });

            // Edit Job
            $(document).on('click', '.editJobBtn', function() {
                let jobId = $(this).data('id');

                $.ajax({
                    url: `/jobs/${jobId}/edit`,
                    type: 'GET',
                    success: function(response) {
                        const job = response.job;

                        // Hidden ID
                        $('#job_id').val(job.id);
                        $('#jobModalLabel').text('Edit Job');

                        // Basic fields
                        $('#title').val(job.title);
                        $('#description').val(job.description);
                        $('#customerinstructions').val(job.customer_instructions);
                        $('#location').val(job.location);
                        $('#amount').val(job.amount || 0);
                        $('#amountPaid').val(job.amount_paid || 0);
                        $('#addonPrice').val(job.addon_price || 0);
                        $('#addonPriceComments').val(job.addon_price_comments);

                        // Set branch WITHOUT triggering change
                        $('#branchid').val(job.branch_id);

                        // Clear previous messages
                        $('.confirm-info-message').remove();

                        // Hide status dropdown and confirm checkbox if approved/completed/confirmed
                        if (job.status === 'approved' || job.status === 'completed' || job.status === 'confirmed') {
                            $('#statusDropdownRow').hide();
                            $('#confirmCheckboxRow').hide();
                            console.log('Status controls hidden - Job is ' + job.status);
                        } else {
                            // Show and set status dropdown
                            $('#statusDropdownRow').show();
                            if ($('#jobstatus').length) {
                                $('#jobstatus').val(job.status).prop('disabled', false).removeClass('bg-light');
                            }

                            // Show and reset confirm checkbox
                            $('#confirmCheckboxRow').show();
                            $('#confirmOnCreation').prop('checked', false);

                            console.log('Status controls shown - Job is ' + job.status);
                        }

                        // Load customers with preselection
                        loadCustomersByBranch(job.branch_id, job.customer_id);

                        // ✅ FIXED: Clear previous selections completely
                        selectedServices = {};

                        // ✅ FIXED: Handle missing service_quantities gracefully
                        const serviceQuantities = job.service_quantities || {};

                        console.log('Job has services:', job.service_ids);
                        console.log('Service quantities:', serviceQuantities);

                        // ✅ FIXED: Pre-populate selectedServices from job data
                        if (job.service_ids && job.service_ids.length > 0) {
                            job.service_ids.forEach(serviceId => {
                                // Find the service in allServices to get its details
                                let serviceData = allServices.find(s => s.id == serviceId);

                                if (serviceData) {
                                    selectedServices[serviceId] = {
                                        name: serviceData.name,
                                        quantity: serviceQuantities[serviceId] || 1,  // ✅ Use with fallback
                                        type: serviceData.service_type
                                    };
                                    console.log(`Pre-populated service ${serviceId}:`, selectedServices[serviceId]);
                                } else {
                                    // Fallback if service not found in allServices
                                    selectedServices[serviceId] = {
                                        name: 'Unknown Service',
                                        quantity: serviceQuantities[serviceId] || 1,
                                        type: job.service_type || 'other'
                                    };
                                    console.warn(`Service ${serviceId} not found in allServices`);
                                }
                            });
                        }

                        console.log('Final selectedServices:', selectedServices);

                        // ✅ Set service type filter (or empty for "All Services")
                        if (job.service_type) {
                            $('#servicetype').val(job.service_type);
                        } else {
                            $('#servicetype').val(''); // All Services
                        }

                        // ✅ Load services with pre-selected state
                        loadServices(
                            job.service_type || '',      // Service type filter (empty = all)
                            job.service_ids || [],       // Pre-selected IDs
                            serviceQuantities            // ✅ Use the safe variable
                        );

                        // Scheduled date
                        if (job.scheduled_date) {
                            let dateValue = job.scheduled_date;
                            if (dateValue.includes(' ')) {
                                dateValue = dateValue.split(' ')[0];
                            }
                            if (dateValue.includes('T')) {
                                dateValue = dateValue.split('T')[0];
                            }
                            $('#scheduleddate').val(dateValue);
                        } else {
                            $('#scheduleddate').val('');
                        }

                        // Scheduled time
                        if (job.scheduled_time) {
                            let timeStr = job.scheduled_time;
                            if (timeStr && timeStr.includes(':')) {
                                let timeParts = timeStr.split(':');
                                if (timeParts.length >= 2) {
                                    let formattedTime = `${timeParts[0].padStart(2, '0')}:${timeParts[1].padStart(2, '0')}`;
                                    $('#scheduledtime').val(formattedTime);
                                } else {
                                    $('#scheduledtime').val('');
                                }
                            } else {
                                $('#scheduledtime').val('');
                            }
                        } else {
                            $('#scheduledtime').val('');
                        }

                        calculateBalance();
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

                // Save current visible selections
                saveCurrentSelections();

                // Inject ALL selected services
                injectSelectedServicesIntoForm();

                console.log('Form submitting with selections:', selectedServices);

                let jobId = $('#job_id').val();
                let url = jobId ? `/jobs/${jobId}` : '{{ route("jobs.store") }}';

                console.log('Job ID:', jobId);
                console.log('Submit URL:', url);

                // Validate: at least one service is selected
                if (Object.keys(selectedServices).length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select at least one service'
                    });
                    $('.injected-service-input').remove();
                    return;
                }

                let formData = new FormData(this);

                // Remove status if dropdown is disabled OR hidden
                if ($('#jobstatus').prop('disabled') || !$('#statusDropdownRow').is(':visible')) {
                    formData.delete('status');
                }

                // Remove confirm checkbox if hidden
                if (!$('#confirmCheckboxRow').is(':visible')) {
                    formData.delete('confirm_on_creation');
                }

                // For update, add _method=PUT
                if (jobId) {
                    formData.append('_method', 'PUT');
                    console.log('Adding PUT method for update');
                }

                // Log what's being sent
                console.log('FormData contents:');
                let serviceIdsCount = 0;
                let serviceQuantitiesCount = 0;
                for (let pair of formData.entries()) {
                    if (pair[0] === 'service_ids[]') {
                        serviceIdsCount++;
                        console.log(`service_ids[]: ${pair[1]}`);
                    } else if (pair[0].startsWith('service_quantities[')) {
                        serviceQuantitiesCount++;
                        console.log(`${pair[0]}: ${pair[1]}`);
                    }
                }
                console.log(`Total service_ids: ${serviceIdsCount}, Total quantities: ${serviceQuantitiesCount}`);

                // Clear previous errors
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
                        $('.injected-service-input').remove();

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $(`#${key}error`).text(value[0]);
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
                        if (!response.success) return;

                        const job = response.job;
                        const jobBranchId = parseInt(job.branch_id, 10); // ✅ use job.branch_id

                        $('#assign_job_id').val(job.id);
                        $('#assign_job_code').text(job.job_code);
                        $('#assign_job_title').text(job.title);

                        let assignedToSelect = $('#assigned_to');
                        assignedToSelect.html('<option value="">Select Staff Member</option>');

                        // ✅ Filter by branch
                        const branchTelecallers = allTelecallers.filter(u => parseInt(u
                            .branch_id, 10) === jobBranchId);
                        const branchFieldStaff = allFieldStaff.filter(u => parseInt(u.branch_id,
                            10) === jobBranchId);

                        // Telecallers
                        if (branchTelecallers.length > 0) {
                            let telecallersOptgroup = $(
                                '<optgroup label="📞 Telecallers"></optgroup>');
                            branchTelecallers.forEach(function(telecaller) {
                                telecallersOptgroup.append($('<option>', {
                                    value: telecaller.id,
                                    text: telecaller.name,
                                    selected: (parseInt(job.assigned_to,
                                        10) === parseInt(telecaller.id,
                                            10))
                                }));
                            });
                            assignedToSelect.append(telecallersOptgroup);
                        }

                        // Field Staff
                        if (branchFieldStaff.length > 0) {
                            let fieldStaffOptgroup = $(
                                '<optgroup label="🔧 Field Staff"></optgroup>');
                            branchFieldStaff.forEach(function(staff) {
                                fieldStaffOptgroup.append($('<option>', {
                                    value: staff.id,
                                    text: staff.name,
                                    selected: (parseInt(job.assigned_to,
                                        10) === parseInt(staff.id, 10))
                                }));
                            });
                            assignedToSelect.append(fieldStaffOptgroup);
                        }

                        // If none found
                        if (branchTelecallers.length === 0 && branchFieldStaff.length === 0) {
                            assignedToSelect.append(
                                '<option value="" disabled>No staff in this branch</option>'
                                );
                        }

                        $('#assign_notes').val('');
                        $('#assignJobModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading assignment:', xhr);
                        Swal.fire('Error!', 'Failed to load Work Order data', 'error');
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
                        Swal.fire('Error!', xhr.responseJSON?.message ||
                            'Failed to assign Work Order', 'error');
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
                                Swal.fire('Deleted!', 'Work Order deleted successfully',
                                    'success').then(() => {
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

            // VIEW MODE TOGGLE HANDLER (same as leads)
            $('#viewModeToggle button').on('click', function() {
                $('#viewModeToggle button').removeClass('active');
                $(this).addClass('active');

                let status = $(this).data('status');
                let mode = $(this).data('mode');

                // Update hidden inputs
                $('#statusFilter').val(status);

                let modeInput = $('#filterMode');
                if (modeInput.length === 0) {
                    $('#filterForm').append('<input type="hidden" id="filterMode" name="mode">');
                    modeInput = $('#filterMode');
                }
                modeInput.val(mode);

                loadJobs();
            });

            // STATUS FILTER CHANGE HANDLER (updated to sync with quick filters)
            $('#statusFilter').on('change', function() {
                let value = $(this).val();

                console.log('Status filter changed:', value);

                // Sync viewModeToggle visual state
                $('#viewModeToggle button').removeClass('active');

                if (value === 'approved') {
                    $('#viewModeToggle button[data-status="approved"]').addClass('active');
                } else if (value === 'confirmed') {
                    $('#viewModeToggle button[data-status="confirmed"]').addClass('active');
                } else if (value === 'completed') {
                    $('#viewModeToggle button[data-status="completed"]').addClass('active');
                } else if (value === '' || !value) {
                    $('#viewModeToggle button[data-status=""][data-mode=""]').addClass('active');
                }

                // Load jobs
                loadJobs();
            });

            // Confirm Job Button
            $(document).on('click', '.confirmJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Confirm Work Order?',
                    text: 'This will submit the work order for admin approval.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#8b5cf6',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/jobs/${jobId}/confirm-status`,
                            type: 'POST',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Confirmed!', response.message, 'success')
                                        .then(() => loadJobs());
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Could not confirm job.', 'error');
                            }
                        });
                    }
                });
            });

            // Approve Job Button
            $(document).on('click', '.approveJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Approve Work Order?',
                    html: '<textarea id="approval_notes" class="swal2-textarea" placeholder="Add approval notes (optional)" style="width: 100%; min-height: 80px; margin: 0 !important;"></textarea>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let notes = $('#approval_notes').val();

                        $.ajax({
                            url: `/jobs/${jobId}/approve`,
                            type: 'POST',
                            data: { approval_notes: notes },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Approved!', response.message, 'success')
                                        .then(() => loadJobs());
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Could not approve job.', 'error');
                            }
                        });
                    }
                });
            });

            // Update Complete Job Button (already exists, just ensure it checks for approved status)
            $(document).on('click', '.completeJobBtn', function() {
                let jobId = $(this).data('id');

                Swal.fire({
                    title: 'Complete Job?',
                    text: 'This will mark the work order as completed',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Complete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/jobs/${jobId}/complete`,
                            type: 'POST',
                            success: function() {
                                Swal.fire('Completed!', 'Work order completed successfully', 'success')
                                    .then(() => loadJobs());
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to complete job', 'error');
                            }
                        });
                    }
                });
            });

            // Export Button Handler
            $('#exportJobsBtn').on('click', function(e) {
                e.preventDefault();

                let totalJobs = parseInt($('#jobCount').text()) || 0;
                let exportLimit = 10000;
                let willExport = Math.min(totalJobs, exportLimit);

                // Show warning if exceeding limit
                if (totalJobs > exportLimit) {
                    Swal.fire({
                        title: 'Export Limit Warning',
                        html: `
                            <div class="text-start">
                                <p><strong>Total Jobs:</strong> ${totalJobs}</p>
                                <p><strong>Export Limit:</strong> ${exportLimit}</p>
                                <p class="text-warning mb-0">
                                    <i class="las la-exclamation-triangle"></i>
                                    Only the first ${exportLimit} jobs will be exported.
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
                        title: 'Exporting Jobs...',
                        html: `
                            <div style="text-align: center;">
                                <p>Processing <strong>${willExport}</strong> jobs</p>
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
                    let status = $('#statusFilter').val();
                    let branchId = $('#branchFilter').val();
                    let serviceId = $('#serviceFilter').val();
                    let dateFrom = $('#dateFrom').val();
                    let dateTo = $('#dateTo').val();
                    let search = $('#searchInput').val();

                    // Add non-empty filters to params
                    if (status && status !== '') params.append('status', status);
                    if (branchId && branchId !== '') params.append('branch_id', branchId);
                    if (serviceId && serviceId !== '') params.append('service_id', serviceId);
                    if (dateFrom) params.append('date_from', dateFrom);
                    if (dateTo) params.append('date_to', dateTo);
                    if (search) params.append('search', search);

                    // Download with iframe
                    let exportUrl = "{{ route('jobs.export') }}" + '?' + params.toString();
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
                            html: `<p><strong>${willExport}</strong> jobs exported successfully</p>
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

            console.log('✅ Jobs management system initialized');
        });
    </script>

@endsection
