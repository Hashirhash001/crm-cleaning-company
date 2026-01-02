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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-primary">{{ $customer->jobs->count() }}</h3>
                        <small class="text-muted">Total Work orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-success">{{ $customer->completedJobs->count() }}</h3>
                        <small class="text-muted">Completed Work orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-info">{{ $customer->jobs->whereIn('status', ['pending', 'assigned', 'in_progress'])->count() }}</h3>
                        <small class="text-muted">Active Work orders</small>
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
                                    <th>Completed Date</th>
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
                                            <span class="text-success fw-bold">₹{{ number_format($job->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->completed_at ? $job->completed_at->format('d M Y') : 'N/A' }}</td>
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
                    $pendingJobs = $customer->jobs->whereIn('status', ['pending', 'assigned', 'in_progress']);
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
                                        @elseif($job->status === 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->amount)
                                            <span class="fw-bold">₹{{ number_format($job->amount, 2) }}</span>
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
                        <select class="form-select" id="servicetype" name="service_type" required>
                            <option value="">Select Service Type</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="pest_control">Pest Control</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="error-text servicetypeerror text-danger d-block mt-1"></span>
                    </div>
                </div>

                {{-- Services with Quantity --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label required-field">Select Services (with quantity)</label>

                        <div class="service-select-box" id="servicesContainer">
                            <p class="text-muted text-center my-3">
                                <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                                Please select a service type first
                            </p>
                        </div>

                        <small class="text-muted">Check services and specify quantity for each</small>
                        <span class="error-text serviceidserror text-danger d-block mt-1"></span>
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
                $('#servicesContainer').html(`
                    <p class="text-muted text-center my-3">
                        <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                        Please select a service type first
                    </p>
                `);

                $('#jobModal').modal('show');
            });

            const $servicetype = $('#servicetype');
            const $servicesContainer = $('#servicesContainer');

            function loadServices(serviceType) {
                if (!serviceType) {
                    $servicesContainer.html(`
                        <p class="text-muted text-center my-3">
                            <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                            Please select a service type first
                        </p>
                    `);
                    return;
                }

                $servicesContainer.html(`<p class="text-center my-3"><i class="las la-spinner la-spin"></i> Loading services...</p>`);

                $.ajax({
                    url: "{{ route('leads.servicesByType') }}",
                    type: "GET",
                    data: { service_type: serviceType },
                    success: function (services) {
                        if (!services || services.length === 0) {
                            $servicesContainer.html(`<p class="text-muted text-center my-3">No services available for this type</p>`);
                            return;
                        }

                        let html = '';
                        services.forEach(function(service) {
                            html += `
                                <div class="service-checkbox-item">
                                    <div class="service-checkbox-wrapper">
                                        <input type="checkbox"
                                            name="service_ids[]"
                                            value="${service.id}"
                                            id="service${service.id}"
                                            class="service-checkbox"
                                            data-service-id="${service.id}">
                                        <label for="service${service.id}">${service.name}</label>
                                    </div>

                                    <div class="service-quantity-wrapper">
                                        <span class="quantity-label">Qty</span>
                                        <input type="number"
                                            name="service_quantities[${service.id}]"
                                            id="quantity${service.id}"
                                            class="service-quantity-input"
                                            min="1"
                                            value="1"
                                            disabled>
                                    </div>
                                </div>
                            `;
                        });

                        $servicesContainer.html(html);

                        // enable/disable qty input
                        $servicesContainer.find('.service-checkbox').on('change', function () {
                            const serviceId = $(this).data('service-id');
                            const $qtyInput = $('#quantity' + serviceId);

                            if (this.checked) {
                                $qtyInput.prop('disabled', false);
                            } else {
                                $qtyInput.prop('disabled', true).val(1);
                            }
                        });
                    },
                    error: function () {
                        $servicesContainer.html(`<p class="text-danger text-center my-3">Error loading services. Please try again.</p>`);
                    }
                });
            }

            $servicetype.on('change', function () {
                loadServices($(this).val());
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

                let formData = new FormData(this);

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
