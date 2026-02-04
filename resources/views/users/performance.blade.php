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

    .leaderboard-table thead th {
        background: rgba(102, 126, 234, 0.08);
        color: #495057;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 15px;
        border: none;
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
        background: rgba(99, 102, 241, 0.08);
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
                        <div class="col-md-3">
                            <label class="form-label fw-bold">⏱️ Time Period</label>
                            <select class="form-select shadow-sm" id="periodSelect" style="border-radius: 10px;">
                                <option value="day">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="6months">Last 6 Months</option>
                                <option value="year">This Year</option>
                                <option value="last_year">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <div class="col-md-3" id="startDateDiv" style="display: none;">
                            <label class="form-label fw-bold">📅 Start Date</label>
                            <input type="date" class="form-control shadow-sm" id="startDate" style="border-radius: 10px;">
                        </div>

                        <div class="col-md-3" id="endDateDiv" style="display: none;">
                            <label class="form-label fw-bold">📅 End Date</label>
                            <input type="date" class="form-control shadow-sm" id="endDate" style="border-radius: 10px;">
                        </div>

                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100 shadow" id="applyFilter"
                                    style="border-radius: 10px; font-weight: bold; background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
                                <i class="las la-filter me-1"></i> Apply Filter
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
                    <div class="table-responsive">
                        <table class="table mb-0 leaderboard-table">
                            <thead>
                                <tr>
                                    <th width="80">Rank</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Branch</th>
                                    <th class="text-center">Leads Created</th>
                                    <th class="text-center">Converted</th>
                                    <th class="text-center">conversion Rate</th>
                                    <th class="text-center">Work orders Approved</th>
                                    <th class="text-end">💰 Leads</th>
                                    <th class="text-end">💰 Jobs</th>
                                    <th class="text-end">🏆 Total</th>
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
$(document).ready(function() {
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // Professional confetti effect
    function createProfessionalConfetti() {
        const colors = [
            '#667eea', '#764ba2', '#f093fb', '#4facfe',
            '#43e97b', '#fa709a', '#fee140', '#30cfd0'
        ];

        const confettiCount = 100;
        const container = $('#confettiContainer');

        for (let i = 0; i < confettiCount; i++) {
            const confetti = $('<div class="confetti-piece"></div>');

            const color = colors[Math.floor(Math.random() * colors.length)];
            const left = Math.random() * 100;
            const animationDuration = 2 + Math.random() * 2;
            const delay = Math.random() * 0.5;
            const rotation = Math.random() * 360;

            confetti.css({
                left: left + '%',
                background: color,
                opacity: 0.8 + Math.random() * 0.2,
                animationDuration: animationDuration + 's',
                animationDelay: delay + 's',
                transform: `rotate(${rotation}deg)`,
                borderRadius: Math.random() > 0.5 ? '50%' : '2px'
            });

            container.append(confetti);

            setTimeout(() => {
                confetti.remove();
            }, (animationDuration + delay) * 1000);
        }
    }

    // Show/hide custom dates
    $('#periodSelect').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#startDateDiv, #endDateDiv').slideDown(300);
        } else {
            $('#startDateDiv, #endDateDiv').slideUp(300);
        }
    });

    // Load performance data
    function loadPerformanceData() {
        const period = $('#periodSelect').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        $.ajax({
            url: '{{ route("users.performance.data") }}',
            type: 'GET',
            data: { period, start_date: startDate, end_date: endDate },
            success: function(response) {
                if (response.success) {
                    updateSummaryCards(response.summary);
                    updateTopPerformers(response.leaderboard.slice(0, 3));
                    updateLeaderboardTable(response.leaderboard);
                    createProfessionalConfetti();
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to load performance data',
                    confirmButtonColor: '#667eea'
                });
            }
        });
    }

    // Update summary cards
    function updateSummaryCards(summary) {
        const html = `
            <div class="col-md-3 fade-in-up">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Total Leads</p>
                                <h2 class="mb-0 fw-bold counter">${summary.total_leads_created}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-user-plus text-primary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.1s;">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Converted</p>
                                <h2 class="mb-0 fw-bold counter">${summary.total_leads_converted}</h2>
                                <small class="text-success fw-bold">${summary.avg_conversion_rate.toFixed(1)}% avg</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-check-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.2s;">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Jobs Approved</p>
                                <h2 class="mb-0 fw-bold counter">${summary.total_jobs_approved}</h2>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-briefcase text-info" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay: 0.3s;">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Total Value</p>
                                <h2 class="mb-0 fw-bold counter text-warning">₹${formatNumber(summary.total_value)}</h2>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-coins text-warning" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#summaryCards').html(html);
    }

    // Update top 3 podium
    function updateTopPerformers(topUsers) {
        if (topUsers.length === 0) {
            $('#topPerformersContainer').html(`
                <div class="col-12 text-center py-5">
                    <div class="glass-card p-5">
                        <i class="las la-trophy" style="font-size: 4rem; color: #ccc;"></i>
                        <p class="text-muted mt-3 mb-0">No performance data yet</p>
                    </div>
                </div>
            `);
            return;
        }

        let html = '';
        const medals = ['🥇', '🥈', '🥉'];

        topUsers.forEach((user) => {
            html += `
                <div class="col-lg-4 col-md-6 mb-3 winner-card">
                    <div class="card glass-card border-0 h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <span class="medal-emoji">${medals[user.rank - 1]}</span>
                            </div>
                            <div class="rank-badge rank-${user.rank} mx-auto mb-3">
                                <span style="font-weight: 900;">#${user.rank}</span>
                            </div>

                            <h5 class="fw-bold mb-1">${user.name}</h5>
                            <p class="text-muted small mb-3">
                                <span class="badge bg-light text-dark">${formatRole(user.role)}</span>
                                <span class="badge bg-light text-dark ms-1">${user.branch}</span>
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="p-2 rounded" style="background: rgba(13, 110, 253, 0.1);">
                                        <h6 class="mb-0 fw-bold text-primary counter">${user.leads_created}</h6>
                                        <small class="text-muted" style="font-size: 0.7rem;">Leads Created</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded" style="background: rgba(25, 135, 84, 0.1);">
                                        <h6 class="mb-0 fw-bold text-success counter">${user.leads_converted}</h6>
                                        <small class="text-muted" style="font-size: 0.7rem;">Leads Converted</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded" style="background: rgba(13, 202, 240, 0.1);">
                                        <h6 class="mb-0 fw-bold text-info counter">${user.jobs_approved}</h6>
                                        <small class="text-muted" style="font-size: 0.7rem;">Work Orders Approved</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Conversion Rate</small>
                                    <span class="badge bg-success">${user.conversion_rate}%</span>
                                </div>
                                <div class="progress progress-thin">
                                    <div class="progress-bar progress-bar-gradient"
                                         style="width: ${Math.min(user.conversion_rate, 100)}%"
                                         role="progressbar"></div>
                                </div>
                            </div>

                            <div class="value-highlight">
                                <small class="text-muted d-block mb-1">Total Value</small>
                                <h4 class="mb-0 fw-bold counter" style="color: #f59e0b;">
                                    ₹${formatNumber(user.total_value)}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#topPerformersContainer').html(html);
    }

    // Update leaderboard table
    function updateLeaderboardTable(leaderboard) {
        $('#userCount').text(leaderboard.length);

        if (leaderboard.length === 0) {
            $('#leaderboardTableBody').html(`
                <tr><td colspan="11" class="text-center py-5 text-muted">
                    <i class="las la-inbox" style="font-size: 3rem;"></i>
                    <p>No data available</p>
                </td></tr>
            `);
            return;
        }

        let html = '';
        leaderboard.forEach(user => {
            const badgeClass = user.conversion_rate >= 50 ? 'bg-success' :
                              (user.conversion_rate >= 25 ? 'bg-warning' : 'bg-danger');

            html += `
                <tr>
                    <td>
                        <div class="rank-badge rank-${user.rank <= 3 ? user.rank : 'other'}"
                             style="width: 45px; height: 45px; font-size: 1rem;">
                            ${user.rank}
                        </div>
                    </td>
                    <td>
                        <a href="{{ url('/users') }}/${user.id}" class="text-decoration-none fw-bold">
                            ${user.name}
                        </a>
                        <br><small class="text-muted">${user.email}</small>
                    </td>
                    <td><span class="badge bg-secondary">${formatRole(user.role)}</span></td>
                    <td>${user.branch}</td>
                    <td class="text-center">
                        <span class="badge bg-primary counter">${user.leads_created}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success counter">${user.leads_converted}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${badgeClass}">${user.conversion_rate}%</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info counter">${user.jobs_approved}</span>
                    </td>
                    <td class="text-end counter">₹${formatNumber(user.leads_value)}</td>
                    <td class="text-end counter">₹${formatNumber(user.jobs_value)}</td>
                    <td class="text-end">
                        <strong class="text-warning counter">₹${formatNumber(user.total_value)}</strong>
                    </td>
                </tr>
            `;
        });

        $('#leaderboardTableBody').html(html);
    }

    // Helper functions
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function formatRole(role) {
        return role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Apply filter
    $('#applyFilter').on('click', loadPerformanceData);

    // Initial load
    loadPerformanceData();
});
</script>
@endsection
