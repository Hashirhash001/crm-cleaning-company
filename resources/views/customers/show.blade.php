@extends('layouts.app')

@section('title', 'Customer Details')

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
                <p><strong>Name:</strong><br>{{ $customer->name }}</p>
                <p><strong>Email:</strong><br><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p>
                <p><strong>Phone:</strong><br>{{ $customer->phone ?? 'N/A' }}</p>
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
                        <small class="text-muted">Total Jobs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-success">{{ $customer->completedJobs->count() }}</h3>
                        <small class="text-muted">Completed Jobs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-info">{{ $customer->jobs->where('status', 'pending')->count() }}</h3>
                        <small class="text-muted">Pending Jobs</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Jobs -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Completed Jobs History</h5>
            </div>
            <div class="card-body">
                @if($customer->completedJobs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Code</th>
                                    <th>Service</th>
                                    <th>Completed Date</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->completedJobs as $job)
                                <tr>
                                    <td><span class="badge bg-success">{{ $job->job_code }}</span></td>
                                    <td>{{ $job->service->name ?? 'N/A' }}</td>
                                    <td>{{ $job->completed_at ? $job->completed_at->format('d M Y') : 'N/A' }}</td>
                                    <td>{{ $job->assignedTo->name ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No completed jobs yet.</p>
                @endif
            </div>
            <!-- Pending Jobs Section - ADD THIS -->
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
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job Code</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Scheduled Date</th>
                                        <th>Assigned To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingJobs as $job)
                                    <tr>
                                        <td><span class="badge bg-info">{{ $job->job_code }}</span></td>
                                        <td>{{ $job->service->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($job->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($job->status === 'assigned')
                                                <span class="badge bg-info">Assigned</span>
                                            @elseif($job->status === 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @endif
                                        </td>
                                        <td>{{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'Not Scheduled' }}</td>
                                        <td>{{ $job->assignedTo->name ?? 'Unassigned' }}</td>
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
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $note->createdBy->name }}</strong>
                                    <small class="text-muted ms-2">{{ $note->created_at->diffForHumans() }}</small>
                                </div>
                                @if($note->job)
                                    <span class="badge bg-info">{{ $note->job->job_code }}</span>
                                @else
                                    <span class="badge bg-secondary">General Note</span>
                                @endif
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
                        <textarea class="form-control" id="note" name="note" rows="4" required></textarea>
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
                        Swal.fire('Error!', 'Failed to add note', 'error');
                    }
                });
            });
        });
    </script>
@endsection
