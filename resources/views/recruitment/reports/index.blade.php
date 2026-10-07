@extends('layouts.app')

@section('title', 'Recruitment Reports')

@section('extra-css')
<style>
    .report-card { transition: transform 0.2s; }
    .report-card:hover { transform: translateY(-2px); }
    .stat-number { font-size: 2rem; font-weight: 700; }
    .report-table th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Recruitment Reports</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Recruitment</li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="las la-filter me-2"></i>Report Filters</h5>
                <a href="{{ route('recruitment.reports') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="las la-redo me-1"></i> Reset
                </a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('recruitment.reports') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date From</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date To</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Department</label>
                            <select class="form-select" name="department_id">
                                <option value="">All</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Position</label>
                            <select class="form-select" name="position_id">
                                <option value="">All</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Reference</label>
                            <select class="form-select" name="reference">
                                <option value="">All</option>
                                @foreach(\App\Models\RecruitmentCandidate::REFERENCES as $ref)
                                    <option value="{{ $ref }}" {{ request('reference') == $ref ? 'selected' : '' }}>{{ $ref }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="las la-search me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Interview Status</label>
                            <select class="form-select" name="interview_status">
                                <option value="">All</option>
                                @foreach(\App\Models\RecruitmentCandidate::INTERVIEW_STATUSES as $s)
                                    <option value="{{ $s }}" {{ request('interview_status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Interview Result</label>
                            <select class="form-select" name="interview_result">
                                <option value="">All</option>
                                @foreach(\App\Models\RecruitmentCandidate::INTERVIEW_RESULTS as $r)
                                    <option value="{{ $r }}" {{ request('interview_result') == $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Joining Status</label>
                            <select class="form-select" name="joining_status">
                                <option value="">All</option>
                                @foreach(\App\Models\RecruitmentCandidate::JOINING_STATUSES as $j)
                                    <option value="{{ $j }}" {{ request('joining_status') == $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Decision Reason</label>
                            <select class="form-select" name="decision_reason">
                                <option value="">All</option>
                                @foreach(\App\Models\RecruitmentCandidate::DECISION_REASONS as $d)
                                    <option value="{{ $d }}" {{ request('decision_reason') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('recruitment.reports.export', request()->all()) }}" class="btn btn-outline-success w-100">
                                <i class="las la-file-excel me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Candidate Summary -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="las la-chart-bar me-2"></i>Candidate Summary</h5></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <div class="stat-number text-primary">{{ $summary['total'] }}</div>
                        <small class="text-muted fw-semibold">Total</small>
                    </div>
                    <div class="col">
                        <div class="stat-number text-success">{{ $summary['selected'] }}</div>
                        <small class="text-muted fw-semibold">Selected</small>
                    </div>
                    <div class="col">
                        <div class="stat-number text-danger">{{ $summary['rejected'] }}</div>
                        <small class="text-muted fw-semibold">Rejected</small>
                    </div>
                    <div class="col">
                        <div class="stat-number text-warning">{{ $summary['hold'] }}</div>
                        <small class="text-muted fw-semibold">Hold</small>
                    </div>
                    <div class="col">
                        <div class="stat-number text-info">{{ $summary['joined'] }}</div>
                        <small class="text-muted fw-semibold">Joined</small>
                    </div>
                    <div class="col">
                        <div class="stat-number" style="color: #fd7e14;">{{ $summary['pending'] }}</div>
                        <small class="text-muted fw-semibold">Pending</small>
                    </div>
                    <div class="col">
                        <div class="stat-number text-dark">{{ $summary['not_joined'] }}</div>
                        <small class="text-muted fw-semibold">Not Joined</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department-wise & Position-wise Reports -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card report-card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="las la-building me-2"></i>Department-wise Report</h5></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Department</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Selected</th>
                                <th class="text-center">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentReport as $dept)
                            <tr>
                                <td>{{ $dept->name }}</td>
                                <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $dept->total_candidates }}</span></td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $dept->selected_count }}</span></td>
                                <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $dept->joined_count }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card report-card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="las la-briefcase me-2"></i>Position-wise Report</h5></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Position</th>
                                <th>Department</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Selected</th>
                                <th class="text-center">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($positionReport as $pos)
                            <tr>
                                <td>{{ $pos->name }}</td>
                                <td><small>{{ optional($pos->department)->name ?? '—' }}</small></td>
                                <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $pos->total_candidates }}</span></td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $pos->selected_count }}</span></td>
                                <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $pos->joined_count }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interview Status, Joining Status & Reference Reports -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card report-card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="las la-clipboard-list me-2"></i>Interview Status</h5></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 report-table">
                        <thead class="table-light"><tr><th>Status</th><th class="text-center">Count</th></tr></thead>
                        <tbody>
                            @forelse($interviewStatusReport as $item)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ \App\Models\RecruitmentCandidate::interviewStatusBadge($item->interview_status) }}-subtle text-{{ \App\Models\RecruitmentCandidate::interviewStatusBadge($item->interview_status) }}">
                                        {{ $item->interview_status }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">{{ $item->count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card report-card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="las la-door-open me-2"></i>Joining Status</h5></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 report-table">
                        <thead class="table-light"><tr><th>Status</th><th class="text-center">Count</th></tr></thead>
                        <tbody>
                            @forelse($joiningStatusReport as $item)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ \App\Models\RecruitmentCandidate::joiningStatusBadge($item->joining_status) }}-subtle text-{{ \App\Models\RecruitmentCandidate::joiningStatusBadge($item->joining_status) }}">
                                        {{ $item->joining_status }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">{{ $item->count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card report-card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="las la-share-alt me-2"></i>Reference Source</h5></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 report-table">
                        <thead class="table-light"><tr><th>Source</th><th class="text-center">Count</th></tr></thead>
                        <tbody>
                            @forelse($referenceReport as $item)
                            <tr>
                                <td>{{ $item->reference }}</td>
                                <td class="text-center fw-bold">{{ $item->count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
