@extends('layouts.app')

@section('title', 'Recruitment Positions')

@section('extra-css')
<style>
    .sortable { cursor: pointer; user-select: none; position: relative; padding-right: 20px !important; }
    .sortable:hover { background-color: #e9ecef; }
    .sortable::after { content: '⇅'; position: absolute; right: 8px; opacity: 0.3; font-size: 14px; }
    .sortable.asc::after { content: '↑'; opacity: 1; color: #0d6efd; }
    .sortable.desc::after { content: '↓'; opacity: 1; color: #0d6efd; }
    .table-loading { opacity: 0.6; pointer-events: none; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Recruitment Positions</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Recruitment</li>
                    <li class="breadcrumb-item active">Positions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchPos" placeholder="Search positions...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select class="form-select" id="deptFilter">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-secondary w-100" id="resetFilters">
                            <i class="las la-redo me-2"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Positions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    Positions List (<span id="posCount">{{ $positions->total() }}</span> total)
                </h4>
                <button type="button" class="btn btn-primary" id="addPosBtn">
                    <i class="las la-plus me-1"></i> Add Position
                </button>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="posTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th class="sortable" data-column="name">Position Name</th>
                                <th>Department</th>
                                <th>Description</th>
                                <th>Candidates</th>
                                <th class="sortable" data-column="is_active">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="posTableBody">
                            @include('recruitment.positions.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $positions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Position Modal -->
<div class="modal fade" id="posModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posModalLabel">Add Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="posForm">
                @csrf
                <input type="hidden" id="pos_id" name="pos_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pos_name" class="form-label">Position Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pos_name" name="name" required>
                        <span class="error-text name_error text-danger d-none"></span>
                    </div>
                    <div class="mb-3">
                        <label for="recruitment_department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select class="form-select" id="recruitment_department_id" name="recruitment_department_id" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="error-text recruitment_department_id_error text-danger d-none"></span>
                    </div>
                    <div class="mb-3">
                        <label for="pos_description" class="form-label">Description</label>
                        <textarea class="form-control" id="pos_description" name="description" rows="3" placeholder="Enter position description..."></textarea>
                        <span class="error-text description_error text-danger d-none"></span>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pos_is_active" name="is_active" checked>
                            <label class="form-check-label" for="pos_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i> Save Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let currentSort = { column: 'name', direction: 'asc' };

    function loadPositions(url = null) {
        let requestUrl = url || '{{ route("recruitment.positions.index") }}';
        let params = {
            search: $('#searchPos').val(),
            department_id: $('#deptFilter').val(),
            is_active: $('#statusFilter').val(),
            sort_column: currentSort.column,
            sort_direction: currentSort.direction
        };

        $('#posTable').addClass('table-loading');
        $.ajax({
            url: requestUrl, type: 'GET', data: params,
            success: function(response) {
                $('#posTableBody').html(response.html);
                $('#paginationContainer').html(response.pagination);
                $('#posCount').text(response.total);
                $('#posTable').removeClass('table-loading');
                updateSortIndicators();
            },
            error: function() {
                $('#posTable').removeClass('table-loading');
                Swal.fire('Error', 'Failed to load positions', 'error');
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
        loadPositions();
    });

    function updateSortIndicators() {
        $('.sortable').removeClass('asc desc');
        $(`.sortable[data-column="${currentSort.column}"]`).addClass(currentSort.direction);
    }

    $('#deptFilter, #statusFilter').on('change', function() { loadPositions(); });
    $('#searchPos').on('keyup', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => loadPositions(), 500);
    });
    $('#resetFilters').click(function() {
        $('#searchPos, #deptFilter, #statusFilter').val('');
        loadPositions();
    });

    $(document).on('click', '#paginationContainer .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url) loadPositions(url);
    });

    // Add Position
    $('#addPosBtn').click(function() {
        $('#posForm')[0].reset();
        $('#pos_id').val('');
        $('#posModalLabel').text('Add Position');
        $('.error-text').text('').addClass('d-none');
        $('#pos_is_active').prop('checked', true);
        $('#posModal').modal('show');
    });

    // Edit Position
    $(document).on('click', '.editPosBtn', function() {
        let posId = $(this).data('id');
        $.ajax({
            url: '{{ url("recruitment/positions") }}/' + posId + '/edit',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let pos = response.position;
                    $('#pos_id').val(pos.id);
                    $('#pos_name').val(pos.name);
                    $('#recruitment_department_id').val(pos.recruitment_department_id);
                    $('#pos_description').val(pos.description || '');
                    $('#pos_is_active').prop('checked', pos.is_active);
                    $('#posModalLabel').text('Edit Position');
                    $('.error-text').text('').addClass('d-none');
                    $('#posModal').modal('show');
                }
            },
            error: function() { Swal.fire('Error', 'Failed to load position data', 'error'); }
        });
    });

    // Submit Form
    $('#posForm').on('submit', function(e) {
        e.preventDefault();
        let posId = $('#pos_id').val();
        let url = posId ? '{{ url("recruitment/positions") }}/' + posId : '{{ route("recruitment.positions.store") }}';
        let formData = new FormData(this);
        formData.delete('is_active');
        formData.append('is_active', $('#pos_is_active').is(':checked') ? '1' : '0');
        if (posId) formData.append('_method', 'PUT');

        $('.error-text').text('').addClass('d-none');
        $.ajax({
            url: url, type: 'POST', data: formData, processData: false, contentType: false,
            success: function(response) {
                $('#posModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => loadPositions());
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            $('.' + key + '_error').text(value[0]).removeClass('d-none');
                        });
                    } else if (xhr.responseJSON.message) {
                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred', 'error');
                }
            }
        });
    });

    // Delete Position
    $(document).on('click', '.deletePosBtn', function() {
        let posId = $(this).data('id');
        let posName = $(this).data('name');
        Swal.fire({
            title: 'Delete Position?',
            html: `Are you sure you want to delete <strong>${posName}</strong>?<br>This action cannot be undone.`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, Delete', cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("recruitment/positions") }}/' + posId, type: 'DELETE',
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => loadPositions());
                    },
                    error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete position', 'error'); }
                });
            }
        });
    });
});
</script>
@endsection
