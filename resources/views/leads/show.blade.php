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
                <p><strong>Branch:</strong><br>{{ $lead->branch->name }}</p>

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
                        $job = $lead->jobs->first(); // Get the first job created from this lead
                    @endphp
                    <div class="alert alert-primary mb-0 mt-2">
                        <h6 class="mb-2"><i class="las la-briefcase"></i> Related Job</h6>
                        <p class="mb-1"><strong>Job Code:</strong> <span class="badge bg-primary">{{ $job->job_code }}</span></p>
                        <p class="mb-1"><strong>Title:</strong><br>{{ Str::limit($job->title, 40) }}</p>
                        <p class="mb-1"><strong>Service:</strong> <span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span></p>
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
                        <p class="mb-1">
                            <strong>Assigned To:</strong>
                            @if($job->assignedTo)
                                {{ $job->assignedTo->name }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </p>
                        <p class="mb-2">
                            <strong>Scheduled:</strong>
                            @if($job->scheduled_date)
                                {{ \Carbon\Carbon::parse($job->scheduled_date)->format('d M Y') }}
                                @if($job->scheduled_time)
                                    at {{ $job->scheduled_time }}
                                @endif
                            @else
                                <span class="text-muted">Not scheduled</span>
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

                @if($lead->status === 'pending' && auth()->user()->role === 'super_admin')
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-primary w-100" onclick="$('#addCallModal').modal('show')">
                            <i class="las la-phone"></i> Log Call
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-info w-100" onclick="$('#addNoteModal').modal('show')">
                            <i class="las la-sticky-note"></i> Add Note
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
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Scheduled Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lead->jobs as $job)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $job->job_code }}</span>
                                </td>
                                <td>
                                    <strong>{{ Str::limit($job->title, 30) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span>
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
                                <td>
                                    @if($job->scheduled_date)
                                        {{ \Carbon\Carbon::parse($job->scheduled_date)->format('d M Y') }}
                                        @if($job->scheduled_time)
                                            <br><small class="text-muted">{{ $job->scheduled_time }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Not scheduled</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('jobs.show', $job->id) }}" class="btn btn-sm btn-primary" target="_blank" title="View Job">
                                        <i class="las la-eye"></i>
                                    </a>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="call_date" class="form-label">Call Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="call_date" name="call_date" required>
                        <span class="error-text call_date_error text-danger d-block mt-1"></span>
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="duration" name="duration" min="0" placeholder="e.g., 5">
                        <span class="error-text duration_error text-danger d-block mt-1"></span>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Call</button>
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

            // Submit Add Call Form
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
        });

        // Global functions for approve/reject
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to approve lead', 'error');
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
