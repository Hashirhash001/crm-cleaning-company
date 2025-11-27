@extends('layouts.app')

@section('title', 'Dashboard')

@section('extra-css')
<style>
    .stat-card {
        height: 100%;
        min-height: 120px;
    }
    .stat-card .card-body {
        display: flex;
        align-items: center;
        height: 100%;
        padding: 1.25rem;
    }
    .card {
        margin-bottom: 1.5rem;
    }

    .stat-card {
        height: 100%;
        min-height: 120px;
    }
    .stat-card .card-body {
        display: flex;
        align-items: center;
        height: 100%;
        padding: 1.25rem;
    }
    .card {
        margin-bottom: 1.5rem;
    }
    .h-100 {
        height: 100% !important;
    }
    /* Ensure metric cards row matches budget card height */
    .row.h-100 {
        height: 100%;
    }

    /* Quick Search Styles */
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
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

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-header {
        background: #f8f9fa;
        padding: 8px 15px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
    }

    .search-result-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }

    .search-result-details {
        font-size: 13px;
        color: #6c757d;
    }

    .search-no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Title with User Info -->
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

    <!-- Branch Filter -->
    @if(auth()->user()->role === 'super_admin')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                        <div class="row align-items-center">
                            <div class="col-md-10">
                                <label class="form-label fw-semibold mb-2">Filter by Branch</label>
                                <select class="form-select" name="branch_id" id="branchFilter" onchange="this.form.submit()">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @if(request('branch_id') == $branch->id) selected @endif>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" style="margin-top: 28px;">
                                    <i class="las la-filter me-1"></i> Apply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="card stat-card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Branches</p>
                            <h4 class="mt-0 mb-0 fw-semibold">{{ $branches->count() }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary text-primary rounded">
                                <i class="las la-building fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
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

        <div class="col-md-3 mb-3">
            <div class="card stat-card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending Leads</p>
                            <h4 class="mt-0 mb-0 fw-semibold">{{ \App\Models\Lead::where('status', 'pending')->count() }}</h4>
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

        <div class="col-md-3 mb-3">
            <div class="card stat-card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Active Jobs</p>
                            <h4 class="mt-0 mb-0 fw-semibold">{{ \App\Models\Job::whereIn('status', ['pending', 'assigned', 'in_progress'])->count() }}</h4>
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
    @endif

    <!-- User Role & Branch Info Card (For non-super-admin, non-lead-manager users) -->
    @if(!in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="row align-items-center text-white">
                        <div class="col-md-6">
                            <h5 class="text-white mb-2">
                                <i class="las la-user-circle me-2"></i>Welcome, {{ auth()->user()->name }}
                            </h5>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-white text-{{ auth()->user()->role === 'field_staff' ? 'info' : 'warning' }} px-3 py-2">
                                    <i class="las la-id-badge me-1"></i>
                                    {{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}
                                </span>
                                @if(auth()->user()->branch)
                                    <span class="badge bg-white text-dark px-3 py-2">
                                        <i class="las la-building me-1"></i>
                                        {{ auth()->user()->branch->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <p class="mb-1 opacity-90">
                                <i class="las la-envelope me-2"></i>{{ auth()->user()->email }}
                            </p>
                            @if(auth()->user()->phone)
                            <p class="mb-0 opacity-90">
                                <i class="las la-phone me-2"></i>{{ auth()->user()->phone }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Search and Create Lead for Telecallers -->
    @if(auth()->user()->role === 'telecallers')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex gap-2 align-items-center">
                        <!-- Search Bar -->
                        <div class="flex-grow-1 position-relative">
                            <input type="text"
                                class="form-control"
                                id="telecallerQuickSearch"
                                placeholder="Search by phone number or email..."
                                autocomplete="off">
                            <i class="las la-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #6c757d;"></i>

                            <!-- Search Results Dropdown -->
                            <div id="searchResults" class="search-results-dropdown" style="display: none;">
                                <div class="search-loading text-center py-3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2 text-muted">Searching...</span>
                                </div>
                                <div class="search-content"></div>
                            </div>
                        </div>

                        <!-- Create Lead Button -->
                        <a href="{{ route('leads.create') }}" class="btn btn-primary">
                            <i class="las la-plus me-1"></i> Create Lead
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Budget and Metrics Row -->
    <div class="row mb-3">
        <!-- Budget Welcome Card -->
        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
        <div class="col-lg-8 col-xl-7 mb-3">
            <div class="card mb-0 h-100 overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="row align-items-center h-100">
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
                        <div class="col-md-4 text-end d-none d-md-block position-relative">
                            <img src="{{ asset('assets/images/extra/fund.png') }}" alt="" style="max-width: 100%; height: auto; max-height: 180px; object-fit: contain;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Metrics Cards -->
        <div class="{{ in_array(auth()->user()->role, ['super_admin', 'lead_manager']) ? 'col-lg-4 col-xl-5' : 'col-lg-12' }}">
            <div class="row h-100">
                <div class="col-6 mb-3">
                    <div class="card bg-corner-img mb-0 h-100">
                        <div class="card-body d-flex align-items-center">
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

                <div class="col-6 mb-3">
                    <div class="card bg-corner-img mb-0 h-100">
                        <div class="card-body d-flex align-items-center">
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

                <div class="col-6 mb-3">
                    <div class="card bg-corner-img mb-0 h-100">
                        <div class="card-body d-flex align-items-center">
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

                <div class="col-6 mb-3">
                    <div class="card bg-corner-img mb-0 h-100">
                        <div class="card-body d-flex align-items-center">
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
        </div>
    </div>

    <!-- Overdue Followups -->
    @if($followupData['overdueFollowups']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-header bg-danger-subtle">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title text-danger mb-0">
                                <i class="las la-exclamation-circle me-2"></i>Overdue Followups
                            </h4>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-danger">{{ $followupData['overdueFollowups']->count() }} items</span>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Lead Details</th>
                                    <th>Priority</th>
                                    <th>Scheduled</th>
                                    <th>Assigned To</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($followupData['overdueFollowups'] as $followup)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">
                                            <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-dark text-decoration-none">
                                                {{ $followup->lead->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $followup->lead->lead_code }} | {{ $followup->lead->phone }}</small>
                                        @if($followup->notes)
                                        <p class="mb-0 text-muted small mt-1">{{ Str::limit($followup->notes, 50) }}</p>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-danger">{{ ucfirst($followup->priority) }}</span></td>
                                    <td>
                                        <span class="text-danger fw-medium">{{ $followup->followup_date->format('d M Y') }}</span>
                                        @if($followup->followup_time)
                                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $followup->assignedToUser->name }}</td>
                                    <td class="text-center">
                                        @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to == auth()->id()))
                                            <button type="button" class="btn btn-sm btn-success markFollowupDone" data-id="{{ $followup->id }}">
                                                <i class="las la-check me-1"></i> Complete
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

    <!-- This Week's Followups -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">
                                <i class="las la-calendar-week me-2"></i>This Week's Followups
                            </h4>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-primary">{{ $followupData['weeklyFollowups']->count() }} items</span>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-2">
                    @if($followupData['weeklyFollowups']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Lead Details</th>
                                        <th>Priority</th>
                                        <th>Scheduled</th>
                                        <th>Assigned To</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($followupData['weeklyFollowups'] as $followup)
                                    <tr class="{{ $followup->followup_date->isToday() ? 'table-warning' : '' }}">
                                        <td>
                                            <h6 class="mb-0">
                                                <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-dark text-decoration-none">
                                                    {{ $followup->lead->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">{{ $followup->lead->lead_code }} | {{ $followup->lead->phone }}</small>
                                            @if($followup->notes)
                                            <p class="mb-0 text-muted small mt-1">{{ Str::limit($followup->notes, 50) }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $followup->priority == 'high' ? 'danger' : ($followup->priority == 'medium' ? 'warning' : 'info') }}">
                                                {{ ucfirst($followup->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $followup->followup_date->format('d M Y') }}</span>
                                            @if($followup->followup_time)
                                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}</small>
                                            @endif
                                            @if($followup->followup_date->isToday())
                                                <br><span class="badge bg-warning">Today</span>
                                            @endif
                                        </td>
                                        <td>{{ $followup->assignedToUser->name }}</td>
                                        <td class="text-center">
                                            @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to == auth()->id()))
                                                <button type="button" class="btn btn-sm btn-success markFollowupDone" data-id="{{ $followup->id }}">
                                                    <i class="las la-check me-1"></i> Complete
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
                    @else
                        <div class="text-center py-5">
                            <i class="las la-calendar-check" style="font-size: 4rem; opacity: 0.2;"></i>
                            <p class="text-muted mt-3">No followups scheduled for the selected period</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Statistics -->
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
            confirmButtonColor: '#22c55e',
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


    // Quick Search for Telecallers
    let searchTimeout;

    $('#telecallerQuickSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        let query = $(this).val().trim();

        if (query.length < 3) {
            $('#searchResults').hide();
            return;
        }

        // Show loading
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

        // Display Leads
        if (response.leads && response.leads.length > 0) {
            hasResults = true;
            html += '<div class="search-result-header"><i class="las la-clipboard-list me-1"></i> Leads</div>';
            response.leads.forEach(function(lead) {
                html += '<div class="search-result-item" onclick="window.location.href=\'/leads/' + lead.id + '\'">' +
                        '<div class="search-result-title">' +
                        '<span class="badge bg-primary me-2">' + lead.lead_code + '</span>' +
                        lead.name +
                        '<span class="badge badge-' + lead.status + ' ms-2">' + lead.status + '</span>' +
                        '</div>' +
                        '<div class="search-result-details">' +
                        '<i class="las la-phone"></i> ' + lead.phone +
                        (lead.email ? ' | <i class="las la-envelope"></i> ' + lead.email : '') +
                        ' | <i class="las la-briefcase"></i> ' + (lead.service || 'N/A') +
                        '</div>' +
                        '</div>';
            });
        }

        // Display Customers
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
                        '<div class="search-result-details">' +
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

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#telecallerQuickSearch, #searchResults').length) {
            $('#searchResults').hide();
        }
    });
});
</script>
@endsection
