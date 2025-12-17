@extends('layouts.app')

@section('title', 'Dashboard')

@section('extra-css')
<style>
    /* Professional Unified Dashboard Styles */
    :root {
        --primary-blue: #2563eb;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --info-cyan: #06b6d4;
        --purple: #8b5cf6;
        --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .stat-card {
        height: 100%;
        min-height: 120px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
        height: 100%;
        padding: 1.25rem;
    }

    .card {
        margin-bottom: 1.5rem;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
    }

    /* Priority Cards - Unified for all users */
    .priority-card {
        border-left: 4px solid;
        border-radius: 8px;
    }

    .priority-card.urgent {
        border-left-color: var(--danger-red);
        background: linear-gradient(90deg, rgba(239, 68, 68, 0.05), #fff);
    }

    .priority-card.high {
        border-left-color: var(--warning-orange);
        background: linear-gradient(90deg, rgba(245, 158, 11, 0.05), #fff);
    }

    .priority-card.medium {
        border-left-color: var(--info-cyan);
        background: linear-gradient(90deg, rgba(6, 182, 212, 0.05), #fff);
    }

    .priority-card.normal {
        border-left-color: var(--success-green);
        background: linear-gradient(90deg, rgba(16, 185, 129, 0.05), #fff);
    }

    /* Section Headers - Unified Design */
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header.urgent {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .section-header.high {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .section-header.medium {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .section-header.normal {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .section-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Followup Item Styling */
    .followup-item {
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        background: #fff;
    }

    .followup-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .followup-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.75rem;
    }

    .followup-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .followup-meta {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Time Preference Badge - Highlighted */
    .time-preference-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        border: 2px solid #fcd34d;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
    }

    .time-preference-badge i {
        font-size: 1.1rem;
        margin-right: 0.4rem;
    }

    /* Lead Status Colors */
    .status-pending { background-color: #fef3c7; color: #92400e; }
    .status-site_visit { background-color: #dbeafe; color: #1e3a8a; }
    .status-approved { background-color: #d1fae5; color: #065f46; }
    .status-they_will_confirm { background-color: #e0e7ff; color: #3730a3; }

    /* Search Results Dropdown */
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        max-height: 400px;
        overflow-y: auto;
        z-index: 1050;
        margin-top: 5px;
    }

    .search-result-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }

    .search-result-item:hover {
        background-color: #f8f9fa;
    }

    .search-result-header {
        background: #f8f9fa;
        padding: 8px 15px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: #6c757d;
    }

    .search-result-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }

    .search-no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.2;
        margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-card {
            min-height: 100px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .time-preference-badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <div>
                    <h4 class="page-title mb-1">Dashboard</h4>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-{{ auth()->user()->role === 'super_admin' ? 'primary' : (auth()->user()->role === 'lead_manager' ? 'success' : (auth()->user()->role === 'field_staff' ? 'info' : 'warning')) }} me-2">
                            {{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}
                        </span>
                        @if(auth()->user()->role !== 'super_admin' && auth()->user()->branch)
                            <span class="badge bg-dark">{{ auth()->user()->branch->name }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">CTree</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search and Create Lead -->
    @if(in_array(auth()->user()->role, ['telecallers', 'super_admin', 'lead_manager']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="row align-items-end g-2">
                        <div class="col-lg-7 col-md-6">
                            <label for="telecallerQuickSearch" class="form-label fw-semibold mb-2">
                                <i class="las la-search me-1"></i>Quick Search for Customers and Leads
                            </label>
                            <div class="position-relative">
                                <input type="text"
                                       class="form-control"
                                       id="telecallerQuickSearch"
                                       placeholder="Search by code, name, phone number or email..."
                                       autocomplete="off">
                                <i class="las la-search position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #6c757d; pointer-events: none;"></i>

                                <div id="searchResults" class="search-results-dropdown" style="display: none;">
                                    <div class="search-loading text-center py-3" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <span class="ms-2 text-muted">Searching...</span>
                                    </div>
                                    <div class="search-content"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <a href="{{ route('leads.create') }}" class="btn btn-primary w-100">
                                <i class="las la-plus me-1"></i> Create Lead
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- SUPER ADMIN DASHBOARD -->
    @if(auth()->user()->role === 'super_admin')
        <!-- Quick Stats Row -->
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Customers</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $totalCustomers }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                    <i class="las la-user-friends fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Users</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $totalUsers }}</h4>
                                <small class="text-success">{{ $activeUsers }} Active</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success text-success rounded">
                                    <i class="las la-users fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending Leads</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $pendingLeads }}</h4>
                                <small class="text-muted">Awaiting Approval</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-clipboard-list fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Active Jobs</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $activeJobs }}</h4>
                                <small class="text-muted">In Progress</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-briefcase fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0 overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="text-white fw-semibold fs-20 lh-base mb-3">
                                    <i class="las la-wallet me-2"></i>Today's Budget Status
                                </h3>
                                <div class="row text-white g-3 mb-3">
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Daily Limit</small>
                                        <h5 class="text-white mb-0 fs-16">₹{{ number_format($dailyBudget, 0) }}</h5>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Used Today</small>
                                        <h5 class="text-white mb-0 fs-16">₹{{ number_format($todayTotal, 0) }}</h5>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Remaining</small>
                                        <h5 class="text-white mb-0 fs-16">₹{{ number_format($remaining, 0) }}</h5>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Usage</small>
                                        <h5 class="text-white mb-0 fs-16">{{ number_format($percentage, 1) }}%</h5>
                                    </div>
                                </div>
                                <div class="progress" style="height: 10px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <img src="{{ asset('assets/images/extra/fund.png') }}" alt="" style="max-width: 100%; height: auto; max-height: 180px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('dashboard') }}">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <label class="form-label fw-semibold mb-2">
                                        <i class="las la-filter me-1"></i> Filter Sales & Followups by Branch
                                    </label>
                                    <select class="form-select" name="branch_id" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales & Followup Metrics Row -->
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Sales This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">₹{{ number_format($approvedWeekly, 0) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-hand-holding-usd fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Sales This Month</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">₹{{ number_format($approvedMonthly, 0) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success text-success rounded">
                                    <i class="las la-money-check-alt fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Week's Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">{{ $followupData['thisWeek'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-calendar-week fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Overdue Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">{{ $followupData['overdue'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded">
                                    <i class="las la-exclamation-triangle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- LEAD MANAGER DASHBOARD -->
    @if(auth()->user()->role === 'lead_manager')
        <!-- Budget Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0 overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="text-white fw-semibold fs-20 lh-base mb-3">
                                    <i class="las la-wallet me-2"></i>Today's Budget Status
                                </h3>
                                <div class="row text-white g-3 mb-3">
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Daily Limit</small>
                                        <h5 class="text-white mb-0 fs-16">₹{{ number_format($dailyBudget, 0) }}</h5>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Used Today</small>
                                        <h5 class="text-white mb-0 fs-16">₹{{ number_format($todayTotal, 0) }}</h5>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Remaining</small>
                                        <h5 class="text-white mb-0 fs-16">₹{{ number_format($remaining, 0) }}</h5>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <small class="d-block opacity-75 mb-1">Usage</small>
                                        <h5 class="text-white mb-0 fs-16">{{ number_format($percentage, 1) }}%</h5>
                                    </div>
                                </div>
                                <div class="progress" style="height: 10px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <img src="{{ asset('assets/images/extra/fund.png') }}" alt="" style="max-width: 100%; height: auto; max-height: 180px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('dashboard') }}">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <label class="form-label fw-semibold mb-2">
                                        <i class="las la-filter me-1"></i> Filter Sales & Followups by Branch
                                    </label>
                                    <select class="form-select" name="branch_id" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales & Followup Metrics -->
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Sales This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">₹{{ number_format($approvedWeekly, 0) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-hand-holding-usd fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Sales This Month</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">₹{{ number_format($approvedMonthly, 0) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success text-success rounded">
                                    <i class="las la-money-check-alt fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Week's Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">{{ $followupData['thisWeek'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-calendar-week fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Overdue Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold fs-18">{{ $followupData['overdue'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded">
                                    <i class="las la-exclamation-triangle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TELECALLER DASHBOARD -->
    @if(auth()->user()->role === 'telecallers')
        <!-- Telecaller Lead Stats -->
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Leads</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['total'] ?? 0 }}</h4>
                                <small class="text-muted">Assigned to you</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                    <i class="las la-clipboard-list fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['pending'] ?? 0 }}</h4>
                                <small class="text-warning">Needs attention</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-hourglass-half fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Site Visit</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['site_visit'] ?? 0 }}</h4>
                                <small class="text-info">Scheduled visits</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-map-marker-alt fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Confirmed</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['confirmed'] ?? 0 }}</h4>
                                <small class="text-success">Converted to work orders</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success text-success rounded">
                                    <i class="las la-check-circle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- UNIFIED FOLLOWUP SECTIONS FOR ALL USERS -->

    <!-- Immediate Followups (Today & Overdue) -->
    @if($followupData['immediate']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header urgent">
                <h5><i class="las la-exclamation-circle me-2"></i>Immediate Followups ({{ $followupData['immediate']->count() }})</h5>
                <span class="badge bg-white text-danger">Action Required</span>
            </div>

            <div class="row">
                @foreach($followupData['immediate'] as $followup)
                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="priority-card urgent followup-item">
                        <div class="followup-header">
                            <div>
                                <div class="followup-title">
                                    <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-dark text-decoration-none">
                                        {{ $followup->lead->name }}
                                    </a>
                                    <span class="badge {{ $followup->followup_date->isPast() ? 'bg-danger' : 'bg-warning' }} ms-2">
                                        {{ $followup->followup_date->isPast() ? 'Overdue' : 'Today' }}
                                    </span>
                                </div>
                                <div class="followup-meta">
                                    <i class="las la-tag"></i> {{ $followup->lead->lead_code }} |
                                    <i class="las la-phone"></i> {{ $followup->lead->phone }}
                                </div>
                            </div>
                            <span class="badge bg-danger">
                                <i class="las la-flag"></i> {{ ucfirst($followup->priority) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="text-danger fw-bold">
                                    <i class="las la-calendar"></i> {{ $followup->followup_date->format('d M Y') }}
                                    @if($followup->followup_time)
                                        • {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                    @endif
                                </small>
                            </div>
                            @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to === auth()->id()))
                                <button class="btn btn-sm btn-success markFollowupDone" data-id="{{ $followup->id }}">
                                    <i class="las la-check"></i> Complete
                                </button>
                            @endif
                        </div>

                        @if($followup->callback_time_preference)
                        <div class="mb-2">
                            <span class="time-preference-badge">
                                <i class="las la-phone-volume"></i>
                                Time Preference: {{ ucfirst(str_replace('_', ' ', $followup->callback_time_preference)) }}
                            </span>
                        </div>
                        @endif

                        @if($followup->notes)
                        <div class="mt-2 p-2 bg-light rounded">
                            <small>{{ $followup->notes }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- This Week's Followups -->
    @if($followupData['thisWeekFollowups']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header medium">
                <h5><i class="las la-calendar-week me-2"></i>This Week's Followups ({{ $followupData['thisWeekFollowups']->count() }})</h5>
                <span class="badge bg-white text-info">Upcoming</span>
            </div>

            <div class="row">
                @foreach($followupData['thisWeekFollowups'] as $followup)
                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="priority-card medium followup-item">
                        <div class="followup-header">
                            <div>
                                <div class="followup-title">
                                    <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-dark text-decoration-none">
                                        {{ $followup->lead->name }}
                                    </a>
                                </div>
                                <div class="followup-meta">
                                    <i class="las la-tag"></i> {{ $followup->lead->lead_code }} |
                                    <i class="las la-phone"></i> {{ $followup->lead->phone }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $followup->priority == 'high' ? 'danger' : ($followup->priority == 'medium' ? 'warning' : 'info') }}">
                                <i class="las la-flag"></i> {{ ucfirst($followup->priority) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="fw-bold text-info">
                                    <i class="las la-calendar"></i> {{ $followup->followup_date->format('D, d M Y') }}
                                    @if($followup->followup_time)
                                        • {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                    @endif
                                </small>
                            </div>
                            @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to === auth()->id()))
                                <button class="btn btn-sm btn-success markFollowupDone" data-id="{{ $followup->id }}">
                                    <i class="las la-check"></i> Complete
                                </button>
                            @endif
                        </div>

                        @if($followup->callback_time_preference)
                        <div class="mb-2">
                            <span class="time-preference-badge">
                                <i class="las la-phone-volume"></i>
                                Time Preference: {{ ucfirst(str_replace('_', ' ', $followup->callback_time_preference)) }}
                            </span>
                        </div>
                        @endif

                        @if($followup->notes)
                        <div class="mt-2 p-2 bg-light rounded">
                            <small>{{ $followup->notes }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- This Week's Site Visits (For Telecallers) -->
    @if(auth()->user()->role === 'telecallers' && $followupData['siteVisitsThisWeek']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <h5><i class="las la-map-marked-alt me-2"></i>Site Visits This Week ({{ $followupData['siteVisitsThisWeek']->count() }})</h5>
                <span class="badge bg-white text-info">Scheduled</span>
            </div>

            <div class="row">
                @foreach($followupData['siteVisitsThisWeek'] as $lead)
                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="followup-item" style="border-left: 4px solid #06b6d4;">
                        <div class="followup-header">
                            <div>
                                <div class="followup-title">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="text-dark text-decoration-none">
                                        {{ $lead->name }}
                                    </a>
                                    <span class="badge status-site_visit ms-2">Site Visit</span>
                                </div>
                                <div class="followup-meta">
                                    <i class="las la-tag"></i> {{ $lead->lead_code }} |
                                    <i class="las la-phone"></i> {{ $lead->phone }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            @if($lead->address)
                                <small class="text-muted">
                                    <i class="las la-map-marker"></i> {{ $lead->address }}
                                    @if($lead->district), {{ $lead->district }}@endif
                                </small>
                            @endif
                            @if($lead->services->count() > 0)
                                <br><small class="text-muted">
                                    <i class="las la-tools"></i>
                                    @foreach($lead->services as $service)
                                        <span class="badge bg-info me-1">{{ $service->name }}</span>
                                    @endforeach
                                </small>
                            @endif
                        </div>

                        <div class="mt-2 text-end">
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="las la-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- This Month's Followups -->
    @if($followupData['thisMonthFollowups']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header normal">
                <h5><i class="las la-calendar-alt me-2"></i>This Month's Followups ({{ $followupData['thisMonthFollowups']->count() }})</h5>
                <span class="badge bg-white text-success">Planned</span>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Lead</th>
                                    <th>Priority</th>
                                    <th>Scheduled Date</th>
                                    <th>Time Preference</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($followupData['thisMonthFollowups'] as $followup)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">
                                            <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-dark text-decoration-none">
                                                {{ $followup->lead->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $followup->lead->lead_code }} | {{ $followup->lead->phone }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $followup->priority == 'high' ? 'danger' : ($followup->priority == 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($followup->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $followup->followup_date->format('d M Y') }}
                                        @if($followup->followup_time)
                                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($followup->callback_time_preference)
                                            <span class="badge bg-warning text-dark">
                                                <i class="las la-phone-volume"></i>
                                                {{ ucfirst(str_replace('_', ' ', $followup->callback_time_preference)) }}
                                            </span>
                                        @else
                                            <small class="text-muted">Anytime</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to === auth()->id()))
                                            <button class="btn btn-sm btn-success markFollowupDone" data-id="{{ $followup->id }}">
                                                <i class="las la-check"></i> Complete
                                            </button>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($followup->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Branch Statistics (Super Admin Only) -->
    @if(auth()->user()->role === 'super_admin' && isset($branchStatistics))
    <div class="row">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="las la-chart-bar me-2"></i>Branch Statistics</h4>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Branch Name</th>
                                    <th>Total Users</th>
                                    <th>Active Users</th>
                                    <th>Super Admins</th>
                                    <th>Lead Managers</th>
                                    <th>Field Staff</th>
                                    <th>Telecallers</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchStatistics as $stat)
                                    <tr>
                                        <td><h6 class="mb-0 fw-bold">{{ $stat['branch_name'] }}</h6></td>
                                        <td><span class="badge bg-primary-subtle text-primary">{{ $stat['total_users'] }}</span></td>
                                        <td><span class="badge bg-success-subtle text-success">{{ $stat['active_users'] }}</span></td>
                                        <td>{{ $stat['super_admin_count'] }}</td>
                                        <td>{{ $stat['lead_manager_count'] }}</td>
                                        <td>{{ $stat['field_staff_count'] }}</td>
                                        <td>{{ $stat['telecallers_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <p class="text-muted">No branch data available</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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

    // Mark followup as complete
    $(document).on('click', '.markFollowupDone', function() {
        var followupId = $(this).data('id');
        Swal.fire({
            title: 'Complete Followup?',
            text: 'Mark this followup as completed',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/lead-followups/' + followupId + '/complete',
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
                        } else {
                            Swal.fire('Info', response.message, 'info');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Could not update followup.', 'error');
                    }
                });
            }
        });
    });

    // Quick Search
    let searchTimeout;

    $('#telecallerQuickSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        let query = $(this).val().trim();

        if (query.length < 3) {
            $('#searchResults').hide();
            return;
        }

        $('#searchResults').show();
        $('.search-loading').show();
        $('.search-content').html('');

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('telecaller.quick-search') }}",
                type: 'GET',
                data: { query: query },
                success: function(response) {
                    $('.search-loading').hide();
                    displaySearchResults(response);
                },
                error: function(xhr) {
                    $('.search-loading').hide();
                    $('.search-content').html(
                        '<div class="search-no-results">' +
                        '<i class="las la-exclamation-circle" style="font-size: 2rem; color: #dc3545;"></i>' +
                        '<p class="mb-0 mt-2">Error performing search</p>' +
                        '</div>'
                    );
                }
            });
        }, 500);
    });

    function displaySearchResults(response) {
        let html = '';
        let hasResults = false;

        if (response.leads && response.leads.length > 0) {
            hasResults = true;
            html += '<div class="search-result-header"><i class="las la-clipboard-list me-1"></i> Leads</div>';
            response.leads.forEach(function(lead) {
                html += '<div class="search-result-item" onclick="window.location.href=\'/leads/' + lead.id + '\'">' +
                        '<div class="search-result-title">' +
                        '<span class="badge bg-primary me-2">' + lead.lead_code + '</span>' +
                        lead.name +
                        '<span class="badge ms-2">' + lead.status + '</span>' +
                        '</div>' +
                        '<div style="font-size: 13px; color: #6c757d;">' +
                        '<i class="las la-phone"></i> ' + lead.phone +
                        (lead.email ? ' | <i class="las la-envelope"></i> ' + lead.email : '') +
                        ' | <i class="las la-briefcase"></i> ' + (lead.service || 'N/A') +
                        '</div>' +
                        '</div>';
            });
        }

        if (response.customers && response.customers.length > 0) {
            hasResults = true;
            html += '<div class="search-result-header"><i class="las la-user-check me-1"></i> Existing Customers</div>';
            response.customers.forEach(function(customer) {
                html += '<div class="search-result-item" onclick="window.location.href=\'/customers/' + customer.id + '\'">' +
                        '<div class="search-result-title">' +
                        '<span class="badge bg-success me-2">' + customer.customer_code + '</span>' +
                        customer.name +
                        '<span class="badge bg-info ms-2">' + customer.priority + '</span>' +
                        '</div>' +
                        '<div style="font-size: 13px; color: #6c757d;">' +
                        '<i class="las la-phone"></i> ' + customer.phone +
                        (customer.email ? ' | <i class="las la-envelope"></i> ' + customer.email : '') +
                        ' | <i class="las la-briefcase"></i> ' + customer.total_jobs + ' jobs' +
                        '</div>' +
                        '</div>';
            });
        }

        if (!hasResults) {
            html = '<div class="search-no-results">' +
                   '<i class="las la-search" style="font-size: 2rem; opacity: 0.3;"></i>' +
                   '<p class="mb-0 mt-2">No leads or customers found</p>' +
                   '</div>';
        }

        $('.search-content').html(html);
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#telecallerQuickSearch, #searchResults').length) {
            $('#searchResults').hide();
        }
    });
});
</script>
@endsection
