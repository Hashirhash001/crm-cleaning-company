@extends('layouts.app')

@section('title', 'Work Order Details - ' . $job->job_code)

@section('extra-css')
    <style>
        /* Professional CRM Color Palette - Same as Lead Details */
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

        .job-detail-container {
            background: var(--light-bg);
            min-height: calc(100vh - 100px);
            padding: 1.5rem 0;
        }

        /* Header Card - Simple Blue */
        .job-header-card {
            background: var(--primary-blue);
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
            font-size: 0.85rem;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            align-items: center;
        }

        .price-row:last-of-type {
            border-bottom: none;
            padding-top: 1rem;
            margin-top: 0.5rem;
            /* border-top: 2px solid rgba(255, 255, 255, 0.3); */
        }

        .price-label {
            font-weight: 600;
            font-size: 1rem;
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

        .related-lead-card {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .related-lead-card h6 {
            color: var(--primary-blue);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        /* Buttons - Clean Professional */
        .action-button {
            border-radius: 8px;
            padding: 0.50rem 1rem;
            font-weight: 600;
            border: none;
            font-size: 0.9rem;
        }

        .view-profile-btn,
        .view-lead-btn {
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

        .view-lead-btn {
            background: var(--primary-blue);
            color: #fff;
        }

        .view-lead-btn:hover {
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

        .services-section {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
        .call-log-item,
        .note-item {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 3px solid var(--primary-blue);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
            .job-header-card {
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

        .deleteFollowup,
        .deleteCall,
        .deleteNote {
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .deleteFollowup:hover,
        .deleteCall:hover,
        .deleteNote:hover {
            opacity: 1;
        }
        /* Make long comments readable */
        .price-row.comments-row{
        flex-direction: column;
        align-items: flex-start;
        gap: 0.35rem;
        }

        .price-row.comments-row .price-label{
        flex: none;            /* don’t force label/value columns */
        width: 100%;
        }

        .price-row.comments-row .price-value{
        flex: none;
        width: 100%;
        text-align: left;      /* better for paragraphs */
        font-size: 0.95rem;    /* optional: smaller than amounts */
        font-weight: 500;      /* optional: not as bold as amounts */
        line-height: 1.4;
        }

        .price-value.wrap{
        white-space: normal;
        overflow-wrap: anywhere;  /* breaks long strings */
        word-break: break-word;
        }

    </style>
@endsection

@section('content')
    <div class="job-detail-container">
        <div class="container-fluid">

            <!-- Job Header with Action Buttons -->
            <div class="job-header-card">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <h2 class="mb-0 me-3">{{ $job->title }}</h2>
                            <span class="job-status-badge">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                        </div>
                        <p class="mb-0" style="opacity: 0.95; font-weight: 500;">
                            <i class="las la-tag me-2"></i>{{ $job->job_code }}
                            <i class="las la-calendar ms-3 me-2"></i>{{ $job->created_at->format('d M Y') }}
                        </p>
                    </div>

                    <div class="col-md-8 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('jobs.index') }}" class="btn btn-light action-button me-2">
                            <i class="las la-arrow-left me-2"></i>Back to Work Orders
                        </a>

                        @php
                            $user = auth()->user();
                        @endphp

                        @if ($user->role === 'super_admin' || $user->role === 'lead_manager' || $user->role === 'telecallers')
                            <button type="button" class="btn btn-primary action-button me-2 editJobBtn"
                                data-id="{{ $job->id }}">
                                <i class="las la-edit me-2"></i>Edit
                            </button>
                        @endif

                        {{-- Complete Job Button - Only show if status is confirmed and user is authorized --}}
                        @if(
                            $job->status === 'approved' &&
                            (
                                $user->role === 'super_admin' ||
                                $user->role === 'telecallers' ||
                                ($user->role === 'field_staff' && $user->id === $job->assigned_to)
                            )
                        )
                            <button type="button" class="btn btn-success action-button me-2" onclick="completeJob({{ $job->id }})">
                                <i class="las la-check-circle me-2"></i>Complete Job
                            </button>
                        @endif

                        @if ($user->role === 'super_admin')
                            <button type="button" class="btn btn-danger action-button" onclick="deleteJob()">
                                <i class="las la-trash-alt me-2"></i>Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-4">

                    <!-- PRICE DETAILS CARD -->
                    @if ($job->amount)
                        <div class="price-card">
                            <h5><i class="las la-rupee-sign"></i>Financial Details</h5>

                            <div class="price-row">
                                <span class="price-label">Total Amount</span>
                                <span class="price-value">₹{{ number_format($job->amount, 2) }}</span>
                            </div>

                            <div class="price-row">
                                <span class="price-label">Amount Paid</span>
                                <span class="price-value">₹{{ number_format($job->amount_paid ?? 0, 2) }}</span>
                            </div>

                            @if ($job->payment_mode)
                                <div class="price-row">
                                    <span class="price-label">Payment Mode</span>
                                    <span
                                        class="price-value text-uppercase">{{ str_replace('_', ' ', $job->payment_mode) }}</span>
                                </div>
                            @endif

                            <div class="price-row">
                                <span class="price-label">Balance Amount</span>
                                <span
                                    class="price-value balance-highlight">₹{{ number_format($job->amount - ($job->amount_paid ?? 0), 2) }}</span>
                            </div>

                            @if(($job->addon_price ?? 0) > 0)
                            <div class="price-row">
                                <span class="price-label">Add-on Price</span>
                                <span class="price-value">{{ number_format($job->addon_price, 2) }}</span>
                            </div>
                            @endif

                            @if(!empty($job->addon_price_comments))
                            <div class="price-row comments-row">
                                <span class="price-label">Add-on Comments</span>
                                <span class="price-value wrap">{{ $job->addon_price_comments }}</span>
                            </div>
                            @endif

                        </div>
                    @else
                        <div class="info-card text-center">
                            <i class="las la-money-bill-wave" style="font-size: 3rem; opacity: 0.15; color: #cbd5e0;"></i>
                            <p class="text-muted mb-0 mt-3" style="font-weight: 500;">Financial details not set</p>
                        </div>
                    @endif

                    <!-- Customer Information Section -->
                    @if ($job->customer)
                        <div class="customer-link-card">
                            <h6><i class="las la-user-check me-2"></i>Customer Information</h6>
                            <div class="mb-2">
                                <strong>Customer Code:</strong>
                                <span class="badge bg-success ms-2">{{ $job->customer->customer_code }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Name:</strong> {{ $job->customer->name }}
                            </div>
                            <a href="{{ route('customers.show', $job->customer->id) }}" class="btn view-profile-btn">
                                <i class="las la-external-link-alt me-2"></i>View Customer Profile
                            </a>
                        </div>
                    @endif

                    <!-- Related Lead Section -->
                    @if ($job->lead)
                        <div class="related-lead-card">
                            <h6><i class="las la-link me-2"></i>Related Lead</h6>
                            <div class="mb-2">
                                <strong>Lead Code:</strong>
                                <span class="badge bg-primary ms-2">{{ $job->lead->lead_code }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Name:</strong> {{ $job->lead->name }}
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span
                                    class="badge bg-{{ $job->lead->status == 'approved' ? 'success' : 'warning' }}">{{ ucfirst($job->lead->status) }}</span>
                            </div>
                            <a href="{{ route('leads.show', $job->lead->id) }}" class="btn view-lead-btn">
                                <i class="las la-external-link-alt me-2"></i>View Lead Details
                            </a>
                        </div>
                    @endif

                </div>

                <!-- Right Column -->
                <div class="col-lg-8">

                    <!-- Job Details -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <h5><i class="las la-briefcase"></i>Work Order Details</h5>
                        </div>

                        @if ($job->branch)
                            <div class="info-row">
                                <span class="info-label">Branch</span>
                                <span class="info-value">{{ $job->branch->name }}</span>
                            </div>
                        @endif

                        @if ($job->services && $job->services->count() > 0)
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                                    <span class="info-label">Services</span>
                                    <span class="badge bg-primary">{{ $job->services->count() }}
                                        {{ Str::plural('Service', $job->services->count()) }}</span>
                                </div>

                                <!-- Detailed List View (Same as Index Page) -->
                                <div class="service-list-table" style="width: 100%;">
                                    @php
                                        $totalQuantity = $job->services->sum(function ($service) {
                                            return $service->pivot->quantity ?? 1;
                                        });
                                    @endphp

                                    @foreach ($job->services as $service)
                                        <div class="service-list-item"
                                            style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 6px; margin-bottom: 0.5rem; border: 1px solid #e2e8f0;">
                                            <span class="service-name"
                                                style="font-weight: 600; color: var(--text-primary);">
                                                <i
                                                    class="las la-{{ $service->service_type == 'cleaning' ? 'broom' : ($service->service_type == 'pestcontrol' ? 'bug' : 'cogs') }} me-2 text-primary"></i>
                                                {{ $service->name }}
                                                <small
                                                    class="text-muted ms-1">({{ ucfirst(str_replace('_', ' ', $service->service_type)) }})</small>
                                            </span>
                                            <span class="service-qty"
                                                style="background: var(--primary-blue); color: #fff; padding: 0.25rem 0.75rem; border-radius: 5px; font-size: 0.85rem; font-weight: 600;">
                                                Qty: {{ $service->pivot->quantity ?? 1 }}
                                            </span>
                                        </div>
                                    @endforeach

                                    @if ($totalQuantity > $job->services->count())
                                        <div class="mt-2 text-end">
                                            <small class="text-muted"><strong>Total Items:</strong>
                                                {{ $totalQuantity }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif


                        @if ($job->location)
                            <div class="info-row">
                                <span class="info-label">Location</span>
                                <span class="info-value">{{ $job->location }}</span>
                            </div>
                        @endif

                        <div class="info-row">
                            <span class="info-label">Assigned To</span>
                            <span class="info-value">{{ $job->assignedTo->name ?? 'Unassigned' }}</span>
                        </div>

                        @if ($job->scheduled_date)
                            <div class="info-row">
                                <span class="info-label">Scheduled Date</span>
                                <span class="info-value">
                                    {{ \Carbon\Carbon::parse($job->scheduled_date)->format('d M Y') }}
                                    @if ($job->scheduled_time)
                                        <br><small>{{ \Carbon\Carbon::parse($job->scheduled_time)->format('h:i A') }}</small>
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

                        @if ($job->confirmed_at)
                            <div class="info-row">
                                <span class="info-label">Confirmed At</span>
                                <span class="info-value">{{ $job->confirmed_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif

                        @if ($job->assigned_at)
                            <div class="info-row">
                                <span class="info-label">Assigned At</span>
                                <span class="info-value">{{ $job->assigned_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif

                        @if ($job->started_at)
                            <div class="info-row">
                                <span class="info-label">Started At</span>
                                <span class="info-value">{{ $job->started_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif

                        @if ($job->completed_at)
                            <div class="info-row">
                                <span class="info-label">Completed At</span>
                                <span class="info-value">{{ $job->completed_at->format('d M Y, h:i A') }}</span>
                            </div>
                        @endif

                        @if ($job->description)
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label mb-2">Description</span>
                                <span class="info-value" style="text-align: left;">{{ $job->description }}</span>
                            </div>
                        @endif

                        @if ($job->customer_instructions)
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label mb-2">Customer Instructions</span>
                                <span class="info-value"
                                    style="text-align: left;">{{ $job->customer_instructions }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Scheduled Followups -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5><i class="las la-calendar-check"></i>Scheduled Followups</h5>
                                <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal"
                                    data-bs-target="#addFollowupModal">
                                    <i class="las la-plus me-1"></i>Add Followup
                                </button>
                            </div>
                        </div>

                        @if ($job->followups && $job->followups->count() > 0)
                            <div class="followup-timeline">
                                @foreach ($job->followups as $followup)
                                    <div
                                        class="followup-item {{ $followup->followup_date->isToday() ? 'today' : '' }} {{ $followup->followup_date->isPast() && $followup->status == 'pending' ? 'overdue' : '' }} {{ $followup->status == 'completed' ? 'completed' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span
                                                    class="badge bg-{{ $followup->priority == 'high' ? 'danger' : ($followup->priority == 'medium' ? 'warning' : 'info') }}">
                                                    <i class="las la-flag me-1"></i>{{ ucfirst($followup->priority) }}
                                                </span>
                                                @if ($followup->followup_date->isToday())
                                                    <span class="badge bg-warning ms-2">Today</span>
                                                @endif
                                                @if ($followup->followup_date->isPast() && $followup->status == 'pending')
                                                    <span class="badge bg-danger ms-2">Overdue</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span
                                                    class="badge bg-{{ $followup->status == 'completed' ? 'success' : ($followup->status == 'cancelled' ? 'secondary' : 'primary') }}">
                                                    {{ ucfirst($followup->status) }}
                                                </span>
                                                @if (auth()->user()->role == 'super_admin' ||
                                                        $followup->created_by == auth()->id() ||
                                                        $followup->assigned_to == auth()->id())
                                                    <button class="btn btn-sm btn-danger ms-2 deleteFollowup"
                                                        data-id="{{ $followup->id }}" title="Delete Followup">
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
                                                    @if ($followup->followup_time)
                                                        <br><strong><i class="las la-clock me-1"></i>Time:</strong>
                                                        {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <strong><i class="las la-user me-1"></i>Assigned To:</strong>
                                                    {{ $followup->assignedTo->name ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        @if ($followup->notes)
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small class="text-muted">{{ $followup->notes }}</small>
                                            </div>
                                        @endif
                                        @if ($followup->status == 'pending' && (auth()->user()->role == 'super_admin' || $followup->assigned_to == auth()->id()))
                                            <div class="mt-3">
                                                <button class="btn btn-sm btn-success markFollowupComplete"
                                                    data-id="{{ $followup->id }}">
                                                    <i class="las la-check me-1"></i>Mark Complete
                                                </button>
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
                                <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal"
                                    data-bs-target="#addCallModal">
                                    <i class="las la-plus me-1"></i>Add Call
                                </button>
                            </div>
                        </div>

                        @if ($job->calls && $job->calls->count() > 0)
                            @foreach ($job->calls as $call)
                                <div class="call-log-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong>{{ $call->user->name }}</strong>
                                            <span
                                                class="badge bg-{{ $call->outcome == 'completed' ? 'success' : ($call->outcome == 'issue_reported' ? 'danger' : 'warning') }} ms-2">
                                                {{ ucfirst(str_replace('_', ' ', $call->outcome)) }}
                                            </span>
                                            <p class="text-muted small mb-1 mt-1">
                                                {{ \Carbon\Carbon::parse($call->call_date)->format('d M Y') }}
                                                @if ($call->duration)
                                                    • Duration: {{ $call->duration }} min
                                                @endif
                                            </p>
                                            @if ($call->notes)
                                                <p class="mb-0 small">{{ $call->notes }}</p>
                                            @endif
                                        </div>
                                        @if (auth()->user()->role == 'super_admin' || auth()->user()->role == 'lead_manager' || $call->user_id == auth()->id())
                                            <button class="btn btn-sm btn-danger deleteCall"
                                                data-id="{{ $call->id }}" title="Delete Call">
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
                                <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal"
                                    data-bs-target="#addNoteModal">
                                    <i class="las la-plus me-1"></i>Add Note
                                </button>
                            </div>
                        </div>

                        @if ($job->notes && $job->notes->count() > 0)
                            @foreach ($job->notes as $note)
                                <div class="note-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $note->createdBy->name }}</strong>
                                                <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0 mt-2">{{ $note->note }}</p>
                                        </div>
                                        @if (auth()->user()->role == 'superadmin' || auth()->user()->role == 'lead_manager' || $note->created_by == auth()->id())
                                            <button class="btn btn-sm btn-danger ms-2 deleteNote"
                                                data-id="{{ $note->id }}" title="Delete Note">
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
                                <input type="date" class="form-control" name="followup_date" required
                                    min="{{ date('Y-m-d') }}">
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
    <div class="modal fade" id="addCallModal" tabindex="-1">
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
                                <label for="call_date" class="form-label">Call Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="call_date" name="call_date" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration"
                                    min="0" placeholder="e.g., 5">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="outcome" class="form-label">Call Outcome <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="outcome" name="outcome" required>
                                <option value="">Select Outcome</option>
                                <option value="completed">Completed</option>
                                <option value="rescheduled">Rescheduled</option>
                                <option value="issue_reported">Issue Reported</option>
                                <option value="follow_up_needed">Follow-up Needed</option>
                                <option value="no_answer">No Answer</option>
                                <option value="other">Other</option>
                            </select>
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
                                    <label for="followup_date" class="form-label">Followup Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="followup_date" name="followup_date"
                                        min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="followup_time" class="form-label">Followup Time</label>
                                    <input type="time" class="form-control" id="followup_time" name="followup_time">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="callback_time_preference" class="form-label">Time Preference for
                                    Callback</label>
                                <select class="form-select" id="callback_time_preference"
                                    name="callback_time_preference">
                                    <option value="anytime">Anytime</option>
                                    <option value="morning">Morning (9 AM - 12 PM)</option>
                                    <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                                    <option value="evening">Evening (4 PM - 8 PM)</option>
                                </select>
                                <small class="text-muted">Best time to reach the customer</small>
                            </div>

                            <div class="mb-3">
                                <label for="followup_priority" class="form-label">Priority <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="followup_priority" name="followup_priority">
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="followup_notes" class="form-label">Followup Notes</label>
                                <textarea class="form-control" id="followup_notes" name="followup_notes" rows="2"
                                    placeholder="What needs to be discussed in the followup?"></textarea>
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
    <div class="modal fade" id="addNoteModal" tabindex="-1">
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

    <!-- Edit Job Modal (Same as Jobs Index) -->
    <div class="modal fade" id="jobModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="jobModalLabel">Edit Work Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="jobForm">
                    @csrf
                    <input type="hidden" id="jobid" name="jobid">

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
                                    <span class="error-text branchiderror text-danger d-block mt-1"></span>
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label class="form-label">Branch</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ auth()->user()->branch->name ?? 'N/A' }}" readonly
                                        style="cursor:not-allowed;">
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
                                </select>
                                <span class="error-text customeriderror text-danger d-block mt-1"></span>
                                <small class="text-muted">Select branch first to load customers.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="servicetype" class="form-label required-field">Service Type</label>
                                <select class="form-select" id="servicetype" name="service_type" required>
                                    <option value="">Select Service Type</option>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="pest_control">Pest Control</option>
                                    <option value="other">Other</option>
                                </select>
                                <span class="error-text servicetypeerror text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- Services with Quantity --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label required-field">Select Services (with quantity)</label>

                                <div class="service-select-box" id="servicesContainer">
                                    <p class="text-muted text-center my-3">
                                        <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                                        Please select a service type first
                                    </p>
                                </div>

                                <small class="text-muted">Check services and specify quantity for each</small>
                                <span class="error-text serviceidserror text-danger d-block mt-1"></span>
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

                        {{-- Customer Instructions --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="customerinstructions" class="form-label">Customer Instructions</label>
                                <textarea class="form-control" id="customerinstructions" name="customer_instructions" rows="2"
                                    placeholder="Special instructions or preferences for this job..."></textarea>
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
                                        <option value="pending">Pending</option>
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
                                    <strong>Work order Code:</strong> <span
                                        class="badge bg-primary ms-2">{{ $job->job_code }}</span><br>
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
                                @if ($telecallers->count() > 0)
                                    <optgroup label="📞 Telecallers">
                                        @foreach ($telecallers as $telecaller)
                                            <option value="{{ $telecaller->id }}"
                                                {{ $job->assigned_to == $telecaller->id ? 'selected' : '' }}>
                                                {{ $telecaller->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                <!-- Field Staff Group -->
                                @if ($field_staff->count() > 0)
                                    <optgroup label="🔧 Field Staff">
                                        @foreach ($field_staff as $staff)
                                            <option value="{{ $staff->id }}"
                                                {{ $job->assigned_to == $staff->id ? 'selected' : '' }}>
                                                {{ $staff->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <small class="text-muted">
                                <i class="las la-info-circle"></i> Select a staff member to assign this work order to
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

            let currentJobServiceIds = [];

            // Initialize Select2 on assign dropdown
            $('#assigned_to').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search and select staff member',
                allowClear: true,
                dropdownParent: $('#assignJobModal'),
                width: '100%'
            });

            // Show/Hide followup section based on call outcome
            $('#outcome').on('change', function() {
                let outcome = $(this).val();
                if (outcome === 'follow_up_needed' || outcome === 'rescheduled') {
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

            // Submit Add Call Form with Followup
            $('#addCallForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                $.ajax({
                    url: '{{ route('jobs.addCall', $job->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addCallModal').modal('hide');
                        let message = response.message;
                        if (response.followup_created) {
                            message +=
                                '<br><small class="text-success">Followup scheduled successfully!</small>';
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
                        Swal.fire('Error!', 'Failed to log call', 'error');
                    }
                });
            });

            // Submit Add Followup Form
            $('#addFollowupForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                $.ajax({
                    url: '{{ route('jobs.addFollowup', $job->id) }}',
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
                        Swal.fire('Error!', 'Failed to schedule followup', 'error');
                    }
                });
            });

            // Submit Add Note Form
            $('#addNoteForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                $.ajax({
                    url: '{{ route('jobs.addNote', $job->id) }}',
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
                        Swal.fire('Error!', 'Failed to add note', 'error');
                    }
                });
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
                            url: `/job-followups/${followupId}/complete`,
                            type: 'POST',
                            success: function(response) {
                                if (response.success) {
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
                                Swal.fire('Error', 'Could not complete followup',
                                    'error');
                            }
                        });
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
                            url: `/jobs/{{ $job->id }}/followups/${followupId}`,
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
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Failed to delete followup', 'error');
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
                            url: `/jobs/{{ $job->id }}/calls/${callId}`,
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
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Failed to delete call log', 'error');
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
                            url: `/jobs/{{ $job->id }}/notes/${noteId}`,
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
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Failed to delete note', 'error');
                            }
                        });
                    }
                });
            });

            const jobModal = $('#jobModal');

            const jobForm = $('#jobForm');
            const jobid = $('#jobid');

            const title = $('#title');
            const branchid = $('#branchid');
            const customerId = $('#customerId');
            const servicetype = $('#servicetype');
            const servicesContainer = $('#servicesContainer');

            const amount = $('#amount');
            const amountPaid = $('#amountPaid');
            const balanceAmount = $('#balanceAmount');

            const location = $('#location');
            const scheduleddate = $('#scheduleddate');
            const scheduledtime = $('#scheduledtime');
            const description = $('#description');
            const customerinstructions = $('#customerinstructions');

            // ----------------------------
            // Select2: Customer dropdown
            // ----------------------------
            function initializeCustomerSelect2() {
                if ($('.select2-customer').data('select2')) {
                    $('.select2-customer').select2('destroy');
                }

                $('.select2-customer').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search by name, code, or phone',
                    allowClear: true,
                    dropdownParent: jobModal,
                    width: '100%'
                });
            }

            function resetCustomerDropdown() {
                customerId.html('<option value="">Select Customer</option>').val('').trigger('change');
            }

            function loadCustomersByBranch(branchId, preselectCustomerId = null) {
                resetCustomerDropdown();
                if (!branchId) return;

                customerId.html('<option value="">Loading customers...</option>');

                $.ajax({
                    url: "{{ route('customers.byBranch') }}",
                    type: "GET",
                    data: {
                        branch_id: branchId
                    },
                    success: function(customers) {
                        let html = '<option value="">Select Customer</option>';

                        customers.forEach(c => {
                            const code = c.customer_code ?? '';
                            const phone = c.phone ?? '';
                            const text = (code ? (code + ' - ') : '') + c.name + (phone ? (
                                ' - ' + phone) : '');
                            html += `<option value="${c.id}">${text}</option>`;
                        });

                        customerId.html(html);
                        initializeCustomerSelect2();

                        if (preselectCustomerId) {
                            customerId.val(preselectCustomerId).trigger('change');
                        }
                    },
                    error: function() {
                        resetCustomerDropdown();
                    }
                });
            }

            // Ensure select2 is ready when modal opens
            jobModal.on('shown.bs.modal', function() {
                if (!$('.select2-customer').data('select2')) {
                    initializeCustomerSelect2();
                }
            });

            // Branch change => reload customers
            $(document).on('change', '#branchid', function() {
                const branchId = $(this).val();
                if (branchId) loadCustomersByBranch(branchId);
                else resetCustomerDropdown();
            });

            // ----------------------------
            // Balance calc
            // ----------------------------
            function calculateBalance() {
                const total = parseFloat(amount.val()) || 0;
                const paid = parseFloat(amountPaid.val()) || 0;
                const bal = total - paid;
                balanceAmount.val(bal.toFixed(2));
            }

            $(document).on('input', '#amount, #amountPaid', calculateBalance);

            // ----------------------------
            // Load services by type + qty
            // ----------------------------
            function loadServices(serviceType, preselectedIds = [], preselectedQty = {}) {
                if (!serviceType) {
                    servicesContainer.html(`
                            <p class="text-muted text-center my-3">
                            <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                            Please select a service type first
                            </p>
                        `);
                    return;
                }

                servicesContainer.html(
                    '<p class="text-center my-3"><i class="las la-spinner la-spin"></i> Loading services...</p>'
                );

                $.ajax({
                    url: "{{ route('leads.servicesByType') }}",
                    type: "GET",
                    data: {
                        service_type: serviceType
                    },
                    success: function(services) {
                        if (!services || services.length === 0) {
                            servicesContainer.html(
                                '<p class="text-muted text-center my-3">No services available for this type</p>'
                            );
                            return;
                        }

                        let html = '';

                        services.forEach(service => {
                            const isChecked = (preselectedIds || []).includes(service.id);
                            const qtyValue = (preselectedQty && preselectedQty[service.id]) ?
                                preselectedQty[service.id] : 1;

                            html += `
                                <div class="service-checkbox-item justify-content-between">
                                <div class="service-checkbox-wrapper">
                                    <input type="checkbox"
                                        name="service_ids[]"
                                        value="${service.id}"
                                        id="service_${service.id}"
                                        class="service-checkbox"
                                        data-service-id="${service.id}"
                                        ${isChecked ? 'checked' : ''}>
                                    <label for="service_${service.id}">${service.name}</label>
                                </div>

                                <div class="service-quantity-wrapper">
                                    <span class="quantity-label">Qty</span>
                                    <input type="number"
                                        name="service_quantities[${service.id}]"
                                        id="quantity_${service.id}"
                                        class="service-quantity-input"
                                        min="1"
                                        value="${qtyValue}"
                                        ${isChecked ? '' : 'disabled'}>
                                </div>
                                </div>
                            `;
                        });

                        servicesContainer.html(html);
                    },
                    error: function() {
                        servicesContainer.html(
                            '<p class="text-danger text-center my-3">Error loading services. Please try again.</p>'
                        );
                    }
                });
            }

            // Service type change => reload services
            $(document).on('change', '#servicetype', function() {
                loadServices($(this).val(), currentJobServiceIds, {});
            });

            // Enable/disable qty based on checkbox
            $(document).on('change', '.service-checkbox', function() {
                const serviceId = $(this).data('service-id');
                const qtyInput = $('#quantity_' + serviceId);

                if (this.checked) {
                    qtyInput.prop('disabled', false);
                    if (!qtyInput.val()) qtyInput.val(1);
                } else {
                    qtyInput.prop('disabled', true).val(1);
                }
            });

            // Show/hide confirmation message based on checkbox
            $(document).on('change', '#confirmOnCreation', function() {
                if ($(this).is(':checked')) {
                    // Show info message
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

            // ----------------------------
            // Edit button => load job data and open modal
            // ----------------------------
            $(document).on('click', '.editJobBtn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `/jobs/${id}/edit`,
                    type: 'GET',
                    success: function(response) {
                        if (!response.success) return;

                        const job = response.job;

                        jobid.val(job.id);
                        title.val(job.title || '');
                        description.val(job.description || '');
                        customerinstructions.val(job.customer_instructions || '');
                        $('#addonPrice').val(job.addon_price || '');
                        $('#addonPriceComments').val(job.addon_price_comments || '');

                        // Branch
                        branchid.val(job.branch_id);

                        // Customers (branch-based) + preselect
                        loadCustomersByBranch(job.branch_id, job.customer_id);

                        // Amounts
                        amount.val(job.amount || 0);
                        amountPaid.val(job.amount_paid || 0);
                        calculateBalance();

                        // Other fields
                        location.val(job.location || '');

                        // Dates/times
                        if (job.scheduled_date) {
                            scheduleddate.val((job.scheduled_date + '').split(' ')[0].split('T')[0]);
                        } else {
                            scheduleddate.val('');
                        }

                        if (job.scheduled_time) {
                            scheduledtime.val((job.scheduled_time + '').substring(0, 5));
                        } else {
                            scheduledtime.val('');
                        }

                        //  Clear previous messages
                        $('.confirm-info-message').remove();

                        // Hide status dropdown and confirm checkbox if approved or completed
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

                        // ---- Services (FIXED: quantities mapping) ----
                        const serviceType = job.service_type ?? job.servicetype ?? '';
                        const serviceIds = job.service_ids ?? job.serviceids ?? [];
                        const serviceQty = job.service_quantities ?? job.servicequantities ?? {};

                        servicetype.val(serviceType);
                        currentJobServiceIds = serviceIds;

                        // Make sure qty keys are numeric-consistent
                        const normalizedQty = {};
                        Object.keys(serviceQty || {}).forEach(k => {
                            normalizedQty[parseInt(k, 10)] = parseInt(serviceQty[k], 10) || 1;
                        });

                        loadServices(serviceType, currentJobServiceIds, normalizedQty);

                        $('.error-text').text('');
                        $('#jobModalLabel').text('Edit Work Order');
                        jobModal.modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading job:', xhr);
                        Swal.fire('Error!', 'Failed to load job data', 'error');
                    }
                });
            });

            // ----------------------------
            // Submit (PUT)
            // ----------------------------
            jobForm.on('submit', function(e) {
                e.preventDefault();

                const id = jobid.val();
                if (!id) return;

                if ($('.service-checkbox:checked').length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select at least one service'
                    });
                    return;
                }

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                // ✅ NEW: Remove status if dropdown is disabled OR hidden
                if ($('#jobstatus').prop('disabled') || !$('#statusDropdownRow').is(':visible')) {
                    formData.delete('status');
                }

                // ✅ NEW: Remove confirm checkbox if hidden
                if (!$('#confirmCheckboxRow').is(':visible')) {
                    formData.delete('confirm_on_creation');
                }

                $('.error-text').text('');

                $.ajax({
                    url: `/jobs/${id}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        jobModal.modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Work Order updated successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload(); // reload details page
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            $.each(errors, function(key, value) {
                                $(`.${key.replaceAll('_','')}error`).text(value[0]);
                                $(`.${key}error`).text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Something went wrong', 'error');
                        }
                    }
                });
            });

            // Calculate balance on amount change
            $(document).on('input', '#amount, #amount_paid', function() {
                calculateBalance();
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
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to assign job',
                            'error');
                    }
                });
            });
        });

        function confirmJobStatus(jobId) {
            Swal.fire({
                title: 'Confirm work order Status?',
                text: 'This will change the work order status to confirmed.',
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
                            if (response.success) {
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
                            Swal.fire('Error', xhr.responseJSON?.message || 'Could not confirm job.',
                                'error');
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
                            Swal.fire('Deleted!', 'work order deleted successfully', 'success').then(() => {
                                window.location.href = '{{ route('jobs.index') }}';
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
                text: 'This will mark the work order as in progress',
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
                            Swal.fire('Started!', 'work order started successfully', 'success').then(() => {
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
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to complete job', 'error');
                        }
                    });
                }
            });
        }

    </script>
@endsection
