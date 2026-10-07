@extends('layouts.app')

@section('title', 'Candidates')

@section('extra-css')
<style>
    /* Prevent overall page horizontal scrollbar */
    html, body, .page-wrapper, .page-content, .container-fluid {
        max-width: 100vw;
        overflow-x: hidden;
    }

    .sortable { cursor: pointer; user-select: none; position: relative; padding-right: 20px !important; }
    .sortable:hover { background-color: #e9ecef; }
    .sortable::after { content: '⇅'; position: absolute; right: 8px; opacity: 0.3; font-size: 14px; }
    .sortable.asc::after { content: '↑'; opacity: 1; color: #0d6efd; }
    .sortable.desc::after { content: '↓'; opacity: 1; color: #0d6efd; }
    .table-loading { opacity: 0.6; pointer-events: none; }
    .stat-card { transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }

    /* Candidate table internal horizontal scroll */
    .card-body .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 0;
    }
    #candidateTable {
        white-space: nowrap;
        font-size: 0.85rem;
        width: 100%;
    }
    #candidateTable th, #candidateTable td {
        padding: 0.5rem 0.6rem;
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Candidates</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Recruitment</li>
                    <li class="breadcrumb-item active">Candidates</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- Success / Error Messages --}}
@if(session('success'))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="las la-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
@endif

