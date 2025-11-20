@extends('layouts.app')

@section('title', 'Dashboard')

@section('extra-css')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .followup-card {
        border-left: 4px solid #007bff;
    }

    .followup-card.overdue {
        border-left-color: #dc3545;
    }

    .followup-card.today {
        border-left-color: #ffc107;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Dashboard</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Branch Filter for Super Admin -->
@if(auth()->user()->role === 'super_admin')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label for="branchFilter" class="form-label fw-semibold">Filter by Branch</label>
                        <select class="form-select" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @if(request('branch_id') == $branch->id) selected @endif>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-secondary w-100" id="resetFilter">
                            <i class="fas fa-redo me-2"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Budget Overview - For Super Admin and Lead Managers -->
@if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="las la-money-bill-wave"></i> Today's Budget Status</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <p class="text-muted mb-1">Daily Limit</p>
                            <h4 class="text-primary">₹{{ number_format($dailyBudget, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <p class="text-muted mb-1">Used Today</p>
                            <h4 class="text-danger">₹{{ number_format($todayTotal, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <p class="text-muted mb-1">Remaining</p>
                            <h4 class="text-success">₹{{ number_format($remaining, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <p class="text-muted mb-1">Usage</p>
                            <h4 class="{{ $percentage > 90 ? 'text-danger' : ($percentage > 70 ? 'text-warning' : 'text-success') }}">
                                {{ number_format($percentage, 1) }}%
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 25px;">
                    <div class="progress-bar {{ $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-success') }}"
                         role="progressbar"
                         style="width: {{ min($percentage, 100) }}%"
                         aria-valuenow="{{ $percentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ number_format($percentage, 1) }}%
                    </div>
                </div>
                @if($percentage > 90)
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="las la-exclamation-triangle"></i>
                        <strong>Warning!</strong> Daily budget is almost exhausted!
                    </div>
                @elseif($percentage > 70)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="las la-info-circle"></i>
                        Budget usage is high. Monitor approvals carefully.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Followup Overview Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Overdue Followups</h6>
                        <h2 class="fw-bold">{{ $followupData['overdue'] }}</h2>
                    </div>
                    <div class="avatar-md bg-white-subtle rounded">
                        <i class="las la-exclamation-circle fs-24 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-dark-50">Today's Followups</h6>
                        <h2 class="fw-bold">{{ $followupData['today'] }}</h2>
                    </div>
                    <div class="avatar-md bg-white-subtle rounded">
                        <i class="las la-calendar-day fs-24 text-dark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">This Week</h6>
                        <h2 class="fw-bold">{{ $followupData['thisWeek'] }}</h2>
                    </div>
                    <div class="avatar-md bg-white-subtle rounded">
                        <i class="las la-calendar-week fs-24 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">This Month</h6>
                        <h2 class="fw-bold">{{ $followupData['thisMonth'] }}</h2>
                    </div>
                    <div class="avatar-md bg-white-subtle rounded">
                        <i class="las la-calendar-alt fs-24 text-white"></i>
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
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0 text-white">
                    <i class="las la-exclamation-triangle"></i> Overdue Followups
                </h5>
            </div>
            <div class="card-body">
                @foreach($followupData['overdueFollowups'] as $followup)
                <div class="followup-card overdue card mb-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1">
                                    <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-decoration-none">
                                        {{ $followup->lead->name }}
                                    </a>
                                    <span class="badge bg-danger ms-2">{{ $followup->priority }}</span>
                                </h6>
                                <small class="text-muted">{{ $followup->lead->lead_code }} | {{ $followup->lead->phone }}</small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Scheduled:</small>
                                <strong class="text-danger">
                                    {{ $followup->followup_date->format('d M Y') }}
                                    @if($followup->followup_time)
                                        {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Assigned To:</small>
                                <strong>{{ $followup->assignedToUser->name }}</strong>
                            </div>
                        </div>
                        @if($followup->notes)
                        <div class="mt-2">
                            <small class="text-muted">{{ $followup->notes }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- This Week's Followups -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="las la-calendar-week"></i> This Week's Followups</h5>
            </div>
            <div class="card-body">
                @if($followupData['weeklyFollowups']->count() > 0)
                    @foreach($followupData['weeklyFollowups'] as $followup)
                    <div class="followup-card {{ $followup->followup_date->isToday() ? 'today' : '' }} card mb-2">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">
                                        <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-decoration-none">
                                            {{ $followup->lead->name }}
                                        </a>
                                        <span class="badge bg-{{ $followup->priority === 'high' ? 'danger' : ($followup->priority === 'medium' ? 'warning' : 'info') }} ms-2">
                                            {{ $followup->priority }}
                                        </span>
                                    </h6>
                                    <small class="text-muted">{{ $followup->lead->lead_code }} | {{ $followup->lead->phone }}</small>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Scheduled:</small>
                                    <strong>
                                        {{ $followup->followup_date->format('d M Y') }}
                                        @if($followup->followup_time)
                                            {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                        @endif
                                    </strong>
                                    @if($followup->followup_date->isToday())
                                        <span class="badge bg-warning ms-2">Today</span>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Assigned To:</small>
                                    <strong>{{ $followup->assignedToUser->name }}</strong>
                                </div>
                            </div>
                            @if($followup->notes)
                            <div class="mt-2">
                                <small class="text-muted">{{ $followup->notes }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">No followups scheduled for this week</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="row">
    @if(auth()->user()->role === 'super_admin')
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">Total Users</h6>
                        <h2 class="fw-bold" id="totalUsersCount">{{ $totalUsers ?? 0 }}</h2>
                    </div>
                    <div class="avatar-md bg-primary-subtle rounded">
                        <i class="fas fa-users fs-24 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">Active Users</h6>
                        <h2 class="fw-bold" id="activeUsersCount">{{ $activeUsers ?? 0 }}</h2>
                    </div>
                    <div class="avatar-md bg-success-subtle rounded">
                        <i class="fas fa-check-circle fs-24 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">Your Role</h6>
                        <h5 class="fw-bold">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</h5>
                    </div>
                    <div class="avatar-md bg-info-subtle rounded">
                        <i class="fas fa-id-badge fs-24 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">Selected Branch</h6>
                        <h5 class="fw-bold" id="branchName">{{ $selectedBranchName }}</h5>
                    </div>
                    <div class="avatar-md bg-warning-subtle rounded">
                        <i class="fas fa-building fs-24 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">Your Role</h6>
                        <h5 class="fw-bold">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</h5>
                    </div>
                    <div class="avatar-md bg-info-subtle rounded">
                        <i class="fas fa-id-badge fs-24 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">Your Branch</h6>
                        <h5 class="fw-bold">{{ auth()->user()->branch->name ?? 'N/A' }}</h5>
                    </div>
                    <div class="avatar-md bg-warning-subtle rounded">
                        <i class="fas fa-building fs-24 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Branch Statistics Table for Super Admin -->
@if(auth()->user()->role === 'super_admin')
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Branch Statistics</h4>
            </div>
            <div class="card-body">
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
                        <tbody id="branchStatsTable">
                            @forelse($branchStatistics as $stat)
                                <tr class="branch-row" data-branch-id="{{ $stat['branch_id'] }}">
                                    <td><h6 class="m-0">{{ $stat['branch_name'] }}</h6></td>
                                    <td><span class="badge bg-primary">{{ $stat['total_users'] }}</span></td>
                                    <td><span class="badge bg-success">{{ $stat['active_users'] }}</span></td>
                                    <td>{{ $stat['super_admin_count'] }}</td>
                                    <td>{{ $stat['lead_manager_count'] }}</td>
                                    <td>{{ $stat['field_staff_count'] }}</td>
                                    <td>{{ $stat['telecallers_count'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
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
@endsection

@section('extra-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        @if(session('success'))
            alert('{{ session('success') }}');
        @endif

        $('#branchFilter').change(function() {
            let branchId = $(this).val();
            let url = '{{ route("dashboard") }}';
            if (branchId) {
                url += '?branch_id=' + branchId;
            }
            window.location.href = url;
        });

        $('#resetFilter').click(function() {
            window.location.href = '{{ route("dashboard") }}';
        });
    });
</script>
@endsection
