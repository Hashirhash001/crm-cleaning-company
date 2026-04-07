@extends('layouts.app')

@section('title', 'Performance Leaderboard')

@section('extra-css')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* Clean gradient background */
    body {
        background: #f7f9fc;
        position: relative;
        min-height: 100vh;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.08), transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.08), transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(139, 92, 246, 0.08), transparent 50%);
        pointer-events: none;
        z-index: 0;
    }

    .content-wrapper {
        position: relative;
        z-index: 1;
    }

    /* Professional glassmorphism cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    /* Professional rank badges */
    .rank-badge {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.5rem;
        position: relative;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }

    .rank-badge:hover {
        transform: scale(1.1) rotate(5deg);
    }

    /* Winner badges */
    .rank-1 {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FFD700 100%);
        color: #fff;
        box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
    }

    .rank-1::after {
        content: '👑';
        position: absolute;
        top: -35px;
        font-size: 2.5rem;
        animation: crownFloat 2s ease-in-out infinite;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.3));
    }

    .rank-2 {
        background: linear-gradient(135deg, #E8E8E8 0%, #C0C0C0 50%, #E8E8E8 100%);
        color: #555;
        box-shadow: 0 10px 30px rgba(192, 192, 192, 0.4);
    }

    .rank-3 {
        background: linear-gradient(135deg, #E9967A 0%, #CD7F32 50%, #E9967A 100%);
        color: #fff;
        box-shadow: 0 10px 30px rgba(205, 127, 50, 0.4);
    }

    .rank-other {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #555;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }

    @keyframes crownFloat {
        0%, 100% {
            transform: translateY(0px) rotate(-5deg);
            opacity: 1;
        }
        50% {
            transform: translateY(-8px) rotate(5deg);
            opacity: 0.9;
        }
    }

    /* Winner cards animation */
    .winner-card {
        animation: slideInScale 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .winner-card:nth-child(1) { animation-delay: 0.1s; }
    .winner-card:nth-child(2) { animation-delay: 0.2s; }
    .winner-card:nth-child(3) { animation-delay: 0.3s; }

    @keyframes slideInScale {
        0% {
            opacity: 0;
            transform: translateY(30px) scale(0.9);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Stat cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.12);
    }

    .stat-icon {
        transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.15) rotate(5deg);
    }

    /* Progress bar */
    .progress-thin {
        height: 8px;
        background: rgba(0,0,0,0.05);
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar-gradient {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 1s ease-in-out;
    }

    /* Table styling */
    .leaderboard-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .leaderboard-table tbody tr {
        background: white;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .leaderboard-table tbody tr:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05), rgba(255, 255, 255, 1));
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
        transform: translateY(-2px);
    }

    .leaderboard-table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
        border: none;
        border-top: 8px solid transparent;
    }

    /* First row - no top border */
    .leaderboard-table tbody tr:first-child td {
        border-top: none;
    }

    /* Last row - rounded bottom corners */
    .leaderboard-table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 20px;
    }

    .leaderboard-table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 20px;
    }

    /* Badge effects */
    .badge {
        transition: all 0.2s ease;
        font-weight: 600;
        padding: 0.4em 0.8em;
    }

    .badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Professional confetti */
    .confetti-piece {
        position: fixed;
        width: 8px;
        height: 12px;
        top: -10px;
        z-index: 9999;
        animation: confetti-fall linear forwards;
        pointer-events: none;
    }

    @keyframes confetti-fall {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(720deg);
            opacity: 0;
        }
    }

    /* Page title */
    .page-title-box {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        padding: 24px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        backdrop-filter: blur(10px);
    }

    .page-title {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800;
        margin: 0;
    }

    /* Main page title */
    .page-title {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800;
        margin: 0;
    }

    /* Top Performers heading */
    .text-center h3 {
        color: #1e293b !important;
        text-shadow: none !important;
    }

    /* Subtitle text */
    .text-white-50 {
        color: #64748b !important;
    }

    /* Breadcrumb */
    .breadcrumb {
        color: #475569;
    }

    .breadcrumb-item a {
        color: #4f46e5;
    }

    .breadcrumb-item.active {
        color: #64748b;
    }

    /* Table header text */
    .leaderboard-table thead th {
        background: #f0f1ff !important;
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.1);
        position: sticky;
        top: 0;
        z-index: 2;
        color: #1e293b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 15px;
        border: none;
    }

    /* Card title in table header */
    .card-title {
        color: #1e293b !important;
    }

    /* Glass cards - ensure text is dark */
    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(99, 102, 241, 0.1);
        box-shadow: 0 8px 32px rgba(99, 102, 241, 0.08);
        border-radius: 20px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: #1e293b;
    }

    /* Stat cards text */
    .stat-card {
        background: white;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.05);
        color: #1e293b;
    }

    /* Filter card */
    .filter-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.08);
        border: none;
        color: #1e293b;
    }

    /* Page title box */
    .page-title-box {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        padding: 24px !important;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.08);
        backdrop-filter: blur(10px);
    }

    /* Table body text */
    .leaderboard-table tbody tr {
        background: white;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.04);
        color: #1e293b;
    }

    /* Muted text */
    .text-muted {
        color: #64748b !important;
    }

    /* Remove white text colors that were for dark background */
    .text-white {
        color: #1e293b !important;
    }

    /* Loading text */
    .spinner-professional + p {
        color: #64748b !important;
    }

    /* Form labels */
    .form-label {
        color: #334155;
    }

    /* Update button gradient to match theme */
    #applyFilter {
        background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
    }

    /* Winner card text adjustments */
    .winner-card .glass-card {
        background: rgba(255, 255, 255, 0.98);
        color: #1e293b;
    }

    /* Ensure all headings are dark */
    h1, h2, h3, h4, h5, h6 {
        color: #1e293b;
    }


    /* Trophy */
    .trophy-icon {
        display: inline-block;
    }

    @keyframes trophyBounce {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }

    /* Filter card */
    .filter-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
    }

    /* Loading spinner */
    .spinner-professional {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(102, 126, 234, 0.2);
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Medal pulse */
    .medal-emoji {
        font-size: 3rem;
        display: inline-block;
        animation: medalPulse 2s ease-in-out infinite;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }

    @keyframes medalPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    /* Value highlight */
    .value-highlight {
        background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 165, 0, 0.1));
        border-radius: 12px;
        padding: 16px;
        border: 2px solid rgba(255, 215, 0, 0.3);
    }

    /* Fade in animation */
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .rank-badge {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        .rank-1::after {
            font-size: 2rem;
            top: -30px;
        }
    }

    .leaderboard-table tbody tr:first-child td {
        border-top: none;
        padding-top: 25px;
    }

