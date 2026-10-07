@extends('layouts.app')

@section('title', 'Recruitment Departments')

@section('extra-css')
<style>
    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
        padding-right: 20px !important;
    }
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
            <h4 class="page-title">Recruitment Departments</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Recruitment</li>
                    <li class="breadcrumb-item active">Departments</li>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchDept" placeholder="Search departments...">
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

<!-- Departments Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    Departments List (<span id="deptCount">{{ $departments->total() }}</span> total)
                </h4>
                <button type="button" class="btn btn-primary" id="addDeptBtn">
                    <i class="las la-plus me-1"></i> Add Department
                </button>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="deptTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th class="sortable" data-column="name">Department Name</th>
                                <th>Description</th>
                                <th>Positions</th>
                                <th>Candidates</th>
                                <th class="sortable" data-column="is_active">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deptTableBody">
                            @include('recruitment.departments.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $departments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptModalLabel">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deptForm">
                @csrf
                <input type="hidden" id="dept_id" name="dept_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <span class="error-text name_error text-danger d-none"></span>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter department description..."></textarea>
                        <span class="error-text description_error text-danger d-none"></span>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i> Save Department
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

    function loadDepartments(url = null) {
        let requestUrl = url || '{{ route("recruitment.departments.index") }}';
        let params = {
            search: $('#searchDept').val(),
            is_active: $('#statusFilter').val(),
            sort_column: currentSort.column,
            sort_direction: currentSort.direction
        };

        $('#deptTable').addClass('table-loading');
        $.ajax({
            url: requestUrl, type: 'GET', data: params,
            success: function(response) {
                $('#deptTableBody').html(response.html);
                $('#paginationContainer').html(response.pagination);
                $('#deptCount').text(response.total);
                $('#deptTable').removeClass('table-loading');
                updateSortIndicators();
            },
            error: function() {
                $('#deptTable').removeClass('table-loading');
                Swal.fire('Error', 'Failed to load departments', 'error');
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
        loadDepartments();
    });

    function updateSortIndicators() {
        $('.sortable').removeClass('asc desc');
        $(`.sortable[data-column="${currentSort.column}"]`).addClass(currentSort.direction);
    }

    $('#statusFilter').on('change', function() { loadDepartments(); });
    $('#searchDept').on('keyup', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => loadDepartments(), 500);
    });
    $('#resetFilters').click(function() {
        $('#searchDept, #statusFilter').val('');
        loadDepartments();
    });

    $(document).on('click', '#paginationContainer .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url) loadDepartments(url);
    });

    // Add Department
    $('#addDeptBtn').click(function() {
        $('#deptForm')[0].reset();
        $('#dept_id').val('');
        $('#deptModalLabel').text('Add Department');
        $('.error-text').text('').addClass('d-none');
        $('#is_active').prop('checked', true);
        $('#deptModal').modal('show');
    });

    // Edit Department
    $(document).on('click', '.editDeptBtn', function() {
        let deptId = $(this).data('id');
        $.ajax({
            url: '{{ url("recruitment/departments") }}/' + deptId + '/edit',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let dept = response.department;
                    $('#dept_id').val(dept.id);
                    $('#name').val(dept.name);
                    $('#description').val(dept.description || '');
                    $('#is_active').prop('checked', dept.is_active);
                    $('#deptModalLabel').text('Edit Department');
                    $('.error-text').text('').addClass('d-none');
                    $('#deptModal').modal('show');
                }
            },
            error: function() { Swal.fire('Error', 'Failed to load department data', 'error'); }
        });
    });

    // Submit Form
    $('#deptForm').on('submit', function(e) {
        e.preventDefault();
        let deptId = $('#dept_id').val();
        let url = deptId ? '{{ url("recruitment/departments") }}/' + deptId : '{{ route("recruitment.departments.store") }}';
        let formData = new FormData(this);
        formData.delete('is_active');
        formData.append('is_active', $('#is_active').is(':checked') ? '1' : '0');
        if (deptId) formData.append('_method', 'PUT');

        $('.error-text').text('').addClass('d-none');
        $.ajax({
            url: url, type: 'POST', data: formData, processData: false, contentType: false,
            success: function(response) {
                $('#deptModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => loadDepartments());
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

    // Delete Department
    $(document).on('click', '.deleteDeptBtn', function() {
        let deptId = $(this).data('id');
        let deptName = $(this).data('name');
        Swal.fire({
            title: 'Delete Department?',
            html: `Are you sure you want to delete <strong>${deptName}</strong>?<br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("recruitment/departments") }}/' + deptId,
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => loadDepartments());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete department', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
