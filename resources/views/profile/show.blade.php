@extends('layouts.app')

@section('title', 'My Profile')

@section('extra-css')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .profile-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid rgba(255, 255, 255, 0.3);
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        letter-spacing: 2px;
    }

    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .info-row {
        padding: 1rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">My Profile</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Profile Header -->
<div class="profile-header">
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="profile-avatar-large">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
        </div>
        <div class="col">
            <h3 class="mb-1 text-white fw-bold">{{ $user->name }}</h3>
            <p class="mb-2 text-white-50">
                <i class="las la-user-tag me-1"></i>
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </p>
            @if($user->branch)
                <p class="mb-0 text-white-50">
                    <i class="las la-building me-1"></i>
                    {{ $user->branch->name }}
                </p>
            @endif
        </div>
        <div class="col-auto">
            <a href="{{ route('profile.edit') }}" class="btn btn-light">
                <i class="las la-edit me-1"></i> Edit Profile
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
@if(in_array($user->role, ['lead_manager', 'telecallers', 'field_staff']))
<div class="row mb-4">
    @if(in_array($user->role, ['lead_manager', 'telecallers']))
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="las la-clipboard-list text-primary" style="font-size: 3rem;"></i>
                <h3 class="mb-0 mt-2 text-primary">{{ $user->assignedLeads->count() }}</h3>
                <small class="text-muted">Assigned Leads</small>
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="las la-tasks text-success" style="font-size: 3rem;"></i>
                <h3 class="mb-0 mt-2 text-success">{{ $user->assignedJobs->count() }}</h3>
                <small class="text-muted">Assigned Jobs</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="las la-check-circle text-info" style="font-size: 3rem;"></i>
                <h3 class="mb-0 mt-2 text-info">{{ $user->assignedJobs->whereIn('status', ['completed', 'approved'])->count() }}</h3>
                <small class="text-muted">Completed Jobs</small>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Profile Details -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-user-circle me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Full Name</strong>
                    <span>{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Email Address</strong>
                    <span>{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Phone Number</strong>
                    <span>{{ $user->phone ?? 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Role</strong>
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                </div>
                @if($user->branch)
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Branch</strong>
                    <span class="badge bg-info">{{ $user->branch->name }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-info-circle me-2"></i>Account Details
                </h5>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Member Since</strong>
                    <span>{{ $user->created_at->format('d M Y') }}</span>
                    <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Account Status</strong>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1">Last Updated</strong>
                    <span>{{ $user->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-lock me-2"></i>Security
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Keep your account secure by using a strong password.</p>
                <a href="{{ route('profile.edit') }}#change-password" class="btn btn-outline-primary w-100">
                    <i class="las la-key me-1"></i> Change Password
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        let successData = @json(json_decode(session('success'), true));
        Swal.fire({
            icon: 'success',
            title: successData.title,
            text: successData.message,
            timer: 3000,
            showConfirmButton: false
        });
    @endif
</script>
@endsection
