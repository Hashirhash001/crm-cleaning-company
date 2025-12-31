@extends('layouts.app')

@section('title', 'All Followups')

@section('extra-css')
<style>
    /* Prevent horizontal page scroll */
    body {
        overflow-x: hidden;
    }

    .page-content {
        overflow-x: hidden;
    }

    /* Card styling */
    .card {
        overflow: hidden;
        max-width: 100%;
    }

    .followups-card .card-body {
        padding: 0;
        overflow: hidden;
    }

    /* Table Container */
    .table-container {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 600px;
        position: relative;
        width: 100%;
    }

    /* Custom Scrollbar */
    .table-container::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Sticky Table Header */
    .table-container thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
        padding: 12px 15px;
        vertical-align: middle;
        font-weight: 600;
    }

    .table-container tbody td {
        white-space: nowrap;
        padding: 12px 15px;
        vertical-align: middle;
    }

    .table-container tbody tr:hover td {
        background-color: #f8f9fa;
    }

    /* Loading overlay */
    .table-loading {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }

    /* Badge styling */
    .badge-lead {
        background-color: #17a2b8;
        color: #fff;
    }

    .badge-job {
        background-color: #6f42c1;
        color: #fff;
    }

    /* Stats cards */
    .stats-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .stats-card.active {
        border-color: #0d6efd;
        box-shadow: 0 10px 20px rgba(13,110,253,0.2);
    }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding-left: 15px; padding-right: 15px;">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">All Followups</h4>
                <div>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Followups</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-3" id="statsContainer">
        <div class="col-md-2">
            <div class="card stats-card" data-filter="total">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-primary" id="stat-total">0</h3>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card" data-filter="pending">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-warning" id="stat-pending">0</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card" data-filter="overdue">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-danger" id="stat-overdue">0</h3>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card" data-filter="today">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-info" id="stat-today">0</h3>
                    <small class="text-muted">Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card" data-filter="high">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-danger" id="stat-high">0</h3>
                    <small class="text-muted">High Priority</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card" data-filter="completed">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-success" id="stat-completed">0</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body" style="padding: 20px;">
                    <form id="filterForm">
                        <!-- First Row -->
                        <div class="row align-items-end g-3">
                            <!-- Type Filter -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-2">Type</label>
                                <select class="form-select" id="typeFilter" name="type">
                                    <option value="">All Types</option>
                                    <option value="lead">Leads Only</option>
                                    <option value="job">Work Orders Only</option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-2">Status</label>
                                <select class="form-select" id="statusFilter" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" selected>Pending</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <!-- Priority Filter -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-2">Priority</label>
                                <select class="form-select" id="priorityFilter" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            <!-- Date Range Filter -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-2">Date Range</label>
                                <select class="form-select" id="dateRangeFilter" name="date_range">
                                    <option value="">All Dates</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="today">Today</option>
                                    <option value="this_week">This Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>

                            <!-- Date From (hidden by default) -->
                            <div class="col-md-2" id="dateFromContainer" style="display: none;">
                                <label class="form-label fw-semibold mb-2">From Date</label>
                                <input type="date" class="form-control" id="dateFrom" name="date_from">
                            </div>

                            <!-- Date To (hidden by default) -->
                            <div class="col-md-2" id="dateToContainer" style="display: none;">
                                <label class="form-label fw-semibold mb-2">To Date</label>
                                <input type="date" class="form-control" id="dateTo" name="date_to">
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="row align-items-end g-3 mt-2">
                            @if(auth()->user()->role === 'super_admin')
                            <!-- Branch Filter -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-2">Branch</label>
                                <select class="form-select" id="branchFilter" name="branch_id">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assigned To Filter -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-2">Assigned To</label>
                                    <select class="form-select" id="assignedToFilter" name="assigned_to">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }}
                                                @php
                                                    $roleDisplay = [
                                                        'super_admin' => 'Super Admin',
                                                        'lead_manager' => 'Lead Manager',
                                                        'telecaller' => 'Telecaller',
                                                        'telecallers' => 'Telecaller',
                                                        'field_staff' => 'Field Staff',
                                                        'field_worker' => 'Field Worker'
                                                    ];
                                                    echo '(' . ($roleDisplay[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role))) . ')';
                                                @endphp
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            @endif

                            <!-- Search -->
                            <div class="col">
                                <label class="form-label fw-semibold mb-2">Search</label>
                                <input type="text" id="searchInput" name="search" class="form-control" placeholder="Name, Phone, Code...">
                            </div>

                            <!-- Filter Button -->
                            <div class="col-auto ms-auto">
                                <button type="submit" class="btn btn-success">
                                    <i class="las la-filter me-1"></i> Filter
                                </button>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-secondary" id="resetBtn">
                                    <i class="las la-redo-alt me-1"></i> Reset
                                </button>
                            </div>

                            {{-- <!-- Refresh Button -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-info" id="refreshBtn">
                                    <i class="las la-sync me-1"></i> Refresh
                                </button>
                            </div> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Followups Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card followups-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Followups List (<span id="followupCount">0</span> total)</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover mb-0" id="followupsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 100px;">Type</th>
                                    <th style="min-width: 100px;">Priority</th>
                                    <th style="min-width: 180px;">Customer/Lead</th>
                                    <th style="min-width: 130px;">Contact</th>
                                    <th style="min-width: 150px;">Date & Time</th>
                                    <th style="min-width: 120px;">Preference</th>
                                    @if(auth()->user()->role === 'super_admin')
                                        <th style="min-width: 150px;">Assigned To</th>
                                    @endif
                                    <th style="min-width: 100px;">Status</th>
                                    <th style="min-width: 150px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="followupsTableBody">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="card-footer">
                    <div id="paginationContainer"></div>
                </div>
            </div>
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

    // Load followups on page load
    loadFollowups();

    // Auto-refresh every 60 seconds
    setInterval(function() {
        loadFollowups();
    }, 60000);

    // ============================================
    // LOAD FOLLOWUPS FUNCTION
    // ============================================
    function loadFollowups(page = 1) {
        const searchTerm = $('#searchInput').val();
        const type = $('#typeFilter').val();
        const status = $('#statusFilter').val();
        const priority = $('#priorityFilter').val();
        const dateRange = $('#dateRangeFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const branchId = $('#branchFilter').val();
        const assignedTo = $('#assignedToFilter').val();

        console.log('🔍 Loading followups with:', {
            search: searchTerm,
            type, status, priority, dateRange
        });

        $.ajax({
            url: '{{ route("followups.index") }}',
            type: 'GET',
            data: {
                search: searchTerm,
                type: type,
                status: status,
                priority: priority,
                date_range: dateRange,
                date_from: dateFrom,
                date_to: dateTo,
                branch_id: branchId,
                assigned_to: assignedTo,
                page: page
            },
            beforeSend: function() {
                $('#followupsTableBody').addClass('table-loading');
            },
            success: function(response) {
                console.log('✅ Followups loaded:', response);

                if (response.success) {
                    renderFollowups(response.followups);
                    renderPagination(response.pagination);
                    updateStats(response.stats);
                    $('#followupCount').text(response.pagination.total);
                }

                $('#followupsTableBody').removeClass('table-loading');
            },
            error: function(xhr) {
                console.error('❌ Error loading followups:', xhr);
                $('#followupsTableBody').removeClass('table-loading');
                $('#followupsTableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center text-danger py-5">
                            <i class="las la-exclamation-triangle" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Error Loading Followups</h5>
                            <p>${xhr.responseJSON?.message || 'Please try again later'}</p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="las la-redo"></i> Reload Page
                            </button>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ============================================
    // RENDER FOLLOWUPS
    // ============================================
    function renderFollowups(followups) {
        if (!followups || followups.length === 0) {
            $('#followupsTableBody').html(`
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-2">No followups found</h5>
                        <p class="text-muted">Try adjusting your filters</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        const today = '{{ now()->format("Y-m-d") }}';
        const isAdmin = {{ auth()->user()->role === 'super_admin' ? 'true' : 'false' }};

        // Role name mapping
        const roleNames = {
            'super_admin': 'Super Admin',
            'lead_manager': 'Lead Manager',
            'telecaller': 'Telecaller',
            'telecallers': 'Telecaller',
            'field_staff': 'Field Staff',
            'field_worker': 'Field Worker'
        };

        followups.forEach(function(followup) {
            // FIXED: Only overdue if date is BEFORE today (not including today)
            const isOverdue = followup.followup_date < today;
            const isToday = followup.followup_date === today;

            // Type badge
            const typeBadge = followup.source_type === 'lead'
                ? '<span class="badge badge-lead"><i class="las la-clipboard-list"></i> LEAD</span>'
                : '<span class="badge badge-job"><i class="las la-briefcase"></i> WORK ORDER</span>';

            // Priority badge
            const priorityColors = { high: 'danger', medium: 'warning', low: 'secondary' };
            const priorityBadge = `<span class="badge bg-${priorityColors[followup.priority]}">
                <i class="las la-flag"></i> ${followup.priority.toUpperCase()}
            </span>`;

            // Customer name and link
            const customerName = followup.source_type === 'lead'
                ? (followup.lead?.name || 'N/A')
                : (followup.job?.customer?.name || 'N/A');

            const customerLink = followup.source_type === 'lead'
                ? `/leads/${followup.lead_id}`
                : `/jobs/${followup.job_id}`;

            const code = followup.source_type === 'lead'
                ? (followup.lead?.lead_code || '')
                : (followup.job?.job_code || '');

            const phone = followup.source_type === 'lead'
                ? (followup.lead?.phone || 'N/A')
                : (followup.job?.customer?.phone || 'N/A');

            // Date formatting
            const dateObj = new Date(followup.followup_date);
            const dateFormatted = dateObj.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            // FIXED: Date badge logic - only overdue if date is in the past (not today)
            let dateBadge = '';
            let dateClass = '';
            if (isOverdue) {
                dateBadge = '<span class="badge bg-danger mb-1">OVERDUE</span><br>';
                dateClass = 'text-danger fw-bold';
            } else if (isToday) {
                dateBadge = '<span class="badge bg-warning text-dark mb-1">TODAY</span><br>';
                dateClass = 'text-warning fw-bold';
            }

            // Time formatting
            let timeFormatted = '';
            if (followup.followup_time) {
                try {
                    const timeDate = new Date('2000-01-01 ' + followup.followup_time);
                    timeFormatted = timeDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch(e) {
                    timeFormatted = followup.followup_time;
                }
            }

            // FIXED: Assigned to with proper role name
            let assignedToText = 'N/A';
            let assignedToUser = null;

            if (followup.source_type === 'lead') {
                assignedToUser = followup.assigned_to_user;
            } else {
                assignedToUser = followup.assigned_to;
            }

            if (assignedToUser) {
                const userName = assignedToUser.name || 'N/A';
                const userRole = assignedToUser.role || '';
                const roleName = roleNames[userRole.toLowerCase()] || userRole;
                assignedToText = `${userName}<br><small class="text-muted">(${roleName})</small>`;
            }

            // Preference
            const preference = followup.callback_time_preference
                ? followup.callback_time_preference.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
                : 'Anytime';

            // Status badge
            const statusBadge = followup.status === 'pending'
                ? '<span class="badge bg-warning text-dark">Pending</span>'
                : '<span class="badge bg-success">Completed</span>';

            // Action button
            let actionHtml = '';
            if (followup.status === 'pending') {
                const btnClass = followup.source_type === 'lead' ? 'markLeadFollowupDone' : 'markJobFollowupDone';
                actionHtml = `
                    <button class="btn btn-sm btn-success ${btnClass}" data-id="${followup.id}">
                        <i class="las la-check"></i> Complete
                    </button>
                `;
            } else {
                actionHtml = '<span class="text-success"><i class="las la-check-circle"></i> Done</span>';
            }

            html += `
                <tr>
                    <td>${typeBadge}</td>
                    <td>${priorityBadge}</td>
                    <td>
                        <div>
                            <strong><a href="${customerLink}" class="text-primary text-decoration-none">${customerName}</a></strong>
                        </div>
                        <small class="text-muted">${code}</small>
                    </td>
                    <td><small><i class="las la-phone"></i> ${phone}</small></td>
                    <td>
                        ${dateBadge}
                        <strong class="${dateClass}">${dateFormatted}</strong>
                        ${timeFormatted ? '<br><small class="text-muted">' + timeFormatted + '</small>' : ''}
                    </td>
                    <td><small>${preference}</small></td>
                    ${isAdmin ? `<td><small>${assignedToText}</small></td>` : ''}
                    <td>${statusBadge}</td>
                    <td class="text-center">${actionHtml}</td>
                </tr>
            `;
        });

        $('#followupsTableBody').html(html);
    }


    // ============================================
    // RENDER PAGINATION
    // ============================================
    function renderPagination(pagination) {
        if (!pagination || pagination.total === 0) {
            $('#paginationContainer').html('');
            return;
        }

        let html = '<nav><ul class="pagination mb-0">';

        // Previous button
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link page-link-nav" href="#" data-page="${pagination.current_page - 1}">Previous</a>
            </li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }

        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `<li class="page-item">
                    <a class="page-link page-link-nav" href="#" data-page="${i}">${i}</a>
                </li>`;
            }
        }

        // Next button
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item">
                <a class="page-link page-link-nav" href="#" data-page="${pagination.current_page + 1}">Next</a>
            </li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        html += '</ul></nav>';
        $('#paginationContainer').html(html);
    }

    // ============================================
    // UPDATE STATS
    // ============================================
    function updateStats(stats) {
        $('#stat-total').text(stats.total);
        $('#stat-pending').text(stats.pending);
        $('#stat-overdue').text(stats.overdue);
        $('#stat-today').text(stats.today);
        $('#stat-high').text(stats.high_priority);
        $('#stat-completed').text(stats.completed);
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================

    // Form submit
    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        loadFollowups();
    });

    // Search input
    $(document).on('keyup', '#searchInput', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            loadFollowups();
        }, 500);
    });

    // Filter changes
    $(document).on('change', '#typeFilter, #statusFilter, #priorityFilter, #dateRangeFilter, #branchFilter, #assignedToFilter', function() {
        loadFollowups();
    });

    // Date range custom
    $(document).on('change', '#dateRangeFilter', function() {
        if ($(this).val() === 'custom') {
            $('#dateFromContainer, #dateToContainer').show();
        } else {
            $('#dateFromContainer, #dateToContainer').hide();
            $('#dateFrom, #dateTo').val('');
        }
        loadFollowups();
    });

    $(document).on('change', '#dateFrom, #dateTo', function() {
        loadFollowups();
    });

    // Pagination click
    $(document).on('click', '.page-link-nav', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            loadFollowups(page);
        }
    });

    // Reset button
    $('#resetBtn').click(function() {
        $('#filterForm')[0].reset();
        $('#statusFilter').val('pending');
        $('#dateFromContainer, #dateToContainer').hide();
        $('.stats-card').removeClass('active');
        loadFollowups();
    });

    // Refresh button
    $('#refreshBtn').click(function() {
        loadFollowups();
        Swal.fire({
            icon: 'success',
            title: 'Refreshed!',
            text: 'Data has been reloaded',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Stats card quick filter
    $(document).on('click', '.stats-card', function() {
        const filterType = $(this).data('filter');
        $('.stats-card').removeClass('active');
        $(this).addClass('active');

        // Reset filters
        $('#statusFilter').val('');
        $('#priorityFilter').val('');
        $('#dateRangeFilter').val('');

        switch(filterType) {
            case 'pending':
                $('#statusFilter').val('pending');
                break;
            case 'completed':
                $('#statusFilter').val('completed');
                break;
            case 'overdue':
                $('#statusFilter').val('pending');
                $('#dateRangeFilter').val('overdue');
                break;
            case 'today':
                $('#statusFilter').val('pending');
                $('#dateRangeFilter').val('today');
                break;
            case 'high':
                $('#statusFilter').val('pending');
                $('#priorityFilter').val('high');
                break;
            case 'total':
                // Show all
                $('#statusFilter').val('');
                break;
        }

        loadFollowups();
    });

    // Mark Lead Followup as Done
    $(document).on('click', '.markLeadFollowupDone', function() {
        const $btn = $(this);
        const followupId = $btn.data('id');

        Swal.fire({
            title: 'Mark as Completed?',
            text: 'Are you sure you want to mark this followup as completed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

                $.ajax({
                    url: `/lead-followups/${followupId}/complete`,
                    type: 'POST',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Completed!',
                            text: 'Followup marked as completed',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadFollowups();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to complete followup', 'error');
                        $btn.prop('disabled', false).html('<i class="las la-check"></i> Complete');
                    }
                });
            }
        });
    });

    // Mark Job Followup as Done
    $(document).on('click', '.markJobFollowupDone', function() {
        const $btn = $(this);
        const followupId = $btn.data('id');

        Swal.fire({
            title: 'Mark as Completed?',
            text: 'Are you sure you want to mark this followup as completed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

                $.ajax({
                    url: `/job-followups/${followupId}/complete`,
                    type: 'POST',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Completed!',
                            text: 'Followup marked as completed',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadFollowups();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to complete followup', 'error');
                        $btn.prop('disabled', false).html('<i class="las la-check"></i> Complete');
                    }
                });
            }
        });
    });

    console.log('✅ Followups management system initialized');
});
</script>
@endsection
