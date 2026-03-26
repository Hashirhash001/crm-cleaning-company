@extends('layouts.app')

@section('title', 'Completed Work Orders')

@section('extra-css')
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css"/>

<style>
/* ── Reset ──────────────────────────────────────────────────── */
body          { overflow-x: hidden; }
.page-content { overflow-x: hidden; }
.card         { overflow: hidden; max-width: 100%; }
.row          { margin-left: 0; margin-right: 0; }

/* ── Stat Cards ─────────────────────────────────────────────── */
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.1rem 1.3rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    display: flex; align-items: center; gap: 1rem;
    height: 100%;
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.stat-label { font-size: 0.78rem; color: #94a3b8; margin-top: 2px; }
.stat-value  { font-size: 1.45rem; font-weight: 700; color: #1e293b; line-height: 1.1; }

/* ── Filter card ────────────────────────────────────────────── */
.filter-card { border-radius: 12px; border: 1px solid #e2e8f0; }
.filter-card .card-body { padding: 1.25rem 1.5rem; }
.filter-card .form-label {
    font-size: 0.82rem; font-weight: 600;
    color: #374151; margin-bottom: 5px;
}
.filter-card .form-control,
.filter-card .form-select {
    font-size: 0.88rem; height: 38px;
    padding: 0.4rem 0.75rem;
    border-radius: 8px; border-color: #e2e8f0;
}
.filter-card .form-control:focus,
.filter-card .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.filter-card .btn { height: 38px; font-size: 0.88rem; border-radius: 8px; padding: 0 1rem; }

/* ── Jobs card ──────────────────────────────────────────────── */
.jobs-card { border-radius: 12px; border: 1px solid #e2e8f0; }
.jobs-card .card-body { padding: 0; }
.jobs-card .card-header {
    background: #fff; border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.25rem;
}

/* ── View Mode Tabs ─────────────────────────────────────────── */
#viewModeToggle {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    padding: 0 1.25rem;
    background: #fafafa;
    gap: 0;
}
#viewModeToggle .tab-btn {
    border: none; border-bottom: 2px solid transparent;
    border-radius: 0; background: transparent;
    padding: 0.7rem 1.2rem; font-size: 0.875rem;
    font-weight: 500; color: #64748b;
    margin-bottom: -2px; cursor: pointer;
    transition: color .15s, border-color .15s;
    white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}
#viewModeToggle .tab-btn:hover  { color: #1e293b; background: #f1f5f9; }
#viewModeToggle .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; font-weight: 600; }
#viewModeToggle .tab-btn.tab-staff.active { color: #d97706; border-bottom-color: #d97706; }

/* ── Table ──────────────────────────────────────────────────── */
.table-container {
    overflow-x: auto; overflow-y: auto;
    max-height: 620px; position: relative; width: 100%;
}
.table-container::-webkit-scrollbar { width: 6px; height: 6px; }
.table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
.table-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

.table-container thead th {
    position: sticky; top: 0;
    background: #f8fafc; z-index: 10;
    white-space: nowrap; padding: 11px 14px;
    font-weight: 600; font-size: 0.82rem;
    color: #374151; text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 2px solid #e2e8f0;
    box-shadow: 0 2px 2px -1px rgba(0,0,0,.05);
}
.table-container tbody td {
    white-space: nowrap; padding: 11px 14px;
    vertical-align: middle; font-size: 0.88rem;
}
.table-container tbody tr:hover td { background: #f8fafc; }
.table-container tbody tr { border-bottom: 1px solid #f1f5f9; }

/* ── Status pills ───────────────────────────────────────────── */
.status-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.76rem; font-weight: 600;
}
.status-pill.status-completed    { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.status-pill.status-pending-staff{ background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }

/* ── Action buttons ─────────────────────────────────────────── */
.action-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1rem; cursor: pointer;
    transition: transform .15s, opacity .15s;
    text-decoration: none; flex-shrink: 0;
}
.action-btn:hover { transform: scale(1.1); opacity: .85; }

/* ── Job title link ─────────────────────────────────────────── */
.job-title-link { color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.88rem; }
.job-title-link:hover { color: #1e40af; text-decoration: underline; }

/* ── Loading state ──────────────────────────────────────────── */
.table-loading { opacity: 0.45; pointer-events: none; transition: opacity .2s; }

/* ── Pagination ─────────────────────────────────────────────── */
.jobs-card .card-footer {
    background: #fafafa; border-top: 1px solid #e2e8f0;
    padding: 0.75rem 1.25rem;
}
.jobs-card .card-footer .pagination { margin: 0; }

/* ── Add Staff Modal ────────────────────────────────────────── */
.staff-type-card {
    border: 2px solid #e2e8f0; border-radius: 10px;
    padding: 0.75rem 1rem; cursor: pointer;
    transition: border-color .15s, background .15s;
    display: flex; align-items: center; gap: 0.6rem;
    font-size: 0.88rem; font-weight: 500; color: #374151;
}
.staff-type-card:hover { border-color: #7c3aed; background: #faf5ff; }
.staff-type-card.selected { border-color: #7c3aed; background: #f5f3ff; color: #6d28d9; }
.staff-type-card i { font-size: 1.1rem; }

/* Loading spinner inside select */
.select-loading { position: relative; }
.select-loading::after {
    content: '';
    position: absolute; right: 32px; top: 50%;
    transform: translateY(-50%);
    width: 14px; height: 14px;
    border: 2px solid #e2e8f0;
    border-top-color: #7c3aed;
    border-radius: 50%;
    animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding: 0 16px;">

    {{-- Page Header --}}
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title mb-0">
                    <i class="ph ph-check-circle me-2" style="color:#16a34a;"></i>
                    Completed Work Orders
                </h4>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('jobs.index') }}">Work Orders</a></li>
                    <li class="breadcrumb-item active">Completed</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;">
                    <i class="ph ph-check-circle"></i>
                </div>
                <div>
                    <div class="stat-value" id="statCompleted">{{ $totalCompleted }}</div>
                    <div class="stat-label">Total Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="ph ph-clock"></i>
                </div>
                <div>
                    <div class="stat-value" id="statPending">{{ $staffPending }}</div>
                    <div class="stat-label">Staff Pending Approval</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef9c3;color:#ca8a04;">
                    <i class="ph ph-star"></i>
                </div>
                <div>
                    <div class="stat-value" id="statRating">
                        {{ number_format(\App\Models\JobRating::avg('rating') ?? 0, 1) }}
                    </div>
                    <div class="stat-label">Avg Customer Rating</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                <div class="stat-icon" style="background:#bfdbfe;color:#2563eb;">
                    <i class="ph ph-briefcase"></i>
                </div>
                <div>
                    <a href="{{ route('jobs.index') }}" class="fw-semibold text-primary d-block"
                       style="font-size:0.9rem;text-decoration:none;">
                        All Work Orders <i class="ph ph-arrow-right"></i>
                    </a>
                    <div class="stat-label">Jump to jobs list</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card filter-card shadow-none">
                <div class="card-body">
                    <form id="filterForm" autocomplete="off">
                        <div class="row align-items-end g-3">

                            @if(auth()->user()->role === 'super_admin')
                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="form-label">
                                    <i class="ph ph-buildings me-1"></i>Branch
                                </label>
                                <select id="branchFilter" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="form-label">
                                    <i class="ph ph-calendar me-1"></i>Completed From
                                </label>
                                <input type="date" id="dateFrom" class="form-control">
                            </div>

                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="form-label">
                                    <i class="ph ph-calendar-check me-1"></i>Completed To
                                </label>
                                <input type="date" id="dateTo" class="form-control">
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label">
                                    <i class="ph ph-magnifying-glass me-1"></i>Search
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"
                                          style="background:#f8fafc;border-color:#e2e8f0;border-right:none;">
                                        <i class="ph ph-magnifying-glass" style="color:#94a3b8;"></i>
                                    </span>
                                    <input type="text" id="searchInput" class="form-control"
                                           style="border-left:none;"
                                           placeholder="Job code, title, customer…">
                                </div>
                            </div>

                            <div class="col-auto d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="ph ph-funnel me-1"></i>Filter
                                </button>
                                <button type="button" id="resetBtn" class="btn btn-secondary">
                                    <i class="ph ph-arrow-counter-clockwise me-1"></i>Reset
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="row">
        <div class="col-12">
            <div class="card jobs-card shadow-none">

                {{-- Card Header --}}
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0 fw-bold" style="color:#1e293b;">Completed Orders</h5>
                        <span class="badge bg-primary rounded-pill" id="jobCount">{{ $jobs->total() }}</span>
                        <span id="pendingBadge"
                              class="badge rounded-pill"
                              style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;
                                     {{ $staffPending > 0 ? '' : 'display:none;' }}">
                            <i class="ph ph-clock"></i>
                            <span id="pendingBadgeText">{{ $staffPending }}</span> Staff Pending
                        </span>
                    </div>
                </div>

                {{-- Status Tabs --}}
                <div id="viewModeToggle">
                    <button class="tab-btn active" data-status="" type="button">
                        <i class="ph ph-list"></i> All
                    </button>
                    <button class="tab-btn" data-status="completed" type="button">
                        <i class="ph ph-check-circle"></i> Completed
                    </button>
                    <button class="tab-btn tab-staff" data-status="staff_pending_approval" type="button">
                        <i class="ph ph-clock"></i> Staff Pending
                        <span class="badge rounded-pill bg-warning text-dark ms-1"
                              id="tabPendingCount"
                              style="font-size:0.68rem;{{ $staffPending > 0 ? '' : 'display:none;' }}">
                            {{ $staffPending }}
                        </span>
                    </button>
                </div>

                <input type="hidden" id="statusFilter" value="">

                {{-- Table --}}
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover mb-0" id="completedTable">
                            <thead>
                                <tr>
                                    <th>Job Code</th>
                                    <th>Title / Branch</th>
                                    <th>Customer</th>
                                    <th>Staff</th>
                                    <th>Rating</th>
                                    <th>Completed</th>
                                    <th>Status</th>
                                    <th style="text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                @include('jobs.partials.completed-rows')
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="card-footer">
                    <div id="paginationContainer">
                        {{ $jobs->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════
     ADD STAFF MODAL
═══════════════════════════════════════════ --}}
<div class="modal fade" id="addStaffInlineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">

            <div class="modal-header"
                 style="background:linear-gradient(135deg,#f3e8ff,#ede9fe);border-bottom:1px solid #ddd6fe;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:#6d28d9;">
                        <i class="ph ph-user-plus me-2"></i>Add Staff Members
                    </h5>
                    <small class="text-muted" id="modalJobInfo" style="font-size:0.78rem;"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- ── Entry Builder ── --}}
                <div id="staffEntryBuilder">

                    {{-- Staff Type --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2" style="font-size:0.83rem;">
                            Staff Type <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <div class="staff-type-card selected flex-fill" data-type="registered" id="typeCardRegistered">
                                <i class="ph ph-identification-card" style="color:#7c3aed;"></i>
                                Registered User
                            </div>
                            <div class="staff-type-card flex-fill" data-type="temporary" id="typeCardTemporary">
                                <i class="ph ph-user-circle-dashed" style="color:#64748b;"></i>
                                Temporary / External
                            </div>
                        </div>
                    </div>

                    {{-- Role --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.83rem;">
                            Role <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <div class="staff-type-card flex-fill" data-role="supervisor" id="roleCardSupervisor">
                                <i class="ph ph-user-circle-gear" style="color:#2563eb;"></i>
                                Supervisor
                            </div>
                            <div class="staff-type-card flex-fill" data-role="worker" id="roleCardWorker">
                                <i class="ph ph-hard-hat" style="color:#7c3aed;"></i>
                                Worker
                            </div>
                        </div>
                    </div>

                    {{-- Registered: User dropdown --}}
                    <div id="inlineRegisteredFields" class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.83rem;">
                            Select User <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative" id="userSelectWrapper">
                            <select id="inlineStaffUserId" class="form-select"
                                    style="border-radius:8px;font-size:0.88rem;">
                                <option value="">— Select a role first —</option>
                            </select>
                        </div>
                        <small class="text-muted" id="noStaffHint" style="display:none;font-size:0.78rem;">
                            <i class="ph ph-warning me-1" style="color:#f59e0b;"></i>
                            No registered users found for this role.
                        </small>
                    </div>

                    {{-- Temporary fields --}}
                    <div id="inlineTemporaryFields" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.83rem;">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="tempName" class="form-control"
                                style="border-radius:8px;" placeholder="Enter full name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.83rem;">Mobile Number</label>
                            <input type="text" id="tempPhone" class="form-control"
                                style="border-radius:8px;" placeholder="Phone number (optional)">
                        </div>
                    </div>

                    {{-- Notes for this entry --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.83rem;">Notes</label>
                        <input type="text" id="entryNotes" class="form-control"
                               style="border-radius:8px;font-size:0.88rem;"
                               placeholder="Optional notes for this staff member…">
                    </div>

                    {{-- Add to list button --}}
                    <button type="button" id="addToListBtn"
                            class="btn btn-outline-primary w-100" style="border-radius:8px;">
                        <i class="ph ph-plus-circle me-1"></i> Add to List
                    </button>
                </div>

                {{-- ── Queued Staff List ── --}}
                <div id="staffQueueSection" class="mt-3" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0" style="font-size:0.83rem;color:#374151;">
                            <i class="ph ph-users me-1" style="color:#7c3aed;"></i>
                            Staff to be Added
                            <span class="badge rounded-pill ms-1"
                                  style="background:#ede9fe;color:#6d28d9;font-size:0.75rem;"
                                  id="queueCount">0</span>
                        </label>
                    </div>
                    <div id="staffQueueList"
                         style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;"></div>
                </div>

            </div>

            <div class="modal-footer" style="border-top:1px solid #e2e8f0;background:#fafafa;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;">
                    <i class="ph ph-x me-1"></i>Cancel
                </button>
                <button type="button" id="saveAllStaffBtn"
                        class="btn btn-primary" style="border-radius:8px;" disabled>
                    <i class="ph ph-floppy-disk me-1"></i>
                    Save All Staff (<span id="saveCount">0</span>)
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// ═══════════════════════════════════════════════════════════
//  GLOBAL SCOPE — expose loadJobs so quickApproveStaff
//  can call it from outside $(document).ready()
// ═══════════════════════════════════════════════════════════
let loadJobs;   // declared globally, assigned inside ready()


// ── QUICK APPROVE / REJECT (global — called from row buttons) ──────────────
function quickApproveStaff(jobId, action) {
    const isApprove = action === 'approve';
    Swal.fire({
        title             : isApprove ? 'Approve Staff?' : 'Reject & Remove?',
        text              : isApprove
            ? 'Staff will be approved and the work order stays Completed.'
            : 'All pending staff will be removed. Work order stays Completed.',
        icon              : 'question',
        showCancelButton  : true,
        confirmButtonColor: isApprove ? '#10b981' : '#ef4444',
        confirmButtonText : isApprove ? 'Yes, Approve' : 'Yes, Reject',
        cancelButtonText  : 'Cancel',
    }).then(r => {
        if (!r.isConfirmed) return;

        // Show inline spinner on the row buttons
        $(`tr[data-job-id="${jobId}"] .quickApproveBtn`).prop('disabled', true);

        $.ajax({
            url    : `/jobs/${jobId}/staff/approve`,
            type   : 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data   : { action },
            success(res) {
                Swal.fire({
                    icon             : 'success',
                    title            : isApprove ? 'Approved!' : 'Rejected!',
                    text             : res.message,
                    timer            : 2000,
                    showConfirmButton: false,
                }).then(() => {
                    if (typeof loadJobs === 'function') loadJobs();
                });
            },
            error(xhr) {
                $(`tr[data-job-id="${jobId}"] .quickApproveBtn`).prop('disabled', false);
                Swal.fire('Error!', xhr.responseJSON?.message || 'Action failed', 'error');
            }
        });
    });
}


// ═══════════════════════════════════════════════════════════
//  DOCUMENT READY
// ═══════════════════════════════════════════════════════════
$(document).ready(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const addStaffModal  = new bootstrap.Modal(document.getElementById('addStaffInlineModal'));

    // ── Pre-loaded staff lists from controller ──────────────────────────────
    const allSupervisors = @json($supervisors ?? []);
    const allWorkers     = @json($workers ?? []);

    // ── Multi-entry builder state ───────────────────────────────────────────
    let selectedRole  = '';
    let selectedType  = 'registered';
    let currentJobId  = null;
    let staffQueue    = [];   // array of entry objects to be saved


    // ════════════════════════════════════════════════════════
    //  LOAD JOBS
    // ════════════════════════════════════════════════════════
    loadJobs = function (page = 1) {
        const data = {
            status   : $('#statusFilter').val(),
            search   : $('#searchInput').val(),
            date_from: $('#dateFrom').val(),
            date_to  : $('#dateTo').val(),
            page,
        };

        @if(auth()->user()->role === 'super_admin')
        data.branch_id = $('#branchFilter').val();
        @endif

        $('#jobsTableBody').addClass('table-loading');

        $.ajax({
            url    : "{{ route('jobs.completed') }}",
            type   : 'GET',
            data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success(res) {
                $('#jobsTableBody').html(res.html);
                $('#paginationContainer').html(res.pagination);
                $('#jobCount').text(res.total);
                $('#statPending').text(res.staff_pending);
                $('#statCompleted').text(res.total_completed);

                // Header pending badge
                if (res.staff_pending > 0) {
                    $('#pendingBadgeText').text(res.staff_pending);
                    $('#pendingBadge').show();
                } else {
                    $('#pendingBadge').hide();
                }

                // Tab pending count
                if (res.staff_pending > 0) {
                    $('#tabPendingCount').text(res.staff_pending).show();
                } else {
                    $('#tabPendingCount').hide();
                }

                $('#jobsTableBody').removeClass('table-loading');
            },
            error() {
                $('#jobsTableBody').removeClass('table-loading');
                Swal.fire('Error!', 'Failed to load data. Please try again.', 'error');
            }
        });
    };


    // ════════════════════════════════════════════════════════
    //  STATUS TABS
    // ════════════════════════════════════════════════════════
    $(document).on('click', '#viewModeToggle .tab-btn', function () {
        $('#viewModeToggle .tab-btn').removeClass('active');
        $(this).addClass('active');
        $('#statusFilter').val($(this).data('status'));
        loadJobs();
    });


    // ════════════════════════════════════════════════════════
    //  FILTER FORM
    // ════════════════════════════════════════════════════════
    $('#filterForm').on('submit', e => { e.preventDefault(); loadJobs(); });


    // ════════════════════════════════════════════════════════
    //  LIVE SEARCH
    // ════════════════════════════════════════════════════════
    let searchTimer;
    $('#searchInput').on('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadJobs(), 420);
    });


    // ════════════════════════════════════════════════════════
    //  DATE / BRANCH FILTERS
    // ════════════════════════════════════════════════════════
    $('#dateFrom, #dateTo, #branchFilter').on('change', () => loadJobs());


    // ════════════════════════════════════════════════════════
    //  RESET
    // ════════════════════════════════════════════════════════
    $('#resetBtn').on('click', () => {
        $('#searchInput').val('');
        $('#dateFrom, #dateTo').val('');
        $('#branchFilter').val('');
        $('#statusFilter').val('');
        $('#viewModeToggle .tab-btn').removeClass('active');
        $('#viewModeToggle .tab-btn[data-status=""]').addClass('active');
        loadJobs();
    });


    // ════════════════════════════════════════════════════════
    //  PAGINATION
    // ════════════════════════════════════════════════════════
    $(document).on('click', '#paginationContainer .pagination a', function (e) {
        e.preventDefault();
        const m = $(this).attr('href').match(/page=(\d+)/);
        if (m) loadJobs(parseInt(m[1]));
    });


    // ════════════════════════════════════════════════════════
    //  QUICK APPROVE / REJECT (delegated)
    // ════════════════════════════════════════════════════════
    $(document).on('click', '.quickApproveBtn', function () {
        quickApproveStaff($(this).data('id'), $(this).data('action'));
    });


    // ════════════════════════════════════════════════════════
    //  ADD STAFF MODAL — Multi-entry builder
    // ════════════════════════════════════════════════════════

    // ── Helper: render the queued staff list ────────────────
    function renderQueue() {
        const $list    = $('#staffQueueList');
        const $section = $('#staffQueueSection');
        const count    = staffQueue.length;

        $('#queueCount, #saveCount').text(count);
        $('#saveAllStaffBtn').prop('disabled', count === 0);

        if (count === 0) { $section.hide(); return; }
        $section.show();

        let html = '';
        staffQueue.forEach((entry, idx) => {
            const roleColor = entry.role === 'supervisor' ? '#2563eb' : '#7c3aed';
            const roleIcon  = entry.role === 'supervisor' ? 'ph-user-circle-gear' : 'ph-hard-hat';
            const typeIcon  = entry.staff_type === 'registered' ? 'ph-identification-card' : 'ph-user-circle-dashed';
            const typeColor = entry.staff_type === 'registered' ? '#7c3aed' : '#64748b';

            html += `<div class="d-flex align-items-center justify-content-between px-3 py-2"
                          style="border-bottom:1px solid #f1f5f9;background:${idx % 2 === 0 ? '#fff' : '#fafafa'}">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph ${roleIcon}" style="color:${roleColor};font-size:1.1rem;"></i>
                    <div>
                        <span class="fw-semibold" style="font-size:0.88rem;">${entry.display_name}</span>
                        <span class="badge ms-1" style="background:#ede9fe;color:#6d28d9;font-size:0.72rem;">
                            ${entry.role}
                        </span>
                        <span class="badge ms-1" style="background:#f1f5f9;color:#64748b;font-size:0.72rem;">
                            <i class="ph ${typeIcon}" style="color:${typeColor};"></i>
                            ${entry.staff_type}
                        </span>
                        ${entry.notes ? `<div style="font-size:0.75rem;color:#94a3b8;">${entry.notes}</div>` : ''}
                    </div>
                </div>
                <button type="button" class="removeQueueEntry"
                        data-idx="${idx}"
                        style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;
                               width:28px;height:28px;padding:0;display:flex;align-items:center;
                               justify-content:center;flex-shrink:0;cursor:pointer;">
                    <i class="ph ph-x" style="font-size:0.9rem;pointer-events:none;"></i>
                </button>
            </div>`;
        });
        $list.html(html);
    }

    // ── Helper: populate user dropdown ──────────────────────
    function populateUserDropdown(role) {
        const $select  = $('#inlineStaffUserId');
        const $hint    = $('#noStaffHint');
        const $wrapper = $('#userSelectWrapper');

        $select.html('<option value="">Loading…</option>');
        $wrapper.addClass('select-loading');

        const list = role === 'supervisor' ? allSupervisors : allWorkers;

        setTimeout(() => {
            $wrapper.removeClass('select-loading');
            if (!list || list.length === 0) {
                $select.html('<option value="">No users found for this role</option>');
                $hint.show();
            } else {
                let html = '<option value="">— Select a user —</option>';
                list.forEach(u => { html += `<option value="${u.id}">${u.name}</option>`; });
                $select.html(html);
                $hint.hide();
            }
        }, 300);
    }

    // ── Helper: reset builder fields (not the queue) ────────
    function resetEntryBuilder() {
        selectedRole = '';
        selectedType = 'registered';

        $('#typeCardRegistered').addClass('selected');
        $('#typeCardTemporary').removeClass('selected');
        $('#roleCardSupervisor, #roleCardWorker').removeClass('selected');

        $('#inlineRegisteredFields').show();
        $('#inlineTemporaryFields').hide();
        $('#inlineStaffUserId').html('<option value="">— Select a role first —</option>');
        $('#noStaffHint').hide();
        $('#tempName, #tempPhone, #entryNotes').val('');
    }

    // ── Open modal ──────────────────────────────────────────
    $(document).on('click', '.addStaffInlineBtn', function () {
        currentJobId = $(this).data('id');
        $('#modalJobInfo').text(`${$(this).data('code')} — ${$(this).data('title')}`);

        staffQueue = [];
        resetEntryBuilder();
        renderQueue();

        addStaffModal.show();
    });

    // ── Staff TYPE card click ────────────────────────────────
    $(document).on('click', '.staff-type-card[data-type]', function () {
        selectedType = $(this).data('type');
        $('.staff-type-card[data-type]').removeClass('selected');
        $(this).addClass('selected');

        if (selectedType === 'registered') {
            $('#inlineRegisteredFields').show();
            $('#inlineTemporaryFields').hide();
            if (selectedRole) populateUserDropdown(selectedRole);
        } else {
            $('#inlineRegisteredFields').hide();
            $('#inlineTemporaryFields').show();
        }
    });

    // ── Staff ROLE card click ────────────────────────────────
    $(document).on('click', '.staff-type-card[data-role]', function () {
        selectedRole = $(this).data('role');
        $('.staff-type-card[data-role]').removeClass('selected');
        $(this).addClass('selected');
        if (selectedType === 'registered') populateUserDropdown(selectedRole);
    });

    // ── ADD TO LIST button ───────────────────────────────────
    $('#addToListBtn').on('click', function () {
        if (!selectedRole) {
            Swal.fire('Missing Role', 'Please select Supervisor or Worker.', 'warning');
            return;
        }

        let entry = {
            role      : selectedRole,
            staff_type: selectedType,
            notes     : $('#entryNotes').val().trim(),
        };

        if (selectedType === 'registered') {
            const userId   = $('#inlineStaffUserId').val();
            const userName = $('#inlineStaffUserId option:selected').text();
            if (!userId) {
                Swal.fire('Missing User', 'Please select a user.', 'warning');
                return;
            }
            // Prevent duplicate
            if (staffQueue.find(e => e.staff_type === 'registered' && e.user_id == userId && e.role === selectedRole)) {
                Swal.fire('Duplicate', `${userName} is already in the list for this role.`, 'warning');
                return;
            }
            entry.user_id      = userId;
            entry.display_name = userName;
        } else {
            const name = $('#tempName').val().trim();
            const phone = $('#tempPhone').val().trim();
            if (!name) {
                Swal.fire('Missing Name', "Please enter the staff member's name.", 'warning');
                return;
            }
            entry.temp_name    = name;
            entry.temp_phone   = phone;
            entry.display_name = name;
        }

        staffQueue.push(entry);
        renderQueue();

        // Clear input fields only — keep role/type so user can add more quickly
        $('#inlineStaffUserId').val('');
        $('#tempName, #tempPhone, #entryNotes').val('');
    });

    // ── REMOVE entry from queue ──────────────────────────────
    $(document).on('click', '.removeQueueEntry', function () {
        staffQueue.splice($(this).data('idx'), 1);
        renderQueue();
    });

    // ── SAVE ALL ─────────────────────────────────────────────
    $('#saveAllStaffBtn').on('click', function () {
        if (staffQueue.length === 0) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'
        );

        $.ajax({
            url        : `/jobs/${currentJobId}/staff/bulk`,
            type       : 'POST',
            contentType: 'application/json',
            data       : JSON.stringify({ staff: staffQueue }),
            headers    : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success(res) {
                addStaffModal.hide();
                Swal.fire({
                    icon             : 'success',
                    title            : 'Staff Added!',
                    text             : res.message,
                    timer            : 2200,
                    showConfirmButton: false,
                }).then(() => loadJobs());
            },
            error(xhr) {
                $btn.prop('disabled', false).html(
                    `<i class="ph ph-floppy-disk me-1"></i>Save All Staff (<span id="saveCount">${staffQueue.length}</span>)`
                );
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to save staff.', 'error');
            }
        });
    });

});
</script>
@endsection
