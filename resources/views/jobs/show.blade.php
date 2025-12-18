@extends('layouts.app')

@section('title', 'Work Order Details - ' . $job->job_code)

@section('extra-css')
<style>
    /* Professional CRM Styling */
    :root {
        --primary-blue: #2563eb;
        --primary-blue-dark: #1e40af;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --light-bg: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    .job-detail-container {
        background: var(--light-bg);
        min-height: calc(100vh - 100px);
        padding: 1.5rem 0;
    }

    /* Header Card */
    .job-header-card {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #143a78 100%);
        border-radius: 12px;
        padding: 2rem;
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
        margin-bottom: 1.5rem;
    }

    .job-header-card h2 {
        font-weight: 700;
        margin: 0;
    }

    .job-status-badge {
        padding: 0.5rem 1.2rem;
        border-radius: 6px;
        font-weight: 600;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: rgba(255, 255, 255, 0.2);
    }

    /* Info Cards */
    .info-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }

    .info-card-header {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .info-card-header h5 i {
        color: var(--primary-blue);
        margin-right: 0.5rem;
        font-size: 1.3rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: start;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.9rem;
        min-width: 140px;
    }

    .info-value {
        color: var(--text-primary);
        font-weight: 600;
        text-align: right;
        font-size: 0.95rem;
        flex: 1;
    }

    /* Amount Card */
    .amount-card {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 12px;
        padding: 1.8rem;
        color: #fff;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        margin-bottom: 1.5rem;
    }

    .amount-card h5 {
        color: #fff;
        font-weight: 700;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .amount-value {
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
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

    /* Customer Notes Compact */
    .note-compact {
        background: #fffbeb;
        border-left: 3px solid var(--warning-orange);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .note-compact .note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .note-compact strong {
        color: var(--text-primary);
        font-size: 0.9rem;
    }

    .note-compact small {
        color: var(--text-secondary);
        font-size: 0.8rem;
    }

    .note-compact p {
        margin: 0;
        color: var(--text-primary);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    /* Action Buttons */
    .action-button {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        border: none;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .action-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Status Badges */
    .badge.bg-pending {
        background-color: #f59e0b !important;
    }

    .badge.bg-confirmed {
        background-color: #8b5cf6 !important;
    }

    .badge.bg-assigned {
        background-color: #3b82f6 !important;
    }

    .badge.bg-in_progress {
        background-color: #06b6d4 !important;
    }

    .badge.bg-completed {
        background-color: #10b981 !important;
    }

    .badge.bg-cancelled {
        background-color: #ef4444 !important;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.2;
        margin-bottom: 1rem;
    }

    /* Customer Link Card */
    .customer-link-card {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .customer-link-card h6 {
        color: var(--success-green);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .view-customer-btn {
        width: 100%;
        padding: 0.85rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        font-size: 0.95rem;
        background: var(--success-green);
        color: #fff;
    }

    .view-customer-btn:hover {
        background: #059669;
    }

    /* Notes Section Styling */
    .notes-full-width {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border-color);
        margin-top: 1.5rem;
    }

    .notes-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    @media (max-width: 768px) {
        .notes-container {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="job-detail-container">
    <div class="container-fluid">

        <!-- Job Header -->
        <div class="job-header-card">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <h2 class="mb-0 me-3">{{ $job->title }}</h2>
                        <span class="job-status-badge bg-{{ $job->status }}">
                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                    </div>
                    <p class="mb-0" style="opacity: 0.95; font-weight: 500;">
                        <i class="las la-tag me-2"></i>{{ $job->job_code }}
                        <i class="las la-calendar ms-3 me-2"></i>{{ $job->created_at->format('d M Y') }}
                    </p>
                </div>

                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('jobs.index') }}" class="btn btn-light action-button me-2">
                        <i class="las la-arrow-left me-2"></i>Back to Jobs
                    </a>

                    @php
                        $user = auth()->user();
                        $canEdit = in_array($user->role, ['super_admin', 'lead_manager', 'telecallers']);
                    @endphp

                    @if($canEdit)
                        <button type="button" class="btn btn-primary action-button me-2 editJobBtn" data-id="{{ $job->id }}">
                            <i class="las la-edit me-2"></i>Edit
                        </button>
                    @endif

                    @if(auth()->user()->role === 'super_admin')
                        <button type="button" class="btn btn-success action-button me-2 assignJobBtn" data-id="{{ $job->id }}">
                            <i class="las la-user-check me-2"></i>Assign
                        </button>

                        @if($job->status === 'pending')
                        <button type="button" class="btn btn-info action-button me-2" onclick="confirmJobStatus({{ $job->id }})">
                            <i class="las la-check-double me-2"></i>Confirm
                        </button>
                        @endif

                        <button type="button" class="btn btn-danger action-button" onclick="deleteJob({{ $job->id }})">
                            <i class="las la-trash-alt me-2"></i>Delete
                        </button>
                    @endif

                    @if(auth()->user()->role === 'field_staff' && auth()->id() === $job->assigned_to)
                        @if($job->status === 'assigned')
                            <button type="button" class="btn btn-success action-button" onclick="startJob({{ $job->id }})">
                                <i class="las la-play me-2"></i>Start Job
                            </button>
                        @endif
                        @if($job->status === 'in_progress')
                            <button type="button" class="btn btn-success action-button" onclick="completeJob({{ $job->id }})">
                                <i class="las la-check-circle me-2"></i>Complete Job
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- TOP SECTION: Service & Job Details -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">

                <!-- Amount Card -->
                @if($job->amount)
                <div class="amount-card">
                    <h5><i class="las la-rupee-sign me-2"></i>Job Amount</h5>
                    <div class="amount-value">₹{{ number_format($job->amount, 2) }}</div>
                </div>
                @endif

                <!-- Customer Info -->
                @if($job->customer)
                <div class="customer-link-card">
                    <h6><i class="las la-user me-2"></i>Customer Information</h6>
                    <div class="mb-2">
                        <strong>Customer Code:</strong>
                        <span class="badge bg-success ms-2">{{ $job->customer->customer_code }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Name:</strong> {{ $job->customer->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Phone:</strong> {{ $job->customer->phone }}
                    </div>
                    <a href="{{ route('customers.show', $job->customer->id) }}" class="btn view-customer-btn">
                        <i class="las la-external-link-alt me-2"></i>View Customer Profile
                    </a>
                </div>
                @endif

            </div>

            <!-- Middle Column - Service Details -->
            <div class="col-lg-4">

                <!-- Service Information -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-tools"></i>Service Details</h5>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Service Type</span>
                        <span class="info-value">
                            @if($job->lead && $job->lead->service_type)
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $job->lead->service_type)) }}</span>
                            @elseif($job->services->first())
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $job->services->first()->service_type)) }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>

                    {{-- Show job services if available --}}
                    @if($job->services && $job->services->count() > 0)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <span class="info-label mb-2">Services</span>
                        <div class="services-section">
                            @foreach($job->services as $service)
                                <span class="service-badge">{{ $service->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    {{-- Fallback to lead services --}}
                    @elseif($job->lead && $job->lead->services && $job->lead->services->count() > 0)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <span class="info-label mb-2">Services (from Lead)</span>
                        <div class="services-section">
                            @foreach($job->lead->services as $service)
                                <span class="service-badge">{{ $service->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    {{-- Single service fallback --}}
                    @elseif($job->service)
                    <div class="info-row">
                        <span class="info-label">Service</span>
                        <span class="info-value">
                            <span class="service-badge">{{ $job->service->name }}</span>
                        </span>
                    </div>
                    @endif

                    @if($job->description)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <span class="info-label mb-2">Description</span>
                        <span class="info-value" style="text-align: left; font-size: 0.9rem;">{{ $job->description }}</span>
                    </div>
                    @endif

                    @if($job->customer_instructions)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <span class="info-label mb-2">Customer Instructions</span>
                        <span class="info-value" style="text-align: left; font-size: 0.9rem;">{{ $job->customer_instructions }}</span>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Right Column - Job Details -->
            <div class="col-lg-4">

                <!-- Job Details -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-briefcase"></i>Job Information</h5>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Branch</span>
                        <span class="info-value">{{ $job->branch->name }}</span>
                    </div>

                    @if($job->location)
                    <div class="info-row">
                        <span class="info-label">Location</span>
                        <span class="info-value">{{ $job->location }}</span>
                    </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">
                            {{ $job->assignedTo->name ?? 'Unassigned' }}
                        </span>
                    </div>

                    @if($job->scheduled_date)
                    <div class="info-row">
                        <span class="info-label">Scheduled Date</span>
                        <span class="info-value">
                            {{ $job->scheduled_date->format('d M Y') }}
                            @if($job->scheduled_time)
                                <br><small>{{ $job->scheduled_time }}</small>
                            @endif
                        </span>
                    </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Created By</span>
                        <span class="info-value">{{ $job->createdBy->name ?? 'System' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Created At</span>
                        <span class="info-value">{{ $job->created_at->format('d M Y, h:i A') }}</span>
                    </div>

                    @if($job->confirmed_at)
                    <div class="info-row">
                        <span class="info-label">Confirmed At</span>
                        <span class="info-value">{{ $job->confirmed_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif

                    @if($job->assigned_at)
                    <div class="info-row">
                        <span class="info-label">Assigned At</span>
                        <span class="info-value">{{ $job->assigned_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif

                    @if($job->started_at)
                    <div class="info-row">
                        <span class="info-label">Started At</span>
                        <span class="info-value">{{ $job->started_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif

                    @if($job->completed_at)
                    <div class="info-row">
                        <span class="info-label">Completed At</span>
                        <span class="info-value">{{ $job->completed_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- BOTTOM SECTION: Customer Notes - Full Width -->
        @if($job->customer)
        <div class="notes-full-width">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="las la-sticky-note text-warning me-2"></i>
                    Customer Notes
                    <span class="badge bg-secondary ms-2">{{ $job->customer->customerNotes->count() }}</span>
                </h5>
            </div>

            @if($job->customer->customerNotes->count() > 0)
                <div class="notes-container">
                    @foreach($job->customer->customerNotes->sortByDesc('created_at') as $note)
                    <div class="note-compact">
                        <div class="note-header">
                            <div>
                                <strong>{{ $note->createdBy->name }}</strong>
                                <small class="d-block text-muted mt-1">
                                    <i class="las la-clock me-1"></i>{{ $note->created_at->diffForHumans() }}
                                </small>
                            </div>
                            @if($note->job)
                            <span class="badge bg-info">{{ $note->job->job_code }}</span>
                            @endif
                        </div>
                        <p class="mt-2">{{ $note->note }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="las la-clipboard-list"></i>
                    <p class="mb-0">No customer notes available</p>
                </div>
            @endif
        </div>
        @endif

    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Job</h5>
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
                            <label for="amount" class="form-label">Amount (₹)</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0">
                            <span class="error-text amount_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
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
                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Select Branch</option>
                                @foreach(\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text branch_id_error text-danger d-block mt-1"></span>
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
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                            <span class="error-text location_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="scheduled_date" class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date">
                            <span class="error-text scheduled_date_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="scheduled_time" class="form-label">Scheduled Time</label>
                            <input type="time" class="form-control" id="scheduled_time" name="scheduled_time">
                            <span class="error-text scheduled_time_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            <span class="error-text description_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="customer_instructions" class="form-label">Customer Instructions</label>
                            <textarea class="form-control" id="customer_instructions" name="customer_instructions" rows="2"></textarea>
                            <span class="error-text customer_instructions_error text-danger d-block mt-1"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Job</button>
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
                <h5 class="modal-title">
                    <i class="las la-user-check me-2"></i>Assign Job
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignJobForm">
                @csrf
                <input type="hidden" id="assign_job_id">

                <div class="modal-body">
                    <!-- Job Information -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="las la-info-circle fs-20 me-2"></i>
                            <div>
                                <strong>Job Code:</strong> <span class="badge bg-primary ms-2">{{ $job->job_code }}</span><br>
                                <strong>Title:</strong> {{ $job->title }}
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
                                    <option value="{{ $telecaller->id }}" {{ $job->assigned_to == $telecaller->id ? 'selected' : '' }}>
                                        {{ $telecaller->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif

                            <!-- Field Staff Group -->
                            @if($field_staff->count() > 0)
                            <optgroup label="🔧 Field Staff">
                                @foreach($field_staff as $staff)
                                    <option value="{{ $staff->id }}" {{ $job->assigned_to == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i> Select a staff member to assign this job to
                        </small>
                    </div>

                    <!-- Assignment Notes (Optional) -->
                    <div class="mb-3">
                        <label for="assign_notes" class="form-label">
                            <i class="las la-comment me-1"></i>Assignment Notes (Optional)
                        </label>
                        <textarea class="form-control" id="assign_notes" name="assign_notes" rows="2"
                                  placeholder="Add any notes about this assignment..."></textarea>
                        <small class="text-muted">Optional information about this assignment</small>
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

        let currentJobServiceIds = [];

        // Initialize Select2 on assign dropdown
        $('#assigned_to').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select staff member',
            allowClear: true,
            dropdownParent: $('#assignJobModal'),
            width: '100%'
        });

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

        // Edit Job Button
        $('.editJobBtn').click(function() {
            let jobId = $(this).data('id');

            $.ajax({
                url: '/jobs/' + jobId + '/edit',
                type: 'GET',
                success: function(response) {
                    $('#job_id').val(response.job.id);
                    $('#title').val(response.job.title || '');
                    $('#description').val(response.job.description || '');
                    $('#customer_instructions').val(response.job.customer_instructions || '');
                    $('#branch_id').val(response.job.branch_id || '');
                    $('#location').val(response.job.location || '');
                    $('#amount').val(response.job.amount || '');

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

                    // Set dates
                    if (response.job.scheduled_date) {
                        let dateValue = response.job.scheduled_date.split(' ')[0];
                        $('#scheduled_date').val(dateValue);
                    }

                    if (response.job.scheduled_time) {
                        let timeStr = response.job.scheduled_time;
                        if (timeStr && timeStr.includes(':')) {
                            let timeParts = timeStr.split(':');
                            let formattedTime = timeParts[0].padStart(2, '0') + ':' + timeParts[1].padStart(2, '0');
                            $('#scheduled_time').val(formattedTime);
                        }
                    }

                    $('.error-text').text('');
                    $('#jobModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to load job data', 'error');
                }
            });
        });

        // Submit Edit Form
        $('#jobForm').on('submit', function(e) {
            e.preventDefault();

            let jobId = $('#job_id').val();

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
            formData.append('_method', 'PUT');

            $('.error-text').text('');

            $.ajax({
                url: '/jobs/' + jobId,
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
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
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

        // Assign Job Button
        $('.assignJobBtn').click(function() {
            let jobId = $(this).data('id');
            $('#assign_job_id').val(jobId);
            $('#assign_notes').val(''); // Clear notes

            // Show modal (dropdown already has current assignment pre-selected from blade)
            $('#assignJobModal').modal('show');
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to assign job', 'error');
                }
            });
        });
    });

    function confirmJobStatus(jobId) {
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
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Could not confirm job.', 'error');
                    }
                });
            }
        });
    }

    function deleteJob(jobId) {
        Swal.fire({
            title: 'Delete Job?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/jobs/' + jobId,
                    type: 'DELETE',
                    success: function() {
                        Swal.fire('Deleted!', 'Job deleted successfully', 'success').then(() => {
                            window.location.href = '{{ route("jobs.index") }}';
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete job', 'error');
                    }
                });
            }
        });
    }

    function startJob(jobId) {
        Swal.fire({
            title: 'Start Job?',
            text: 'This will mark the job as in progress',
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
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to start job', 'error');
                    }
                });
            }
        });
    }

    function completeJob(jobId) {
        Swal.fire({
            title: 'Complete Job?',
            text: 'This will mark the job as completed',
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
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to complete job', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
