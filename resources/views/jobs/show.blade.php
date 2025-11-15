@extends('layouts.app')

@section('title', 'Job Details')

@section('extra-css')
    <style>
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-assigned {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-in_progress {
            background-color: #0dcaf0;
            color: #000;
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
            <h4 class="page-title">Job Details</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('jobs.index') }}">Jobs</a></li>
                    <li class="breadcrumb-item active">{{ $job->job_code }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Job Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Job Information</h5>
                    <span class="badge badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                </div>
            </div>
            <div class="card-body">
                <p><strong>Job Code:</strong><br><span class="badge bg-primary fs-6">{{ $job->job_code }}</span></p>
                <hr>
                <p><strong>Title:</strong><br>{{ $job->title }}</p>

                @if($job->customer)
                <p><strong>Customer:</strong><br>
                    <a href="{{ route('customers.show', $job->customer_id) }}" class="text-primary">
                        {{ $job->customer->name }}
                    </a>
                </p>
                @endif

                @if($job->service)
                <p><strong>Service:</strong><br><span class="badge bg-info">{{ $job->service->name }}</span></p>
                @endif

                <p><strong>Branch:</strong><br>{{ $job->branch->name }}</p>

                @if($job->location)
                <p><strong>Location:</strong><br>{{ $job->location }}</p>
                @endif

                @if($job->assignedTo)
                <p><strong>Assigned To:</strong><br>{{ $job->assignedTo->name }}</p>
                @else
                <p><strong>Assigned To:</strong><br><span class="text-muted">Unassigned</span></p>
                @endif

                @if($job->scheduled_date)
                <p><strong>Scheduled Date:</strong><br>{{ $job->scheduled_date->format('d M Y') }}
                    @if($job->scheduled_time)
                        at {{ $job->scheduled_time }}
                    @endif
                </p>
                @endif

                @if($job->description)
                <hr>
                <p><strong>Description:</strong><br>{{ $job->description }}</p>
                @endif

                @if($job->customer_instructions)
                <hr>
                <p><strong>Customer Instructions:</strong><br>{{ $job->customer_instructions }}</p>
                @endif

                <hr>
                <p><strong>Created By:</strong><br>{{ $job->createdBy->name ?? 'System' }}</p>
                <p><strong>Created On:</strong><br>{{ $job->created_at->format('d M Y, h:i A') }}</p>

                @if($job->assigned_at)
                <p><strong>Assigned On:</strong><br>{{ $job->assigned_at->format('d M Y, h:i A') }}</p>
                @endif

                @if($job->started_at)
                <p><strong>Started On:</strong><br>{{ $job->started_at->format('d M Y, h:i A') }}</p>
                @endif

                @if($job->completed_at)
                <p><strong>Completed On:</strong><br>{{ $job->completed_at->format('d M Y, h:i A') }}</p>
                @endif

                <hr>

                <!-- Super Admin Action Buttons - ALWAYS AVAILABLE -->
                @if(auth()->user()->role === 'super_admin')
                    <div class="d-flex gap-2 mb-2">
                        {{-- Edit - Always available --}}
                        <button type="button" class="btn btn-info btn-sm flex-grow-1 editJobBtn" data-id="{{ $job->id }}">
                            <i class="las la-pen"></i> Edit
                        </button>

                        {{-- Delete - Always available --}}
                        <button type="button" class="btn btn-danger btn-sm flex-grow-1" onclick="deleteJob({{ $job->id }})">
                            <i class="las la-trash-alt"></i> Delete
                        </button>
                    </div>

                    {{-- Re-assign - Always available --}}
                    <button type="button" class="btn btn-success btn-sm w-100 mb-2 assignJobBtn" data-id="{{ $job->id }}">
                        <i class="las la-user-check"></i> Re-assign Job
                    </button>
                @endif

                <!-- Field Staff Action Buttons -->
                @if(auth()->user()->role === 'field_staff' && auth()->id() === $job->assigned_to)
                    @if($job->status === 'assigned')
                        <button type="button" class="btn btn-success btn-sm w-100 mb-2" onclick="startJob({{ $job->id }})">
                            <i class="las la-play"></i> Start Job
                        </button>
                    @endif

                    @if($job->status === 'in_progress')
                        <button type="button" class="btn btn-success btn-sm w-100 mb-2" onclick="completeJob({{ $job->id }})">
                            <i class="las la-check-circle"></i> Complete Job
                        </button>
                    @endif
                @endif

                <button type="button" class="btn btn-sm btn-primary w-100" onclick="window.location.href='{{ route('jobs.index') }}'">
                    <i class="las la-arrow-left"></i> Back to Jobs
                </button>
            </div>
        </div>
    </div>

    <!-- Customer Notes & Activity -->
    <div class="col-lg-8">
        @if($job->customer && $job->customer->customerNotes->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="las la-sticky-note"></i> Customer Notes</h5>
            </div>
            <div class="card-body">
                @foreach($job->customer->customerNotes->sortByDesc('created_at') as $note)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>{{ $note->createdBy->name }}</strong>
                                <small class="text-muted ms-2">{{ $note->created_at->diffForHumans() }}</small>
                            </div>
                            @if($note->job)
                            <span class="badge bg-info">{{ $note->job->job_code }}</span>
                            @endif
                        </div>
                        <p class="mb-0">{{ $note->note }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="las la-clipboard-list fs-1 text-muted"></i>
                <p class="text-muted mt-2">No customer notes available for this job</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jobModalLabel">Edit Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="jobForm">
                @csrf
                <input type="hidden" id="job_id" name="job_id" value="{{ $job->id }}">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <span class="error-text title_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Select Customer</option>
                                @foreach(\App\Models\Customer::all() as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text customer_id_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="service_id" class="form-label">Service</label>
                            <select class="form-select" id="service_id" name="service_id">
                                <option value="">Select Service</option>
                                @foreach(\App\Models\Service::all() as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text service_id_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <span class="error-text description_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="customer_instructions" class="form-label">Customer Instructions</label>
                            <textarea class="form-control" id="customer_instructions" name="customer_instructions" rows="2"></textarea>
                            <span class="error-text customer_instructions_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Select Branch</option>
                                @foreach(\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="error-text branch_id_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                            <span class="error-text location_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="scheduled_date" class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date">
                            <span class="error-text scheduled_date_error text-danger d-block mt-1"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="scheduled_time" class="form-label">Scheduled Time</label>
                            <input type="time" class="form-control" id="scheduled_time" name="scheduled_time">
                            <span class="error-text scheduled_time_error text-danger d-block mt-1"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Job Modal -->
<div class="modal fade" id="assignJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Re-assign Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignJobForm">
                @csrf
                <input type="hidden" id="assign_job_id" value="{{ $job->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Select Field Staff</option>
                            @foreach(\App\Models\User::where('role', 'field_staff')->get() as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
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

            // Set minimum date for scheduled_date to today
            let today = new Date().toISOString().split('T')[0];
            $('#scheduled_date').attr('min', today);

            // Edit Job Button - COMPLETE FIX FOR DATE LOADING
            $('.editJobBtn').click(function() {
                let jobId = $(this).data('id');

                $.ajax({
                    url: '/jobs/' + jobId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        console.log('Job data:', response.job); // Debug log

                        $('#job_id').val(response.job.id);
                        $('#title').val(response.job.title || '');
                        $('#customer_id').val(response.job.customer_id || '');
                        $('#service_id').val(response.job.service_id || '');
                        $('#description').val(response.job.description || '');
                        $('#customer_instructions').val(response.job.customer_instructions || '');
                        $('#branch_id').val(response.job.branch_id || '');
                        $('#location').val(response.job.location || '');

                        // Set minimum date to today
                        let today = new Date().toISOString().split('T')[0];
                        $('#scheduled_date').attr('min', today);

                        // COMPLETE FIX: Handle scheduled date
                        if (response.job.scheduled_date) {
                            let dateValue = response.job.scheduled_date;

                            // Remove any time portion if present
                            if (dateValue.includes(' ')) {
                                dateValue = dateValue.split(' ')[0];
                            }

                            // Remove 'T' if present (ISO format)
                            if (dateValue.includes('T')) {
                                dateValue = dateValue.split('T')[0];
                            }

                            console.log('Setting date value:', dateValue);
                            $('#scheduled_date').val(dateValue);
                        } else {
                            $('#scheduled_date').val('');
                        }

                        // Set scheduled time
                        if (response.job.scheduled_time) {
                            let timeStr = response.job.scheduled_time;
                            console.log('Time string:', timeStr);

                            if (timeStr && timeStr.includes(':')) {
                                let timeParts = timeStr.split(':');
                                if (timeParts.length >= 2) {
                                    let formattedTime = timeParts[0].padStart(2, '0') + ':' + timeParts[1].padStart(2, '0');
                                    $('#scheduled_time').val(formattedTime);
                                }
                            }
                        } else {
                            $('#scheduled_time').val('');
                        }

                        $('.error-text').text('');
                        $('#jobModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading job:', xhr);
                        Swal.fire('Error!', 'Failed to load job data', 'error');
                    }
                });
            });

            // Submit Edit Job Form
            $('#jobForm').on('submit', function(e) {
                e.preventDefault();

                let jobId = $('#job_id').val();
                let formData = new FormData(this);
                formData.append('_method', 'PUT');

                $('.error-text').text('');

                $.ajax({
                    url: '/jobs/' + jobId,
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
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong', 'error');
                        }
                    }
                });
            });

            // Assign Job Button - COMPLETE FIX FOR USER SELECTION
            $('.assignJobBtn').click(function() {
                let jobId = $(this).data('id');
                $('#assign_job_id').val(jobId);

                // Get job details and auto-select current assignment
                $.ajax({
                    url: '/jobs/' + jobId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        console.log('Job assignment data:', response.job);

                        // Reset first
                        $('#assigned_to').val('');

                        // Auto-select the currently assigned user (if any)
                        if (response.job.assigned_to) {
                            let assignedId = String(response.job.assigned_to);
                            console.log('Setting assigned user ID:', assignedId);

                            setTimeout(function() {
                                $('#assigned_to').val(assignedId);
                                console.log('Selected value:', $('#assigned_to').val());
                            }, 100);
                        }

                        $('#assignJobModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading assignment:', xhr);
                        $('#assigned_to').val('');
                        $('#assignJobModal').modal('show');
                    }
                });
            });

            // Submit Assign Form
            $('#assignJobForm').on('submit', function(e) {
                e.preventDefault();

                let jobId = $('#assign_job_id').val();
                let formData = new FormData(this);

                $.ajax({
                    url: '/jobs/' + jobId + '/assign',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#assignJobModal').modal('hide');
                        Swal.fire('Assigned!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Assign error:', xhr);
                        Swal.fire('Error!', 'Failed to assign job', 'error');
                    }
                });
            });
        });

        function deleteJob(jobId) {
            Swal.fire({
                title: 'Delete Job?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/jobs/' + jobId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            Swal.fire('Deleted!', 'Job deleted successfully', 'success').then(() => {
                                window.location.href = '{{ route("jobs.index") }}';
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to delete job', 'error');
                        }
                    });
                }
            });
        }

        function startJob(jobId) {
            Swal.fire({
                title: 'Start Job?',
                text: 'This will mark the job as in progress',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Start',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/jobs/' + jobId + '/start',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            Swal.fire('Started!', 'Job started successfully', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to start job', 'error');
                        }
                    });
                }
            });
        }

        function completeJob(jobId) {
            Swal.fire({
                title: 'Complete Job?',
                text: 'This will mark the job as completed',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Complete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/jobs/' + jobId + '/complete',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            Swal.fire('Completed!', 'Job completed successfully', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to complete job', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endsection
