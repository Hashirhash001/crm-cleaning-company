@extends('layouts.app')

@section('title', 'Leads Management')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-approved {
            background-color: #28a745;
            color: #fff;
        }

        .badge-rejected {
            background-color: #dc3545;
            color: #fff;
        }

        body {
            overflow-x: hidden;
        }

        .page-content {
            overflow-x: hidden;
        }

        .row {
            margin-left: 0;
            margin-right: 0;
        }

        .row>* {
            padding-left: 12px;
            padding-right: 12px;
        }

        .card {
            overflow: hidden;
        }

        .leads-card .card-body {
            padding: 0;
            overflow: hidden;
        }

        .leads-card .card-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
        }

        /* Table Container - X-AXIS SCROLL ONLY */
        .table-container {
            overflow-x: auto;
            overflow-y: visible;
            max-height: none;
            position: relative;
            width: 100%;
        }

        .table-container::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .table-container table {
            margin-bottom: 0;
            min-width: 100%;
        }

        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            padding: 12px 15px;
            vertical-align: middle;
            font-weight: 600;
        }

        .table-container tbody td {
            white-space: nowrap;
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table-container tbody tr:hover td {
            background-color: #f8f9fa;
        }

        /* Checkbox Styling */
        .checkbox-col {
            width: 50px;
            min-width: 50px;
            text-align: center;
            padding: 12px 10px !important;
        }

        .custom-checkbox {
            width: 12px;
            height: 12px;
            cursor: pointer;
            accent-color: #0d6efd;
            transform: scale(1.2);
        }

        .custom-checkbox:hover {
            transform: scale(1.3);
        }

        #selectAll {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: #198754;
            transform: scale(1.3);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            margin: 0;
        }

        .table-container tbody tr:has(.custom-checkbox:checked) {
            background-color: #e7f3ff;
            border-left: 3px solid #0d6efd;
        }

        /* Modern Selected Leads Preview */
        .leads-preview-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
        }

        .lead-preview-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s ease;
        }

        .lead-preview-item:last-child {
            border-bottom: none;
        }

        .lead-preview-item:hover {
            background-color: #e9ecef;
        }

        .lead-preview-badge {
            background: #0d6efd;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            min-width: 80px;
            text-align: center;
        }

        .lead-preview-name {
            font-weight: 500;
            color: #495057;
            flex: 1;
        }

        .lead-preview-icon {
            color: #6c757d;
            font-size: 18px;
        }

        /* Alert styling for lead info */
        .lead-info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 15px;
        }

        .lead-info-box p {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .lead-info-box p:last-child {
            margin-bottom: 0;
        }

        .lead-info-label {
            font-weight: 600;
            color: #1565c0;
            min-width: 100px;
        }

        .lead-info-value {
            color: #0d47a1;
            font-weight: 500;
        }

        /* Modern form controls */
        .form-select:focus,
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        /* Disabled telecaller dropdown styling */
        #bulk_telecaller:disabled,
        #assign_telecaller:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Warning badge for selected count */
        .selected-count-badge {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            border: 1px solid #ffca2c;
            border-radius: 8px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selected-count-badge i {
            font-size: 24px;
            color: #997404;
        }

        .selected-count-text {
            font-size: 16px;
            font-weight: 600;
            color: #664d03;
        }

        .action-icons {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
        }

        .action-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        .lead-name-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .lead-name-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        .lead-name-link h6 {
            color: inherit;
            font-weight: 600;
            margin: 0;
        }

        #approval_notes {
            margin: 0 !important;
        }

        #rejection_reason {
            margin: 0 !important;
        }

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
            content: '⇅';
            position: absolute;
            right: 8px;
            opacity: 0.3;
        }

        .sortable.asc::after {
            content: '↑';
            opacity: 1;
        }

        .sortable.desc::after {
            content: '↓';
            opacity: 1;
        }

        /* ============================================
               MOBILE RESPONSIVE STYLES
               ============================================ */

        /* Tablets and below (768px) */
        @media (max-width: 768px) {

            /* Page title */
            .page-title-box {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .page-title-box .breadcrumb {
                margin-top: 5px;
            }

            /* Filter section */
            .card-body form .row {
                row-gap: 15px;
            }

            .card-body form .col-3,
            .card-body form .col-6 {
                width: 100%;
                max-width: 100%;
                flex: 0 0 100%;
            }

            .card-body form .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            .card-body form .d-flex .form-control {
                max-width: 100% !important;
            }

            .card-body form .d-flex .btn {
                width: 100%;
                justify-content: center;
            }

            /* Card header */
            .card-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px;
            }

            .card-header .d-flex {
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }

            .card-header .btn {
                width: 100%;
                justify-content: center;
            }

            .card-title {
                font-size: 16px;
            }

            /* Table - Enable horizontal scroll */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-container table {
                min-width: 1200px;
                /* Force horizontal scroll */
            }

            /* Smaller font in table */
            .table-container thead th,
            .table-container tbody td {
                font-size: 13px;
                padding: 8px 10px;
            }

            /* Badges */
            .badge {
                font-size: 10px;
                padding: 3px 8px;
            }

            /* Action icons */
            .action-icons {
                gap: 5px;
            }

            .action-icons i {
                font-size: 20px !important;
            }

            /* Pagination */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }

            .pagination .page-item {
                margin: 2px;
            }

            .pagination .page-link {
                padding: 5px 10px;
                font-size: 14px;
            }

            /* Modals */
            .modal-dialog {
                margin: 10px;
                max-width: calc(100% - 20px);
            }

            .modal-body {
                padding: 15px;
            }

            .modal-header {
                padding: 12px 15px;
            }

            .modal-title {
                font-size: 16px;
            }

            .modal-footer {
                padding: 12px 15px;
                flex-direction: column;
                gap: 10px;
            }

            .modal-footer .btn {
                width: 100%;
                margin: 0 !important;
            }

            /* Lead info box */
            .lead-info-box p {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .lead-info-label {
                min-width: auto;
            }

            /* Selected leads preview */
            .lead-preview-item {
                padding: 10px;
                gap: 8px;
            }

            .lead-preview-badge {
                min-width: 70px;
                font-size: 11px;
            }

            .lead-preview-name {
                font-size: 14px;
            }

            /* Selected count badge */
            .selected-count-badge {
                padding: 10px 15px;
                flex-direction: column;
                text-align: center;
            }

            .selected-count-text {
                font-size: 14px;
            }

            /* Card footer */
            .card-footer {
                padding: 10px 15px;
            }
        }

        /* Mobile phones (576px and below) */
        @media (max-width: 576px) {

            /* Page title */
            .page-title {
                font-size: 18px;
            }

            .breadcrumb {
                font-size: 12px;
            }

            /* Card padding */
            .card-body {
                padding: 15px !important;
            }

            /* Filter labels */
            .form-label {
                font-size: 13px;
                margin-bottom: 5px;
            }

            /* Form controls */
            .form-select,
            .form-control,
            .btn {
                font-size: 14px;
                padding: 8px 12px;
            }

            /* Table */
            .table-container thead th,
            .table-container tbody td {
                font-size: 12px;
                padding: 6px 8px;
            }

            /* Checkbox */
            .custom-checkbox,
            #selectAll {
                width: 18px;
                height: 18px;
                transform: scale(1);
            }

            .custom-checkbox:hover,
            #selectAll:hover {
                transform: scale(1.1);
            }

            /* Lead name */
            .lead-name-link h6 {
                font-size: 13px;
            }

            /* Badges */
            .badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            /* Action icons smaller */
            .action-icons i {
                font-size: 18px !important;
            }

            /* Modal */
            .modal-dialog {
                margin: 5px;
                max-width: calc(100% - 10px);
            }

            .modal-header .modal-title {
                font-size: 14px;
            }

            .modal-body {
                padding: 12px;
            }

            .form-label.fw-semibold {
                font-size: 13px;
            }

            .modal-footer .btn {
                font-size: 14px;
                padding: 8px 15px;
            }

            /* Lead preview items smaller */
            .lead-preview-item {
                padding: 8px;
                gap: 6px;
            }

            .lead-preview-badge {
                min-width: 60px;
                font-size: 10px;
                padding: 3px 6px;
            }

            .lead-preview-name {
                font-size: 13px;
            }

            .lead-preview-icon {
                font-size: 16px;
            }

            /* Selected count badge */
            .selected-count-badge i {
                font-size: 20px;
            }

            .selected-count-text {
                font-size: 13px;
            }

            /* Pagination */
            .pagination .page-link {
                padding: 4px 8px;
                font-size: 12px;
            }
        }

        /* Extra small devices (below 400px) */
        @media (max-width: 400px) {
            .page-title {
                font-size: 16px;
            }

            .card-title {
                font-size: 14px;
            }

            .table-container thead th,
            .table-container tbody td {
                font-size: 11px;
                padding: 5px 6px;
            }

            .btn {
                font-size: 13px;
                padding: 7px 10px;
            }

            .modal-title {
                font-size: 13px;
            }

            .lead-preview-badge {
                min-width: 55px;
                font-size: 9px;
            }

            .lead-preview-name {
                font-size: 12px;
            }
        }

        /* Landscape mode optimization */
        @media (max-width: 768px) and (orientation: landscape) {
            .modal-dialog {
                max-height: 90vh;
                overflow-y: auto;
            }

            .modal-body {
                max-height: calc(90vh - 150px);
                overflow-y: auto;
            }

            .leads-preview-container {
                max-height: 150px;
            }
        }

        /* Ensure modals are scrollable on small screens */
        @media (max-height: 600px) {
            .modal-dialog {
                margin: 5px auto;
            }

            .modal-body {
                max-height: 400px;
                overflow-y: auto;
            }

            .leads-preview-container {
                max-height: 150px;
            }
        }

        /* View Mode Toggle Styles */
        #viewModeToggle {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #viewModeToggle .btn {
            font-weight: 500;
            padding: 10px 20px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        #viewModeToggle .btn:hover:not(.active) {
            /* background-color: #f8f9fa; */
            transform: translateY(-2px);
        }

        #viewModeToggle .btn.active {
            font-weight: 600;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }

        /* Active state colors */
        #viewModeToggle .btn-outline-primary.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        #viewModeToggle .btn-outline-warning.active {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        #viewModeToggle .btn-outline-success.active {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #viewModeToggle .btn {
                font-size: 12px;
                padding: 8px 12px;
            }

            #viewModeToggle .btn i {
                display: none;
                /* Hide icons on mobile */
            }
        }

        #serviceFilter {
            max-height: 300px;
            overflow-y: auto;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 8px !important;
            padding-right: 20px !important;
        }

        /* Per Page Selector Styles */
        .per-page-selector {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .per-page-selector .form-select {
            min-width: 80px;
            font-size: 0.875rem;
            padding: 0.375rem 2rem 0.375rem 0.75rem;
        }

        /* Loading Overlay */
        .table-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(3px);
        }

        .loading-content {
            background: white;
            padding: 30px 50px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInScale 0.3s ease;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .loading-subtext {
            font-size: 13px;
            color: #666;
        }

        /* Table loading state */
        .table-loading {
            position: relative;
            opacity: 0.5;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .per-page-selector {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .per-page-selector .form-label {
                display: none;
            }

            .per-page-selector .form-select {
                width: 100%;
                min-width: auto;
            }
        }

        /* Bulk Assignment Loading Overlay */
        #bulkAssignLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }

        #bulkAssignLoadingOverlay .loading-content {
            background: white;
            padding: 40px 60px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            animation: fadeInScale 0.3s ease;
            min-width: 400px;
        }

        #bulkAssignLoadingOverlay .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #28a745;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        #bulkAssignLoadingOverlay .loading-text {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        #bulkAssignLoadingOverlay .loading-subtext {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        /* Progress Bar */
        #bulkAssignLoadingOverlay .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        #bulkAssignLoadingOverlay .progress-bar {
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            font-weight: 600;
            font-size: 12px;
            transition: width 0.3s ease;
        }

        /* Status badge in preview */
        .lead-preview-item .badge {
            font-size: 10px;
            padding: 3px 8px;
        }

        /* Warning alert for approved leads */
        #approvedLeadsWarning {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border-left: 4px solid #ffc107;
        }

        #approvedLeadsWarning i {
            font-size: 24px;
            color: #f59e0b;
        }

        @media (max-width: 576px) {
            #bulkAssignLoadingOverlay .loading-content {
                min-width: 300px;
                padding: 30px 40px;
            }

            #bulkAssignLoadingOverlay .progress {
                width: 250px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Leads Management</h4>
                <div>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Leads</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body" style="padding: 20px;">
                    <form method="GET" action="{{ route('leads.index') }}" id="filterForm">
                        <div class="row align-items-end g-3">
                            <!-- Status Filter -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Status</label>
                                <select class="form-select" id="filterStatus" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="site_visit" {{ request('status') == 'site_visit' ? 'selected' : '' }}>
                                        Site Visit</option>
                                    <option value="not_accepting_tc"
                                        {{ request('status') == 'not_accepting_tc' ? 'selected' : '' }}>Not Accepting T&C
                                    </option>
                                    <option value="they_will_confirm"
                                        {{ request('status') == 'they_will_confirm' ? 'selected' : '' }}>They Will Confirm
                                    </option>
                                    <option value="date_issue" {{ request('status') == 'date_issue' ? 'selected' : '' }}>
                                        Date Issue</option>
                                    <option value="rate_issue" {{ request('status') == 'rate_issue' ? 'selected' : '' }}>
                                        Rate Issue</option>
                                    <option value="service_not_provided"
                                        {{ request('status') == 'service_not_provided' ? 'selected' : '' }}>Service Not
                                        Provided</option>
                                    <option value="just_enquiry"
                                        {{ request('status') == 'just_enquiry' ? 'selected' : '' }}>Just Enquiry</option>
                                    <option value="immediate_service"
                                        {{ request('status') == 'immediate_service' ? 'selected' : '' }}>Immediate Service
                                    </option>
                                    <option value="no_response" {{ request('status') == 'no_response' ? 'selected' : '' }}>
                                        No Response</option>
                                    <option value="location_not_available"
                                        {{ request('status') == 'location_not_available' ? 'selected' : '' }}>Location Not
                                        Available</option>
                                    <option value="night_work_demanded"
                                        {{ request('status') == 'night_work_demanded' ? 'selected' : '' }}>Night Work
                                        Demanded</option>
                                    <option value="customisation"
                                        {{ request('status') == 'customisation' ? 'selected' : '' }}>Customisation</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                        Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                </select>
                            </div>

                            <!-- Lead Source Filter -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Lead Source</label>
                                <select class="form-select" name="lead_source_id" id="sourceFilter">
                                    <option value="">All Sources</option>
                                    @foreach ($lead_sources as $source)
                                        <option value="{{ $source->id }}"
                                            {{ request('lead_source_id') == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date From -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Date From</label>
                                <input type="date" class="form-control" name="date_from" id="dateFromFilter"
                                    value="{{ request('date_from') }}">
                            </div>
                            <!-- Date To -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Date To</label>
                                <input type="date" class="form-control" name="date_to" id="dateToFilter"
                                    value="{{ request('date_to') }}">
                            </div>
                        </div>

                        <div class="row align-items-end g-3 mt-1">

                            <!-- Service Filter -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Service</label>
                                <select class="form-select" name="service_id" id="serviceFilter">
                                    <option value="">All Services</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}"
                                            {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if (auth()->user()->role === 'super_admin')
                                <!-- Branch Filter -->
                                <div class="col-3">
                                    <label class="form-label fw-semibold mb-2">Branch</label>
                                    <select class="form-select" name="branch_id" id="branchFilter">
                                        <option value="">All Branches</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if (in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                                <!-- Assigned To Filter -->
                                <div class="col-3">
                                    <label class="form-label fw-semibold mb-2">Assigned To</label>
                                    <select class="form-select" name="assigned_to" id="filterassignedto">
                                        <option value="">All Telecallers</option>
                                        <option value="unassigned"
                                            {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>Unassigned
                                        </option>
                                        @foreach ($telecallers as $telecaller)
                                            <option value="{{ $telecaller->id }}"
                                                {{ request('assigned_to') == $telecaller->id ? 'selected' : '' }}>
                                                {{ $telecaller->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Search -->
                            <div class="col-3">
                                <label class="form-label fw-semibold mb-2">Search</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <input type="text" class="form-control" name="search" id="searchInput"
                                        placeholder="Search by name, phone, lead/job/customer code..."
                                        value="{{ request('search') }}" style="max-width: 400px;">
                                </div>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-3 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="las la-filter me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('leads.index') }}" class="btn btn-secondary" id="resetBtn">
                                    <i class="las la-redo-alt me-1"></i> Reset
                                </a>
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
                        Leads List (<span id="leadCount">{{ $leads->total() }}</span> total)
                        @if (auth()->user()->role === 'super_admin' && $pendingcount > 0)
                            <span class="badge bg-warning ms-2">{{ $pendingcount }} Pending Approval</span>
                        @endif
                    </h4>

                    <div class="d-flex gap-2 align-items-center">
                        <div class="per-page-selector">
                            <label class="form-label mb-0 me-2 small text-muted">Show:</label>
                            <select class="form-select form-select-sm" id="perPageSelect"
                                style="width: auto; display: inline-block;">
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                            </select>
                            <span class="badge bg-light text-dark ms-2" id="pageInfo">
                                @if ($leads->total() > 0)
                                    {{ $leads->firstItem() ?? 1 }}-{{ $leads->lastItem() ?? $leads->total() }} of
                                    {{ $leads->total() }}
                                @else
                                    0-0 of 0
                                @endif
                            </span>
                        </div>

                        @if (in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                            <button type="button" class="btn btn-info" id="bulkAssignBtn" style="display: none;">
                                <i class="las la-users me-1"></i> Bulk Assign (<span id="bulkcount">0</span>)
                            </button>
                        @endif

                        <a href="{{ route('leads.bulk-import') }}" class="btn btn-success">
                            <i class="las la-file-import me-1"></i> Bulk Import
                        </a>

                        <a href="{{ route('leads.create') }}" class="btn btn-primary">
                            <i class="las la-plus me-1"></i> Create Lead
                        </a>
                    </div>
                </div>

                <!-- VIEW MODE TOGGLE - ADD HERE -->
                <div class="btn-group w-100" role="group" id="viewModeToggle">
                    <button type="button"
                        class="btn btn-outline-primary {{ !request('status') && !request('mode') ? 'active' : '' }}"
                        data-status="" data-mode="">
                        All Leads
                    </button>

                    <button type="button"
                        class="btn btn-outline-warning {{ request('mode') == 'open' ? 'active' : '' }}" data-status=""
                        data-mode="open">
                        <i class="las la-clock me-1"></i> Open Leads
                    </button>

                    <button type="button"
                        class="btn btn-outline-success {{ request('status') == 'approved' ? 'active' : '' }}"
                        data-status="approved" data-mode="">
                        <i class="las la-check-circle me-1"></i> Work Orders / jobs
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover mb-0" id="leadsTable">
                            <thead class="table-light">
                                <tr>
                                    @if (in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                                        <th class="checkbox-col">
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" class="custom-checkbox" id="selectAll"
                                                    title="Select All">
                                            </div>
                                        </th>
                                    @endif
                                    <th class="sortable" data-column="code">Lead Code</th>
                                    <th class="sortable" data-column="name">Name</th>
                                    <th class="sortable" data-column="phone">Phone</th>
                                    <th class="sortable" data-column="service_type">Service Type</th>
                                    <th class="sortable" data-column="status">Status</th>
                                    <th class="sortable" data-column="source">Source</th>
                                    @if (auth()->user()->role === 'super_admin')
                                        <th class="sortable" data-column="branch">Branch</th>
                                    @endif
                                    <th class="sortable" data-column="assigned">Assigned To</th>
                                    @if (auth()->user()->role === 'super_admin')
                                        <th class="sortable" data-column="created-by">Created By</th>
                                    @endif
                                    <th class="sortable" data-column="date">Created Date</th>
                                    <th class="sortable" data-column="approved_date">Approved Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="leadsTableBody">
                                @include('leads.partials.table-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div id="paginationContainer">
                        {{ $leads->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Assign Modal -->
    <div class="modal fade" id="assignLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="las la-user-plus me-2"></i>Assign Lead to Telecaller
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignLeadForm">
                    @csrf
                    <input type="hidden" id="assign_lead_id" name="lead_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lead Information</label>
                            <div class="lead-info-box">
                                <p class="mb-2">
                                    <span class="lead-info-label">
                                        <i class="las la-tag"></i> Lead Code:
                                    </span>
                                    <span class="lead-info-value" id="assign_lead_code"></span>
                                </p>
                                <p class="mb-0">
                                    <span class="lead-info-label">
                                        <i class="las la-user"></i> Name:
                                    </span>
                                    <span class="lead-info-value" id="assign_lead_name"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Branch Selection -->
                        @if (auth()->user()->role === 'super_admin')
                            <div class="mb-3">
                                <label for="assign_branch" class="form-label fw-semibold">
                                    <i class="las la-building me-1"></i>Select Branch <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="assign_branch" name="branch_id" required>
                                    <option value="">Choose branch...</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    <i class="las la-info-circle"></i> Lead's current branch will be pre-selected
                                </small>
                            </div>
                        @else
                            <input type="hidden" id="assign_branch" name="branch_id"
                                value="{{ auth()->user()->branch_id }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Branch</label>
                                <div class="alert alert-info mb-0 py-2">
                                    <i class="las la-building me-1"></i>
                                    <strong
                                        id="assign_branch_display">{{ auth()->user()->branch->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        @endif

                        <!-- Telecaller Selection -->
                        <div class="mb-3">
                            <label for="assign_telecaller" class="form-label fw-semibold">
                                <i class="las la-user-tie me-1"></i>Select Telecaller <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="assign_telecaller" name="assigned_to" required
                                @if (auth()->user()->role === 'super_admin') disabled @endif>
                                <option value="">
                                    @if (auth()->user()->role === 'super_admin')
                                        Please select a branch first...
                                    @else
                                        Choose telecaller...
                                    @endif
                                </option>
                            </select>
                            <small class="text-muted">
                                <i class="las la-info-circle"></i>
                                @if (auth()->user()->role === 'super_admin')
                                    Select a branch above to see available telecallers
                                @else
                                    Only telecallers from your branch are shown
                                @endif
                            </small>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="assign_notes" class="form-label fw-semibold">
                                <i class="las la-comment me-1"></i>Notes (Optional)
                            </label>
                            <textarea class="form-control" id="assign_notes" name="notes" rows="3"
                                placeholder="Add any notes about this assignment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="las la-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-check me-1"></i> Assign Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="las la-users me-2"></i>Bulk Assign Leads to Telecaller
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="bulkAssignForm">
                    @csrf
                    <div class="modal-body">

                        <!-- ===== FIX: Update this section ===== -->
                        <div class="alert alert-light border mb-3"
                            style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border-left: 4px solid #ffc107;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="las la-check-circle" style="font-size: 28px; color: #997404;"></i>
                                <div>
                                    <strong id="selectedcount" style="font-size: 24px; color: #664d03;">0</strong>
                                    <span style="font-size: 16px; color: #664d03;"> leads selected for assignment</span>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Leads Preview -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="las la-list me-1"></i>Selected Leads Preview
                            </label>
                            <div id="selectedleadspreview" class="leads-preview-container"></div>
                            <small class="text-muted mt-2 d-block">
                                <i class="las la-info-circle"></i>
                                You can assign leads of any status, including approved work orders
                            </small>
                        </div>

                        <!-- Branch Filter -->
                        @if (auth()->user()->role === 'super_admin')
                            <div class="mb-3">
                                <label for="bulkbranch" class="form-label fw-semibold">
                                    <i class="las la-building me-1"></i>Select Branch <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="bulkbranch" name="branch_id" required>
                                    <option value="">Choose branch...</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    <i class="las la-info-circle"></i> Select a branch to see available telecallers
                                </small>
                            </div>
                        @else
                            <input type="hidden" id="bulkbranch" name="branch_id"
                                value="{{ auth()->user()->branch_id }}">
                        @endif

                        <!-- Telecaller Selection -->
                        <div class="mb-3">
                            <label for="bulktelecaller" class="form-label fw-semibold">
                                <i class="las la-user-tie me-1"></i>Assign To Telecaller <span
                                    class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="bulktelecaller" name="assigned_to" required disabled>
                                <option value="">Please select a branch first...</option>
                            </select>
                            <small class="text-muted">
                                <i class="las la-info-circle"></i> Select a branch above to see available telecallers
                            </small>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="bulknotes" class="form-label fw-semibold">
                                <i class="las la-comment me-1"></i>Notes (Optional)
                            </label>
                            <textarea class="form-control" id="bulknotes" name="notes" rows="3"
                                placeholder="Add any notes about this bulk assignment..."></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="las la-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="las la-check-double me-1"></i> Assign All Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('extra-scripts')
    <script src="{{ asset('assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        @if (session('success'))
            let successData = @json(json_decode(session('success'), true));
            Swal.fire({
                icon: 'success',
                title: successData.title,
                html: `<p>${successData.message}</p>
                    ${successData.lead_code ? `<p><strong>Lead Code:</strong> <span class="badge bg-primary">${successData.lead_code}</span></p>` : ''}
                    ${successData.name ? `<p><strong>Name:</strong> ${successData.name}</p>` : ''}`,
                // timer: 4000,
                showConfirmButton: true,
                confirmButtonColor: '#28a745'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
            });
        @endif

        // ============================================
        // LOADING OVERLAY FUNCTIONS
        // ============================================
        function showLoadingOverlay(recordCount) {
            let message = 'Loading leads...';
            let subtext = `Fetching ${recordCount} records`;

            if (recordCount >= 500) {
                message = 'Loading large dataset...';
                subtext = `Please wait, fetching ${recordCount} records`;
            }

            let overlay = `
                <div class="table-loading-overlay" id="loadingOverlay">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">${message}</div>
                        <div class="loading-subtext">${subtext}</div>
                    </div>
                </div>
            `;

            $('body').append(overlay);
        }

        function hideLoadingOverlay() {
            $('#loadingOverlay').fadeOut(200, function() {
                $(this).remove();
            });
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#serviceFilter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search and select service',
                allowClear: true,
                width: '100%'
            });

            // Data from server
            const allTelecallers = @json(
                $telecallers->map(function ($t) {
                    return ['id' => $t->id, 'name' => $t->name, 'branch_id' => $t->branch_id];
                }));

            const allBranches = @json($branches);

            console.log('All Telecallers:', allTelecallers);
            console.log('All Branches:', allBranches);

            let currentSort = {
                column: null,
                direction: 'asc'
            };

            // ============================================
            // VIEW MODE TOGGLE HANDLER
            // ============================================
            $('#viewModeToggle button').on('click', function() {
                $('#viewModeToggle button').removeClass('active');
                $(this).addClass('active');

                let status = $(this).data('status');
                let mode = $(this).data('mode');

                console.log('View Mode Clicked:', {
                    status: status,
                    mode: mode
                });

                // Update status filter
                if (status) {
                    $('#filterStatus').val(status);
                } else {
                    $('#filterStatus').val(''); // Reset status for "All Leads" and "Open Leads"
                }

                // Add or update hidden mode input
                let modeInput = $('#filterMode');
                if (modeInput.length === 0) {
                    $('#filterForm').append('<input type="hidden" id="filterMode" name="mode">');
                    modeInput = $('#filterMode');
                }
                modeInput.val(mode);

                // Load leads with new filter
                loadLeads();
            });

            // ============================================
            // STATUS FILTER CHANGE HANDLER
            // ============================================
            $('#filterStatus').on('change', function() {
                let value = $(this).val();

                // Clear mode when status is changed manually
                let modeInput = $('#filterMode');
                if (modeInput.length === 0) {
                    $('#filterForm').append('<input type="hidden" id="filterMode" name="mode">');
                    modeInput = $('#filterMode');
                }

                // Sync viewModeToggle visual state
                $('#viewModeToggle button').removeClass('active');

                if (value === 'approved') {
                    $('#viewModeToggle button[data-status="approved"]').addClass('active');
                    modeInput.val('');
                } else if (value === '') {
                    // Check if we're in open mode
                    if (modeInput.val() === 'open') {
                        $('#viewModeToggle button[data-mode="open"]').addClass('active');
                    } else {
                        $('#viewModeToggle button[data-status=""][data-mode=""]').addClass('active');
                        modeInput.val('');
                    }
                } else {
                    // Some other status selected, clear mode
                    modeInput.val('');
                }

                // Load leads
                loadLeads();
            });

            // ============================================
            // PER PAGE CHANGE HANDLER
            // ============================================
            $('#perPageSelect').on('change', function() {
                let perPage = $(this).val();

                // Show warning for large datasets
                if (perPage >= 500) {
                    Swal.fire({
                        title: 'Loading ' + perPage + ' records',
                        text: 'This may take a few seconds. Continue?',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Load',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#0d6efd'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            loadLeads();
                        } else {
                            // Reset to previous value
                            $(this).val('15');
                        }
                    });
                } else {
                    loadLeads();
                }
            });

            // ============================================
            // AJAX LOAD FUNCTION WITH SORTING
            // ============================================
            function loadLeads(url = null) {
                let requestUrl = url || '{{ route('leads.index') }}';

                let params = {
                    status: $('#filterStatus').val(),
                    branch_id: $('#branchFilter').val(),
                    lead_source_id: $('#sourceFilter').val(),
                    search: $('#searchInput').val(),
                    date_from: $('#dateFromFilter').val(),
                    date_to: $('#dateToFilter').val(),
                    assigned_to: $('#filterassignedto').val(),
                    service_id: $('#serviceFilter').val(),
                    sort_column: currentSort.column,
                    sort_direction: currentSort.direction,
                    per_page: $('#perPageSelect').val(),
                    mode: $('#filterMode').val() // Include mode parameter
                };

                let perPage = parseInt($('#perPageSelect').val());
                let showOverlay = perPage >= 100;

                if (showOverlay) {
                    showLoadingOverlay(perPage);
                } else {
                    $('#leadsTable').addClass('table-loading');
                }

                $.ajax({
                    url: requestUrl,
                    type: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#leadsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#leadCount').text(response.total);

                        // Update page info with proper null checks
                        if (response.from && response.to) {
                            $('#pageInfo').text(response.from + '-' + response.to + ' of ' + response
                                .total);
                        } else if (response.total == 0) {
                            $('#pageInfo').text('0-0 of 0');
                        } else {
                            $('#pageInfo').text('1-' + response.total + ' of ' + response.total);
                        }

                        // Update sort indicators
                        if (response.current_sort) {
                            updateSortIndicators(response.current_sort.column, response.current_sort
                                .direction);
                        }

                        // Hide loading indicators
                        if (showOverlay) {
                            hideLoadingOverlay();
                        } else {
                            $('#leadsTable').removeClass('table-loading');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading leads:', xhr);

                        if (showOverlay) {
                            hideLoadingOverlay();
                        } else {
                            $('#leadsTable').removeClass('table-loading');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load leads'
                        });
                    }
                });
            }

            // ============================================
            // UPDATE SORT INDICATORS
            // ============================================
            function updateSortIndicators(column, direction) {
                $('.sortable').removeClass('asc desc');
                $(`.sortable[data-column="${column}"]`).addClass(direction);
            }

            // ============================================
            // SORT COLUMN CLICK HANDLER
            // ============================================
            $(document).on('click', '.sortable', function() {
                let column = $(this).data('column');

                // Toggle direction if same column, otherwise reset to asc
                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                // Load leads with new sorting
                loadLeads();
            });

            // ============================================
            // PAGINATION CLICK HANDLER
            // ============================================
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                if (url) {
                    loadLeads(url);
                }
            });

            // ============================================
            // FILTER FORM SUBMIT
            // ============================================
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadLeads();
            });

            // ============================================
            // SEARCH WITH DEBOUNCE
            // ============================================
            let searchTimeout;
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadLeads();
                }, 500);
            });

            // ============================================
            // RESET FILTERS
            // ============================================
            $('#resetBtn').on('click', function(e) {
                e.preventDefault();
                $('#filterStatus').val('');
                $('#branchFilter').val('');
                $('#sourceFilter').val('');
                $('#dateFromFilter').val('');
                $('#dateToFilter').val('');
                $('#searchInput').val('');
                $('#filter_assigned_to').val('');
                $('#serviceFilter').val('').trigger('change');

                currentSort = {
                    column: null,
                    direction: 'asc'
                };
                $('.sortable').removeClass('asc desc');

                window.location.href = '{{ route('leads.index') }}';
            });

            // Select All checkbox
            $('#selectAll').on('change', function() {
                $('.lead-checkbox').prop('checked', $(this).is(':checked'));
                updateBulkAssignButton();
            });

            // Individual checkbox change
            $(document).on('change', '.lead-checkbox', function() {
                updateBulkAssignButton();

                let total = $('.lead-checkbox').length;
                let checked = $('.lead-checkbox:checked').length;
                $('#selectAll').prop('checked', total === checked);
            });

            // Update bulk assign button visibility and count
            function updateBulkAssignButton() {
                let checkedCount = $('.lead-checkbox:checked').length;
                $('#bulkcount').text(checkedCount);

                if (checkedCount > 0) {
                    $('#bulkAssignBtn').fadeIn();
                } else {
                    $('#bulkAssignBtn').fadeOut();
                }
            }

            // Single Assign - Branch change handler
            $('#assign_branch').on('change', function() {
                let selectedBranchId = $(this).val();
                let telecallerSelect = $('#assign_telecaller');

                console.log('Single Assign - Branch Selected:', selectedBranchId);

                telecallerSelect.html('<option value="">Choose telecaller...</option>');

                if (selectedBranchId && selectedBranchId !== '') {
                    telecallerSelect.prop('disabled', false);

                    let filteredTelecallers = allTelecallers.filter(t => t.branch_id == selectedBranchId);
                    console.log('Single Assign - Filtered Telecallers:', filteredTelecallers);

                    filteredTelecallers.forEach(function(telecaller) {
                        telecallerSelect.append(
                            $('<option>', {
                                value: telecaller.id,
                                text: telecaller.name
                            })
                        );
                    });

                    if (filteredTelecallers.length === 0) {
                        telecallerSelect.append(
                            '<option value="" disabled>No telecallers in this branch</option>');
                    }
                } else {
                    telecallerSelect.prop('disabled', true);
                    telecallerSelect.html('<option value="">Please select a branch first...</option>');
                }
            });

            // Single Assign Lead
            $(document).on('click', '.assignLeadBtn', function() {
                let leadId = $(this).data('id');
                let leadName = $(this).data('name');
                let leadCode = $(this).data('code');
                let branchId = $(this).data('branch');

                console.log('Single Assign - Lead Details:', {
                    leadId: leadId,
                    leadName: leadName,
                    leadCode: leadCode,
                    branchId: branchId
                });

                $('#assign_lead_id').val(leadId);
                $('#assign_lead_name').text(leadName);
                $('#assign_lead_code').text(leadCode);
                $('#assign_lead_branch').val(branchId);
                $('#assign_notes').val('');

                // Find branch name
                let branch = allBranches.find(b => b.id == branchId);
                console.log('Found Branch:', branch);
                $('#assign_lead_branch_name').text(branch ? branch.name : 'N/A');

                // Filter telecallers by branch
                let telecallerSelect = $('#assign_telecaller');
                telecallerSelect.html('<option value="">Choose telecaller...</option>');

                let filteredTelecallers = allTelecallers.filter(t => t.branch_id == branchId);
                console.log('Filtered Telecallers for branch ' + branchId + ':', filteredTelecallers);

                filteredTelecallers.forEach(function(telecaller) {
                    telecallerSelect.append(
                        $('<option>', {
                            value: telecaller.id,
                            text: telecaller.name
                        })
                    );
                });

                if (filteredTelecallers.length === 0) {
                    telecallerSelect.append(
                        '<option value="" disabled>No telecallers in this branch</option>');
                    console.warn('No telecallers found for branch:', branchId);
                }

                $('#assignLeadModal').modal('show');
            });

            $('#assignLeadForm').on('submit', function(e) {
                e.preventDefault();

                let leadId = $('#assign_lead_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: `/leads/${leadId}/assign`,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#assignLeadModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Assigned!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadLeads();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to assign lead',
                            'error');
                    }
                });
            });

            // Bulk Assign branch filter
            $('#bulk_branch').on('change', function() {
                let selectedBranchId = $(this).val();
                let telecallerSelect = $('#bulk_telecaller');

                console.log('Bulk Assign - Branch Selected:', selectedBranchId);

                telecallerSelect.html('<option value="">Choose telecaller...</option>');

                if (selectedBranchId && selectedBranchId !== '') {
                    telecallerSelect.prop('disabled', false);

                    let filteredTelecallers = allTelecallers.filter(t => t.branch_id == selectedBranchId);
                    console.log('Bulk Assign - Filtered Telecallers:', filteredTelecallers);

                    filteredTelecallers.forEach(function(telecaller) {
                        telecallerSelect.append(
                            $('<option>', {
                                value: telecaller.id,
                                text: telecaller.name
                            })
                        );
                    });

                    if (filteredTelecallers.length === 0) {
                        telecallerSelect.append(
                            '<option value="" disabled>No telecallers in this branch</option>');
                    }
                } else {
                    telecallerSelect.prop('disabled', true);
                    telecallerSelect.html('<option value="">Please select a branch first...</option>');
                }
            });

            // ============================================
            // BULK ASSIGNMENT WITH LOADING
            // ============================================

            // Show bulk assignment loading overlay
            function showBulkAssignLoading(count, telecallerName) {
                let overlay = `
                    <div class="table-loading-overlay" id="bulkAssignLoadingOverlay">
                        <div class="loading-content">
                            <div class="loading-spinner"></div>
                            <div class="loading-text">Assigning ${count} leads...</div>
                            <div class="loading-subtext">Assigning to ${telecallerName}</div>
                            <div class="progress mt-3" style="height: 20px; width: 300px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar"
                                    style="width: 0%"
                                    id="bulkAssignProgress">0%</div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(overlay);
            }

            function hideBulkAssignLoading() {
                $('#bulkAssignLoadingOverlay').fadeOut(200, function() {
                    $(this).remove();
                });
            }

            // Update progress bar
            function updateBulkAssignProgress(percentage) {
                $('#bulkAssignProgress')
                    .css('width', percentage + '%')
                    .text(Math.round(percentage) + '%');
            }

            // Bulk Assign Button Click Handler
            $('#bulkAssignBtn').on('click', function() {
                console.log('=== BULK ASSIGN BUTTON CLICKED ===');

                // Clear previous data
                $('#approvedLeadsWarning').remove();
                $('#selectedleadspreview').html('');

                let selectedLeads = [];
                let previewHtml = '';
                let branchCounts = {};
                let statusCounts = {
                    approved: 0,
                    pending: 0,
                    other: 0
                };

                // Collect all checked leads
                $('.lead-checkbox:checked').each(function() {
                    let leadId = $(this).val();
                    let leadName = $(this).data('name');
                    let leadCode = $(this).data('code');
                    let branchId = $(this).data('branch');
                    let status = $(this).data('status');

                    selectedLeads.push(leadId);

                    // Count by status
                    if (status === 'approved') {
                        statusCounts.approved++;
                    } else if (status === 'pending') {
                        statusCounts.pending++;
                    } else {
                        statusCounts.other++;
                    }

                    // Count per branch
                    if (branchId) {
                        branchCounts[branchId] = (branchCounts[branchId] || 0) + 1;
                    }

                    // Build preview HTML
                    let statusBadgeClass = 'secondary';
                    let statusLabel = status || 'Unknown';

                    if (status === 'approved') {
                        statusBadgeClass = 'success';
                        statusLabel = 'Approved';
                    } else if (status === 'pending') {
                        statusBadgeClass = 'warning';
                        statusLabel = 'Pending';
                    }

                    previewHtml += `
                        <div class="lead-preview-item">
                            <i class="las la-user-circle lead-preview-icon"></i>
                            <span class="lead-preview-badge">${leadCode}</span>
                            <span class="lead-preview-name">${leadName}</span>
                            <span class="badge bg-${statusBadgeClass} ms-auto text-white">${statusLabel}</span>
                        </div>
                    `;
                });

                console.log('Selected Leads Count:', selectedLeads.length);
                console.log('Status Counts:', statusCounts);
                console.log('Branch Counts:', branchCounts);

                // ===== FIX: Update the count properly =====
                $('#selectedcount').text(selectedLeads.length);

                // Update preview
                $('#selectedleadspreview').html(previewHtml);

                // Reset form
                $('#bulkbranch').val('');
                $('#bulknotes').val('');
                $('#bulktelecaller')
                    .html('<option value="">Please select a branch first...</option>')
                    .prop('disabled', true);

                // Auto-select branch if all from same branch
                let branchIds = Object.keys(branchCounts);
                if (branchIds.length === 1 && branchIds[0] != 'null' && branchIds[0] != 'undefined') {
                    $('#bulkbranch').val(branchIds[0]).trigger('change');
                }

                // Show warning for approved leads
                if (statusCounts.approved > 0) {
                    $('#approvedLeadsWarning').remove();

                    let warningHtml = `
                        <div class="alert alert-warning mb-3" id="approvedLeadsWarning">
                            <div class="d-flex align-items-center gap-2">
                                <i class="las la-exclamation-triangle" style="font-size: 24px;"></i>
                                <div>
                                    <strong>Note:</strong> You are reassigning <strong>${statusCounts.approved}</strong> approved lead(s).
                                    <br>
                                    <small>The assignment will be updated but work orders will remain unchanged.</small>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#bulkAssignModal .modal-body').prepend(warningHtml);
                }

                // Show modal
                $('#bulkAssignModal').modal('show');
            });

            // ============================================
            // MODAL CLEANUP ON CLOSE
            // ============================================
            $('#bulkAssignModal').on('hidden.bs.modal', function() {
                $('#approvedLeadsWarning').remove();
                $('#selectedleadspreview').html('');
                $('#selectedcount').text('0');
                $('#bulknotes').val('');
            });

            // ============================================
            // BULK ASSIGN FORM SUBMIT - FIXED
            // ============================================
            $('#bulkbranch').on('change', function() {
                let selectedBranchId = $(this).val();
                let telecallerSelect = $('#bulktelecaller');

                console.log('Bulk - Branch Selected:', selectedBranchId);

                telecallerSelect.html('<option value="">Choose telecaller...</option>');

                if (selectedBranchId && selectedBranchId != '') {
                    telecallerSelect.prop('disabled', false);

                    let filteredTelecallers = allTelecallers.filter(t => t.branch_id == selectedBranchId);

                    console.log('Bulk - Filtered Telecallers:', filteredTelecallers);

                    if (filteredTelecallers.length > 0) {
                        filteredTelecallers.forEach(function(telecaller) {
                            telecallerSelect.append(
                                $('<option>', {
                                    value: telecaller.id,
                                    text: telecaller.name
                                })
                            );
                        });
                    } else {
                        telecallerSelect.append(
                            '<option value="" disabled>No telecallers in this branch</option>');
                    }
                } else {
                    telecallerSelect.prop('disabled', true);
                    telecallerSelect.html('<option value="">Please select a branch first...</option>');
                }
            });

            // ============================================
            // BULK ASSIGN FORM SUBMIT - FIXED
            // ============================================
            $('#bulkAssignForm').on('submit', function(e) {
                e.preventDefault();

                // Re-query selected leads to ensure accuracy
                let selectedLeads = [];
                $('.lead-checkbox:checked').each(function() {
                    selectedLeads.push($(this).val());
                });

                let assignedTo = $('#bulktelecaller').val();
                let notes = $('#bulknotes').val();
                let telecallerName = $('#bulktelecaller option:selected').text();

                console.log('Form Submit - Data:', {
                    selectedCount: selectedLeads.length,
                    assignedTo: assignedTo,
                    telecallerName: telecallerName,
                    leads: selectedLeads
                });

                // Validation
                if (selectedLeads.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Leads Selected',
                        text: 'Please select at least one lead to assign',
                        confirmButtonColor: '#dc3545'
                    });
                    return false;
                }

                if (!assignedTo || assignedTo === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Telecaller Selected',
                        text: 'Please select a telecaller to assign the leads to',
                        confirmButtonColor: '#dc3545'
                    });
                    return false;
                }

                // Close modal and show loading
                $('#bulkAssignModal').modal('hide');
                showBulkAssignLoading(selectedLeads.length, telecallerName);
                updateBulkAssignProgress(10);

                $.ajax({
                    url: '{{ route('leads.bulkAssign') }}',
                    type: 'POST',
                    data: {
                        lead_ids: selectedLeads,
                        assigned_to: assignedTo,
                        notes: notes
                    },
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();

                        // Simulate progress
                        let progress = 10;
                        let interval = setInterval(function() {
                            if (progress < 90) {
                                progress += 10;
                                updateBulkAssignProgress(progress);
                            }
                        }, 200);

                        xhr.addEventListener('loadend', function() {
                            clearInterval(interval);
                            updateBulkAssignProgress(100);
                        });

                        return xhr;
                    },
                    success: function(response) {
                        console.log('Bulk Assign Success:', response);

                        updateBulkAssignProgress(100);

                        setTimeout(function() {
                            hideBulkAssignLoading();

                            let successHtml = `
                                <div class="text-center">
                                    <i class="las la-check-circle text-success" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 mb-4">Bulk Assignment Complete!</h5>
                                    <div class="text-start mx-auto" style="max-width: 350px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                            <span class="text-muted">Total Assigned:</span>
                                            <strong class="text-primary">${response.count} leads</strong>
                                        </div>
                                        ${response.approved_count > 0 ? `
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                <span class="text-muted">Approved Leads:</span>
                                                <strong class="text-success">${response.approved_count}</strong>
                                            </div>
                                            ` : ''}
                                        <div class="d-flex justify-content-between align-items-center pt-1">
                                            <span class="text-muted">Assigned To:</span>
                                            <strong class="text-info">${response.telecaller_name}</strong>
                                        </div>
                                    </div>
                                </div>
                            `;

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: successHtml,
                                timer: 5000,
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                // Reset checkboxes
                                $('.lead-checkbox').prop('checked', false);
                                $('#selectAll').prop('checked', false);
                                updateBulkAssignButton();

                                // Reload leads
                                loadLeads();
                            });
                        }, 500);
                    },
                    error: function(xhr) {
                        console.error('Bulk Assign Error:', xhr);
                        hideBulkAssignLoading();

                        let errorMsg = 'Failed to assign leads';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Assignment Failed',
                            text: errorMsg,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });

                return false;
            });

            // For non-super-admin users (lead managers)
            @if (auth()->user()->role === 'lead_manager')
                let userBranchId = {{ auth()->user()->branch_id ?? 'null' }};
                if (userBranchId) {
                    // When bulk assign modal opens, auto-enable telecaller dropdown for lead manager
                    $('#bulkAssignModal').on('show.bs.modal', function() {
                        let telecallerSelect = $('#bulk_telecaller');
                        telecallerSelect.prop('disabled', false);
                        telecallerSelect.html('<option value="">Choose telecaller...</option>');

                        let filteredTelecallers = allTelecallers.filter(t => t.branch_id == userBranchId);

                        filteredTelecallers.forEach(function(telecaller) {
                            telecallerSelect.append(
                                $('<option>', {
                                    value: telecaller.id,
                                    text: telecaller.name
                                })
                            );
                        });
                    });
                }
            @endif

            // Delete Lead
            $(document).on('click', '.deleteLeadBtn', function() {
                let leadId = $(this).data('id');
                let leadName = $(this).data('name');

                Swal.fire({
                    title: 'Delete Lead?',
                    text: `Are you sure you want to delete ${leadName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/leads/${leadId}`,
                            type: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        loadLeads();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Could not delete lead.', 'error');
                            }
                        });
                    }
                });
            });

            // Approve Lead
            $(document).on('click', '.approveLeadBtn', function() {
                let leadId = $(this).data('id');
                let leadName = $(this).data('name');
                let leadAmount = $(this).data('amount');

                if (!leadAmount || leadAmount == 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Amount Required',
                        text: 'Please set the lead amount before approving.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Approve Lead?',
                    html: `
                        <p>Lead: <strong>${leadName}</strong></p>
                        <p>Amount: <strong>₹${leadAmount}</strong></p>
                        <textarea id="approval_notes" class="swal2-textarea" placeholder="Add approval notes (optional)" style="width: 100%; min-height: 80px; padding: 10px; border: 1px solid #d9d9d9; border-radius: 4px;"></textarea>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let notes = $('#approval_notes').val();
                        $.ajax({
                            url: `/leads/${leadId}/approve`,
                            type: 'POST',
                            data: {
                                approval_notes: notes
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Approved!',
                                        html: `${response.message}<br>
                                               <small>Customer: ${response.customer_code}</small><br>
                                               <small>Job: ${response.job_code}</small><br>
                                               <small>Remaining Budget: ${response.remaining_budget}</small>`,
                                        timer: 4000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        loadLeads();
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'Could not approve lead.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                if (xhr.responseJSON && xhr.responseJSON.budget_info) {
                                    let info = xhr.responseJSON.budget_info;
                                    errorMsg += `<br><br><small>
                                        Daily Limit: ${info.daily_limit}<br>
                                        Used Today: ${info.today_total}<br>
                                        Remaining: ${info.remaining}<br>
                                        Requested: ${info.requested}<br>
                                        Excess: ${info.excess}
                                    </small>`;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Approval Failed',
                                    html: errorMsg
                                });
                            }
                        });
                    }
                });
            });

            // Reject Lead
            $(document).on('click', '.rejectLeadBtn', function() {
                let leadId = $(this).data('id');
                let leadName = $(this).data('name');

                Swal.fire({
                    title: 'Reject Lead?',
                    html: `
                        <p>Are you sure you want to reject <strong>${leadName}</strong>?</p>
                        <textarea id="rejection_reason" class="swal2-textarea" placeholder="Enter rejection reason (required)" required style="width: 100%; min-height: 80px; padding: 10px; border: 1px solid #d9d9d9; border-radius: 4px;"></textarea>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Reject',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    preConfirm: () => {
                        const reason = $('#rejection_reason').val();
                        if (!reason) {
                            Swal.showValidationMessage('Rejection reason is required');
                        }
                        return {
                            reason: reason
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/leads/${leadId}/reject`,
                            type: 'POST',
                            data: {
                                rejection_reason: result.value.reason
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Rejected!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        loadLeads();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Could not reject lead.', 'error');
                            }
                        });
                    }
                });
            });

            function sortLeadsTable(column, direction) {
                let rows = $('#leadsTableBody tr').get();

                rows.sort(function(a, b) {
                    let aVal, bVal;

                    switch (column) {
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
                        case 'branch':
                            aVal = $(a).data('branch');
                            bVal = $(b).data('branch');
                            break;
                        case 'status':
                            let statusOrder = {
                                'pending': 1,
                                'approved': 2,
                                'rejected': 3
                            };
                            aVal = statusOrder[$(a).data('status')];
                            bVal = statusOrder[$(b).data('status')];
                            break;
                        case 'assigned':
                            aVal = $(a).data('assigned');
                            bVal = $(b).data('assigned');
                            break;
                        case 'created-by':
                            aVal = $(a).data('created-by');
                            bVal = $(b).data('created-by');
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
