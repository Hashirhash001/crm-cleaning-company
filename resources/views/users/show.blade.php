@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('extra-css')
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        position: relative;
        min-height: 120px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .stat-card.primary { border-left-color: #2563eb; }
    .stat-card.success { border-left-color: #10b981; }
    .stat-card.warning { border-left-color: #f59e0b; }
    .stat-card.danger { border-left-color: #ef4444; }
    .stat-card.info { border-left-color: #06b6d4; }
    .stat-card.secondary { border-left-color: #64748b; }

    /* Clickable indicator */
    .stat-card.clickable::before {
        content: '👁';
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 1.2rem;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .stat-card.clickable:hover::before {
        opacity: 0.6;
    }

    .stat-card.clickable::after {
        content: 'Click to view';
        position: absolute;
        bottom: 8px;
        right: 10px;
        font-size: 0.7rem;
        color: #64748b;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .stat-card.clickable:hover::after {
        opacity: 1;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
        margin-top: 2rem;
    }

    .section-title:first-of-type {
        margin-top: 0;
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .metric-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        font-weight: 700;
        margin: 0 auto 1rem;
    }

    .stat-row {
        margin-bottom: 1rem;
    }

    .stat-card .card-body {
        padding: 1rem;
    }

    /* Off-canvas custom styles */
    .offcanvas {
        width: 90vw !important;
        max-width: 1200px !important;
    }

    .offcanvas-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
    }

    .offcanvas-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .offcanvas-body {
        padding: 1.5rem;
    }

    /* Table in offcanvas */
    .offcanvas-body .table {
        font-size: 0.875rem;
    }

    .offcanvas-body .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    /* Loading state */
    .offcanvas-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 300px;
    }

    .spinner-border-custom {
        width: 3rem;
        height: 3rem;
        color: #667eea;
    }

    @media (max-width: 768px) {
        .metric-value {
            font-size: 1.5rem;
        }

        .stat-card {
            min-height: 100px;
        }

        .offcanvas {
            width: 100vw !important;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">User Profile</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- User Info Card -->
    <div class="col-lg-3 col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="user-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                </p>

                @if($user->is_active)
                    <span class="badge bg-success mb-3">
                        <i class="las la-check-circle"></i> Active
                    </span>
                @else
                    <span class="badge bg-secondary mb-3">
                        <i class="las la-times-circle"></i> Inactive
                    </span>
                @endif

                <div class="mt-3 text-start">
                    <p class="mb-2">
                        <i class="las la-envelope text-primary"></i>
                        <small class="ms-2">{{ $user->email }}</small>
                    </p>
                    <p class="mb-2">
                        <i class="las la-phone text-success"></i>
                        <small class="ms-2">{{ $user->phone ?? 'N/A' }}</small>
                    </p>
                    <p class="mb-2">
                        <i class="las la-building text-info"></i>
                        <small class="ms-2">{{ $user->branch->name ?? 'N/A' }}</small>
                    </p>
                    <p class="mb-0">
                        <i class="las la-calendar text-warning"></i>
                        <small class="ms-2">Joined {{ $user->created_at->format('d M Y') }}</small>
                    </p>
                </div>

                <hr>

                <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="las la-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Stats & Details -->
    <div class="col-lg-9 col-md-8">

        {{-- FIELD STAFF & TELECALLERS - ASSIGNED JOBS STATISTICS --}}
        @if(in_array($user->role, ['field_staff', 'telecallers']))
        <h5 class="section-title">
            <i class="las la-briefcase me-2"></i>Assigned Work Orders Overview
            <small class="text-muted ms-2" style="font-size: 0.75rem; font-weight: 400;">(Click cards to view details)</small>
        </h5>

        <div class="row stat-row">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card primary clickable" onclick="showDetails('{{ $user->id }}', 'all', 'All Assigned Work Orders', {{ $totalAssignedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $totalAssignedJobs }}</div>
                        <div class="metric-label">Total Work Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card info clickable" onclick="showDetails('{{ $user->id }}', 'confirmed', 'Confirmed Jobs', {{ $confirmedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $confirmedJobs }}</div>
                        <div class="metric-label">Confirmed</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card warning clickable" onclick="showDetails('{{ $user->id }}', 'approved', 'Approved Jobs', {{ $approvedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-warning">{{ $approvedJobs }}</div>
                        <div class="metric-label">Approved</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card success clickable" onclick="showDetails('{{ $user->id }}', 'completed', 'Completed Jobs', {{ $completedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $completedJobs }}</div>
                        <div class="metric-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row stat-row">
            <!-- Combined Work Value (Approved + Completed) -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <div class="metric-value text-primary">₹{{ number_format($totalJobsValue, 0) }}</div>
                        <div class="metric-label">Work Value (Approved + Completed)</div>
                    </div>
                </div>
            </div>

            <!-- Total Amount Collected -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="metric-value text-success">₹{{ number_format($totalAmountCollected, 0) }}</div>
                        <div class="metric-label">Total Paid</div>
                    </div>
                </div>
            </div>

            <!-- Total Due Amount -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card danger clickable" onclick="showDetails('{{ $user->id }}', 'due_combined', 'Amount Due (Approved + Completed)', {{ $approvedAndCompletedJobs->filter(function($job) { return ($job->amount - $job->amount_paid) > 0; })->count() }})">
                    <div class="card-body">
                        <div class="metric-value text-danger">₹{{ number_format($totalDueAmount, 0) }}</div>
                        <div class="metric-label">Total Due</div>
                    </div>
                </div>
            </div>

            <!-- Payment Progress Percentage -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card info">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $totalJobsValue > 0 ? round(($totalAmountCollected / $totalJobsValue) * 100, 1) : 0 }}%</div>
                        <div class="metric-label">Payment Progress</div>
                    </div>
                </div>
            </div>
        </div>


        {{-- FIELD STAFF & TELECALLERS - ASSIGNED LEADS STATISTICS --}}
        @if($totalAssignedLeads > 0)
        <h5 class="section-title">
            <i class="las la-user-plus me-2"></i>Assigned Leads Overview
            <small class="text-muted ms-2" style="font-size: 0.75rem; font-weight: 400;">(Click cards to view details)</small>
        </h5>

        <div class="row stat-row">
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card stat-card primary clickable" onclick="showDetails('{{ $user->id }}', 'assigned_all_leads', 'All Assigned Leads', {{ $totalAssignedLeads }})">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $totalAssignedLeads }}</div>
                        <div class="metric-label">Total Leads</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card stat-card info clickable" onclick="showDetails('{{ $user->id }}', 'assigned_confirmed_leads', 'Confirmed Leads', {{ $assignedConfirmedLeads }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $assignedConfirmedLeads }}</div>
                        <div class="metric-label">Confirmed</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card stat-card success clickable" onclick="showDetails('{{ $user->id }}', 'assigned_converted_leads', 'Converted Leads', {{ $assignedLeadsConvertedToJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $assignedLeadsConvertedToJobs }}</div>
                        <div class="metric-label">Converted ({{ $assignedConversionRate }}%)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row stat-row">
            <div class="col-md-4 mb-3">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <div class="metric-value text-primary">₹{{ number_format($assignedLeadsValue, 0) }}</div>
                        <div class="metric-label">Total Value</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card info">
                    <div class="card-body">
                        <div class="metric-value text-info">₹{{ number_format($assignedConfirmedLeadsValue, 0) }}</div>
                        <div class="metric-label">Confirmed Value</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="metric-value text-success">₹{{ number_format($assignedApprovedLeadsValue, 0) }}</div>
                        <div class="metric-label">Converted Value</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @endif

        {{-- LEAD MANAGER & SUPER ADMIN - CREATED LEADS STATISTICS --}}
        @if(in_array($user->role, ['lead_manager', 'super_admin']))
        <h5 class="section-title">
            <i class="las la-user-plus me-2"></i>Created Leads Overview
            <small class="text-muted ms-2" style="font-size: 0.75rem; font-weight: 400;">(Click cards to view details)</small>
        </h5>

        <div class="row stat-row">
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card stat-card primary clickable" onclick="showDetails('{{ $user->id }}', 'all_leads', 'All Created Leads', {{ $totalCreatedLeads }})">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $totalCreatedLeads }}</div>
                        <div class="metric-label">Total Leads</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card stat-card info clickable" onclick="showDetails('{{ $user->id }}', 'confirmed_leads', 'Confirmed Leads', {{ $confirmedLeads }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $confirmedLeads }}</div>
                        <div class="metric-label">Confirmed</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card stat-card success clickable" onclick="showDetails('{{ $user->id }}', 'converted_leads', 'Converted Leads', {{ $leadsConvertedToJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $leadsConvertedToJobs }}</div>
                        <div class="metric-label">Converted ({{ $conversionRate }}%)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row stat-row">
            <div class="col-md-4 mb-3">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <div class="metric-value text-primary">₹{{ number_format($totalLeadsValue, 0) }}</div>
                        <div class="metric-label">Total Value</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card info">
                    <div class="card-body">
                        <div class="metric-value text-info">₹{{ number_format($confirmedLeadsValue, 0) }}</div>
                        <div class="metric-label">Confirmed Value</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="metric-value text-success">₹{{ number_format($approvedLeadsValue, 0) }}</div>
                        <div class="metric-label">Converted Value</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Created Jobs Statistics --}}
        @if($totalCreatedJobs > 0)
        <h5 class="section-title">
            <i class="las la-tasks me-2"></i>Created Jobs Overview
            <small class="text-muted ms-2" style="font-size: 0.75rem; font-weight: 400;">(Click cards to view details)</small>
        </h5>

        <div class="row stat-row">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card primary clickable" onclick="showDetails('{{ $user->id }}', 'all_created_jobs', 'All Created Jobs', {{ $totalCreatedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $totalCreatedJobs }}</div>
                        <div class="metric-label">Total Jobs</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card info clickable" onclick="showDetails('{{ $user->id }}', 'created_confirmed', 'Created & Confirmed Jobs', {{ $createdConfirmedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $createdConfirmedJobs }}</div>
                        <div class="metric-label">Confirmed</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card warning clickable" onclick="showDetails('{{ $user->id }}', 'created_approved', 'Created & Approved Jobs', {{ $createdApprovedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-warning">{{ $createdApprovedJobs }}</div>
                        <div class="metric-label">Approved</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card success clickable" onclick="showDetails('{{ $user->id }}', 'created_completed', 'Created & Completed Jobs', {{ $createdCompletedJobs }})">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $createdCompletedJobs }}</div>
                        <div class="metric-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row stat-row">
            <div class="col-md-4 mb-3">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <div class="metric-value text-primary">₹{{ number_format($createdJobsValue, 0) }}</div>
                        <div class="metric-label">Total Value</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card warning">
                    <div class="card-body">
                        <div class="metric-value text-warning">₹{{ number_format($createdApprovedJobsValue ?? 0, 0) }}</div>
                        <div class="metric-label">Approved Value</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="metric-value text-success">₹{{ number_format($createdCompletedValue, 0) }}</div>
                        <div class="metric-label">Completed Value</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        {{-- SUPER ADMIN / REPORTING USER PLACEHOLDER --}}
        @if(in_array($user->role, ['super_admin', 'reporting_user']) && $totalCreatedLeads == 0 && $totalCreatedJobs == 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="las la-user-shield" style="font-size: 5rem; color: #cbd5e0;"></i>
                <h5 class="mt-3">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</h5>
                <p class="text-muted">This user has administrative access to the system.</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- OFF-CANVAS PANEL --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailsOffcanvas" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasTitle">
            <i class="las la-list-alt me-2"></i>
            <span id="offcanvasTitleText">Details</span>
            <span class="badge bg-white text-primary ms-2" id="offcanvasCount">0</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="offcanvasContent">
        <div class="offcanvas-loading">
            <div class="spinner-border spinner-border-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentOffcanvas = null;

function showDetails(userId, type, title, count) {
    // Set title and count
    $('#offcanvasTitleText').text(title);
    $('#offcanvasCount').text(count);

    // Show loading state
    $('#offcanvasContent').html(`
        <div class="offcanvas-loading">
            <div class="spinner-border spinner-border-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

    // Show offcanvas
    if (!currentOffcanvas) {
        currentOffcanvas = new bootstrap.Offcanvas(document.getElementById('detailsOffcanvas'));
    }
    currentOffcanvas.show();

    // Load content via AJAX
    loadDetails(userId, type);
}

function loadDetails(userId, type, page = 1) {
    $.ajax({
        url: `/users/${userId}/details/${type}`,
        type: 'GET',
        data: { page: page },
        success: function(response) {
            $('#offcanvasContent').html(response.html);

            // Handle pagination clicks
            $('#offcanvasContent').find('.pagination a').on('click', function(e) {
                e.preventDefault();
                const url = new URL($(this).attr('href'));
                const page = url.searchParams.get('page');
                loadDetails(userId, type, page);
            });
        },
        error: function(xhr) {
            $('#offcanvasContent').html(`
                <div class="alert alert-danger">
                    <i class="las la-exclamation-triangle me-2"></i>
                    Failed to load data. Please try again.
                </div>
            `);
        }
    });
}
</script>
@endsection
