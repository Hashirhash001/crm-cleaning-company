@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">User Details</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- User Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">User Information</h5>
                    @if($user->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong><br>{{ $user->name }}</p>
                <p><strong>Email:</strong><br><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
                <p><strong>Phone:</strong><br>{{ $user->phone ?? 'N/A' }}</p>
                <p><strong>Role:</strong><br><span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span></p>
                <p><strong>Branch:</strong><br>{{ $user->branch->name ?? 'N/A' }}</p>
                <p><strong>Joined:</strong><br>{{ $user->created_at->format('d M Y') }}</p>

                <hr>
                <button type="button" class="btn btn-sm btn-primary w-100" onclick="window.location.href='{{ route('users.index') }}'">
                    <i class="las la-arrow-left"></i> Back to Users
                </button>
            </div>
        </div>
    </div>

    <!-- Stats & Jobs -->
    <div class="col-lg-8">
        <!-- Stats Cards -->
        @if($user->role === 'field_staff')
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-primary">{{ $totalJobs }}</h3>
                        <small class="text-muted">Total Jobs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-success">{{ $completedJobs }}</h3>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-info">{{ $inProgressJobs }}</h3>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-warning">{{ $pendingJobs }}</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user->role === 'lead_manager')
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-primary">{{ $user->createdLeads->count() }}</h3>
                        <small class="text-muted">Total Leads Created</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-success">{{ $user->createdLeads->where('status', 'approved')->count() }}</h3>
                        <small class="text-muted">Approved Leads</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Assigned Jobs (For Field Staff) -->
        @if($user->role === 'field_staff')
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Assigned Jobs</h5>
            </div>
            <div class="card-body">
                @if($user->assignedJobs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Code</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Scheduled Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->assignedJobs->sortByDesc('created_at') as $job)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $job->job_code }}</span></td>
                                    <td>
                                        @if($job->customer)
                                            <a href="{{ route('customers.show', $job->customer->id) }}" target="_blank">
                                                {{ $job->customer->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $job->service->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($job->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($job->status === 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @elseif($job->status === 'assigned')
                                            <span class="badge bg-info">Assigned</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'Not Scheduled' }}</td>
                                    <td>
                                        <a href="javascript:void(0)" class="viewJobBtn" data-id="{{ $job->id }}">
                                            <i class="las la-eye text-secondary fs-18"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No jobs assigned yet.</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Created Leads (For Lead Manager) -->
        @if($user->role === 'lead_manager')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Created Leads</h5>
            </div>
            <div class="card-body">
                @if($user->createdLeads->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Lead Code</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->createdLeads->sortByDesc('created_at')->take(20) as $lead)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $lead->lead_code }}</span></td>
                                    <td>{{ $lead->name }}</td>
                                    <td>{{ $lead->email }}</td>
                                    <td>{{ $lead->phone }}</td>
                                    <td>
                                        @if($lead->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($lead->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->created_at->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No leads created yet.</p>
                @endif
            </div>
        </div>
        @endif

        @if($user->role === 'super_admin' || $user->role === 'reporting_user')
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="las la-user-shield fs-1 text-muted"></i>
                <h5 class="mt-3">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</h5>
                <p class="text-muted">This user has administrative access to the system.</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- View Job Modal -->
<div class="modal fade" id="viewJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobDetailsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // View Job
            $(document).on('click', '.viewJobBtn', function() {
                let jobId = $(this).data('id');

                $.ajax({
                    url: '/jobs/' + jobId,
                    type: 'GET',
                    success: function(response) {
                        $('#jobDetailsContent').html(response.html);
                        $('#viewJobModal').modal('show');
                    }
                });
            });
        });
    </script>
@endsection
