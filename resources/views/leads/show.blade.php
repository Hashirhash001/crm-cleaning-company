@extends('layouts.app')

@section('title', 'Lead Details')

@section('extra-css')
    <style>
        .swal2-textarea{
            margin: 0 !important;
        }

        /* Job Status Badges */
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-in_progress {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-completed {
            background-color: #28a745;
            color: #fff;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Lead Details</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                    <li class="breadcrumb-item active">{{ $lead->lead_code }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Lead Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lead Information</h5>
                    @if($lead->status === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($lead->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                    @else
                        <span class="badge bg-danger">Rejected</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p><strong>Lead Code:</strong><br><span class="badge bg-primary fs-6">{{ $lead->lead_code }}</span></p>
                <hr>
                <p><strong>Name:</strong><br>{{ $lead->name }}</p>
                <p><strong>Email:</strong><br><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>
                <p><strong>Phone:</strong><br><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></p>
                <p><strong>Service:</strong><br><span class="badge bg-info">{{ $lead->service->name ?? 'N/A' }}</span></p>
                <p><strong>Source:</strong><br><span class="badge bg-secondary">{{ $lead->source->name }}</span></p>
                <p>
                    <strong>Assigned To:</strong><br>
                    @if($lead->assignedTo)
                        <span class="badge bg-info">
                            <i class="las la-user"></i> {{ $lead->assignedTo->name }}
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            <i class="las la-user-times"></i> Unassigned
                        </span>
                    @endif
                </p>
                <p><strong>Branch:</strong><br>{{ $lead->branch->name }}</p>

                <!-- Amount Display -->
                <hr>
                <div class="amount-section">
                    <p class="mb-2"><strong>Lead Amount:</strong></p>
                    @if($lead->amount)
                        <h4 class="text-success mb-2">₹{{ number_format($lead->amount, 2) }}</h4>
                        @if($lead->amountUpdatedBy && $lead->amount_updated_at)
                            <small class="text-muted d-block">
                                Updated by {{ $lead->amountUpdatedBy->name }}<br>
                                {{ \Carbon\Carbon::parse($lead->amount_updated_at)->format('d M Y, h:i A') }}
                            </small>
                        @endif
                    @else
                        <span class="badge bg-warning">Not Set</span>
                        <p class="text-muted small mt-1">Amount can be added when editing the lead</p>
                    @endif
                </div>

                {{-- Customer and Job Information --}}
                @if($lead->customer)
                <hr>
                <div class="alert alert-success mb-0">
                    <h6 class="mb-2"><i class="las la-user-check"></i> Converted to Customer</h6>
                    <p class="mb-1"><strong>Customer Code:</strong> <span class="badge bg-success">{{ $lead->customer->customer_code }}</span></p>
                    <p class="mb-2"><strong>Name:</strong> {{ $lead->customer->name }}</p>
                    <a href="{{ route('customers.show', $lead->customer->id) }}" class="btn btn-sm btn-success w-100 mb-2" target="_blank">
                        <i class="las la-external-link-alt"></i> View Customer Profile
                    </a>
                </div>

                {{-- Related Job Information --}}
                @if($lead->jobs && $lead->jobs->count() > 0)
                    @php
                        $job = $lead->jobs->first();
                    @endphp
                    <div class="alert alert-primary mb-0 mt-2">
                        <h6 class="mb-2"><i class="las la-briefcase"></i> Related Job</h6>
                        <p class="mb-1"><strong>Job Code:</strong> <span class="badge bg-primary">{{ $job->job_code }}</span></p>
                        <p class="mb-1"><strong>Title:</strong><br>{{ Str::limit($job->title, 40) }}</p>
                        <p class="mb-1"><strong>Service:</strong> <span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span></p>
                        @if($job->amount)
                        <p class="mb-1"><strong>Amount:</strong> <span class="text-success fw-bold">₹{{ number_format($job->amount, 2) }}</span></p>
                        @endif
                        <p class="mb-1">
                            <strong>Status:</strong>
                            @if($job->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($job->status === 'in_progress')
                                <span class="badge bg-info">In Progress</span>
                            @elseif($job->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </p>
                        <a href="{{ route('jobs.show', $job->id) }}" class="btn btn-sm btn-primary w-100" target="_blank">
                            <i class="las la-external-link-alt"></i> View Job Details
                        </a>

                        @if($lead->jobs->count() > 1)
                            <small class="d-block mt-2 text-center text-muted">
                                <i class="las la-info-circle"></i> +{{ $lead->jobs->count() - 1 }} more {{ Str::plural('job', $lead->jobs->count() - 1) }}
                            </small>
                        @endif
                    </div>
                @endif
                @endif

                @if($lead->description)
                <hr>
                <p><strong>Description:</strong><br>{{ $lead->description }}</p>
                @endif

                <hr>
                <p><strong>Created By:</strong><br>{{ $lead->createdBy->name }}</p>
                <p><strong>Created On:</strong><br>{{ $lead->created_at->format('d M Y, h:i A') }}</p>

                @if($lead->status === 'approved' && $lead->approval_notes)
                <hr>
                <p><strong>Approval Notes:</strong><br>{{ $lead->approval_notes }}</p>
                @endif

                @if($lead->status === 'rejected' && $lead->approval_notes)
                <hr>
                <p><strong>Rejection Reason:</strong><br>{{ $lead->approval_notes }}</p>
                @endif

                <hr>

                @php
                    $canEdit = false;
                    $user = auth()->user();

                    if ($lead->status === 'pending') {
                        if ($user->role === 'super_admin') {
                            $canEdit = true;
                        } elseif ($user->role === 'lead_manager' && $lead->created_by === $user->id) {
                            $canEdit = true;
                        } elseif ($user->role === 'telecallers' && $lead->assigned_to === $user->id) {
                            $canEdit = true;
                        }
                    }
                @endphp

                <!-- Edit Lead Button -->
                @if($canEdit)
                <button type="button" class="btn btn-info btn-sm w-100 mb-2" onclick="editLeadFromShow()">
                    <i class="las la-edit"></i> Edit Lead
                </button>
                @endif

                <!-- Approve/Reject Buttons -->
                @if($lead->status === 'pending' && in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-success btn-sm flex-grow-1" onclick="approveLead({{ $lead->id }})">
                        <i class="las la-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger btn-sm flex-grow-1" onclick="rejectLead({{ $lead->id }})">
                        <i class="las la-times"></i> Reject
                    </button>
                </div>
                @endif

                <button type="button" class="btn btn-sm btn-primary w-100" onclick="window.location.href='{{ route('leads.index') }}'">
                    <i class="las la-arrow-left"></i> Back to Leads
                </button>
            </div>
        </div>
    </div>

    <!-- Activity & Actions -->
    <div class="col-lg-8">
        <!-- Quick Actions -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-primary w-100" onclick="$('#addCallModal').modal('show')">
                            <i class="las la-phone"></i> Log Call
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-info w-100" onclick="$('#addNoteModal').modal('show')">
                            <i class="las la-sticky-note"></i> Add Note
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-success w-100" onclick="$('#addFollowupModal').modal('show')">
                            <i class="las la-calendar-plus"></i> Schedule Followup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Jobs Section -->
        @if($lead->status === 'approved' && $lead->jobs && $lead->jobs->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="las la-briefcase"></i> Related Jobs</h5>
                    <span class="badge bg-primary">{{ $lead->jobs->count() }} {{ Str::plural('Job', $lead->jobs->count()) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Job Code</th>
                                <th>Title</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lead->jobs as $job)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $job->job_code }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('jobs.show', $job->id) }}" class="text-decoration-none text-dark fw-bold" target="_blank" title="View Job Details">
                                        {{ Str::limit($job->title, 40) }}
                                        <i class="las la-external-link-alt ms-1 text-primary"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($job->amount)
                                        <span class="text-success fw-bold">₹{{ number_format($job->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($job->status === 'pending')
                                        <span class="badge badge-pending">Pending</span>
                                    @elseif($job->status === 'in_progress')
                                        <span class="badge badge-in_progress">In Progress</span>
                                    @elseif($job->status === 'completed')
                                        <span class="badge badge-completed">Completed</span>
                                    @else
                                        <span class="badge badge-cancelled">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($job->assignedTo)
                                        {{ $job->assignedTo->name }}
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif


        <!-- Call History -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="las la-phone"></i> Call History</h5>
                    <span class="badge bg-primary">{{ $lead->calls->count() }} {{ Str::plural('Call', $lead->calls->count()) }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($lead->calls->count() > 0)
                    <div class="timeline">
                        @foreach($lead->calls as $call)
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $call->user->name }}</strong>
                                        <small class="text-muted ms-2">{{ $call->call_date->format('d M Y, h:i A') }}</small>
                                    </div>
                                    <div class="text-end">
                                        @if($call->outcome === 'interested')
                                            <span class="badge bg-success">Interested</span>
                                        @elseif($call->outcome === 'not_interested')
                                            <span class="badge bg-danger">Not Interested</span>
                                        @elseif($call->outcome === 'callback')
                                            <span class="badge bg-warning">Callback</span>
                                        @elseif($call->outcome === 'no_answer')
                                            <span class="badge bg-secondary">No Answer</span>
                                        @else
                                            <span class="badge bg-dark">Wrong Number</span>
                                        @endif
                                        @if($call->duration)
                                            <br><small class="text-muted">{{ $call->duration }} min</small>
                                        @endif
                                    </div>
                                </div>
                                @if($call->notes)
                                <p class="mb-0"><strong>Notes:</strong> {{ $call->notes }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No calls logged yet.</p>
                @endif
            </div>
        </div>

        <!-- Notes -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="las la-sticky-note"></i> Notes & Comments</h5>
                    <span class="badge bg-primary">{{ $lead->notes->count() }} {{ Str::plural('Note', $lead->notes->count()) }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($lead->notes->count() > 0)
                    @foreach($lead->notes as $note)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $note->createdBy->name }}</strong>
                                    <small class="text-muted ms-2">{{ $note->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <p class="mb-0">{{ $note->note }}</p>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">No notes added yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Call Modal -->
<div class="modal fade" id="addCallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="call_date" class="form-label">Call Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="call_date" name="call_date" required>
                            <span class="error-text call_date_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="0" placeholder="e.g., 5">
                            <span class="error-text duration_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="outcome" class="form-label">Call Outcome <span class="text-danger">*</span></label>
                        <select class="form-select" id="outcome" name="outcome" required>
                            <option value="">Select Outcome</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="callback">Callback Required</option>
                            <option value="no_answer">No Answer</option>
                            <option value="wrong_number">Wrong Number</option>
                        </select>
                        <span class="error-text outcome_error text-danger d-block mt-1"></span>
                    </div>

                    <div class="mb-3">
                        <label for="call_notes" class="form-label">Call Notes</label>
                        <textarea class="form-control" id="call_notes" name="notes" rows="3" placeholder="Enter call details..."></textarea>
                    </div>

                    <!-- Followup Section - Shows when outcome is 'callback' or 'interested' -->
                    <div id="followupSection" style="display: none;">
                        <hr>
                        <h6 class="mb-3"><i class="las la-calendar-plus"></i> Schedule Followup</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="followup_date" class="form-label">Followup Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="followup_date" name="followup_date">
                                <span class="error-text followup_date_error text-danger d-block mt-1"></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="followup_time" class="form-label">Followup Time</label>
                                <input type="time" class="form-control" id="followup_time" name="followup_time">
                                <span class="error-text followup_time_error text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="followup_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="followup_priority" name="followup_priority">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                            <span class="error-text followup_priority_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="mb-3">
                            <label for="followup_notes" class="form-label">Followup Notes</label>
                            <textarea class="form-control" id="followup_notes" name="followup_notes" rows="2" placeholder="What needs to be discussed in the followup?"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Call & Followup</button>
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
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="note" name="note" rows="4" required placeholder="Enter note..."></textarea>
                        <span class="error-text note_error text-danger d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Followup Modal (Manual) -->
<div class="modal fade" id="addFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Followup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFollowupForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manual_followup_date" class="form-label">Followup Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="manual_followup_date" name="followup_date" required>
                            <span class="error-text followup_date_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="manual_followup_time" class="form-label">Followup Time</label>
                            <input type="time" class="form-control" id="manual_followup_time" name="followup_time">
                            <span class="error-text followup_time_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="manual_followup_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" id="manual_followup_priority" name="priority" required>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="low">Low</option>
                        </select>
                        <span class="error-text priority_error text-danger d-block mt-1"></span>
                    </div>

                    <div class="mb-3">
                        <label for="manual_followup_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="manual_followup_notes" name="notes" rows="3" placeholder="What needs to be discussed?"></textarea>
                        <span class="error-text notes_error text-danger d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Schedule Followup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editLeadForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <span class="error-text name_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <span class="error-text email_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                            <span class="error-text phone_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_service_id" class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_service_id" name="service_id" required>
                                <option value="">Select Service</option>
                                @foreach(\App\Models\Service::where('is_active', true)->get() as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text service_id_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_lead_source_id" class="form-label">Lead Source <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_lead_source_id" name="lead_source_id" required>
                                <option value="">Select Source</option>
                                @foreach(\App\Models\LeadSource::where('is_active', true)->get() as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text lead_source_id_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_assigned_to" class="form-label">Assign To Telecaller</label>
                            <select class="form-select" id="edit_assigned_to" name="assigned_to">
                                <option value="">Select Telecaller (Optional)</option>
                                @foreach(\App\Models\User::where('role', 'telecallers')->where('is_active', true)->orderBy('name')->get() as $telecaller)
                                    <option value="{{ $telecaller->id }}">{{ $telecaller->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text assigned_to_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'super_admin')
                    <div class="mb-3">
                        <label for="edit_branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_branch_id" name="branch_id" required>
                            <option value="">Select Branch</option>
                            @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <span class="error-text branch_id_error text-danger d-block mt-1"></span>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Lead Amount (₹)</label>
                        <input type="number" class="form-control" id="edit_amount" name="amount"
                               step="0.01" min="0" placeholder="Enter amount">
                        <span class="error-text amount_error text-danger d-block mt-1"></span>
                        <small class="text-muted">Required before approval</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        <span class="error-text description_error text-danger d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Lead</button>
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

            // Set current datetime for call_date
            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            $('#call_date').val(now.toISOString().slice(0,16));

            // Set default followup date to tomorrow
            let tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            $('#manual_followup_date').val(tomorrow.toISOString().split('T')[0]);
            $('#manual_followup_date').attr('min', new Date().toISOString().split('T')[0]);

            // Show/Hide followup section based on outcome
            $('#outcome').on('change', function() {
                let outcome = $(this).val();
                if (outcome === 'callback' || outcome === 'interested') {
                    $('#followupSection').slideDown();

                    // Set default followup date to tomorrow
                    let tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    $('#followup_date').val(tomorrow.toISOString().split('T')[0]);

                    // Make followup fields required
                    $('#followup_date').prop('required', true);
                } else {
                    $('#followupSection').slideUp();
                    // Remove required attribute
                    $('#followup_date').prop('required', false);
                    // Clear followup fields
                    $('#followup_date').val('');
                    $('#followup_time').val('');
                    $('#followup_notes').val('');
                }
            });

            // Submit Add Call Form with Followup
            $('#addCallForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('.error-text').text('');

                $.ajax({
                    url: '/leads/{{ $lead->id }}/calls',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addCallModal').modal('hide');

                        let message = response.message;
                        if (response.followup_created) {
                            message += '<br><small class="text-success">Followup scheduled successfully!</small>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: message,
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
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to log call', 'error');
                        }
                    }
                });
            });

            // Submit Add Note Form
            $('#addNoteForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('.error-text').text('');

                $.ajax({
                    url: '/leads/{{ $lead->id }}/notes',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addNoteModal').modal('hide');
                        $('#note').val('');
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
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to add note', 'error');
                        }
                    }
                });
            });

            // Submit Manual Followup Form
            $('#addFollowupForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('.error-text').text('');

                $.ajax({
                    url: '/leads/{{ $lead->id }}/followups',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addFollowupModal').modal('hide');
                        $('#addFollowupForm')[0].reset();
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
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to schedule followup', 'error');
                        }
                    }
                });
            });
        });

        // Edit Lead from Show Page
        function editLeadFromShow() {
            $('#edit_name').val('{{ $lead->name }}');
            $('#edit_email').val('{{ $lead->email }}');
            $('#edit_phone').val('{{ $lead->phone }}');
            $('#edit_service_id').val('{{ $lead->service_id }}');
            $('#edit_lead_source_id').val('{{ $lead->lead_source_id }}');
            $('#edit_assigned_to').val('{{ $lead->assigned_to }}');
            $('#edit_amount').val('{{ $lead->amount }}');
            $('#edit_description').val(`{{ $lead->description }}`);

            @if(auth()->user()->role === 'super_admin')
                $('#edit_branch_id').val('{{ $lead->branch_id }}');
            @endif

            $('.error-text').text('');
            $('#editLeadModal').modal('show');
        }

        // Submit Edit Lead Form
        $('#editLeadForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            $('.error-text').text('');

            $.ajax({
                url: '/leads/{{ $lead->id }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editLeadModal').modal('hide');
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
                            $('.' + key + '_error').text(value[0]);
                        });
                    } else {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update lead', 'error');
                    }
                }
            });
        });

        // Approve Lead with Budget Check
        function approveLead(leadId) {
            Swal.fire({
                title: 'Approve Lead?',
                html: '<textarea id="approval_notes_input" class="swal2-textarea" placeholder="Enter approval notes (optional)" style="width: 100%; min-height: 80px;"></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#28a745',
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    document.getElementById('approval_notes_input').focus();
                },
                preConfirm: () => {
                    return document.getElementById('approval_notes_input').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/leads/' + leadId + '/approve',
                        type: 'POST',
                        data: {
                            approval_notes: result.value || '',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            let message = response.message;
                            if (response.remaining_budget) {
                                message += '<br><small class="text-muted">Remaining Budget: ' + response.remaining_budget + '</small>';
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                html: message,
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'Failed to approve lead';

                            // Handle budget exceeded error
                            if (xhr.responseJSON?.budget_info) {
                                let budget = xhr.responseJSON.budget_info;
                                errorMessage = `
                                    <div class="text-start">
                                        <p><strong>${xhr.responseJSON.message}</strong></p>
                                        <hr>
                                        <p class="mb-1">Daily Limit: ${budget.daily_limit}</p>
                                        <p class="mb-1">Used Today: ${budget.today_total}</p>
                                        <p class="mb-1">Remaining: ${budget.remaining}</p>
                                        <p class="mb-1">Requested: ${budget.requested}</p>
                                        <p class="mb-0 text-danger"><strong>Excess: ${budget.excess}</strong></p>
                                    </div>
                                `;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Cannot Approve',
                                html: errorMessage
                            });
                        }
                    });
                }
            });
        }

        function rejectLead(leadId) {
            Swal.fire({
                title: 'Reject Lead?',
                html: '<textarea id="rejection_reason_input" class="swal2-textarea" placeholder="Enter rejection reason (required)" style="width: 100%; min-height: 80px;" required></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    document.getElementById('rejection_reason_input').focus();
                },
                preConfirm: () => {
                    const value = document.getElementById('rejection_reason_input').value;
                    if (!value) {
                        Swal.showValidationMessage('Rejection reason is required');
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: '/leads/' + leadId + '/reject',
                        type: 'POST',
                        data: {
                            rejection_reason: result.value,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to reject lead', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endsection
