@extends('layouts.app')

@section('title', 'System Settings')

@section('extra-css')
<style>
    .settings-container {
        background: #f8f9fb;
        min-height: calc(100vh - 100px);
        padding: 2rem 0;
    }

    .settings-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        border: 1px solid #e8ecef;
        transition: all 0.3s;
    }

    .settings-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    }

    .settings-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e8ecef;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px 16px 0 0;
    }

    .settings-header h5 {
        color: #fff;
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .settings-header i {
        color: rgba(255,255,255,0.9);
    }

    .settings-body {
        padding: 2rem;
    }

    .budget-input-wrapper {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .budget-input-wrapper input {
        padding-left: 2.5rem;
        height: 56px;
        font-size: 1.1rem;
        font-weight: 600;
        border: 2px solid #e8ecef;
        border-radius: 12px;
        transition: all 0.3s;
    }

    .budget-input-wrapper input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .budget-currency-symbol {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        font-weight: 600;
        color: #667eea;
    }

    .btn-update-budget {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #fff;
        padding: 0.875rem 2rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    }

    .btn-update-budget:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    .status-metric-card {
        background: linear-gradient(135deg, #f8f9fb 0%, #fff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid #e8ecef;
        transition: all 0.3s;
    }

    .status-metric-card:hover {
        transform: translateY(-4px);
        border-color: #667eea;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.1);
    }

    .metric-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #8896ab;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }

    .usage-progress-wrapper {
        background: #f8f9fb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .progress-custom {
        height: 12px;
        border-radius: 20px;
        background: #e8ecef;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 20px;
        transition: width 0.6s ease;
        position: relative;
        overflow: hidden;
    }

    .progress-bar-custom::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .alert-modern i {
        font-size: 1.5rem;
    }

    .info-text {
        color: #8896ab;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h2 {
        color: #1e293b;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .page-header p {
        color: #64748b;
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="settings-container">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="las la-cog me-2"></i>System Settings</h2>
            <p>Manage your daily budget limits and monitor usage</p>
        </div>

        <div class="row g-4">
            <!-- Budget Configuration Card -->
            <div class="col-lg-5">
                <div class="settings-card">
                    <div class="settings-header">
                        <h5><i class="las la-money-bill-wave me-2"></i>Daily Budget Limit</h5>
                    </div>
                    <div class="settings-body">
                        <form id="budgetForm">
                            @csrf
                            <div class="budget-input-wrapper">
                                <span class="budget-currency-symbol">₹</span>
                                <input type="number"
                                       class="form-control"
                                       id="daily_budget_limit"
                                       name="daily_budget_limit"
                                       value="{{ $dailyBudget }}"
                                       step="0.01"
                                       min="0"
                                       required
                                       placeholder="Enter budget limit">
                            </div>

                            <p class="info-text">
                                <i class="las la-info-circle me-1"></i>
                                This is the maximum amount that can be approved per day across all branches
                            </p>

                            <button type="submit" class="btn btn-update-budget w-100">
                                <i class="las la-save me-2"></i>Update Budget Limit
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="settings-card mt-4">
                    <div class="settings-body">
                        <h6 class="mb-3 fw-semibold">Quick Information</h6>
                        {{-- <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <span class="text-muted">Current Date</span>
                            <strong>{{ now()->format('d M Y') }}</strong>
                        </div> --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <span class="text-muted">Total Approved Leads Today</span>
                            <strong class="text-success">{{ \App\Models\Lead::whereDate('approved_at', today())->where('status', 'approved')->count() }} Leads</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Budget Reset</span>
                            <strong>Daily at 12:00 AM</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Status Card -->
            <div class="col-lg-7">
                <div class="settings-card">
                    <div class="settings-header">
                        <h5><i class="las la-chart-line me-2"></i>Today's Budget Status</h5>
                    </div>
                    <div class="settings-body">
                        @php
                            $todayTotal = \App\Models\Lead::whereDate('approved_at', today())
                                ->where('status', 'approved')
                                ->sum('amount');
                            $remaining = $dailyBudget - $todayTotal;
                            $percentage = $dailyBudget > 0 ? ($todayTotal / $dailyBudget) * 100 : 0;
                        @endphp

                        <!-- Metric Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="status-metric-card">
                                    <div class="metric-label">Daily Limit</div>
                                    <div class="metric-value text-primary">₹{{ number_format($dailyBudget, 0) }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="status-metric-card">
                                    <div class="metric-label">Used Today</div>
                                    <div class="metric-value text-danger">₹{{ number_format($todayTotal, 0) }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="status-metric-card">
                                    <div class="metric-label">Remaining</div>
                                    <div class="metric-value text-success">₹{{ number_format($remaining, 0) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Progress -->
                        <div class="usage-progress-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-semibold">Budget Usage</h6>
                                <span class="badge bg-{{ $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success') }} px-3 py-2">
                                    {{ number_format($percentage, 1) }}%
                                </span>
                            </div>

                            <div class="progress-custom">
                                <div class="progress-bar-custom bg-{{ $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success') }}"
                                     style="width: {{ min($percentage, 100) }}%">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">0%</small>
                                <small class="text-muted">100%</small>
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        @if($percentage > 90)
                            <div class="alert alert-modern alert-danger mt-4">
                                <i class="las la-exclamation-triangle"></i>
                                <div>
                                    <strong>Critical Warning!</strong>
                                    <p class="mb-0 small">Daily budget is almost exhausted. Immediate action required!</p>
                                </div>
                            </div>
                        @elseif($percentage > 70)
                            <div class="alert alert-modern alert-warning mt-4">
                                <i class="las la-info-circle"></i>
                                <div>
                                    <strong>High Usage Alert</strong>
                                    <p class="mb-0 small">Budget usage is high. Please monitor approvals carefully.</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-modern alert-success mt-4">
                                <i class="las la-check-circle"></i>
                                <div>
                                    <strong>Budget Healthy</strong>
                                    <p class="mb-0 small">You have sufficient budget remaining for today.</p>
                                </div>
                            </div>
                        @endif
                    </div>
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

    $('#budgetForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-2"></i>Updating...');

        $.ajax({
            url: '{{ route("settings.updateDailyBudget") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    confirmButtonColor: '#667eea',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update budget limit',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });

    // Number formatting on input
    $('#daily_budget_limit').on('input', function() {
        let value = $(this).val();
        if(value) {
            // Optional: Add thousand separators as user types
            // This is just a visual enhancement
        }
    });
});
</script>
@endsection
