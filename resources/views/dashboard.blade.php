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

    /* Custom Toast Styling */
    .toast {
        min-width: 300px;
    }

    .toast-success {
        border-left: 4px solid #28a745;
    }

    .toast-error {
        border-left: 4px solid #dc3545;
    }

    .toast-info {
        border-left: 4px solid #17a2b8;
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

<!-- Dashboard Stats Cards -->
<div class="row">
    <!-- Total Users - Super Admin Only -->
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

    <!-- Active Users - Super Admin Only -->
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

    <!-- Your Role - All Users -->
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

    <!-- Selected Branch - All Users -->
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal">
                            @if(auth()->user()->role === 'super_admin')
                                Selected Branch
                            @else
                                Your Branch
                            @endif
                        </h6>
                        <h5 class="fw-bold" id="branchName">
                            @if(auth()->user()->role === 'super_admin')
                                @if(request('branch_id'))
                                    {{ $selectedBranchName ?? 'All Branches' }}
                                @else
                                    All Branches
                                @endif
                            @else
                                {{ auth()->user()->branch->name ?? 'N/A' }}
                            @endif
                        </h5>
                    </div>
                    <div class="avatar-md bg-warning-subtle rounded">
                        <i class="fas fa-building fs-24 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- For Non-Super Admin Users - Show only 2 cards in full width -->
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
                                <th>Reporting Users</th>
                            </tr>
                        </thead>
                        <tbody id="branchStatsTable">
                            @forelse($branchStatistics as $stat)
                                <tr class="branch-row" data-branch-id="{{ $stat['branch_id'] }}">
                                    <td>
                                        <h6 class="m-0">{{ $stat['branch_name'] }}</h6>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $stat['total_users'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $stat['active_users'] }}</span>
                                    </td>
                                    <td>{{ $stat['super_admin_count'] }}</td>
                                    <td>{{ $stat['lead_manager_count'] }}</td>
                                    <td>{{ $stat['field_staff_count'] }}</td>
                                    <td>{{ $stat['reporting_user_count'] }}</td>
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

<!-- Toast Container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Check for success message and show toast
        @if(session('success'))
            showToast('{{ session('success') }}');
        @endif

        // Function to show toast
        function showToast(message) {
            $('#toastMessage').text(message);
            var toastElement = document.getElementById('successToast');
            var toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toast.show();
        }

        // Branch Filter Change
        $('#branchFilter').change(function() {
            let branchId = $(this).val();
            let url = '{{ route("dashboard") }}';

            if (branchId) {
                url += '?branch_id=' + branchId;
            }

            window.location.href = url;
        });

        // Reset Filter
        $('#resetFilter').click(function() {
            window.location.href = '{{ route("dashboard") }}';
        });
    });
</script>
@endsection
