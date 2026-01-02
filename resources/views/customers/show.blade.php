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

        <!-- Completed Jobs -->
        <div class="card mb-3">
            <div class="card-header">
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
