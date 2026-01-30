@extends('layouts.app')

@section('title', 'Services Management')

@section('extra-css')
<style>
    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
        padding-right: 20px !important;
    }
    .sortable:hover {
        background-color: #e9ecef;
    }
    .sortable::after {
        content: '⇅';
        position: absolute;
        right: 8px;
        opacity: 0.3;
        font-size: 14px;
    }
    .sortable.asc::after {
        content: '↑';
        opacity: 1;
        color: #0d6efd;
    }
    .sortable.desc::after {
        content: '↓';
        opacity: 1;
        color: #0d6efd;
    }
    .table-loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Services Management</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Services</li>
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
                        <label class="form-label fw-semibold">Service Type</label>
                        <select class="form-select" id="serviceTypeFilter">
                            <option value="">All Types</option>
                            @foreach($serviceTypes as $type)
                                <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
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
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchService" placeholder="Search services...">
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

<!-- Services Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    Services List (<span id="serviceCount">{{ $services->total() }}</span> total)
                </h4>
                <button type="button" class="btn btn-success" id="addServiceBtn">
                    <i class="las la-plus me-1"></i> Add Service
                </button>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="servicesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-column="name">Service Name</th>
                                <th class="sortable" data-column="service_type">Type</th>
                                <th>Description</th>
                                <th class="sortable" data-column="price">Price</th>
                                <th class="sortable" data-column="is_active">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody">
                            @include('services.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $services->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalLabel">Add Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="serviceForm">
                @csrf
                <input type="hidden" id="service_id" name="service_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <span class="error-text name_error text-danger d-none"></span>
                    </div>

                    <div class="mb-3">
                        <label for="service_type" class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="service_type" name="service_type" required>
                            <option value="">Select or type a service type</option>
                        </select>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i> You can type a custom service type if not listed
                        </small>
                        <span class="error-text service_type_error text-danger d-none"></span>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price (₹)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" placeholder="0.00">
                        <small class="text-muted">Optional - Leave blank if price varies</small>
                        <span class="error-text price_error text-danger d-none"></span>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter service description..."></textarea>
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
                        <i class="las la-save me-1"></i> Save Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let currentSort = {
            column: 'created_at',
            direction: 'desc'
        };

        let availableServiceTypes = @json($serviceTypes); // ✅ Get from backend

        // Initialize Select2 with tagging for service_type (INSIDE MODAL)
        function initServiceTypeSelect2() {
            $('#service_type').select2({
                theme: 'bootstrap-5',
                tags: true,
                placeholder: 'Select or type a service type',
                allowClear: true,
                dropdownParent: $('#serviceModal'), // ✅ FIX: Modal dropdown parent
                data: availableServiceTypes.map(type => ({
                    id: type,
                    text: type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
                })),
                createTag: function (params) {
                    const term = $.trim(params.term);

                    if (term === '') {
                        return null;
                    }

                    // Convert to snake_case
                    const slug = term.toLowerCase().replace(/\s+/g, '_');

                    return {
                        id: slug,
                        text: term + ' (New)',
                        newTag: true
                    };
                }
            });
        }

        // Load services with filters
        function loadServices(url = null) {
            let requestUrl = url || '{{ route("services.index") }}';

            let params = {
                service_type: $('#serviceTypeFilter').val(),
                is_active: $('#statusFilter').val(),
                search: $('#searchService').val(),
                sort_column: currentSort.column,
                sort_direction: currentSort.direction
            };

            $('#servicesTable').addClass('table-loading');

            $.ajax({
                url: requestUrl,
                type: 'GET',
                data: params,
                success: function(response) {
                    $('#servicesTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    $('#serviceCount').text(response.total);
                    $('#servicesTable').removeClass('table-loading');
                    updateSortIndicators();

                    // ✅ Update available types dynamically
                    if (response.serviceTypes) {
                        availableServiceTypes = response.serviceTypes;
                    }
                },
                error: function() {
                    $('#servicesTable').removeClass('table-loading');
                    Swal.fire('Error', 'Failed to load services', 'error');
                }
            });
        }
        // Sorting
        $(document).on('click', '.sortable', function() {
            let column = $(this).data('column');
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }
            loadServices();
        });

        function updateSortIndicators() {
            $('.sortable').removeClass('asc desc');
            $(`.sortable[data-column="${currentSort.column}"]`).addClass(currentSort.direction);
        }

        // Filters
        $('#serviceTypeFilter, #statusFilter').on('change', function() {
            loadServices();
        });

        $('#searchService').on('keyup', function() {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => loadServices(), 500);
        });

        $('#resetFilters').click(function() {
            $('#serviceTypeFilter, #statusFilter, #searchService').val('');
            loadServices();
        });

        // Pagination
        $(document).on('click', '#paginationContainer .pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            if (url) loadServices(url);
        });

        // Add Service
        $('#addServiceBtn').click(function() {
            $('#serviceForm')[0].reset();
            $('#service_id').val('');
            $('#serviceModalLabel').text('Add Service');
            $('.error-text').text('').addClass('d-none');

            // ✅ Destroy and reinitialize Select2
            if ($('#service_type').hasClass('select2-hidden-accessible')) {
                $('#service_type').select2('destroy');
            }
            initServiceTypeSelect2();

            $('#serviceModal').modal('show');
        });

        // Edit Service
        $(document).on('click', '.editServiceBtn', function() {
            let serviceId = $(this).data('id');

            $.ajax({
                url: '/services/' + serviceId + '/edit',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let service = response.service;
                        $('#service_id').val(service.id);
                        $('#name').val(service.name);
                        $('#service_type').val(service.service_type);
                        $('#price').val(service.price || '');
                        $('#description').val(service.description || '');
                        $('#is_active').prop('checked', service.is_active);
                        $('#serviceModalLabel').text('Edit Service');
                        $('.error-text').text('').addClass('d-none');

                        // ✅ Reinitialize and set value
                        if ($('#service_type').hasClass('select2-hidden-accessible')) {
                            $('#service_type').select2('destroy');
                        }
                        initServiceTypeSelect2();

                        // ✅ Set the value and trigger change
                        $('#service_type').val(service.service_type).trigger('change');

                        $('#serviceModal').modal('show');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to load service data', 'error');
                }
            });
        });

        // Submit Form
        $('#serviceForm').on('submit', function(e) {
            e.preventDefault();

            let serviceId = $('#service_id').val();
            let url = serviceId ? '/services/' + serviceId : '{{ route("services.store") }}';
            let formData = new FormData(this);

            // Remove the default checkbox value and set it explicitly
            formData.delete('is_active');
            formData.append('is_active', $('#is_active').is(':checked') ? '1' : '0');

            if (serviceId) {
                formData.append('_method', 'PUT');
            }

            $('.error-text').text('').addClass('d-none');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#serviceModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => loadServices());
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('.' + key + '_error').text(value[0]).removeClass('d-none');
                        });
                    } else {
                        Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred', 'error');
                    }
                }
            });
        });

        // Delete Service
        $(document).on('click', '.deleteServiceBtn', function() {
            let serviceId = $(this).data('id');
            let serviceName = $(this).data('name');

            Swal.fire({
                title: 'Delete Service?',
                html: `Are you sure you want to delete <strong>${serviceName}</strong>?<br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/services/' + serviceId,
                        type: 'DELETE',
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => loadServices());
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete service', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