</style>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Confetti container -->
    <div id="confettiContainer"></div>

    <!-- Page Title -->
    <div class="row mb-4 mt-4">
        <div class="col-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">
                    <i class="las la-trophy trophy-icon me-2" style="font-size: 2rem;"></i>
                    Performance Leaderboard
                </h4>
                <ol class="breadcrumb mb-0" style="background: transparent;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Performance</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card border-0">
                <div class="card-body">
                    <div class="row align-items-end g-3">

                        {{-- Period --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Time Period</label>
                            <select class="form-select shadow-sm" id="periodSelect" style="border-radius:10px;">
                                <option value="day">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="lastmonth">Last Month</option>
                                <option value="6months">Last 6 Months</option>
                                <option value="year">This Year</option>
                                <option value="lastyear">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        {{-- Custom date range --}}
                        <div class="col-md-2" id="startDateDiv" style="display:none;">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" class="form-control shadow-sm" id="startDate" style="border-radius:10px;">
                        </div>
                        <div class="col-md-2" id="endDateDiv" style="display:none;">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" class="form-control shadow-sm" id="endDate" style="border-radius:10px;">
                        </div>

                        {{-- Branch filter (super_admin & lead_manager both see all) --}}
                        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Branch</label>
                            <select class="form-select shadow-sm" id="branchFilter" style="border-radius:10px;">
                                <option value="">All Branches</option>
                                @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Role filter --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Role</label>
                            <select class="form-select shadow-sm" id="roleFilter" style="border-radius:10px;">
                                <option value="">All Roles</option>
                                <option value="telecallers">Telecallers</option>
                                <option value="field_staff">Field Staff</option>
                                <option value="supervisor">Supervisors</option>
                                <option value="worker">Workers</option>
                            </select>
                        </div>

                        {{-- Job Status Filter --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Job Status</label>
                            <select class="form-select shadow-sm" id="jobStatusFilter" style="border-radius:10px;">
                                <option value="both">Approved + Completed</option>
                                <option value="approved">Approved Only</option>
                                <option value="completed">Completed Only</option>
                            </select>
                        </div>

                        {{-- Apply --}}
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-primary w-100 shadow" id="applyFilter"
                                    style="border-radius:10px;font-weight:bold;
                                        background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                                <i class="las la-filter me-1"></i>Apply Filter
                            </button>
                        </div>

                        {{-- Export CSV --}}
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-success w-100 shadow" id="exportCsvBtn"
                                    style="border-radius:10px;font-weight:bold;">
                                <i class="las la-file-csv me-1"></i>Export CSV
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Summary Cards -->
    <div class="row mb-4" id="summaryCards">
        <!-- Populated via AJAX -->
    </div>

    <!-- Top 3 Podium -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <i class="las la-medal" style="font-size: 2rem; vertical-align: middle;"></i>
                    Top Performers
                </h3>
                <p class="text-white-50">Celebrating excellence and dedication</p>
            </div>
        </div>
        <div class="col-12">
            <div class="row justify-content-center" id="topPerformersContainer">
                <div class="col-12 text-center py-5">
                    <div class="spinner-professional mx-auto"></div>
                    <p class="text-white mt-3">Loading champions...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Leaderboard -->
    <div class="row">
        <div class="col-12">
            <div class="card glass-card border-0 shadow-lg">
                <div class="card-header border-0" style="background: rgba(255,255,255,0.3); border-radius: 16px 16px 0 0;">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="las la-list me-2"></i>
                        Full Rankings (<span id="userCount" class="counter">0</span> Competitors)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-x:auto; max-height:600px; overflow-y:auto;">
                        <table class="table mb-0 leaderboard-table" style="min-width:900px;">
                            <thead style="position:sticky;top:0;z-index:2;">
                                <tr>
                                    <th style="width:60px">Rank</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Branch</th>
                                    <th class="text-center">Leads Created</th>
                                    <th class="text-center">Converted</th>
                                    <th class="text-center">Conv. Rate</th>
                                    <th class="text-center">Jobs Done</th>
                                    <th class="text-end">Jobs Value</th>
                                    <th class="text-end">Addon Value</th>
                                    <th class="text-end">Total Value</th>
                                </tr>
                            </thead>
                            <tbody id="leaderboardTableBody">
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="spinner-professional mx-auto"></div>
                                        <p class="text-muted mt-2">Loading rankings...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Confetti ────────────────────────────────────────────────────────────
    function createProfessionalConfetti() {
        const colors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a'];
        for (let i = 0; i < 80; i++) {
            const el = $('<div class="confetti-piece">');
            el.css({
                left             : Math.random() * 100 + '%',
                background       : colors[Math.floor(Math.random() * colors.length)],
                opacity          : 0.7 + Math.random() * 0.3,
                animationDuration: (2 + Math.random() * 2) + 's',
                animationDelay   : Math.random() * 0.5 + 's',
                transform        : `rotate(${Math.random() * 360}deg)`,
                borderRadius     : Math.random() > 0.5 ? '50%' : '2px',
            });
            $('#confettiContainer').append(el);
            setTimeout(() => el.remove(), 4500);
        }
    }

    // ── Show/hide custom date range ─────────────────────────────────────────
    $('#periodSelect').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#startDateDiv, #endDateDiv').slideDown(300);
        } else {
            $('#startDateDiv, #endDateDiv').slideUp(300);
        }
    });

    // ── Helpers ─────────────────────────────────────────────────────────────
    function formatNumber(n) {
        return (n || 0).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatRole(role) {
        const map = {
            telecallers: 'Telecaller',
            field_staff: 'Field Staff',
            supervisor : 'Supervisor',
            worker     : 'Worker',
        };
        return map[role] || role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function renderStars(rating) {
        if (!rating) return '<span class="text-muted">—</span>';
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="las la-star" style="color:${i <= Math.round(rating) ? '#f59e0b' : '#e2e8f0'};font-size:0.85rem;"></i>`;
        }
        return `<span title="${rating}/5">${stars} <small class="text-muted">${rating}</small></span>`;
    }

    // ── Load performance data ───────────────────────────────────────────────
    function loadPerformanceData() {
        const data = {
            period    : $('#periodSelect').val(),
            start_date: $('#startDate').val(),
            end_date  : $('#endDate').val(),
            branch_id : $('#branchFilter').val() || '',
            role_filter: $('#roleFilter').val() || '',
            job_status  : $('#jobStatusFilter').val() || 'both',
        };

        $('#topPerformersContainer').html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-3">Loading champions…</p>
            </div>`);
        $('#leaderboardTableBody').html(`
            <tr><td colspan="11" class="text-center py-5">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-2">Loading rankings…</p>
            </td></tr>`);

        $.ajax({
            url    : "{{ route('users.performance.data') }}",
            type   : 'GET',
            data,
            success(res) {
                if (!res.success) return;
                updateSummaryCards(res.summary);
                updateTopPerformers(res.leaderboard.slice(0, 3));
                updateLeaderboardTable(res.leaderboard);
                if (res.leaderboard.length > 0) createProfessionalConfetti();
            },
            error() {
                Swal.fire({ icon: 'error', title: 'Oops…', text: 'Failed to load performance data' });
            }
        });
    }

    // ── Export CSV ──────────────────────────────────────────────────────────
    $('#exportCsvBtn').on('click', function () {
        const params = new URLSearchParams({
            period     : $('#periodSelect').val(),
            start_date : $('#startDate').val(),
            end_date   : $('#endDate').val(),
            branch_id  : $('#branchFilter').val() || '',
            role_filter: $('#roleFilter').val() || '',
            job_status  : $('#jobStatusFilter').val() || 'both',
            export     : 'csv',
        });
        window.location.href = "{{ route('users.performance.data') }}?" + params.toString();
    });

    // ── Summary cards ───────────────────────────────────────────────────────
    function updateSummaryCards(summary) {
        const html = `
            <div class="col-md-3 fade-in-up">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Total Leads</p>
                                <h2 class="mb-0 fw-bold">${summary.total_leads_created}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-user-plus text-primary" style="font-size:2rem"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay:.1s">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Converted</p>
                                <h2 class="mb-0 fw-bold">${summary.total_leads_converted}</h2>
                                <small class="text-success fw-bold">${(summary.avg_conversion_rate || 0).toFixed(1)}% avg</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-check-circle text-success" style="font-size:2rem"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay:.2s">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Jobs Completed</p>
                                <h2 class="mb-0 fw-bold">${summary.total_jobs_approved}</h2>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-briefcase text-info" style="font-size:2rem"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay:.3s">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Total Value</p>
                                <h2 class="mb-0 fw-bold text-warning">${formatNumber(summary.total_value)}</h2>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-coins text-warning" style="font-size:2rem"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        $('#summaryCards').html(html);
    }

    // ── Top 3 Podium ────────────────────────────────────────────────────────
    function updateTopPerformers(topUsers) {
        if (!topUsers || topUsers.length === 0) {
            $('#topPerformersContainer').html(`
                <div class="col-12 text-center py-5">
                    <div class="glass-card p-5 d-inline-block">
                        <i class="las la-trophy" style="font-size:4rem;color:#ccc"></i>
                        <p class="text-muted mt-3 mb-0">No performance data yet</p>
                    </div>
                </div>`);
            return;
        }

        const medals = ['🥇', '🥈', '🥉'];

        let html = '';
        topUsers.forEach(user => {
            const rankClass   = user.rank <= 3 ? `rank-${user.rank}` : 'rank-other';
            const medalEmoji  = medals[user.rank - 1] ?? '';
            const isStaff     = ['supervisor', 'worker'].includes(user.role);

            html += `
                <div class="col-lg-4 col-md-6 mb-3 winner-card">
                    <div class="card glass-card border-0 h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-2"><span class="medal-emoji">${medalEmoji}</span></div>
                            <div class="rank-badge ${rankClass} mx-auto mb-3">
                                <span style="font-weight:900">${user.rank}</span>
                            </div>
                            <h5 class="fw-bold mb-1">${user.name}</h5>
                            <p class="text-muted small mb-3">
                                <span class="badge bg-light text-dark">${formatRole(user.role)}</span>
                                <span class="badge bg-light text-dark ms-1">${user.branch}</span>
                            </p>
                            <div class="row g-2 mb-3 justify-content-center">
                                ${!isStaff ? `
                                <div class="col-4">
                                    <div class="p-2 rounded" style="background:rgba(13,110,253,0.1)">
                                        <h6 class="mb-0 fw-bold text-primary">${user.leads_created}</h6>
                                        <small class="text-muted" style="font-size:0.7rem">Leads</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded" style="background:rgba(25,135,84,0.1)">
                                        <h6 class="mb-0 fw-bold text-success">${user.leads_converted}</h6>
                                        <small class="text-muted" style="font-size:0.7rem">Converted</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded" style="background:rgba(13,202,240,0.1)">
                                        <h6 class="mb-0 fw-bold text-info">${user.jobs_approved}</h6>
                                        <small class="text-muted" style="font-size:0.7rem">Jobs Done</small>
                                    </div>
                                </div>` : `
                                <div class="col-6">
                                    <div class="p-2 rounded" style="background:rgba(13,202,240,0.1)">
                                        <h6 class="mb-0 fw-bold text-info">${user.jobs_approved}</h6>
                                        <small class="text-muted" style="font-size:0.7rem">Jobs Done</small>
                                    </div>
                                </div>`}
                                <div class="col-6">
                                    <div class="p-2 rounded" style="background:rgba(245,158,11,0.1)">
                                        <small class="text-muted d-block" style="font-size:0.7rem">Addon Value</small>
                                        <small class="fw-bold ${user.addon_value > 0 ? 'text-warning' : 'text-muted'}">
                                            ${formatNumber(user.addon_value)}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="value-highlight">
                                <small class="text-muted d-block mb-1">Total Value</small>
                                <h4 class="mb-0 fw-bold" style="color:#f59e0b">${formatNumber(user.total_value)}</h4>
                            </div>
                        </div>
                    </div>
                </div>`;
        });

        $('#topPerformersContainer').html(html);
    }

    // ── Full leaderboard table ───────────────────────────────────────────────
    function updateLeaderboardTable(leaderboard) {
        $('#userCount').text(leaderboard.length);

        if (!leaderboard || leaderboard.length === 0) {
            $('#leaderboardTableBody').html(`
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="las la-inbox" style="font-size:3rem;color:#ccc"></i>
                        <p class="text-muted mt-2 mb-0">No activity data for this period</p>
                    </td>
                </tr>`);
            return;
        }

        const roleConfig = {
            'super_admin'  : { bg: '#1e40af', light: '#eff6ff', icon: 'la-shield-alt', label: 'Super Admin'  },
            'lead_manager' : { bg: '#065f46', light: '#ecfdf5', icon: 'la-user-tie',   label: 'Lead Manager' },
            'telecallers' : { bg: '#92400e', light: '#fffbeb', icon: 'la-phone',      label: 'Telecaller'   },
            'field_staff'  : { bg: '#1d4ed8', light: '#eff6ff', icon: 'la-hard-hat',   label: 'Field Staff'  },
            'supervisor'  : { bg: '#6d28d9', light: '#f5f3ff', icon: 'la-user-cog',   label: 'Supervisor'   },
            'worker'      : { bg: '#be185d', light: '#fdf2f8', icon: 'la-tools',      label: 'Worker'       },
        };

        function roleBadge(role) {
            const rc = roleConfig[role] || { bg: '#475569', light: '#f1f5f9', icon: 'la-user', label: role };
            return `<span style="
                display:inline-flex;align-items:center;gap:0.3rem;
                background:${rc.light};color:${rc.bg};
                border:1px solid ${rc.bg}22;
                padding:0.25rem 0.65rem;border-radius:999px;
                font-size:0.75rem;font-weight:600;white-space:nowrap;">
                <i class="las ${rc.icon}" style="font-size:0.85rem;"></i>${rc.label}
            </span>`;
        }

        let html = '';
        leaderboard.forEach(user => {
            const isStaff   = ['supervisor', 'worker'].includes(user.role);
            const convBadge = user.conversion_rate >= 50 ? 'bg-success'
                            : user.conversion_rate >= 25 ? 'bg-warning' : 'bg-danger';

            const rankHtml = `<div class="rank-badge ${user.rank <= 3 ? 'rank-' + user.rank : 'rank-other'}"
                style="width:40px;height:40px;font-size:0.95rem;">
                <span style="font-weight:900">${user.rank}</span>
            </div>`;

            html += `<tr>
                <td>${rankHtml}</td>
                <td>
                    <a href="/users/${user.id}" class="text-decoration-none fw-bold">${user.name}</a>
                    <br><small class="text-muted">${user.email}</small>
                </td>
                <td>${roleBadge(user.role)}</td>
                <td>${user.branch}</td>
                <td class="text-center">
                    ${!isStaff ? `<span class="badge bg-primary">${user.leads_created}</span>` : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-center">
                    ${!isStaff ? `<span class="badge bg-success">${user.leads_converted}</span>` : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-center">
                    ${!isStaff ? `<span class="badge ${convBadge}">${user.conversion_rate}%</span>` : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-center">
                    <span class="badge bg-info">${user.jobs_approved}</span>
                </td>
                <td class="text-end">${formatNumber(user.jobs_value)}</td>
                <td class="text-end">
                    <span class="${user.addon_value > 0 ? 'fw-semibold text-warning' : 'text-muted'}">
                        ${formatNumber(user.addon_value)}
                    </span>
                </td>
                <td class="text-end">
                    <strong class="text-warning">${formatNumber(user.total_value)}</strong>
                </td>
            </tr>`;
        });

        $('#leaderboardTableBody').html(html);
    }

    // ── Events ──────────────────────────────────────────────────────────────
    $('#applyFilter').on('click', loadPerformanceData);

    // Initial load
    loadPerformanceData();
});
</script>
@endsection
