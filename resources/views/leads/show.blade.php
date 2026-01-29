@extends('layouts.app')

@section('title', 'Lead Details - ' . $lead->name)

@section('extra-css')
<style>
    /* Professional CRM Color Palette */
    :root {
        --primary-blue: #2563eb;
        --primary-blue-dark: #1e40af;
        --secondary-gray: #64748b;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --light-bg: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    .lead-detail-container {
        background: var(--light-bg);
        min-height: calc(100vh - 100px);
        padding: 1.5rem 0;
    }

    /* Header Card - Simple Blue */
    .lead-header-card {
        background: var(--primary-blue);
        border-radius: 12px;
        padding: 2rem;
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
        margin-bottom: 1.5rem;
    }

    .lead-header-card h2 {
        font-weight: 700;
        margin: 0;
    }

    .lead-status-badge {
        padding: 0.5rem 1.2rem;
        border-radius: 6px;
        font-weight: 600;
        color: #fff;
        /* font-size: 0.85rem; */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: rgba(255, 255, 255, 0.2);
    }

    /* Price Card - Professional Teal/Blue */
    .price-card {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        border-radius: 12px;
        padding: 1.8rem;
        color: #fff;
        box-shadow: 0 2px 8px rgba(8, 145, 178, 0.2);
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .price-card h5 {
        color: #fff;
        font-weight: 700;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
    }

    .price-card h5 i {
        margin-right: 0.5rem;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        padding: 0.9rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.15);
        align-items: center;
    }

    .price-row:last-of-type {
        border-bottom: none;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 2px solid rgba(255,255,255,0.3);
    }

    .price-label {
        font-weight: 500;
        font-size: 0.95rem;
        opacity: 0.95;
    }

    .price-value {
        font-weight: 700;
        font-size: 1.2rem;
    }

    .balance-highlight {
        font-size: 1.6rem;
        color: #fef3c7;
        font-weight: 800;
    }

    .price-updated-info {
        /* margin-top: 1.5rem; */
        padding-top: 1rem;
        border-top: 1px solid rgba(255,255,255,0.15);
        font-size: 0.85rem;
        opacity: 0.9;
    }

    /* Info Cards - Clean White */
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
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .info-value {
        color: var(--text-primary);
        font-weight: 600;
        text-align: right;
        font-size: 0.95rem;
    }

    /* Special Cards */
    .converted-customer-card {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .converted-customer-card h6 {
        color: var(--success-green);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .related-job-card {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .related-job-card h6 {
        color: var(--primary-blue);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    /* Buttons - Clean & Professional */
    .action-button {
        border-radius: 8px;
        padding: 0.50rem 1rem;
        font-weight: 600;
        border: none;
        font-size: 0.9rem;
    }

    .view-profile-btn, .view-job-btn {
        width: 100%;
        padding: 0.85rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        font-size: 0.95rem;
    }

    .view-profile-btn {
        background: var(--success-green);
        color: #fff;
    }

    .view-profile-btn:hover {
        background: #059669;
    }

    .view-job-btn {
        background: var(--primary-blue);
        color: #fff;
    }

    .view-job-btn:hover {
        background: var(--primary-blue-dark);
    }

   /* Service Badges with Quantities */
   .services-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .service-badge {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #3b82f6 100%);
        color: #fff;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        transition: all 0.2s ease;
    }

    .service-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }

    .quantity-badge {
        background: rgba(255, 255, 255, 0.25);
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Alternative: Service list with table-like display */
    .service-list-table {
        width: 100%;
        margin-top: 0.5rem;
    }

    .service-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        border: 1px solid #e2e8f0;
    }

    .service-list-item:last-child {
        margin-bottom: 0;
    }

    .service-name {
        font-weight: 600;
        color: var(--text-primary);
    }

    .service-qty {
        background: var(--primary-blue);
        color: #fff;
        padding: 0.25rem 0.75rem;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Followup Timeline - Clean */
    .followup-timeline {
        position: relative;
    }

    .followup-item {
        position: relative;
        padding: 1.2rem;
        background: var(--card-bg);
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 3px solid var(--primary-blue);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        border-left: 3px solid var(--primary-blue);
    }

    .followup-item.overdue {
        border-left-color: var(--danger-red);
        background: #fef2f2;
    }

    .followup-item.completed {
        border-left-color: var(--success-green);
        background: #f0fdf4;
        opacity: 0.85;
    }

    .followup-item.today {
        border-left-color: var(--warning-orange);
        background: #fffbeb;
    }

    /* Call Logs & Notes */
    .call-log-item, .note-item {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 3px solid var(--primary-blue);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        border-left: 3px solid var(--primary-blue);
    }

    .note-item {
        background: #fffbeb;
        border-left-color: var(--warning-orange);
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
        color: var(--text-secondary);
    }

    /* Badge Colors */
    .badge {
        /* padding: 0.4rem 0.9rem; */
        border-radius: 6px;
        font-weight: 600;
        /* font-size: 0.8rem; */
    }

    .badge.bg-success {
        background: var(--success-green) !important;
    }

    .badge.bg-primary {
        background: var(--primary-blue) !important;
    }

    .badge.bg-info {
        background: #0891b2 !important;
    }

    .badge.bg-warning {
        background: var(--warning-orange) !important;
        color: #fff !important;
    }

    .badge.bg-danger {
        background: var(--danger-red) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .lead-header-card {
            padding: 1.5rem;
        }

        .price-card {
            padding: 1.3rem;
        }

        .info-card {
            padding: 1.2rem;
        }

        .price-value {
            font-size: 1rem;
        }

        .balance-highlight {
            font-size: 1.3rem;
        }
    }

    .deleteFollowup, .deleteCall, .deleteNote {
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .deleteFollowup:hover, .deleteCall:hover, .deleteNote:hover {
        opacity: 1;
    }

</style>
@endsection

@section('content')
<div class="lead-detail-container">
    <div class="container-fluid">

        <!-- Lead Header with Action Buttons -->
        <div class="lead-header-card">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <h2 class="mb-0 me-3">{{ $lead->name }}</h2>

                        @if($lead->status === 'approved')
                            {{-- Static badge for approved leads --}}
                            <span class="lead-status-badge">
                                {{ $lead->status_label }}
                            </span>
                        @else
                            {{-- Editable dropdown for non-approved leads --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light lead-status-badge dropdown-toggle d-flex align-items-center gap-2"
                                        type="button"
                                        id="leadStatusDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <span>{{ $lead->status_label }}</span>
                                    <i class="las la-pen fs-6 opacity-75"></i>
                                </button>

                                <ul class="dropdown-menu" aria-labelledby="leadStatusDropdown">
                                    @foreach(\App\Models\Lead::getStatusLabels() as $key => $label)
                                        @if($key === 'approved')
                                            @continue
                                        @endif
                                        <li>
                                            <a href="#"
                                               class="dropdown-item lead-status-option {{ $lead->status === $key ? 'active fw-bold' : '' }}"
                                               data-status="{{ $key }}">
                                                {{ $label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <p class="mb-0" style="opacity: 0.95; font-weight: 500;">
                        <i class="las la-tag me-2"></i>{{ $lead->lead_code }}
                        <i class="las la-calendar ms-3 me-2"></i>{{ $lead->created_at->format('d M Y') }}
                    </p>
                </div>

                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('leads.index') }}" class="btn btn-light action-button me-2">
                        <i class="las la-arrow-left me-2"></i>Back to Leads
                    </a>

                    @php
                        $user = auth()->user();
                    @endphp

                    {{-- EDIT button --}}
                    @if(
                        // Super admin can edit all leads
                        $user->role === 'super_admin'
                        // Other roles can edit only if lead is not approved/rejected
                        || (
                            !in_array($lead->status, ['approved', 'rejected'])
                            && (
                                $user->role === 'lead_manager'
                                || ($user->role === 'telecallers' && $lead->branch_id === $user->branch_id)
                            )
                        )
                    )
                        <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-primary action-button me-2">
                            <i class="las la-edit me-2"></i>Edit
                        </a>
                    @endif

                    {{-- Convert / Reject buttons: only when not approved/rejected --}}
                    @if(!in_array($lead->status, ['approved', 'rejected']))
                        @if(in_array($user->role, ['super_admin', 'lead_manager']))
                            <button type="button" class="btn btn-success action-button me-2" onclick="approveLead()">
                                <i class="las la-check me-2"></i>Convert to Work Order
                            </button>

                            @if($user->role === 'super_admin')
                                <button type="button" class="btn btn-danger action-button" onclick="rejectLead()">
                                    <i class="las la-times me-2"></i>Reject
                                </button>
                            @endif
                        @endif
                    @endif

                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">

                <!-- PRICE DETAILS CARD -->
                @if($lead->amount)
                <div class="price-card">
                    <h5><i class="las la-rupee-sign"></i>Price Details</h5>

                    <div class="price-row">
                        <span class="price-label">Total Service Cost</span>
                        <span class="price-value">₹{{ number_format($lead->amount, 2) }}</span>
                    </div>

                    <div class="price-row">
                        <span class="price-label">Advance Paid</span>
                        <span class="price-value">₹{{ number_format($lead->advance_paid_amount, 2) }}</span>
                    </div>

                    @if($lead->payment_mode)
                    <div class="price-row">
                        <span class="price-label">Payment Mode</span>
                        <span class="price-value text-uppercase">{{ str_replace('_', ' ', $lead->payment_mode) }}</span>
                    </div>
                    @endif

                    <div class="price-row">
                        <span class="price-label">Balance Amount</span>
                        <span class="price-value balance-highlight">₹{{ number_format($lead->balance_amount, 2) }}</span>
                    </div>

                    @if($lead->amountUpdatedBy && $lead->amount_updated_at)
                    <div class="price-updated-info">
                        <strong>Updated by:</strong> {{ $lead->amountUpdatedBy->name }}<br>
                        <i class="las la-clock me-1"></i>{{ $lead->amount_updated_at->format('d M Y, h:i A') }}
                    </div>
                    @endif
                </div>
                @else
                <div class="info-card text-center">
                    <i class="las la-money-bill-wave" style="font-size: 3rem; opacity: 0.15; color: #cbd5e0;"></i>
                    <p class="text-muted mb-0 mt-3" style="font-weight: 500;">Price details not set</p>
                </div>
                @endif

                <!-- Converted to Customer Section -->
                @if($lead->status === 'approved' && $lead->customer)
                <div class="converted-customer-card">
                    <h6><i class="las la-user-check me-2"></i>Converted to Customer</h6>
                    <div class="mb-2">
                        <strong>Customer Code:</strong>
                        <span class="badge bg-success ms-2">{{ $lead->customer->customer_code }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Name:</strong> {{ $lead->customer->name }}
                    </div>
                    <a href="{{ route('customers.show', $lead->customer->id) }}" class="btn view-profile-btn">
                        <i class="las la-external-link-alt me-2"></i>View Customer Profile
                    </a>
                </div>
                @endif

                <!-- Related Job Section -->
                @if($lead->status === 'approved' && $lead->jobs && $lead->jobs->count() > 0)
                    @php $job = $lead->jobs->first(); @endphp
                    <div class="related-job-card">
                        <h6><i class="las la-briefcase me-2"></i>Related Work order</h6>
                        <div class="mb-2">
                            <strong>Job Code:</strong>
                            <span class="badge bg-primary ms-2">{{ $job->job_code }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Title:</strong><br>{{ $job->title }}
                        </div>
                        <div class="mb-2">
                            <strong>Service:</strong>
                            <span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Amount:</strong>
                            <span class="text-success fw-bold">₹{{ number_format($job->amount, 2) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                        </div>
                        <a href="{{ route('jobs.show', $job->id) }}" class="btn view-job-btn">
                            <i class="las la-external-link-alt me-2"></i>View Work order Details
                        </a>
                    </div>
                @endif

                <!-- Contact Information -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-user"></i>Contact Information</h5>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $lead->email ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone 1</span>
                        <span class="info-value">{{ $lead->phone }}</span>
                    </div>
                    @if($lead->phone_alternative)
                    <div class="info-row">
                        <span class="info-label">Phone 2</span>
                        <span class="info-value">{{ $lead->phone_alternative }}</span>
                    </div>
                    @endif
                    @if($lead->address)
                    <div class="info-row">
                        <span class="info-label">Address</span>
                        <span class="info-value">{{ $lead->address }}</span>
                    </div>
                    @endif
                    @if($lead->district)
                    <div class="info-row">
                        <span class="info-label">District</span>
                        <span class="info-value">{{ $lead->district }}</span>
                    </div>
                    @endif
                </div>

                <!-- Lead Details -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-info-circle"></i>Lead Details</h5>
                    </div>

                    @if($lead->property_type)
                    <div class="info-row">
                        <span class="info-label">Property Type</span>
                        <span class="info-value">{{ ucfirst($lead->property_type) }}</span>
                    </div>
                    @endif

                    @if($lead->sqft)
                    <div class="info-row">
                        <span class="info-label">SQFT</span>
                        <span class="info-value">{{ $lead->sqft }} sq.ft</span>
                    </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Service Type</span>
                        <span class="info-value">{{ $lead->service_type ? ucfirst(str_replace('_', ' ', $lead->service_type)) : 'N/A' }}</span>
                    </div>

                    @if($lead->services->count() > 0)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                            <span class="info-label">Services</span>
                            <span class="badge bg-primary">
                                {{ $lead->services->count() }} {{ Str::plural('Service', $lead->services->count()) }}
                            </span>
                        </div>
                        <div class="services-badges">
                            @foreach($lead->services as $service)
                            <span class="service-badge">
                                {{ $service->name }}
                                @if($service->pivot && $service->pivot->quantity > 1)
                                    <span class="quantity-badge">× {{ $service->pivot->quantity }}</span>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif


                    <div class="info-row">
                        <span class="info-label">Source</span>
                        <span class="info-value">{{ $lead->source->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Branch</span>
                        <span class="info-value">{{ $lead->branch->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created By</span>
                        <span class="info-value">{{ $lead->createdBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">{{ $lead->assignedTo->name ?? 'Unassigned' }}</span>
                    </div>

                    @if($lead->description)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <span class="info-label mb-2">Description</span>
                        <span class="info-value" style="text-align: left;">{{ $lead->description }}</span>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-lg-8">

                <!-- Scheduled Followups -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-calendar-check"></i>Scheduled Followups</h5>
                            <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                                <i class="las la-plus me-1"></i>Add Followup
                            </button>
                        </div>
                    </div>

                    @if($lead->followups && $lead->followups->count() > 0)
                        <div class="followup-timeline">
                            @foreach($lead->followups as $followup)
                                <div class="followup-item {{ $followup->followup_date->isToday() ? 'today' : '' }} {{ $followup->followup_date->isPast() && $followup->status === 'pending' ? 'overdue' : '' }} {{ $followup->status === 'completed' ? 'completed' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-{{ $followup->priority === 'high' ? 'danger' : ($followup->priority === 'medium' ? 'warning' : 'info') }}">
                                                <i class="las la-flag me-1"></i>{{ ucfirst($followup->priority) }}
                                            </span>
                                            @if($followup->followup_date->isToday())
                                                <span class="badge bg-warning ms-2">Today</span>
                                            @endif
                                            @if($followup->followup_date->isPast() && $followup->status === 'pending')
                                                <span class="badge bg-danger ms-2">Overdue</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $followup->status === 'completed' ? 'success' : ($followup->status === 'cancelled' ? 'secondary' : 'primary') }}">
                                                {{ ucfirst($followup->status) }}
                                            </span>
                                            @if(auth()->user()->role === 'super_admin' || $followup->created_by === auth()->id() || $followup->assigned_to === auth()->id())
                                                <button class="btn btn-sm btn-danger ms-2 deleteFollowup" data-id="{{ $followup->id }}" title="Delete Followup">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><i class="las la-calendar me-1"></i>Date:</strong>
                                                {{ $followup->followup_date->format('d M Y') }}
                                                @if($followup->followup_time)
                                                    <br><strong><i class="las la-clock me-1"></i>Time:</strong>
                                                    {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><i class="las la-user me-1"></i>Assigned To:</strong>
                                                {{ $followup->assignedToUser->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($followup->callback_time_preference)
                                    <div class="mt-2">
                                        <span class="badge bg-info">
                                            <i class="las la-phone me-1"></i>Best Time: {{ $followup->callback_time_preference_label }}
                                        </span>
                                    </div>
                                    @endif

                                    @if($followup->notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-muted">{{ $followup->notes }}</small>
                                        </div>
                                    @endif

                                    @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to === auth()->id()))
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-success markFollowupComplete" data-id="{{ $followup->id }}">
                                                <i class="las la-check me-1"></i>Mark Complete
                                            </button>
                                        </div>
                                    @endif

                                    @if($followup->status === 'completed' && $followup->completed_at)
                                        <div class="mt-2">
                                            <small class="text-success">
                                                <i class="las la-check-circle me-1"></i>
                                                Completed on {{ $followup->completed_at->format('d M Y, h:i A') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    @else
                        <div class="empty-state">
                            <i class="las la-calendar-times"></i>
                            <p class="mb-0">No followups scheduled yet</p>
                        </div>
                    @endif
                </div>

                <!-- Call Logs -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-phone"></i>Call Logs</h5>
                            <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addCallModal">
                                <i class="las la-plus me-1"></i>Add Call
                            </button>
                        </div>
                    </div>

                    @if($lead->calls && $lead->calls->count() > 0)
                    @foreach($lead->calls as $call)
                        <div class="call-log-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong>{{ $call->user->name }}</strong>
                                    <span class="badge bg-{{ $call->outcome === 'interested' ? 'success' : ($call->outcome === 'not_interested' ? 'danger' : 'warning') }} ms-2">
                                        {{ ucfirst(str_replace('_', ' ', $call->outcome)) }}
                                    </span>
                                    <p class="text-muted small mb-1 mt-1">
                                        {{ \Carbon\Carbon::parse($call->call_date)->format('d M Y') }}
                                        @if($call->duration)
                                            • Duration: {{ $call->duration }} min
                                        @endif
                                    </p>
                                    @if($call->notes)
                                        <p class="mb-0 small">{{ $call->notes }}</p>
                                    @endif
                                </div>
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager' || $call->user_id === auth()->id())
                                    <button class="btn btn-sm btn-danger deleteCall" data-id="{{ $call->id }}" title="Delete Call">
                                        <i class="las la-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @else
                        <div class="empty-state">
                            <i class="las la-phone-slash"></i>
                            <p class="mb-0">No call logs yet</p>
                        </div>
                    @endif
                </div>

                <!-- Notes -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-sticky-note"></i>Notes</h5>
                            <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="las la-plus me-1"></i>Add Note
                            </button>
                        </div>
                    </div>

                    @if($lead->notes && $lead->notes->count() > 0)
                        @foreach($lead->notes as $note)
                            <div class="note-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $note->createdBy->name }}</strong>
                                            <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0 mt-2">{{ $note->note }}</p>
                                    </div>
                                    @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager' || $note->created_by === auth()->id())
                                        <button class="btn btn-sm btn-danger ms-2 deleteNote" data-id="{{ $note->id }}" title="Delete Note">
                                            <i class="las la-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    @else
                        <div class="empty-state">
                            <i class="las la-comment-slash"></i>
                            <p class="mb-0">No notes yet</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Add Followup Modal -->
<div class="modal fade" id="addFollowupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="las la-calendar-plus me-2"></i>Schedule Followup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFollowupForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Followup Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="followup_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Followup Time</label>
                            <input type="time" class="form-control" name="followup_time">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Time Preference for Callback</label>
                        <select class="form-select" name="callback_time_preference">
                            <option value="anytime">Anytime</option>
                            <option value="morning">Morning (9 AM - 12 PM)</option>
                            <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                            <option value="evening">Evening (4 PM - 8 PM)</option>
                        </select>
                        <small class="text-muted">Best time to reach the customer</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" required>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Add notes about this followup..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Schedule Followup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Call Modal -->
<div class="modal fade" id="addCallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="call_date" class="form-label">Call Date Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="call_date" name="call_date" required>
                            <span class="error-text call_date_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="0" placeholder="e.g., 5">
                            <span class="error-text duration_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="outcome" class="form-label">Call Outcome <span class="text-danger">*</span></label>
                        <select class="form-select" id="outcome" name="outcome" required>
                            <option value="">Select Outcome</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="callback">Callback Required</option>
                            <option value="no_answer">No Answer</option>
                            <option value="wrong_number">Wrong Number</option>
                        </select>
                        <span class="error-text outcome_error text-danger d-block mt-1"></span>
                    </div>

                    <div class="mb-3">
                        <label for="call_notes" class="form-label">Call Notes</label>
                        <textarea class="form-control" id="call_notes" name="notes" rows="3" placeholder="Enter call details..."></textarea>
                    </div>

                    <!-- Followup Section -->
                    <div id="followupSection" style="display: none;">
                        <hr>
                        <h6 class="mb-3"><i class="las la-calendar-plus"></i> Schedule Followup</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="followup_date" class="form-label">Followup Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="followup_date" name="followup_date">
                                <span class="error-text followup_date_error text-danger d-block mt-1"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="followup_time" class="form-label">Followup Time</label>
                                <input type="time" class="form-control" id="followup_time" name="followup_time">
                                <span class="error-text followup_time_error text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="callback_time_preference" class="form-label">Time Preference for Callback</label>
                            <select class="form-select" id="callback_time_preference" name="callback_time_preference">
                                <option value="anytime">Anytime</option>
                                <option value="morning">Morning (9 AM - 12 PM)</option>
                                <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                                <option value="evening">Evening (4 PM - 8 PM)</option>
                            </select>
                            <small class="text-muted">Best time to reach the customer</small>
                            <span class="error-text callback_time_preference_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="mb-3">
                            <label for="followup_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="followup_priority" name="followup_priority">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                            <span class="error-text followup_priority_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="mb-3">
                            <label for="followup_notes" class="form-label">Followup Notes</label>
                            <textarea class="form-control" id="followup_notes" name="followup_notes" rows="2" placeholder="What needs to be discussed in the followup?"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Call & Followup</button>
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
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="note" name="note" rows="4" required placeholder="Enter note..."></textarea>
                        <span class="error-text note_error text-danger d-block mt-1"></span>
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

    // Set current datetime for call_date
    let now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#call_date').val(now.toISOString().slice(0,16));

    // Show/Hide followup section based on outcome
    $('#outcome').on('change', function() {
        let outcome = $(this).val();
        if (outcome === 'callback' || outcome === 'interested') {
            $('#followupSection').slideDown();
            let tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            $('#followup_date').val(tomorrow.toISOString().split('T')[0]);
            $('#followup_date').prop('required', true);
        } else {
            $('#followupSection').slideUp();
            $('#followup_date').prop('required', false);
            $('#followup_date').val('');
            $('#followup_time').val('');
            $('#followup_notes').val('');
        }
    });

    // Mark followup as complete
    $(document).on('click', '.markFollowupComplete', function() {
        var followupId = $(this).data('id');

        Swal.fire({
            title: 'Complete Followup?',
            text: 'Mark this followup as completed',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/lead-followups/${followupId}/complete`,
                    type: 'POST',
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Completed!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Could not update followup', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.lead-status-option', function (e) {
        e.preventDefault();

        const newStatus = $(this).data('status');
        const leadId = {{ $lead->id }};
        const $btn = $('#leadStatusDropdown');
        const newLabel = $(this).text().trim();

        Swal.fire({
            title: 'Change status?',
            text: 'Update lead status to "' + newLabel + '"',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '{{ route('leads.update-status', $lead->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function (response) {
                    $btn.find('span:first').text(response.status_label);
                    Swal.fire({
                        icon: 'success',
                        title: 'Status updated',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to update status'
                    });
                }
            });
        });
    });


    // Submit Add Call Form with Followup
    $('#addCallForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $('.error-text').text('');

        $.ajax({
            url: '{{ route("leads.addCall", $lead->id) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addCallModal').modal('hide');

                let message = response.message;
                if(response.followup_created) {
                    message += '<br><small class="text-success">Followup scheduled successfully!</small>';
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('.' + key + '_error').text(value[0]);
                    });
                } else {
                    Swal.fire('Error!', 'Failed to log call', 'error');
                }
            }
        });
    });

    // Submit Add Note Form
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $('.error-text').text('');

        $.ajax({
            url: '{{ route("leads.addNote", $lead->id) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addNoteModal').modal('hide');
                $('#note').val('');

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('.' + key + '_error').text(value[0]);
                    });
                } else {
                    Swal.fire('Error!', 'Failed to add note', 'error');
                }
            }
        });
    });

    // Submit Manual Followup Form
    $('#addFollowupForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $('.error-text').text('');

        $.ajax({
            url: '{{ route("leads.addFollowup", $lead->id) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addFollowupModal').modal('hide');
                $('#addFollowupForm')[0].reset();

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('.' + key + '_error').text(value[0]);
                    });
                } else {
                    Swal.fire('Error!', 'Failed to schedule followup', 'error');
                }
            }
        });
    });

    // Delete Followup
    $(document).on('click', '.deleteFollowup', function() {
        var followupId = $(this).data('id');

        Swal.fire({
            title: 'Delete Followup?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/leads/followup/${followupId}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete followup', 'error');
                    }
                });
            }
        });
    });

    // Delete Call Log
    $(document).on('click', '.deleteCall', function() {
        var callId = $(this).data('id');

        Swal.fire({
            title: 'Delete Call Log?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/leads/call/${callId}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete call log', 'error');
                    }
                });
            }
        });
    });

    // Delete Note
    $(document).on('click', '.deleteNote', function() {
        var noteId = $(this).data('id');

        Swal.fire({
            title: 'Delete Note?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/leads/note/${noteId}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete note', 'error');
                    }
                });
            }
        });
    });


    // Approve Lead
    window.approveLead = function() {
        Swal.fire({
            title: 'Convert Lead?',
            html: `
                <div class="text-start mt-3">
                    <label class="form-label fw-semibold">Convert Notes (Optional)</label>
                    <textarea id="approval_notes" class="form-control" rows="3" placeholder="Add any notes about this approval"></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="las la-check me-2"></i>Yes, Convert',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Cancel',
            width: '500px',
            preConfirm: () => {
                return {
                    approval_notes: document.getElementById('approval_notes').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("leads.approve", $lead->id) }}',
                    type: 'POST',
                    data: result.value,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Lead Converted!',
                            html: `
                                <p class="mb-3">${response.message}</p>
                                <div class="text-start">
                                    <p class="mb-2"><strong>Customer Code:</strong> <span class="badge bg-success">${response.customer_code}</span></p>
                                    <p class="mb-2"><strong>Job Code:</strong> <span class="badge bg-primary">${response.job_code}</span></p>
                                    <p class="mb-2"><strong>Amount:</strong> <span class="text-success">${response.amount}</span></p>
                                    <p class="mb-0"><strong>Remaining Budget:</strong> <span class="text-success">${response.remaining_budget}</span></p>
                                </div>
                            `,
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message || 'Failed to approve lead';
                        let budgetInfo = xhr.responseJSON?.budget_info || null;

                        let html = `<p>${message}</p>`;
                        if(budgetInfo) {
                            html += `
                                <div class="mt-3 text-start alert alert-danger">
                                    <p class="mb-1"><strong>Daily Limit:</strong> ${budgetInfo.daily_limit}</p>
                                    <p class="mb-1"><strong>Used Today:</strong> ${budgetInfo.today_total}</p>
                                    <p class="mb-1"><strong>Remaining:</strong> ${budgetInfo.remaining}</p>
                                    <p class="mb-1"><strong>Requested:</strong> ${budgetInfo.requested}</p>
                                    <p class="mb-0 text-danger"><strong>Excess Amount:</strong> ${budgetInfo.excess}</p>
                                </div>
                            `;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Cannot Approve Lead',
                            html: html,
                            width: '500px'
                        });
                    }
                });
            }
        });
    };

    // Reject Lead
    window.rejectLead = function() {
        Swal.fire({
            title: 'Reject Lead?',
            html: `
                <div class="text-start mt-3">
                    <label class="form-label fw-semibold">Rejection Reason *</label>
                    <textarea id="rejection_reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection" required></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="las la-times me-2"></i>Yes, Reject',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel',
            width: '500px',
            preConfirm: () => {
                const val = document.getElementById('rejection_reason').value;
                if(!val || !val.trim()) {
                    Swal.showValidationMessage('Rejection reason is required');
                    return false;
                }
                return { rejection_reason: val };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.ajax({
                    url: '{{ route("leads.reject", $lead->id) }}',
                    type: 'POST',
                    data: result.value,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Lead Rejected',
                            text: response.message,
                            confirmButtonColor: '#ef4444'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reject lead', 'error');
                    }
                });
            }
        });
    };
});
</script>
@endsection
