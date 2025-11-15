@extends('layouts.app')

@section('title', 'Leads Management')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-approved {
            background-color: #28a745;
            color: #fff;
        }
        .badge-rejected {
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
        }

        /* Remove card body padding that causes overflow */
        .leads-card .card-body {
            padding: 0;
            overflow: hidden;
        }

        /* Card Footer Styling */
        .leads-card .card-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
        }

        /* Search Input Styling */
        .search-wrapper .input-group-text {
            border-right: 0;
            background-color: #fff;
            border-color: #ced4da;
        }

        .search-wrapper .form-control {
            border-left: 0;
        }

        .search-wrapper .form-control:focus {
            box-shadow: none;
            border-color: #86b7fe;
        }

        .search-wrapper .input-group:focus-within .input-group-text {
            border-color: #86b7fe;
        }

        /* Table Container - FIXED WIDTH */
        .table-container {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 600px;
            position: relative;
            width: 100%;
            /* Prevent the table from expanding the container */
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

        /* Table styling - REMOVE max-content */
        .table-container table {
            margin-bottom: 0;
            min-width: 100%;
            /* REMOVED: width: max-content; - This was causing the overflow */
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

        /* Column Widths - Adjusted for better fit */
        .table-container th:nth-child(1),
        .table-container td:nth-child(1) { /* Lead Code */
            min-width: 100px;
            width: 100px;
        }

        .table-container th:nth-child(2),
        .table-container td:nth-child(2) { /* Name */
            min-width: 150px;
            width: 150px;
        }

        .table-container th:nth-child(3),
        .table-container td:nth-child(3) { /* Email */
            min-width: 200px;
            width: 200px;
        }

        .table-container th:nth-child(4),
        .table-container td:nth-child(4) { /* Phone */
            min-width: 140px;
            width: 140px;
        }

        .table-container th:nth-child(5),
        .table-container td:nth-child(5) { /* Service */
            min-width: 140px;
            width: 140px;
        }

        .table-container th:nth-child(6),
        .table-container td:nth-child(6) { /* Source */
            min-width: 120px;
            width: 120px;
        }

        .table-container th:nth-child(7),
        .table-container td:nth-child(7) { /* Branch */
            min-width: 100px;
            width: 100px;
        }

        .table-container th:nth-child(8),
        .table-container td:nth-child(8) { /* Status */
            min-width: 110px;
            width: 110px;
        }

        .table-container th:nth-child(9),
        .table-container td:nth-child(9) { /* Created By */
            min-width: 130px;
            width: 130px;
        }

        .table-container th:nth-child(10),
        .table-container td:nth-child(10) { /* Created Date */
            min-width: 120px;
            width: 120px;
        }

        .table-container th:nth-child(11),
        .table-container td:nth-child(11) { /* Action */
            min-width: 180px;
            width: 180px;
            text-align: right;
        }

        /* Table Hover Effect */
        .table-container tbody tr:hover td {
            background-color: #f8f9fa;
        }

        /* Duplicate Alert Animation */
        #duplicateAlertContainer {
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .customer-info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .priority-badge-large {
            font-size: 0.9rem;
            padding: 8px 15px;
        }

        /* SweetAlert fixes */
        .swal-no-animation .swal2-show {
            animation: none !important;
        }
        .swal-no-animation .swal2-hide {
            animation: none !important;
        }
        .swal2-container {
            transition: none !important;
        }
        .swal2-popup {
            animation: none !important;
        }
        .swal2-textarea {
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            padding: 10px;
            font-size: 14px;
            resize: none !important;
            overflow: hidden !important;
            min-height: 80px;
            max-height: 150px;
        }
        .swal2-textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
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

        /* Action Icons Spacing */
        .action-icons {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
        }

        .action-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Card Header Styling */
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        /* Ensure cards don't cause overflow */
        .leads-card {
            overflow: hidden;
            max-width: 100%;
        }

        /* Filter card shouldn't overflow */
        .filter-card {
            overflow: hidden;
            max-width: 100%;
        }

        .swal2-textarea{
            margin: 0 !important;
        }

        /* Clickable lead name styling */
        .lead-name-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .lead-name-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        .lead-name-link h6 {
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
            <h4 class="page-title">Leads Management</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Leads</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section - With Apply Filters Button -->
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body" style="padding: 20px;">
                <form method="GET" action="{{ route('leads.index') }}" id="filterForm">
                    <div class="row align-items-end g-3">
                        <!-- Status Filter -->
                        @if(auth()->user()->role === 'super_admin')
                        <div class="col-3">
                            <label class="form-label fw-semibold mb-2">Status</label>
                            <select class="form-select" name="status" id="statusFilter" style="min-width: 140px;">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        @endif

                        <!-- Branch Filter -->
                        @if(auth()->user()->role === 'super_admin')
                        <div class="col-2">
                            <label class="form-label fw-semibold mb-2">Branch</label>
                            <select class="form-select" name="branch_id" id="branchFilter" style="min-width: 140px;">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Lead Source Filter -->
                        <div class="col-2">
                            <label class="form-label fw-semibold mb-2">Lead Source</label>
                            <select class="form-select" name="lead_source_id" id="sourceFilter" style="min-width: 140px;">
                                <option value="">All Sources</option>
                                @foreach($lead_sources as $source)
                                    <option value="{{ $source->id }}" {{ request('lead_source_id') == $source->id ? 'selected' : '' }}>
                                        {{ $source->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date From -->
                        <div class="col-2">
                            <label class="form-label fw-semibold mb-2">Date From</label>
                            <input type="date"
                                   class="form-control"
                                   name="date_from"
                                   id="dateFromFilter"
                                   value="{{ request('date_from') }}"
                                   style="min-width: 150px;">
                        </div>

                        <!-- Date To -->
                        <div class="col-3">
                            <label class="form-label fw-semibold mb-2">Date To</label>
                            <input type="date"
                                   class="form-control"
                                   name="date_to"
                                   id="dateToFilter"
                                   value="{{ request('date_to') }}"
                                   style="min-width: 150px;">
                        </div>

                        <!-- Search moved to second row -->
                        <div class="col-12 mt-3">
                            <label class="form-label fw-semibold mb-2">Search</label>
                            <div class="d-flex gap-2 align-items-end">
                                <input type="text"
                                       class="form-control"
                                       name="search"
                                       id="searchInput"
                                       placeholder="Search by name, email, phone..."
                                       value="{{ request('search') }}"
                                       style="max-width: 400px;">

                                <!-- Apply Filters Button -->
                                <button type="submit" class="btn btn-primary">
                                    <i class="las la-filter me-1"></i> Apply Filters
                                </button>

                                <!-- Reset Button -->
                                <a href="{{ route('leads.index') }}" class="btn btn-secondary" id="resetBtn">
                                    <i class="las la-redo-alt me-1"></i> Reset
                                </a>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Leads Table with Proper Scrolling -->
<div class="row">
    <div class="col-12">
        <div class="card leads-card">
            <div class="card-header d-flex">
                <h4 class="card-title mb-0">
                    Leads List (<span id="leadCount">{{ $leads->total() }}</span> total)
                    @if(auth()->user()->role === 'super_admin' && $pending_count > 0)
                        <span class="badge bg-warning ms-2">{{ $pending_count }} Pending Approval</span>
                    @endif
                </h4>
                <!-- Add Lead Button -->
                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager')
                <button type="button" class="btn btn-success ms-auto" id="addLeadBtn">
                    <i class="las la-plus me-1"></i> Add Lead
                </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-hover mb-0" id="leadsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Lead Code</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Service</th>
                                <th>Source</th>
                                @if(auth()->user()->role === 'super_admin')
                                    <th>Branch</th>
                                @endif
                                <th>Status</th>
                                @if(auth()->user()->role === 'super_admin')
                                    <th>Created By</th>
                                @endif
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="leadsTableBody">
                            @include('leads.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $leads->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Add/Edit Lead Modal -->
<div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadModalLabel">Add Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="leadForm">
                @csrf
                <input type="hidden" id="lead_id" name="lead_id">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter lead name" required>
                            <span class="error-text name_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                            <span class="error-text email_error text-danger d-block mt-1"></span>
                            <small class="text-muted d-block mt-1">Will check for duplicates automatically</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required>
                            <span class="error-text phone_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text service_id_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lead_source_id" class="form-label">Lead Source <span class="text-danger">*</span></label>
                            <select class="form-select" id="lead_source_id" name="lead_source_id" required>
                                <option value="">Select Source</option>
                                @foreach($lead_sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text lead_source_id_error text-danger d-block mt-1"></span>
                        </div>
                        @if(auth()->user()->role === 'super_admin')
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
                        @endif
                    </div>

                    @if(auth()->user()->role !== 'super_admin')
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Branch</label>
                            <div class="form-control" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                                {{ auth()->user()->branch->name }}
                            </div>
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter lead description"></textarea>
                            <span class="error-text description_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <!-- Duplicate Alert Will Appear Here -->
                    <div id="duplicateAlertContainer"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveLeadBtn">Save Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Job for Customer Modal -->
<div class="modal fade" id="createJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Job for Existing Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createJobForm">
                @csrf
                <input type="hidden" id="job_customer_id" name="customer_id">
                <div class="modal-body">
                    <!-- Customer Info Display -->
                    <div class="customer-info-card mb-3" id="jobCustomerInfo"></div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="job_service_id" class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="job_service_id" name="service_id" required>
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text service_id_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="job_branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="job_branch_id" name="branch_id" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text branch_id_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="job_title" name="title" required>
                            <span class="error-text title_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="job_description" class="form-label">Description</label>
                            <textarea class="form-control" id="job_description" name="description" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="job_customer_instructions" class="form-label">Customer Instructions</label>
                            <textarea class="form-control" id="job_customer_instructions" name="customer_instructions" rows="2" placeholder="E.g., Use eco-friendly products, Key under doormat"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="job_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="job_location" name="location">
                        </div>
                        <div class="col-md-6">
                            <label for="job_scheduled_date" class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control" id="job_scheduled_date" name="scheduled_date">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="job_scheduled_time" class="form-label">Scheduled Time (HH:MM)</label>
                            <input type="time" class="form-control" id="job_scheduled_time" name="scheduled_time">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Job</button>
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

            let duplicateCheckTimeout;
            let isDuplicateFound = false;
            let currentCustomerData = null;

            // AJAX Form Submit Function for Pagination
            function loadLeads(url = null) {
                let formData = $('#filterForm').serialize();
                let requestUrl = url || '{{ route("leads.index") }}';

                $.ajax({
                    url: requestUrl,
                    type: 'GET',
                    data: formData,
                    success: function(response) {
                        $('#leadsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#leadCount').text(response.total);
                    },
                    error: function(xhr) {
                        console.error('Error loading leads:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load leads',
                            customClass: {
                                container: 'swal-no-animation',
                                popup: 'swal-no-animation'
                            }
                        });
                    }
                });
            }

            // Form Submit - AJAX
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadLeads();
            });

            // Pagination Click Handler - AJAX
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                loadLeads(url);
            });

            // Search with debounce
            let searchTimeout;
            $('#searchLead').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadLeads();
                }, 500);
            });

            // Reset button functionality
            $('#resetBtn').on('click', function(e) {
                e.preventDefault();

                // Clear all filters
                $('#statusFilter').val('');
                $('#branchFilter').val('');
                $('#sourceFilter').val('');
                $('#dateFromFilter').val('');
                $('#dateToFilter').val('');
                $('#searchInput').val('');

                // Redirect to clean URL
                window.location.href = '{{ route("leads.index") }}';
            });

            // Add Lead Button
            $('#addLeadBtn').click(function() {
                $('#leadForm')[0].reset();
                $('#lead_id').val('');
                $('#leadModalLabel').text('Add Lead');
                $('.error-text').text('');
                $('#duplicateAlertContainer').html('');
                isDuplicateFound = false;
                currentCustomerData = null;

                @if(auth()->user()->role === 'super_admin')
                    $('#branch_id').val('');
                @endif

                $('#saveLeadBtn').prop('disabled', false).html('Save Lead');
                $('#leadModal').modal('show');
            });

            // Real-time Duplicate Detection
            $('#email, #phone').on('keyup', function() {
                clearTimeout(duplicateCheckTimeout);

                let email = $('#email').val().trim();
                let phone = $('#phone').val().trim();

                // Clear previous alerts
                $('#duplicateAlertContainer').html('');
                isDuplicateFound = false;
                currentCustomerData = null;
                $('#saveLeadBtn').prop('disabled', false).html('Save Lead');

                // Only check if email or phone has reasonable length
                if (email.length < 5 && phone.length < 8) {
                    return;
                }

                duplicateCheckTimeout = setTimeout(function() {
                    $.ajax({
                        url: '/leads/check-duplicate',
                        type: 'POST',
                        data: {
                            email: email,
                            phone: phone
                        },
                        success: function(response) {
                            if (response.exists) {
                                isDuplicateFound = true;
                                currentCustomerData = response.data;
                                showDuplicateAlert(response);
                                $('#saveLeadBtn').prop('disabled', true).html('<i class="las la-ban"></i> Cannot Save - Duplicate Found');
                            }
                        }
                    });
                }, 600);
            });

            function showDuplicateAlert(response) {
                let alertHtml = '';

                if (response.type === 'customer') {
                    let data = response.data;
                    let priorityClass = data.priority === 'high' ? 'danger' : (data.priority === 'medium' ? 'warning' : 'success');
                    let priorityText = data.priority.charAt(0).toUpperCase() + data.priority.slice(1);

                    // Store data globally for later use
                    window.duplicateCustomerData = data;

                    alertHtml = `
                        <div class="alert alert-${priorityClass} border-${priorityClass}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="alert-heading mb-3">
                                        <i class="las la-exclamation-triangle"></i>
                                        Existing Customer Detected!
                                    </h5>
                                </div>
                                <span class="badge bg-${priorityClass} priority-badge-large">${priorityText} Priority</span>
                            </div>

                            <div class="customer-info-card">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <p class="mb-2"><strong>Customer Code:</strong> <span class="badge bg-primary">${data.customer_code}</span></p>
                                        <p class="mb-2"><strong>Name:</strong> ${data.name}</p>
                                        <p class="mb-2"><strong>Email:</strong> ${data.email}</p>
                                        <p class="mb-2"><strong>Phone:</strong> ${data.phone}</p>
                                        <p class="mb-0"><strong>Customer Since:</strong> ${data.customer_since}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="mb-2"><strong>Total Jobs:</strong> <span class="badge bg-info">${data.total_jobs}</span></p>
                                        <p class="mb-2"><strong>Completed Jobs:</strong> <span class="badge bg-success">${data.completed_jobs}</span></p>
                                        <p class="mb-2"><strong>Pending Jobs:</strong> <span class="badge bg-warning">${data.pending_jobs}</span></p>
                                        <p class="mb-2"><strong>Last Service:</strong> ${data.last_service}</p>
                                        <p class="mb-0"><strong>Last Job Date:</strong> ${data.last_job_date}</p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="alert alert-info mb-3">
                                <i class="las la-info-circle"></i>
                                <strong>Cannot create duplicate lead.</strong> This person is already a customer in the system.
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="/customers/${data.customer_id}" class="btn btn-info btn-sm" target="_blank">
                                    <i class="las la-eye"></i> View Customer Profile
                                </a>
                                <button type="button" class="btn btn-success btn-sm" onclick="showCreateJobModal(${data.customer_id})">
                                    <i class="las la-plus-circle"></i> Create New Job for This Customer
                                </button>
                            </div>
                        </div>
                    `;
                } else if (response.type === 'lead') {
                    let data = response.data;
                    let statusClass = data.status === 'pending' ? 'warning' : (data.status === 'approved' ? 'success' : 'danger');
                    let statusText = data.status.charAt(0).toUpperCase() + data.status.slice(1);

                    alertHtml = `
                        <div class="alert alert-warning border-warning">
                            <h5 class="alert-heading mb-3">
                                <i class="las la-exclamation-circle"></i>
                                Duplicate Lead Detected!
                            </h5>

                            <div class="customer-info-card">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Lead Code:</strong> <span class="badge bg-primary">${data.lead_code}</span></p>
                                        <p class="mb-2"><strong>Name:</strong> ${data.name}</p>
                                        <p class="mb-2"><strong>Email:</strong> ${data.email}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Phone:</strong> ${data.phone}</p>
                                        <p class="mb-2"><strong>Status:</strong> <span class="badge bg-${statusClass}">${statusText}</span></p>
                                        <p class="mb-2"><strong>Service:</strong> ${data.service}</p>
                                        <p class="mb-0"><strong>Created:</strong> ${data.created_at}</p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="alert alert-danger mb-3">
                                <i class="las la-ban"></i>
                                <strong>Cannot create duplicate lead.</strong> This email/phone already exists as a lead in the system.
                            </div>

                            <button type="button" class="btn btn-info btn-sm" onclick="viewExistingLead(${data.lead_id})">
                                <i class="las la-eye"></i> View Existing Lead
                            </button>
                        </div>
                    `;
                }

                $('#duplicateAlertContainer').html(alertHtml);
            }

            // Form Submit - Block if duplicate, use AJAX reload
            $('#leadForm').on('submit', function(e) {
                e.preventDefault();

                if (isDuplicateFound) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Create Lead',
                        text: 'A duplicate customer or lead was detected. Please check the warning above.',
                        customClass: {
                            container: 'swal-no-animation',
                            popup: 'swal-no-animation'
                        }
                    });
                    return false;
                }

                let leadId = $('#lead_id').val();
                let url = leadId ? '/leads/' + leadId : '/leads';
                let formData = new FormData(this);

                if (leadId) {
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
                        $('#leadModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: {
                                container: 'swal-no-animation',
                                popup: 'swal-no-animation'
                            }
                        }).then(() => {
                            loadLeads(); // Use AJAX reload instead of location.reload()
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            if (errors) {
                                $.each(errors, function(key, value) {
                                    let errorElement = $('.' + key + '_error');
                                    if (errorElement.length) {
                                        errorElement.text(value[0]);
                                    }
                                });

                                let firstError = Object.values(errors)[0][0];
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Validation Error',
                                    text: firstError,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        container: 'swal-no-animation',
                                        popup: 'swal-no-animation'
                                    }
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    container: 'swal-no-animation',
                                    popup: 'swal-no-animation'
                                }
                            });
                        }
                    }
                });
            });

            // Create Job for Existing Customer
            $('#createJobForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $('.error-text').text('');

                $.ajax({
                    url: '/jobs/create-for-customer',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#createJobModal').modal('hide');
                        $('#leadModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Job Created!',
                            html: `Job <strong>${response.job_code}</strong> has been created successfully for the existing customer.`,
                            confirmButtonText: 'View Jobs',
                            showCancelButton: true,
                            cancelButtonText: 'Stay Here',
                            customClass: {
                                container: 'swal-no-animation',
                                popup: 'swal-no-animation'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '/jobs';
                            } else {
                                loadLeads(); // Use AJAX reload
                            }
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to create job',
                                customClass: {
                                    container: 'swal-no-animation',
                                    popup: 'swal-no-animation'
                                }
                            });
                        }
                    }
                });
            });

            // Edit Lead
            $(document).on('click', '.editLeadBtn', function() {
                let leadId = $(this).data('id');

                $.ajax({
                    url: '/leads/' + leadId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        $('#lead_id').val(response.lead.id);
                        $('#leadModalLabel').text('Edit Lead');
                        $('#name').val(response.lead.name);
                        $('#email').val(response.lead.email);
                        $('#phone').val(response.lead.phone);
                        $('#lead_source_id').val(response.lead.lead_source_id);
                        $('#service_id').val(response.lead.service_id);

                        @if(auth()->user()->role === 'super_admin')
                            $('#branch_id').val(response.lead.branch_id);
                        @endif

                        $('#description').val(response.lead.description);
                        $('.error-text').text('');
                        $('#duplicateAlertContainer').html('');
                        isDuplicateFound = false;
                        $('#leadModal').modal('show');
                    }
                });
            });

            // Delete Lead
            $(document).on('click', '.deleteLeadBtn', function() {
                let leadId = $(this).data('id');

                Swal.fire({
                    title: 'Delete Lead?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        container: 'swal-no-animation',
                        popup: 'swal-no-animation'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/leads/' + leadId,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    customClass: {
                                        container: 'swal-no-animation',
                                        popup: 'swal-no-animation'
                                    }
                                }).then(() => {
                                    loadLeads(); // Use AJAX reload instead of location.reload()
                                });
                            }
                        });
                    }
                });
            });

            // Approve Lead Button
            $(document).on('click', '.approveLeadBtn', function() {
                let leadId = $(this).data('id');
                approveLead(leadId);
            });

            // Reject Lead Button
            $(document).on('click', '.rejectLeadBtn', function() {
                let leadId = $(this).data('id');
                rejectLead(leadId);
            });
        });

        // Global functions
        function approveLead(leadId) {
            Swal.fire({
                title: 'Approve Lead?',
                html: '<textarea id="approval_notes_input" class="swal2-textarea" placeholder="Enter approval notes (optional)" style="width: 100%; min-height: 80px;"></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#28a745',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                focusConfirm: false,
                customClass: {
                    container: 'swal-no-animation',
                    popup: 'swal-no-animation'
                },
                didOpen: () => {
                    document.getElementById('approval_notes_input').focus();
                },
                preConfirm: () => {
                    return document.getElementById('approval_notes_input').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/leads/' + leadId + '/approve',
                        type: 'POST',
                        data: {
                            approval_notes: result.value || '',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false,
                                customClass: {
                                    container: 'swal-no-animation',
                                    popup: 'swal-no-animation'
                                }
                            }).then(() => {
                                loadLeads(); // Use AJAX reload
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to approve lead',
                                customClass: {
                                    container: 'swal-no-animation',
                                    popup: 'swal-no-animation'
                                }
                            });
                        }
                    });
                }
            });
        }

        function rejectLead(leadId) {
            Swal.fire({
                title: 'Reject Lead?',
                html: '<textarea id="rejection_reason_input" class="swal2-textarea" placeholder="Enter rejection reason (required)" style="width: 100%; min-height: 80px;" required></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                focusConfirm: false,
                customClass: {
                    container: 'swal-no-animation',
                    popup: 'swal-no-animation'
                },
                didOpen: () => {
                    document.getElementById('rejection_reason_input').focus();
                },
                preConfirm: () => {
                    const value = document.getElementById('rejection_reason_input').value;
                    if (!value) {
                        Swal.showValidationMessage('Rejection reason is required');
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: '/leads/' + leadId + '/reject',
                        type: 'POST',
                        data: {
                            rejection_reason: result.value,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false,
                                customClass: {
                                    container: 'swal-no-animation',
                                    popup: 'swal-no-animation'
                                }
                            }).then(() => {
                                loadLeads(); // Use AJAX reload
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to reject lead',
                                customClass: {
                                    container: 'swal-no-animation',
                                    popup: 'swal-no-animation'
                                }
                            });
                        }
                    });
                }
            });
        }

        // Global functions (outside document.ready)
        function viewExistingCustomer(customerId) {
            window.open('/customers/' + customerId, '_blank');
        }

        function showCreateJobModal(customerId) {
            if (!window.duplicateCustomerData) {
                Swal.fire('Error!', 'Customer data not available', 'error');
                return;
            }

            $('#job_customer_id').val(customerId);

            let data = window.duplicateCustomerData;
            let priorityClass = data.priority === 'high' ? 'danger' : (data.priority === 'medium' ? 'warning' : 'success');

            let customerInfoHtml = `
                <div class="alert alert-${priorityClass}">
                    <h6 class="mb-2"><i class="las la-user"></i> Customer Information</h6>
                    <p class="mb-1"><strong>Code:</strong> ${data.customer_code} | <strong>Name:</strong> ${data.name}</p>
                    <p class="mb-0"><strong>Priority:</strong> <span class="badge bg-${priorityClass}">${data.priority.toUpperCase()}</span></p>
                </div>
            `;

            $('#jobCustomerInfo').html(customerInfoHtml);
            $('#job_title').val(data.name + ' - Service');

            $('#createJobModal').modal('show');
        }

        function viewExistingLead(leadId) {
            $('#leadModal').modal('hide');
            window.location.href = '/leads/' + leadId;
        }

        // Make loadLeads globally accessible if needed
        window.loadLeads = function(url = null) {
            let formData = $('#filterForm').serialize();
            let requestUrl = url || '{{ route("leads.index") }}';

            $.ajax({
                url: requestUrl,
                type: 'GET',
                data: formData,
                success: function(response) {
                    $('#leadsTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    $('#leadCount').text(response.total);
                },
                error: function(xhr) {
                    console.error('Error loading leads:', xhr);
                }
            });
        };
    </script>
@endsection
