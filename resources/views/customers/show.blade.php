@extends('layouts.app')

@section('title', 'Customer Details')

@section('extra-css')
<style>
    .job-link {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }

    .job-link:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    .job-code-badge {
        cursor: pointer;
        transition: all 0.2s;
    }

    .job-code-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .note-card {
        transition: all 0.3s ease;
    }

    .note-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .deleteNoteBtn {
        opacity: 0.6;
        transition: opacity 0.2s, transform 0.2s;
    }

    .deleteNoteBtn:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    /* Badge styles for job status */
    .badge-pending { background-color: #ffc107; color: #000; }
    .badge-confirmed { background-color: #28a745; color: #fff; }
    .badge-assigned { background-color: #17a2b8; color: #fff; }
    .badge-in_progress { background-color: #007bff; color: #fff; }
    .badge-completed { background-color: #28a745; color: #fff; }
    .badge-cancelled { background-color: #dc3545; color: #fff; }

    /* Services Badges */
    .services-section {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .service-badge {
            background: var(--primary-blue);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .service-checkbox-item {
            padding: 8px 10px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .service-checkbox-wrapper {
            display: flex;
            align-items: center;
        }

        .service-checkbox-item:hover {
            background: #f8f9fa;
        }

        .service-checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }

        .service-checkbox-item label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

        .service-select-box {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            min-height: 150px;
            max-height: 200px;
            overflow-y: auto;
        }

        .service-quantity-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .service-quantity-input {
            width: 80px;
            padding: 4px 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-align: center;
            font-size: 0.875rem;
        }

        .service-quantity-input:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .quantity-label {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
        }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Customer Details</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">{{ $customer->customer_code }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Customer Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Customer Information</h5>
                    @if($customer->priority === 'high')
                        <span class="badge bg-danger">High Priority</span>
                    @elseif($customer->priority === 'medium')
                        <span class="badge bg-warning">Medium Priority</span>
                    @else
                        <span class="badge bg-success">Low Priority</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p><strong>Customer Code:</strong><br><span class="badge bg-primary fs-6">{{ $customer->customer_code }}</span></p>
                <hr>

                {{-- Branch Information --}}
                @if($customer->branch)
                <p>
                    <strong>Branch:</strong><br>
                    <span class="badge bg-info text-dark">
                        <i class="las la-building"></i> {{ $customer->branch->name }}
                    </span>
                </p>
                <hr>
                @endif

                <p><strong>Name:</strong><br>{{ $customer->name }}</p>
                <p><strong>Email:</strong><br>
                    @if($customer->email)
                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </p>
                <p><strong>Phone:</strong><br>
                    @if($customer->phone)
                        <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </p>
                <p><strong>Address:</strong><br>{{ $customer->address ?? 'N/A' }}</p>
                <p><strong>Customer Since:</strong><br>{{ $customer->created_at->format('d M Y') }}</p>

                @if($customer->lead)
                <hr>
                <p><strong>Original Lead:</strong><br><span class="badge bg-secondary">{{ $customer->lead->lead_code }}</span></p>
                @endif

                @if($customer->notes)
                <hr>
                <p><strong>General Notes:</strong><br>{{ $customer->notes }}</p>
                @endif

                <hr>
                <button type="button" class="btn btn-sm btn-primary w-100" onclick="window.location.href='{{ route('customers.index') }}'">
                    <i class="las la-arrow-left"></i> Back to Customers
                </button>
            </div>
        </div>
    </div>

    <!-- Stats & Jobs -->
    <div class="col-lg-8">
        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h3 class="mb-1 text-success fw-bold">₹{{ number_format($customer->total_value ?? 0) }}</h3>
                        <small class="text-muted">Customer Value</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h3 class="mb-1 text-primary">{{ $customer->jobs->count() }}</h3>
                        <small class="text-muted">Total Work Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h3 class="mb-1 text-success">{{ $customer->completedJobs->count() }}</h3>
                        <small class="text-muted">Completed Work Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h3 class="mb-1 text-info">{{ $customer->jobs->whereIn('status', ['pending', 'assigned', 'in_progress'])->count() }}</h3>
                        <small class="text-muted">Pending Work Orders</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            @if(in_array(auth()->user()->role, ['super_admin','lead_manager','telecallers']))
                <button type="button" class="btn btn-primary w-100" id="addJobBtnFromCustomer">
                    <i class="las la-plus me-1"></i> Create New Work Order
                </button>
            @endif
        </div>

        <!-- Completed Jobs -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Completed Work orders History</h5>
            </div>
            <div class="card-body">
                @if($customer->completedJobs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Code</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Scheduled Date</th>
                                    <th>Assigned To</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->completedJobs as $job)
                                <tr>
                                    <td>
                                        <a href="{{ route('jobs.show', $job->id) }}" class="job-code-badge badge bg-success text-decoration-none" title="View Job Details">
                                            {{ $job->job_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('jobs.show', $job->id) }}" class="job-link">
                                            {{ $job->title ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($job->amount)
                                            <span class="text-success fw-bold">₹{{ number_format($job->amount, 0) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'N/A' }}</td>
                                    <td>{{ $job->assignedTo->name ?? 'Unassigned' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('jobs.show', $job->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="las la-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No completed Work orders yet.</p>
                @endif
            </div>
        </div>

        <!-- Pending & Active Jobs Section -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Pending & Active Jobs</h5>
            </div>
            <div class="card-body">
                @php
                    $pendingJobs = $customer->jobs->whereIn('status', ['pending', 'assigned', 'confirmed', 'postponed', 'work_on_hold']);
                @endphp

                @if($pendingJobs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Scheduled Date</th>
                                    <th>Assigned To</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingJobs as $job)
                                <tr>
                                    <td>
                                        <a href="{{ route('jobs.show', $job->id) }}" class="job-code-badge badge bg-info text-decoration-none" title="View Job Details">
                                            {{ $job->job_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('jobs.show', $job->id) }}" class="job-link">
                                            {{ $job->title ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($job->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($job->status === 'assigned')
                                            <span class="badge bg-info">Assigned</span>
                                        @elseif($job->status === 'confirmed')
                                            <span class="badge bg-success">Confirmed</span>
                                        @elseif($job->status === 'postponed')
                                            <span class="badge bg-warning">Postponed</span>
                                        @elseif($job->status === 'work_on_hold')
                                            <span class="badge bg-danger">Work on Hold</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->amount)
                                            <span class="fw-bold">₹{{ number_format($job->amount, 0) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->scheduled_date)
                                            {{ $job->scheduled_date->format('d M Y') }}
                                            @if($job->scheduled_time)
                                                <br><small class="text-muted">{{ $job->scheduled_time }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Not Scheduled</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->assignedTo->name ?? 'Unassigned' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('jobs.show', $job->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="las la-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No pending jobs at the moment.</p>
                @endif
            </div>
        </div>

        <!-- Customer Notes -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Customer Notes & Instructions</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="$('#addNoteModal').modal('show')">
                        <i class="las la-plus"></i> Add Note
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($customer->customerNotes && $customer->customerNotes->count() > 0)
                    @foreach($customer->customerNotes as $note)
                    <div class="card mb-2 note-card" id="note-{{ $note->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $note->createdBy->name }}</strong>
                                    <small class="text-muted ms-2">{{ $note->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    {{-- Show related job if exists --}}
                                    @if($note->job)
                                        <a href="{{ route('jobs.show', $note->job->id) }}"
                                           class="badge bg-info text-decoration-none"
                                           title="View Related Job: {{ $note->job->title }}">
                                            <i class="las la-briefcase"></i> {{ $note->job->job_code }}
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">General Note</span>
                                    @endif

                                    {{-- Delete button: Show for super_admin or note creator --}}
                                    @if(auth()->user()->role === 'super_admin' || $note->created_by === auth()->id())
                                        <button type="button"
                                                class="btn btn-sm btn-link text-danger p-0 deleteNoteBtn"
                                                data-note-id="{{ $note->id }}"
                                                title="Delete Note">
                                            <i class="las la-trash fs-5"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Show related job details if exists --}}
                            @if($note->job)
                                <div class="alert alert-light border-start border-info border-3 py-2 px-3 mb-2">
                                    <small class="d-block">
                                        <i class="las la-briefcase text-info"></i>
                                        <strong>Related to:</strong> {{ $note->job->title }}
                                    </small>
                                    <small class="text-muted d-block">
                                        Status:
                                        <span class="badge badge-{{ $note->job->status }} badge-sm">
                                            {{ ucfirst(str_replace('_', ' ', $note->job->status)) }}
                                        </span>
                                        @if($note->job->amount)
                                            | Amount: ₹{{ number_format($note->job->amount, 2) }}
                                        @endif
                                    </small>
                                </div>
                            @endif

                            <p class="mb-0">{{ $note->note }}</p>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="las la-sticky-note" style="font-size: 4rem; opacity: 0.2;"></i>
                        <p class="text-muted mt-3 mb-0">No notes added yet.</p>
                        <small class="text-muted">Add your first note to keep track of customer interactions</small>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Add/Edit Job Modal -->
<div class="modal fade" id="jobModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
aria-hidden="true">
<div class="modal-dialog modal-lg">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title" id="jobModalLabel">Add Work Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="jobForm">
            @csrf
            <input type="hidden" id="jobid" name="jobid">

            <div class="modal-body">

                {{-- Row 1 --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label required-field">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <span class="error-text titleerror text-danger d-block mt-1"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Branch</label>

                        {{-- Display only --}}
                        <input type="text"
                               class="form-control bg-light"
                               id="branchNameDisplay"
                               value="{{ $customer->branch->name ?? 'N/A' }}"
                               readonly style="cursor:not-allowed;">

                        {{-- Submit value --}}
                        <input type="hidden"
                               id="branchIdHidden"
                               name="branch_id"
                               value="{{ $customer->branch_id }}">
                    </div>
                </div>

                {{-- Row 2 --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Customer</label>

                        {{-- Display only --}}
                        <input type="text"
                               class="form-control bg-light"
                               id="customerNameDisplay"
                               value="{{ $customer->customer_code }} - {{ $customer->name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}"
                               readonly style="cursor:not-allowed;">

                        {{-- Submit value --}}
                        <input type="hidden"
                               id="customerIdHidden"
                               name="customer_id"
                               value="{{ $customer->id }}">
                    </div>

                    <div class="col-md-6">
                        <label for="servicetype" class="form-label required-field">Service Type</label>
                        <select class="form-select" id="servicetype" name="service_type">
                            <option value="">All Services</option>
                            @foreach ($serviceTypes as $type)
                                <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                        <span class="error-text servicetypeerror text-danger d-block mt-1"></span>
                    </div>
                </div>

                {{-- Services with Quantity --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label required-field">Select Services <span class="badge bg-info">Multiple Selection from Any Type</span></label>

                        <!-- Search Box for Services -->
                        <div class="mb-2">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="las la-search"></i>
                                </span>
                                <input type="text"
                                    class="form-control"
                                    id="serviceSearchInput"
                                    placeholder="Search services by name...">
                                <button class="btn btn-outline-secondary" type="button" id="clearServiceSearch">
                                    <i class="las la-times"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div class="service-select-box @error('service_ids') is-invalid @enderror" id="servicesContainer">
                            <p class="text-muted text-center my-5">
                                <i class="las la-spinner la-spin" style="font-size: 2rem;"></i><br>
                                Loading services...
                            </p>
                        </div>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i>
                            You can select services from different types. Use the "Service Type" filter or search box above.
                        </small>
                        @error('service_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Amounts --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01"
                            min="0" placeholder="0.00">
                        <span class="error-text amounterror text-danger d-block mt-1"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="amountPaid" class="form-label">Amount Paid</label>
                        <input type="number" name="amount_paid" id="amountPaid" class="form-control"
                            step="0.01" min="0" value="0" placeholder="Enter amount paid">
                        <small class="text-muted">Balance will be calculated automatically</small>
                        <span class="error-text amountpaiderror text-danger d-block mt-1"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Balance Amount</label>
                        <input type="text" id="balanceAmount" class="form-control" readonly value="0.00">
                    </div>

                    <div class="col-md-6">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                        <span class="error-text locationerror text-danger d-block mt-1"></span>
                    </div>
                </div>

                {{-- Schedule --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="scheduleddate" class="form-label">Scheduled Date</label>
                        <input type="date" class="form-control" id="scheduleddate" name="scheduled_date">
                        <span class="error-text scheduleddateerror text-danger d-block mt-1"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="scheduledtime" class="form-label">Scheduled Time</label>
                        <input type="time" class="form-control" id="scheduledtime" name="scheduled_time">
                        <span class="error-text scheduledtimeerror text-danger d-block mt-1"></span>
                    </div>
                </div>

                {{-- Description --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        <span class="error-text descriptionerror text-danger d-block mt-1"></span>
                    </div>
                </div>

                {{-- Customer Instructions --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="customerinstructions" class="form-label">Customer Instructions</label>
                        <textarea class="form-control" id="customerinstructions" name="customer_instructions" rows="2"
                            placeholder="Special instructions or preferences for this job..."></textarea>
                        <small class="text-muted">E.g., Key under doormat, call before arriving, etc.</small>
                        <span class="error-text customerinstructionserror text-danger d-block mt-1"></span>
                    </div>
                </div>

                {{-- Status Selection - Role-based access --}}
                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager' || auth()->user()->role === 'telecallers')
                    <div class="row mb-3" id="statusDropdownRow">
                        <div class="col-12">
                            <label for="jobstatus" class="form-label">
                                <i class="las la-info-circle me-1"></i> Status
                            </label>
                            <select class="form-select" id="jobstatus" name="status">
                                <option value="pending" selected>Pending</option>
                                <option value="work_on_hold">Work on Hold</option>
                                <option value="postponed">Postponed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <small class="text-muted">
                                <i class="las la-shield-alt"></i> You can manually set the status of this work order.
                            </small>
                            <span class="error-text statuserror text-danger d-block mt-1"></span>
                        </div>
                    </div>
                @endif

                {{-- Confirm on Creation - Only for Telecallers --}}
                @if(auth()->user()->role === 'telecallers')
                    <div class="row mb-3" id="confirmCheckboxRow">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmOnCreation" name="confirm_on_creation" value="1">
                                <label class="form-check-label" for="confirmOnCreation">
                                    <strong>Confirm this work order immediately</strong>
                                    <small class="d-block text-muted">
                                        <i class="las la-info-circle"></i> Check this box to mark the job as "Confirmed" and send it directly for admin approval.
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Work Order</button>
            </div>

        </form>

    </div>
</div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="note" name="note" rows="4" required placeholder="Enter customer note or instruction..."></textarea>
                    </div>

                    @if($customer->jobs->count() > 0)
                    <div class="mb-3">
                        <label for="job_id" class="form-label">Link to Job (Optional)</label>
                        <select class="form-select" id="job_id" name="job_id">
                            <option value="">General Note (Not linked to any job)</option>
                            @foreach($customer->jobs->sortByDesc('created_at') as $job)
                                <option value="{{ $job->id }}">
                                    {{ $job->job_code }} - {{ $job->service->name ?? 'N/A' }}
                                    ({{ ucfirst($job->status) }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Link this note to a specific Work order if relevant</small>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save"></i> Add Note
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

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            const allServices = @json($services);
            let selectedServices = {};
            let currentSearchTerm = '';

            $('#serviceSearchInput').on('input', function() {
                currentSearchTerm = $(this).val().trim();
                console.log('Search changed to:', currentSearchTerm);

                // Save current visible selections before searching
                saveCurrentSelections();

                // Filter visible services
                filterServicesInDOM(currentSearchTerm);
            });

            // Filter services in DOM without reloading
            function filterServicesInDOM(searchTerm) {
                if (!searchTerm) {
                    $('.service-checkbox-item').show();
                    updateSelectedServicesDisplay();
                    return;
                }

                const search = searchTerm.toLowerCase();
                let visibleCount = 0;

                $('.service-checkbox-item').each(function() {
                    const serviceName = $(this).find('.service-checkbox').data('service-name').toLowerCase();
                    if (serviceName.includes(search)) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Show message if no results
                if (visibleCount === 0) {
                    if ($('#noSearchResults').length === 0) {
                        $('#servicesContainer').append(`
                            <p id="noSearchResults" class="text-muted text-center my-3">
                                No services found matching "<strong>${searchTerm}</strong>"
                            </p>
                        `);
                    }
                } else {
                    $('#noSearchResults').remove();
                }

                updateSelectedServicesDisplay();
            }

            // Clear search button
            $('#clearServiceSearch').on('click', function() {
                $('#serviceSearchInput').val('');
                currentSearchTerm = '';
                saveCurrentSelections();
                filterServicesInDOM('');
                $('#serviceSearchInput').focus();
            });

            function calculateBalance() {
                const totalAmount = parseFloat($('#amount').val()) || 0;
                const amountPaid = parseFloat($('#amountPaid').val()) || 0;
                const balance = totalAmount - amountPaid;

                $('#balanceAmount').val('₹' + balance.toFixed(2));

                // Change color based on payment status
                if (balance <= 0) {
                    $('#balanceAmount').removeClass('text-danger text-warning').addClass('text-success');
                } else if (amountPaid > 0) {
                    $('#balanceAmount').removeClass('text-danger text-success').addClass('text-warning');
                } else {
                    $('#balanceAmount').removeClass('text-warning text-success').addClass('text-danger');
                }
            }

            // Calculate balance on amount change
            $(document).on('input', '#amount, #amountPaid', function() {
                calculateBalance();
            });

            $('#addJobBtnFromCustomer').on('click', function () {
                $('#jobForm')[0].reset();
                $('#jobid').val('');
                $('#jobModalLabel').text('Add Work Order');
                $('.error-text').text('');

                // Re-fill fixed values after reset (because reset clears inputs)
                $('#branchNameDisplay').val("{{ $customer->branch->name ?? 'N/A' }}");
                $('#branchIdHidden').val("{{ $customer->branch_id }}");

                $('#customerNameDisplay').val("{{ $customer->customer_code }} - {{ $customer->name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}");
                $('#customerIdHidden').val("{{ $customer->id }}");

                // Reset services box
                // $('#servicesContainer').html(`
                //     <p class="text-muted text-center my-3">
                //         <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                //         Please select a service type first
                //     </p>
                // `);

                $('#servicetype').val('');
                loadServices($(this).val());

                $('#jobModal').modal('show');
            });

            const $servicetype = $('#servicetype');
            const $servicesContainer = $('#servicesContainer');

            // ===========================
            // SERVICE LOADING & SELECTION
            // ===========================

            // INJECT ALL SELECTED SERVICES AS HIDDEN INPUTS
            function injectSelectedServicesIntoForm() {
                // Remove any previously injected hidden inputs
                $('#jobForm').find('.injected-service-input').remove();

                console.log('Injecting selected services into form:', selectedServices);

                // Inject hidden inputs for all selected services
                Object.entries(selectedServices).forEach(([serviceId, data]) => {
                    // Add service ID (checkbox checked)
                    $('#jobForm').append(`<input type="checkbox" name="service_ids[]" value="${serviceId}" checked class="injected-service-input" style="display:none">`);

                    // Add quantity input
                    $('#jobForm').append(`<input type="number" name="service_quantities[${serviceId}]" value="${data.quantity}" class="injected-service-input" style="display:none">`);

                    console.log(`Injected service ${serviceId}: ${data.name} qty ${data.quantity}`);
                });

                console.log('Total injected inputs:', $('#jobForm').find('.injected-service-input').length);
            }

            // SAVE ONLY VISIBLE SELECTIONS
            function saveCurrentSelections() {
                console.log('Saving selections... Current DOM checkboxes:', $('.service-checkbox').length);

                // Only update services that are currently visible in DOM
                let visibleServiceIds = [];

                $('.service-checkbox').each(function() {
                    let serviceId = $(this).data('service-id');
                    visibleServiceIds.push(serviceId);
                    let isChecked = $(this).is(':checked');

                    if (isChecked) {
                        let quantity = parseInt($(`#quantity${serviceId}`).val()) || 1;
                        let serviceName = $(this).data('service-name');
                        let serviceType = $(this).data('service-type');

                        selectedServices[serviceId] = {
                            name: serviceName,
                            quantity: quantity,
                            type: serviceType
                        };
                        console.log('Saved service:', serviceId, selectedServices[serviceId]);
                    } else {
                        // Only remove if this service is visible AND unchecked
                        if (selectedServices.hasOwnProperty(serviceId)) {
                            delete selectedServices[serviceId];
                            console.log('Removed service (unchecked):', serviceId);
                        }
                    }
                });

                console.log('Visible service IDs:', visibleServiceIds);
                console.log('Total selected services:', Object.keys(selectedServices).length, selectedServices);
            }

            function loadServices(serviceType = '', preselectedIds = [], preselectedQty = {}) {
                console.log('Loading services... Type:', serviceType || 'ALL');
                console.log('Preselected IDs:', preselectedIds);
                console.log('Preselected quantities:', preselectedQty);
                console.log('Current selections before load:', selectedServices);

                const container = $('#servicesContainer');

                // ✅ Filter services client-side (NO AJAX)
                let servicesToShow = serviceType
                    ? allServices.filter(s => s.service_type === serviceType)
                    : allServices;

                console.log('Services to show:', servicesToShow.length);

                if (servicesToShow.length === 0) {
                    container.html('<p class="text-muted text-center my-3">No services available</p>');
                    return;
                }

                // ✅ PRE-POPULATE selectedServices BEFORE rendering DOM
                preselectedIds.forEach(serviceId => {
                    // Find the service in allServices to get its details
                    let serviceData = allServices.find(s => s.id == serviceId);

                    if (serviceData) {
                        selectedServices[serviceId] = {
                            name: serviceData.name,
                            quantity: preselectedQty[serviceId] || 1,
                            type: serviceData.service_type
                        };
                        console.log(`Pre-populated service ${serviceId}:`, selectedServices[serviceId]);
                    }
                });

                console.log('selectedServices after pre-population:', selectedServices);

                // Group services by type
                let grouped = {};
                servicesToShow.forEach(service => {
                    let type = service.service_type || 'other';
                    if (!grouped[type]) {
                        grouped[type] = [];
                    }
                    grouped[type].push(service);
                });

                let html = '';

                // Display grouped services with headers
                Object.keys(grouped).sort().forEach(type => {
                    let typeName = type.replace(/_/g, ' ')
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ') + ' Services';

                    let typeColor = type === 'cleaning' ? '#3b82f6' :
                                type === 'pest_control' ? '#10b981' :
                                '#6b7280';

                    html += `
                        <div style="margin-top: ${html ? '15px' : '0'}; padding: 8px 10px; background: ${typeColor}15; border-left: 3px solid ${typeColor}; border-radius: 4px;">
                            <strong style="color: ${typeColor}; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                ${typeName}
                                <span style="font-size: 0.8rem; font-weight: 400;">(${grouped[type].length})</span>
                            </strong>
                        </div>
                    `;

                    // Add services for this type
                    grouped[type].forEach(service => {
                        // ✅ Check if this service should be pre-selected
                        const isChecked = selectedServices.hasOwnProperty(service.id);

                        const qtyValue = isChecked
                            ? selectedServices[service.id].quantity
                            : 1;

                        console.log(`Service ${service.id} (${service.name}) - checked:${isChecked}, qty:${qtyValue}`);

                        html += `
                            <div class="service-checkbox-item">
                                <div class="service-checkbox-wrapper">
                                    <input type="checkbox"
                                        name="service_ids[]"
                                        value="${service.id}"
                                        id="service${service.id}"
                                        class="service-checkbox"
                                        data-service-id="${service.id}"
                                        data-service-name="${service.name}"
                                        data-service-type="${service.service_type}"
                                        ${isChecked ? 'checked' : ''}>
                                    <label for="service${service.id}">${service.name}</label>
                                </div>
                                <div class="service-quantity-wrapper">
                                    <span class="quantity-label">Qty:</span>
                                    <input type="number"
                                        name="service_quantities[${service.id}]"
                                        id="quantity${service.id}"
                                        class="service-quantity-input"
                                        min="1"
                                        value="${qtyValue}"
                                        ${!isChecked ? 'disabled' : ''}>
                                </div>
                            </div>
                        `;
                    });
                });

                container.html(html);

                console.log('DOM rendered. Checkboxes found:', $('.service-checkbox').length);
                console.log('Checked checkboxes:', $('.service-checkbox:checked').length);

                // ✅ Re-bind event handlers AFTER rendering (without triggering them)
                bindServiceEvents();

                updateSelectedServicesDisplay();
            }

            function bindServiceEvents() {
                // ✅ COMPLETELY UNBIND FIRST
                $('.service-checkbox').off('change');
                $('.service-quantity-input').off('change');

                // ✅ Small delay to prevent auto-triggering
                setTimeout(function() {
                    // Enable/disable quantity input based on checkbox
                    $('.service-checkbox').on('change', function() {
                        let serviceId = $(this).data('service-id');
                        let serviceName = $(this).data('service-name');
                        let serviceType = $(this).data('service-type');
                        let quantityInput = $(`#quantity${serviceId}`);

                        if ($(this).is(':checked')) {
                            quantityInput.prop('disabled', false);
                            if (!quantityInput.val()) {
                                quantityInput.val(1);
                            }

                            selectedServices[serviceId] = {
                                name: serviceName,
                                quantity: parseInt(quantityInput.val()),
                                type: serviceType
                            };
                            console.log('Checkbox checked - added to selection:', serviceId, selectedServices[serviceId]);
                        } else {
                            quantityInput.prop('disabled', true);
                            delete selectedServices[serviceId];
                            console.log('Checkbox unchecked - removed from selection:', serviceId);
                        }

                        updateSelectedServicesDisplay();
                    });

                    // Update quantity in persistent state when changed
                    $('.service-quantity-input').on('change', function() {
                        let serviceId = $(this).attr('id').replace('quantity', '');
                        if (selectedServices.hasOwnProperty(serviceId)) {
                            selectedServices[serviceId].quantity = parseInt($(this).val()) || 1;
                            console.log('Quantity updated:', serviceId, selectedServices[serviceId].quantity);
                            updateSelectedServicesDisplay();
                        }
                    });
                }, 100); // 100ms delay to prevent auto-trigger
            }

            // DISPLAY SELECTED SERVICES WITH NAMES (Same as Index Page)
            function updateSelectedServicesDisplay() {
                let selectedCount = Object.keys(selectedServices).length;
                let existingBadge = $('#selectedServicesBadge');

                console.log('Updating display. Selected count:', selectedCount);

                if (selectedCount > 0) {
                    // Build list of selected service names grouped by type
                    let byType = {};

                    Object.entries(selectedServices).forEach(([id, data]) => {
                        let type = data.type || 'other';
                        // FIXED: Initialize array if key doesn't exist
                        if (!byType[type]) {
                            byType[type] = [];
                        }
                        byType[type].push(`${data.name} x${data.quantity}`);
                    });

                    let servicesList = [];

                    // Dynamic display for all service types
                    Object.keys(byType).sort().forEach(type => {
                        let icon = type === 'cleaning' ? '🧹' :
                                type === 'pest_control' ? '🐛' :
                                '📦';

                        let color = type === 'cleaning' ? '#3b82f6' :
                                type === 'pest_control' ? '#10b981' :
                                '#6b7280';

                        servicesList.push(`<span style="color: ${color};">${icon} ${byType[type].join(', ')}</span>`);
                    });

                    let displayList = servicesList.join(' | ');

                    let badgeHtml = `
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <i class="las la-check-circle"></i> <strong>${selectedCount}</strong> service${selectedCount !== 1 ? 's' : ''} selected
                                <br><small style="font-size: 0.75rem">${displayList}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearAllSelections" style="min-width: 100px;">
                                <i class="las la-times"></i> Clear All
                            </button>
                        </div>
                    `;

                    if (existingBadge.length === 0) {
                        $('#servicesContainer').before(`<div id="selectedServicesBadge" class="alert alert-success py-2 px-3 mb-2" style="font-size: 0.85rem">${badgeHtml}</div>`);
                    } else {
                        existingBadge.html(badgeHtml);
                    }

                    // Bind clear all button
                    $('#clearAllSelections').off('click').on('click', function () {
                        Swal.fire({
                            title: 'Clear all selected services?',
                            text: 'This will remove all selected services from the list.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, clear',
                            cancelButtonText: 'Cancel',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                selectedServices = {};
                                console.log('All selections cleared');
                                loadServices($('#servicetype').val());

                                // Swal.fire({
                                //     title: 'Cleared!',
                                //     text: 'All selected services were cleared.',
                                //     icon: 'success',
                                //     timer: 1200,
                                //     showConfirmButton: false
                                // });
                            }
                        });
                    });
                } else {
                    existingBadge.remove();
                }
            }

            // On service type change (create flow)
            $('#servicetype').on('change', function() {
                // Save current selections before switching
                saveCurrentSelections();

                $('#serviceSearchInput').val('');
                loadServices($(this).val());
            });

            // Show/hide confirmation message based on checkbox
            $('#confirmOnCreation').on('change', function() {
                if ($(this).is(':checked')) {
                    // Optional: Show a small info message
                    if ($('.confirm-info-message').length === 0) {
                        $(this).closest('.form-check').after(
                            '<div class="alert alert-info py-2 mt-2 confirm-info-message">' +
                            '<i class="las la-check-circle"></i> ' +
                            'This job will be marked as <strong>Confirmed</strong> and sent directly to admin for approval.' +
                            '</div>'
                        );
                    }

                    // Disable status dropdown and reset to pending
                    $('#jobstatus').val('pending').prop('disabled', true).addClass('bg-light');
                } else {
                    $('.confirm-info-message').remove();
                    // Re-enable status dropdown
                    $('#jobstatus').prop('disabled', false).removeClass('bg-light');
                }
            });

            // Form Submit
            $('#jobForm').on('submit', function(e) {
                e.preventDefault();

                let jobId = $('#jobid').val();
                let url = jobId
                        ? `{{ url('/jobs') }}/${jobId}`
                        : `{{ route('jobs.store') }}`;

                console.log('Job ID:', jobId); // Debug
                console.log('Submit URL:', url); // Debug

                // Validate at least one service is selected
                if ($('.service-checkbox:checked').length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select at least one service'
                    });
                    return;
                }

                // Save current visible selections
                saveCurrentSelections();

                // Validate at least one service is selected
                if (Object.keys(selectedServices).length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select at least one service'
                    });
                    return;
                }

                // Inject ALL selected services
                injectSelectedServicesIntoForm();

                const formData = new FormData(this);

                // Remove status if dropdown is disabled OR hidden
                if ($('#jobstatus').prop('disabled') || !$('#statusDropdownRow').is(':visible')) {
                    formData.delete('status');
                }

                // Remove confirm checkbox if hidden
                if (!$('#confirmCheckboxRow').is(':visible')) {
                    formData.delete('confirm_on_creation');
                }

                // For update, add _method PUT
                if (jobId) {
                    formData.append('_method', 'PUT');
                    console.log('Adding PUT method for update'); // Debug
                }

                // Clear previous errors
                $('.error-text').text('');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#jobModal').modal('hide');
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
                        // Remove injected inputs on error
                        $('.injected-service-input').remove();

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $(`#${key}error`).text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Something went wrong', 'error');
                        }
                    }
                });
            });

            // Submit Add Note Form
            $('#addNoteForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: '/customers/{{ $customer->id }}/notes',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addNoteModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Note Added!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to add note',
                        });
                    }
                });
            });

            // DELETE NOTE
            $(document).on('click', '.deleteNoteBtn', function() {
                let noteId = $(this).data('note-id');
                let noteCard = $('#note-' + noteId);

                Swal.fire({
                    title: 'Delete Note?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/customers/{{ $customer->id }}/notes/' + noteId,
                            type: 'DELETE',
                            success: function(response) {
                                noteCard.fadeOut(300, function() {
                                    $(this).remove();

                                    if ($('.note-card').length === 0) {
                                        location.reload();
                                    }
                                });

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'Failed to delete note',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
                    }
                });
            });

            // Tooltip for job codes
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