<!-- Statistics Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary-subtle text-primary me-3">
                    <i class="las la-users"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold" id="statTotal">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Total Candidates</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-success-subtle text-success me-3">
                    <i class="las la-user-check"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-success" id="statSelected">{{ $stats['selected'] }}</h3>
                    <small class="text-muted">Selected</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-info-subtle text-info me-3">
                    <i class="las la-user-plus"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-info" id="statJoined">{{ $stats['joined'] }}</h3>
                    <small class="text-muted">Joined</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-warning-subtle text-warning me-3">
                    <i class="las la-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-warning" id="statPending">{{ $stats['pending'] }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchCandidate" placeholder="Search by name, phone or email...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Department</label>
                        <select class="form-select" id="filterDept">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Position</label>
                        <select class="form-select" id="filterPosition">
                            <option value="">All Positions</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Interview Status</label>
                        <select class="form-select" id="filterInterviewStatus">
                            <option value="">All</option>
                            @foreach(\App\Models\RecruitmentCandidate::INTERVIEW_STATUSES as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Joining Status</label>
                        <select class="form-select" id="filterJoiningStatus">
                            <option value="">All</option>
                            @foreach(\App\Models\RecruitmentCandidate::JOINING_STATUSES as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-secondary w-100" id="resetFilters" title="Reset Filters">
                            <i class="las la-redo"></i>
                        </button>
                    </div>
                </div>
                <!-- Additional filters row -->
                <div class="row g-3 mt-1">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Interview Result</label>
                        <select class="form-select" id="filterInterviewResult">
                            <option value="">All</option>
                            @foreach(\App\Models\RecruitmentCandidate::INTERVIEW_RESULTS as $result)
                                <option value="{{ $result }}">{{ $result }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Reference</label>
                        <select class="form-select" id="filterReference">
                            <option value="">All</option>
                            @foreach(\App\Models\RecruitmentCandidate::REFERENCES as $ref)
                                <option value="{{ $ref }}">{{ $ref }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Candidates Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title mb-0">
                    Candidates (<span id="candidateCount">{{ $candidates->total() }}</span> total)
                </h4>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-danger d-none" id="bulkDeleteBtn">
                        <i class="las la-trash me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                            <li>
                                <a class="dropdown-item exportOption" href="#" data-format="xlsx">
                                    <i class="las la-file-excel text-success me-2 fs-16"></i> Excel (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item exportOption" href="#" data-format="csv">
                                    <i class="las la-file-csv text-primary me-2 fs-16"></i> CSV (.csv)
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('recruitment.candidates.create') }}" class="btn btn-primary">
                        <i class="las la-plus me-1"></i> Add Candidate
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="candidateTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAllCandidates">
                                </th>
                                <th width="50">SL.</th>
                                <th class="sortable" data-column="full_name">Candidate Name</th>
                                <th class="sortable" data-column="phone">Phone</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th class="sortable" data-column="interview_date">Interview Date</th>
                                <th>Time</th>
                                <th>Interviewer</th>
                                <th class="sortable" data-column="interview_status">Interview Status</th>
                                <th class="sortable" data-column="interview_result">Result</th>
                                <th class="sortable" data-column="joining_status">Joining Status</th>
                                <th class="sortable" data-column="expected_join_date">Expected Join</th>
                                <th class="sortable" data-column="joined_date">Joined Date</th>
                                <th class="sortable" data-column="follow_up_date">Follow-up</th>
                                <th>Decision</th>
                                <th>Remarks</th>
                                <th>Reference</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="candidateTableBody">
                            @include('recruitment.candidates.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $candidates->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let currentSort = { column: 'created_at', direction: 'desc' };

    function getFilterParams() {
        return {
            search: $('#searchCandidate').val(),
            department_id: $('#filterDept').val(),
            position_id: $('#filterPosition').val(),
            interview_status: $('#filterInterviewStatus').val(),
            interview_result: $('#filterInterviewResult').val(),
            joining_status: $('#filterJoiningStatus').val(),
            reference: $('#filterReference').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val(),
            sort_column: currentSort.column,
            sort_direction: currentSort.direction
        };
    }

    function loadCandidates(url = null) {
        let requestUrl = url || '{{ route("recruitment.candidates.index") }}';
        let params = getFilterParams();

        $('#candidateTable').addClass('table-loading');
        $.ajax({
            url: requestUrl, type: 'GET', data: params,
            success: function(response) {
                $('#candidateTableBody').html(response.html);
                $('#paginationContainer').html(response.pagination);
                $('#candidateCount').text(response.total);
                if (response.stats) {
                    $('#statTotal').text(response.stats.total);
                    $('#statSelected').text(response.stats.selected);
                    $('#statJoined').text(response.stats.joined);
                    $('#statPending').text(response.stats.pending);
                }
                $('#candidateTable').removeClass('table-loading');
                updateSortIndicators();
            },
            error: function() {
                $('#candidateTable').removeClass('table-loading');
                Swal.fire('Error', 'Failed to load candidates', 'error');
            }
        });
    }

    $(document).on('click', '.sortable', function() {
        let column = $(this).data('column');
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        loadCandidates();
    });

    function updateSortIndicators() {
        $('.sortable').removeClass('asc desc');
        $(`.sortable[data-column="${currentSort.column}"]`).addClass(currentSort.direction);
    }

    // Filter change handlers
    $('#filterDept, #filterPosition, #filterInterviewStatus, #filterInterviewResult, #filterJoiningStatus, #filterReference, #filterDateFrom, #filterDateTo')
        .on('change', function() { loadCandidates(); });

    $('#searchCandidate').on('keyup', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => loadCandidates(), 500);
    });

    $('#resetFilters').click(function() {
        $('#searchCandidate, #filterDept, #filterPosition, #filterInterviewStatus, #filterInterviewResult, #filterJoiningStatus, #filterReference, #filterDateFrom, #filterDateTo').val('');
        loadCandidates();
    });

    // Pagination
    $(document).on('click', '#paginationContainer .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url) loadCandidates(url);
    });

    // Update select-all and bulk delete button visibility
    function updateBulkDeleteState() {
        let checkedCount = $('.candidate-select-checkbox:checked').length;
        let totalCount = $('.candidate-select-checkbox').length;

        $('#selectedCount').text(checkedCount);
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteBtn').addClass('d-none');
        }

        if (totalCount > 0 && checkedCount === totalCount) {
            $('#selectAllCandidates').prop('checked', true);
        } else {
            $('#selectAllCandidates').prop('checked', false);
        }
    }

    // Toggle all checkboxes
    $(document).on('change', '#selectAllCandidates', function() {
        let isChecked = $(this).is(':checked');
        $('.candidate-select-checkbox').prop('checked', isChecked);
        updateBulkDeleteState();
    });

    // Individual checkbox change
    $(document).on('change', '.candidate-select-checkbox', function() {
        updateBulkDeleteState();
    });

    // Reset checkboxes on AJAX reload
    $(document).ajaxComplete(function() {
        updateBulkDeleteState();
    });

    // Bulk Delete
    $('#bulkDeleteBtn').click(function() {
        let selectedIds = [];
        $('.candidate-select-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: `Delete ${selectedIds.length} Candidate(s)?`,
            text: 'Are you sure you want to delete all selected candidates? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Delete All',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("recruitment.candidates.bulk-delete") }}',
                    type: 'POST',
                    data: { ids: selectedIds },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => loadCandidates());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete selected candidates', 'error');
                    }
                });
            }
        });
    });

    // Export Excel / CSV
    $(document).on('click', '.exportOption', function(e) {
        e.preventDefault();
        let format = $(this).data('format');
        let params = getFilterParams();
        params.format = format;
        let queryString = $.param(params);
        window.location.href = '{{ route("recruitment.candidates.export") }}?' + queryString;
    });

    // Delete candidate
    $(document).on('click', '.deleteCandidateBtn', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        Swal.fire({
            title: 'Delete Candidate?',
            html: `Are you sure you want to delete <strong>${name}</strong>?`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("recruitment/candidates") }}/' + id, type: 'DELETE',
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => loadCandidates());
                    },
                    error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete', 'error'); }
                });
            }
        });
    });
});
</script>
@endsection
