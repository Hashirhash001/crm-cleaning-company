@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">System Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="las la-money-bill-wave"></i> Daily Budget Limit</h5>
            </div>
            <div class="card-body">
                <form id="budgetForm">
                    @csrf
                    <div class="mb-3">
                        <label for="daily_budget_limit" class="form-label">Daily Budget Limit (₹)</label>
                        <input type="number" class="form-control" id="daily_budget_limit"
                               name="daily_budget_limit" value="{{ $dailyBudget }}"
                               step="0.01" min="0" required>
                        <small class="text-muted">Maximum amount that can be approved per day</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save"></i> Update Budget Limit
                    </button>
                </form>
            </div>
        </div>

        <!-- Today's Budget Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="las la-chart-line"></i> Today's Budget Status</h5>
            </div>
            <div class="card-body">
                @php
                    $todayTotal = \App\Models\Lead::whereDate('approved_at', today())
                        ->where('status', 'approved')
                        ->sum('amount');
                    $remaining = $dailyBudget - $todayTotal;
                    $percentage = $dailyBudget > 0 ? ($todayTotal / $dailyBudget) * 100 : 0;
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Daily Limit:</strong></span>
                        <span class="text-primary">₹{{ number_format($dailyBudget, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Used Today:</strong></span>
                        <span class="text-danger">₹{{ number_format($todayTotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span><strong>Remaining:</strong></span>
                        <span class="text-success">₹{{ number_format($remaining, 2) }}</span>
                    </div>

                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-success') }}"
                             role="progressbar"
                             style="width: {{ min($percentage, 100) }}%"
                             aria-valuenow="{{ $percentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ number_format($percentage, 1) }}%
                        </div>
                    </div>
                </div>

                @if($percentage > 90)
                    <div class="alert alert-danger">
                        <i class="las la-exclamation-triangle"></i>
                        <strong>Warning!</strong> Budget is almost exhausted!
                    </div>
                @elseif($percentage > 70)
                    <div class="alert alert-warning">
                        <i class="las la-info-circle"></i>
                        Budget usage is high. Monitor approvals carefully.
                    </div>
                @endif
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

            $.ajax({
                url: '{{ route("settings.updateDailyBudget") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to update budget limit', 'error');
                }
            });
        });
    });
</script>
@endsection
