@extends('layouts.app')

@section('title', 'Google Ads Leads')

@section('extra-css')
<link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
<style>
    .badge-pending { background-color: #ffc107; color: #000; }
    .badge-approved { background-color: #28a745; color: #fff; }
    .badge-rejected { background-color: #dc3545; color: #fff; }

    body { overflow-x: hidden; }
    .page-content { overflow-x: hidden; }
    .row { margin-left: 0; margin-right: 0; }
    .row > * { padding-left: 12px; padding-right: 12px; }
    .card { overflow: hidden; }

    .leads-card .card-body { padding: 0; overflow: hidden; }
    .leads-card .card-footer { padding: 15px 20px; background-color: #f8f9fa; }

    .table-container { overflow-x: auto; overflow-y: visible; max-height: none; position: relative; width: 100%; }
    .table-container::-webkit-scrollbar { width: 10px; height: 10px; }
    .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .table-container::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
    .table-container::-webkit-scrollbar-thumb:hover { background: #555; }

    .table-container table { margin-bottom: 0; table-layout: auto; min-width: 100%; }
    .table thead th { position: sticky; top: 0; z-index: 10; background: #fff; white-space: nowrap; }
    .table tbody td { white-space: nowrap; vertical-align: middle; }

    .google-ads-header { background: linear-gradient(135deg, #4285F4 0%, #34A853 50%, #FBBC05 75%, #EA4335 100%); }

    /* Sortable headers */
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
        content: "⇅";
        position: absolute;
        right: 8px;
        opacity: 0.3;
    }

    .sortable.asc::after {
        content: "↑";
        opacity: 1;
    }

    .sortable.desc::after {
        content: "↓";
        opacity: 1;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">
                            <i class="lab la-google me-2" style="font-size: 1.5rem; color: #4285F4;"></i>
                            Google Ads Leads
                        </h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                            <li class="breadcrumb-item active">Google Ads Leads</li>
                        </ol>
                    </div>
                    <div class="col-auto align-self-center">
                        <span class="badge bg-primary" style="font-size: 14px;">
                            <i class="las la-clipboard-list me-1"></i>
                            <span id="leadCount">{{ $leads->total() }}</span> Leads
                        </span>
                        @if($pendingcount > 0)
                            <span class="badge bg-warning ms-2" style="font-size: 14px;">
                                <i class="las la-clock me-1"></i>
                                {{ $pendingcount }} Pending
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header google-ads-header">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-filter me-2"></i>Filter Google Ads Leads
                    </h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form method="GET" action="{{ route('leads.google-ads') }}" id="filterForm">
                        <div class="row align-items-end g-3">
                            <!-- Status Filter -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Status</label>
                                <select class="form-select" name="status" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <!-- Date From -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Date From</label>
                                <input type="date" class="form-control" name="datefrom" id="dateFromFilter" value="{{ request('datefrom') }}">
                            </div>

                            <!-- Date To -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Date To</label>
                                <input type="date" class="form-control" name="dateto" id="dateToFilter" value="{{ request('dateto') }}">
                            </div>

                            <!-- Search -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Search</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <input type="text" class="form-control" name="search" id="searchInput" placeholder="Name, Phone, Email..." value="{{ request('search') }}" style="max-width: 400px;">
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-3 mt-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="las la-filter me-1"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetBtn">
                                    <i class="las la-redo-alt me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="row">
        <div class="col-12">
            <div class="card leads-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="lab la-google me-2 text-primary"></i>
                        Google Ads Leads List
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover mb-0" id="leadsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-column="code">Lead Code</th>
                                    <th class="sortable" data-column="name">Name</th>
                                    <th class="sortable" data-column="phone">Phone</th>
                                    <th class="sortable" data-column="service">Service</th>
                                    <th class="sortable" data-column="status">Status</th>
                                    <th class="sortable" data-column="source">Source</th>
                                    <th class="sortable" data-column="assigned">Assigned To</th>
                                    <th class="sortable" data-column="date">Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="leadsTableBody">
                                @include('leads.partials.table-rows', ['leads' => $leads])
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer" id="paginationContainer">
                    {{ $leads->links('pagination::bootstrap-5') }}
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
    let currentSort = { column: null, direction: 'asc' };

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadLeads();
    });

    // Reset filters
    $('#resetBtn').on('click', function() {
        $('#statusFilter').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        $('#searchInput').val('');
        window.location.href = "{{ route('leads.google-ads') }}";
    });

    // Load leads via AJAX
    function loadLeads(url = null) {
        let formData = $('#filterForm').serialize();
        let requestUrl = url || "{{ route('leads.google-ads') }}";

        $.ajax({
            url: requestUrl,
            type: 'GET',
            data: formData,
            success: function(response) {
                $('#leadsTableBody').html(response.html);
                $('#paginationContainer').html(response.pagination);
                $('#leadCount').text(response.total);
            },
            error: function(xhr) {
                console.error('Error loading leads:', xhr);
            }
        });
    }

    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url) {
            loadLeads(url);
        }
    });

    // Table sorting
    $(document).on('click', '.sortable', function() {
        let column = $(this).data('column');

        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }

        $('.sortable').removeClass('asc desc');
        $(this).addClass(currentSort.direction);

        sortLeadsTable(column, currentSort.direction);
    });

    function sortLeadsTable(column, direction) {
        let rows = $('#leadsTableBody tr').get();

        rows.sort(function(a, b) {
            let aVal, bVal;

            switch(column) {
                case 'code':
                    aVal = $(a).data('code');
                    bVal = $(b).data('code');
                    break;
                case 'name':
                    aVal = $(a).data('name');
                    bVal = $(b).data('name');
                    break;
                case 'phone':
                    aVal = $(a).data('phone');
                    bVal = $(b).data('phone');
                    break;
                case 'service':
                    aVal = $(a).data('service');
                    bVal = $(b).data('service');
                    break;
                case 'source':
                    aVal = $(a).data('source');
                    bVal = $(b).data('source');
                    break;
                case 'status':
                    let statusOrder = {'pending': 1, 'approved': 2, 'rejected': 3};
                    aVal = statusOrder[$(a).data('status')];
                    bVal = statusOrder[$(b).data('status')];
                    break;
                case 'assigned':
                    aVal = $(a).data('assigned');
                    bVal = $(b).data('assigned');
                    break;
                case 'date':
                    aVal = new Date($(a).data('date'));
                    bVal = new Date($(b).data('date'));
                    break;
            }

            if (direction === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });

        $.each(rows, function(index, row) {
            $('#leadsTableBody').append(row);
        });
    }
});
</script>
@endsection
