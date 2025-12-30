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
        background: #fefce8; /* Soft yellow background */
        color: #713f12; /* Dark brown text */
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        border: 1px solid #fde047;
        box-shadow: 0 2px 8px rgba(234, 179, 8, 0.15); /* Soft yellow shadow */
    }

    .time-preference-badge i {
        font-size: 1rem;
        margin-right: 0.5rem;
        color: #ca8a04;
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

    /* Stat Card Link Styling */
    a.text-decoration-none .stat-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    a.text-decoration-none .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    a.text-decoration-none:hover h4,
    a.text-decoration-none:hover p {
        color: inherit;
    }

   /* ====================================
   SOURCE TYPE BADGES (Lead vs Work Order)
   ==================================== */

    .source-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        color: #fff;
    }

    .source-badge.lead {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }

    .source-badge.job {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3);
    }

    .source-badge i {
        margin-right: 0.35rem;
        font-size: 0.9rem;
    }

    /* ====================================
    PRIORITY BADGES - DISTINCT COLORS
    ==================================== */

    .priority-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.85rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        border: 2px solid;
    }

    .priority-badge.high {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: #fca5a5;
        color: white;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
    }

    .priority-badge.medium {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-color: #fcd34d;
        color: #fff;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);
    }

    .priority-badge.low {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #6ee7b7;
        color: white;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
    }

    .priority-badge i {
        margin-right: 0.35rem;
    }

    /* ====================================
    SECTION HEADERS - DIFFERENT COLORS FOR EACH CARD
    ==================================== */

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.25rem 1.75rem;
        border-radius: 12px;
        margin-bottom: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    /* Animated background effect on hover */
    .section-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s ease;
    }

    .section-header:hover::before {
        left: 100%;
    }

    .section-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    /* IMMEDIATE FOLLOWUPS - RED/URGENT */
    .section-header.urgent {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    .section-header.urgent:hover {
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    /* THIS WEEK - BLUE/CYAN */
    .section-header.week {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
    }

    .section-header.week:hover {
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
    }

    /* SITE VISITS - PURPLE */
    .section-header.sitevisit {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
    }

    .section-header.sitevisit:hover {
        box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4);
    }

    /* THIS MONTH - GREEN */
    .section-header.month {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }

    .section-header.month:hover {
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
    }

    /* Header content */
    .section-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-header h5 i {
        font-size: 1.3rem;
    }

    /* Right side badges and toggle */
    .section-header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-header .badge {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    /* Toggle Icon Animation */
    .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .toggle-icon.collapsed {
        transform: rotate(-90deg);
    }

    /* Collapse Container */
    .followup-collapse-container {
        margin-top: 1.5rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Priority Cards - Enhanced Design */
    .priority-card {
        border-left: 4px solid;
        border-radius: 12px;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .priority-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .priority-card.urgent {
        border-left-color: #dc2626;
        background: linear-gradient(90deg, rgba(220, 38, 38, 0.03), #fff);
    }

    .priority-card.high {
        border-left-color: #f59e0b;
        background: linear-gradient(90deg, rgba(245, 158, 11, 0.03), #fff);
    }

    .priority-card.medium {
        border-left-color: #0ea5e9;
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.03), #fff);
    }

    .priority-card.normal {
        border-left-color: #10b981;
        background: linear-gradient(90deg, rgba(16, 185, 129, 0.03), #fff);
    }

    /* Followup Item Styling */
    .followup-item {
        padding: 1.25rem;
        margin-bottom: 0;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        background: #fff;
    }

    .followup-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .followup-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .followup-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 1.05rem;
    }

    .followup-title a {
        color: #1e293b;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .followup-title a:hover {
        color: #667eea;
    }

    .followup-meta {
        font-size: 0.875rem;
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .followup-meta i {
        margin-right: 0.25rem;
        color: #94a3b8;
    }

    /* Notes Section */
    .followup-notes {
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 8px;
        border-left: 3px solid #64748b;
    }

    .followup-notes small {
        color: #475569;
        line-height: 1.6;
    }

    /* Action Buttons - Enhanced */
    .btn-complete {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
    }

    .btn-complete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
    }

    /* Count Badge in Header */
    .count-badge {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        margin-left: 0.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .section-header {
            padding: 1rem 1.25rem;
            flex-direction: row;
            gap: 0.5rem;
        }

        .section-header h5 {
            font-size: 1rem;
        }

        .section-header-right {
            gap: 0.5rem;
        }

        .section-header .badge {
            padding: 0.35rem 0.75rem;
            font-size: 0.7rem;
        }

        .toggle-icon {
            font-size: 1.2rem;
        }

        .followup-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    /* Ripple Effect */
    .section-header {
        position: relative;
        overflow: hidden;
    }

    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
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
                <a href="{{ route('customers.index') }}" class="text-decoration-none">
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
                </a>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <a href="{{ route('users.index') }}" class="text-decoration-none">
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
                </a>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <a href="{{ route('leads.index', ['status' => 'confirmed']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Confirmed Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $confirmedLeads }}</h4>
                                    <small class="text-warning">Awaiting Approval</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-warning text-warning rounded">
                                        <i class="las la-clipboard-list fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <a href="{{ route('jobs.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending Work Orders</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $pendingWorkOrders }}</h4>
                                    <small class="text-warning">Pending Confirmation</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded">
                                        <i class="las la-briefcase fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
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

    {{-- TELECALLER DASHBOARD --}}
    @if(auth()->user()->role === 'telecallers')
    {{-- Telecaller Lead Stats --}}
    <div class="row mb-3">
        {{-- Total Leads --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="{{ route('leads.index') }}" class="text-decoration-none">
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
            </a>
        </div>

        {{-- Pending Leads --}}
        {{-- <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="{{ route('leads.index', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['pending'] ?? 0 }}</h4>
                                <small class="text-warning">Needs follow-up</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-clock fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div> --}}

        {{-- Site Visit --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="{{ route('leads.index', ['status' => 'site_visit']) }}" class="text-decoration-none">
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
            </a>
        </div>

        {{-- Confirmed --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="{{ route('leads.index', ['status' => 'confirmed']) }}" class="text-decoration-none">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Confirmed</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['confirmed'] ?? 0 }}</h4>
                                <small class="text-warning">Awaiting approval</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-hourglass-half fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Approved --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="{{ route('leads.index', ['status' => 'approved']) }}" class="text-decoration-none">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Approved</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['approved'] ?? 0 }}</h4>
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
            </a>
        </div>
    </div>
    @endif

    {{-- UNIFIED FOLLOWUP SECTIONS FOR ALL USERS --}}

    {{-- Immediate Followups (Today + Overdue) --}}
    @if($followupData['immediate']->count() > 0)
    <div class="row mb-3">
        {{-- Add this near your dashboard header or before followup sections --}}
        <div class="row mb-3">
            <div class="col-12 text-end">
                <a href="{{ route('followups.index') }}" class="btn btn-primary">
                    <i class="las la-list"></i> View All Followups
                </a>
            </div>
        </div>

        <div class="col-12">
            <div class="section-header urgent" data-bs-toggle="collapse" data-bs-target="#immediateFollowupsCollapse" aria-expanded="false">
                <h5>
                    <i class="las la-exclamation-circle"></i>
                    Immediate Followups
                    <span class="count-badge">{{ $followupData['immediate']->count() }}</span>
                </h5>
                <div class="section-header-right">
                    <span class="badge bg-white text-danger">Action Required</span>
                    <i class="las la-angle-down toggle-icon collapsed"></i>
                </div>
            </div>

            <div class="collapse" id="immediateFollowupsCollapse">
                <div class="followup-collapse-container">
                    <div class="row">
                        @foreach($followupData['immediate'] as $followup)
                        <div class="col-xl-6 col-lg-12 mb-3">
                            <div class="priority-card {{ $followup->priority }} followup-item">
                                <div class="followup-header">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            {{-- Source Badge - Lead or Work Order --}}
                                            @if($followup->source_type === 'lead')
                                                <span class="source-badge lead">
                                                    <i class="las la-clipboard-list"></i> LEAD
                                                </span>
                                            @else
                                                <span class="source-badge job">
                                                    <i class="las la-briefcase"></i> WORK ORDER
                                                </span>
                                            @endif

                                            {{-- Priority Badge --}}
                                            <span class="priority-badge {{ $followup->priority }}">
                                                <i class="las la-flag"></i> {{ strtoupper($followup->priority) }}
                                            </span>

                                            {{-- FIX: Overdue/Today Badge - Compare DATE only, not timestamp --}}
                                            @if($followup->followup_date->format('Y-m-d') < now()->format('Y-m-d'))
                                                <span class="badge bg-danger">OVERDUE</span>
                                            @else
                                                <span class="badge bg-warning text-dark">TODAY</span>
                                            @endif
                                        </div>

                                        <div class="followup-title">
                                            @if($followup->source_type === 'lead')
                                                <a href="{{ route('leads.show', $followup->lead_id) }}">
                                                    {{ $followup->lead->name }}
                                                </a>
                                            @else
                                                <a href="{{ route('jobs.show', $followup->job_id) }}">
                                                    {{ $followup->job->customer->name ?? 'N/A' }}
                                                </a>
                                            @endif
                                        </div>

                                        <div class="followup-meta">
                                            @if($followup->source_type === 'lead')
                                                <span><i class="las la-tag"></i> {{ $followup->lead->lead_code }}</span>
                                                <span><i class="las la-phone"></i> {{ $followup->lead->phone }}</span>
                                            @else
                                                <span><i class="las la-tag"></i> {{ $followup->job->job_code }}</span>
                                                <span><i class="las la-phone"></i> {{ $followup->job->customer->phone ?? 'N/A' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                                    <div>
                                        {{-- Show appropriate color based on overdue status --}}
                                        @if($followup->followup_date->format('Y-m-d') < now()->format('Y-m-d'))
                                            <small class="text-danger fw-bold">
                                        @else
                                            <small class="text-warning fw-bold">
                                        @endif
                                            <i class="las la-calendar"></i>
                                            {{ $followup->followup_date->format('d M Y') }}
                                            @if($followup->followup_time)
                                                • {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                            @endif
                                        </small>
                                    </div>
                                    @if($followup->status === 'pending')
                                        @if($followup->source_type === 'lead')
                                            <button class="btn btn-sm btn-complete markLeadFollowupDone" data-id="{{ $followup->id }}">
                                                <i class="las la-check"></i> Complete
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-complete markJobFollowupDone" data-id="{{ $followup->id }}">
                                                <i class="las la-check"></i> Complete
                                            </button>
                                        @endif
                                    @endif
                                </div>

                                @if($followup->callback_time_preference)
                                <div class="mb-2">
                                    <span class="time-preference-badge">
                                        <i class="las la-phone-volume"></i> {{ ucfirst(str_replace('_', ' ', $followup->callback_time_preference)) }}
                                    </span>
                                </div>
                                @endif

                                @if($followup->notes)
                                <div class="followup-notes">
                                    <small>{{ $followup->notes }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- This Week's Followups --}}
    @if($followupData['thisWeekFollowups']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header week" data-bs-toggle="collapse" data-bs-target="#weekFollowupsCollapse" aria-expanded="false">
                <h5>
                    <i class="las la-calendar-week"></i>
                    This Week's Followups
                    <span class="count-badge">{{ $followupData['thisWeekFollowups']->count() }}</span>
                </h5>
                <div class="section-header-right">
                    <span class="badge bg-white text-info">Upcoming</span>
                    <i class="las la-angle-down toggle-icon collapsed"></i>
                </div>
            </div>

            <div class="collapse" id="weekFollowupsCollapse">
                <div class="followup-collapse-container">
                    <div class="row">
                        @foreach($followupData['thisWeekFollowups'] as $followup)
                        <div class="col-xl-6 col-lg-12 mb-3">
                            <div class="priority-card medium followup-item">
                                <div class="followup-header">
                                    <div class="flex-grow-1">
                                        <div class="followup-title">
                                            @if($followup->source_type === 'lead')
                                                <a href="{{ route('leads.show', $followup->lead_id) }}">
                                                    {{ $followup->lead->name }}
                                                </a>
                                                <span class="source-badge lead ms-2">
                                                    <i class="las la-clipboard-list"></i> Lead
                                                </span>
                                            @else
                                                <a href="{{ route('jobs.show', $followup->job_id) }}">
                                                    {{ $followup->job->customer->name ?? 'N/A' }}
                                                </a>
                                                <span class="source-badge job ms-2">
                                                    <i class="las la-briefcase"></i> Work Order
                                                </span>
                                            @endif
                                        </div>
                                        <div class="followup-meta">
                                            @if($followup->source_type === 'lead')
                                                <span><i class="las la-tag"></i> {{ $followup->lead->lead_code }}</span>
                                                <span><i class="las la-phone"></i> {{ $followup->lead->phone }}</span>
                                            @else
                                                <span><i class="las la-tag"></i> {{ $followup->job->job_code }}</span>
                                                <span><i class="las la-phone"></i> {{ $followup->job->customer->phone ?? 'N/A' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="priority-badge {{ $followup->priority }}">
                                            <i class="las la-flag"></i> {{ ucfirst($followup->priority) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="fw-bold text-info">
                                            <i class="las la-calendar"></i>
                                            {{ $followup->followup_date->format('D, d M Y') }}
                                            @if($followup->followup_time)
                                                • {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                            @endif
                                        </small>
                                    </div>
                                    @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to == auth()->id()))
                                        @if($followup->source_type === 'lead')
                                            <button class="btn btn-sm btn-complete markLeadFollowupDone" data-id="{{ $followup->id }}">
                                                <i class="las la-check"></i> Complete
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-complete markJobFollowupDone" data-id="{{ $followup->id }}">
                                                <i class="las la-check"></i> Complete
                                            </button>
                                        @endif
                                    @endif
                                </div>

                                @if($followup->callback_time_preference)
                                <div class="mb-2">
                                    <span class="time-preference-badge">
                                        <i class="las la-phone-volume"></i> {{ ucfirst(str_replace('_', ' ', $followup->callback_time_preference)) }}
                                    </span>
                                </div>
                                @endif

                                @if($followup->notes)
                                <div class="followup-notes">
                                    <small>{{ $followup->notes }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Site Visits This Week - FOR SUPER_ADMIN AND TELECALLERS --}}
    @if(in_array(auth()->user()->role, ['telecallers', 'super_admin']) && $followupData['siteVisitsThisWeek']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header sitevisit" data-bs-toggle="collapse" data-bs-target="#siteVisitsCollapse" aria-expanded="false">
                <h5>
                    <i class="las la-map-marked-alt"></i>
                    Site Visits This Week
                    <span class="count-badge">{{ $followupData['siteVisitsThisWeek']->count() }}</span>
                </h5>
                <div class="section-header-right">
                    <span class="badge bg-white text-dark">Scheduled</span>
                    <i class="las la-angle-down toggle-icon collapsed"></i>
                </div>
            </div>

            <div class="collapse" id="siteVisitsCollapse">
                <div class="followup-collapse-container">
                    <div class="row">
                        @foreach($followupData['siteVisitsThisWeek'] as $lead)
                        <div class="col-xl-6 col-lg-12 mb-3">
                            <div class="followup-item" style="border-left: 4px solid #a855f7;">
                                <div class="followup-header">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="source-badge" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);">
                                                <i class="las la-map-marker-alt"></i> SITE VISIT
                                            </span>
                                            <span class="badge bg-info">{{ $lead->lead_code }}</span>
                                        </div>

                                        <div class="followup-title">
                                            <a href="{{ route('leads.show', $lead->id) }}">
                                                {{ $lead->name }}
                                            </a>
                                        </div>

                                        <div class="followup-meta">
                                            <span><i class="las la-phone"></i> {{ $lead->phone }}</span>
                                            @if($lead->email)
                                                <span><i class="las la-envelope"></i> {{ $lead->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($lead->address || $lead->services->count() > 0)
                                <div class="mt-3">
                                    @if($lead->address)
                                        <small class="text-muted d-block mb-2">
                                            <i class="las la-map-marker"></i>
                                            {{ $lead->address }}@if($lead->district), {{ $lead->district }}@endif
                                        </small>
                                    @endif

                                    @if($lead->services->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($lead->services as $service)
                                                <span class="badge bg-purple-soft text-purple">
                                                    <i class="las la-tools"></i> {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @endif

                                @if($lead->site_visit_date || $lead->site_visit_time)
                                <div class="mt-2">
                                    <small class="text-purple fw-semibold">
                                        <i class="las la-calendar-check"></i>
                                        @if($lead->site_visit_date)
                                            {{ \Carbon\Carbon::parse($lead->site_visit_date)->format('d M Y') }}
                                        @endif
                                        @if($lead->site_visit_time)
                                            • {{ \Carbon\Carbon::parse($lead->site_visit_time)->format('h:i A') }}
                                        @endif
                                    </small>
                                </div>
                                @endif

                                <div class="mt-3 text-end">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-purple">
                                        <i class="las la-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- This Month's Followups --}}
    @if($followupData['thisMonthFollowups']->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="section-header month" data-bs-toggle="collapse" data-bs-target="#monthFollowupsCollapse" aria-expanded="false">
                <h5>
                    <i class="las la-calendar-alt"></i>
                    This Month's Followups
                    <span class="count-badge">{{ $followupData['thisMonthFollowups']->count() }}</span>
                </h5>
                <div class="section-header-right">
                    <span class="badge bg-white text-success">Planned</span>
                    <i class="las la-angle-down toggle-icon collapsed"></i>
                </div>
            </div>

            <div class="collapse" id="monthFollowupsCollapse">
                <div class="followup-collapse-container">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-semibold">Type</th>
                                            <th class="fw-semibold">Lead/Customer</th>
                                            <th class="fw-semibold">Priority</th>
                                            <th class="fw-semibold">Scheduled Date</th>
                                            <th class="fw-semibold">Time Preference</th>
                                            <th class="fw-semibold text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($followupData['thisMonthFollowups'] as $followup)
                                        <tr>
                                            <td>
                                                @if($followup->source_type === 'lead')
                                                    <span class="source-badge lead">
                                                        <i class="las la-clipboard-list"></i> Lead
                                                    </span>
                                                @else
                                                    <span class="source-badge job">
                                                        <i class="las la-briefcase"></i> Work Order
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <h6 class="mb-0">
                                                    @if($followup->source_type === 'lead')
                                                        <a href="{{ route('leads.show', $followup->lead_id) }}" class="text-dark text-decoration-none">
                                                            {{ $followup->lead->name }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('jobs.show', $followup->job_id) }}" class="text-dark text-decoration-none">
                                                            {{ $followup->job->customer->name ?? 'N/A' }}
                                                        </a>
                                                    @endif
                                                </h6>
                                                <small class="text-muted">
                                                    @if($followup->source_type === 'lead')
                                                        {{ $followup->lead->lead_code }} • {{ $followup->lead->phone }}
                                                    @else
                                                        {{ $followup->job->job_code }} • {{ $followup->job->customer->phone ?? 'N/A' }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <span class="priority-badge {{ $followup->priority }}">
                                                    <i class="las la-flag"></i> {{ ucfirst($followup->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $followup->followup_date->format('d M Y') }}</strong>
                                                @if($followup->followup_time)
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($followup->callback_time_preference)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="las la-phone-volume"></i> {{ ucfirst(str_replace('_', ' ', $followup->callback_time_preference)) }}
                                                    </span>
                                                @else
                                                    <small class="text-muted">Anytime</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to == auth()->id()))
                                                    @if($followup->source_type === 'lead')
                                                        <button class="btn btn-sm btn-complete markLeadFollowupDone" data-id="{{ $followup->id }}">
                                                            <i class="las la-check"></i> Complete
                                                        </button>
                                                    @else
                                                        <button class="btn btn-sm btn-complete markJobFollowupDone" data-id="{{ $followup->id }}">
                                                            <i class="las la-check"></i> Complete
                                                        </button>
                                                    @endif
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

    // Toggle icon rotation on collapse
    $('.section-header').on('click', function() {
        const icon = $(this).find('.toggle-icon');
        icon.toggleClass('collapsed');
    });

    // Smooth scroll to expanded section
    $(document).on('shown.bs.collapse', function (e) {
        $('html, body').animate({
            scrollTop: $(e.target).prev('.section-header').offset().top - 20
        }, 300);
    });

    // Add ripple effect on header click
    $('.section-header').on('click', function(e) {
        const ripple = $('<span class="ripple"></span>');
        $(this).append(ripple);

        const x = e.pageX - $(this).offset().left;
        const y = e.pageY - $(this).offset().top;

        ripple.css({
            left: x,
            top: y
        });

        setTimeout(() => ripple.remove(), 600);
    });

    // ========================================
    // Mark LEAD Followup as complete
    // ========================================
    $(document).on('click', '.markLeadFollowupDone', function() {
        var followupId = $(this).data('id');

        Swal.fire({
            title: 'Complete Lead Followup?',
            text: 'Mark this lead followup as completed',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d'
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

    // ========================================
    // Mark JOB Followup as complete
    // ========================================
    $(document).on('click', '.markJobFollowupDone', function() {
        var followupId = $(this).data('id');

        Swal.fire({
            title: 'Complete Work Order Followup?',
            text: 'Mark this work order followup as completed',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/job-followups/${followupId}/complete`,
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
