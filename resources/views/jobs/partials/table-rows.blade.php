@forelse($jobs as $job)
    <tr data-job-id="{{ $job->id }}" data-status="{{ $job->status }}" data-branch="{{ $job->branch_id }}">
        <td><span class="badge bg-primary">{{ $job->job_code }}</span></td>
        <td>
            <a href="{{ route('jobs.show', $job->id) }}" class="job-title-link">
                {{ Str::limit($job->title, 40) }}
            </a>
        </td>
        <td>
            @if($job->customer)
                <a href="{{ route('customers.show', $job->customer_id) }}" class="text-primary">
                    {{ $job->customer->name }}
                </a>
            @else
                <span class="text-muted">No Customer</span>
            @endif
        </td>
        <td><span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span></td>
        <td>
            @if($job->amount)
                <strong class="text-success">₹{{ number_format($job->amount, 2) }}</strong>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td><span class="badge badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span></td>
        <td>{{ $job->branch->name }}</td>
        @if(auth()->user()->role === 'super_admin')
            <td>{{ $job->assignedTo->name ?? 'Unassigned' }}</td>
            <td>
                @if($job->scheduled_date)
                    {{ $job->scheduled_date->format('d M Y') }}
                    @if($job->scheduled_time)
                        <br><small class="text-muted">{{ $job->scheduled_time }}</small>
                    @endif
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </td>
        @endif
        <td style="text-align: center;">
            <div class="action-icons" style="justify-content: center;">
                @if(auth()->user()->role === 'super_admin')
                    <a href="javascript:void(0)" class="editJobBtn" data-id="{{ $job->id }}" title="Edit">
                        <i class="las la-pen text-secondary fs-18"></i>
                    </a>
                    <a href="javascript:void(0)" class="deleteJobBtn" data-id="{{ $job->id }}" title="Delete">
                        <i class="las la-trash-alt text-danger fs-18"></i>
                    </a>
                    <a href="javascript:void(0)" class="assignJobBtn" data-id="{{ $job->id }}" title="Re-assign">
                        <i class="las la-user-check text-info fs-18"></i>
                    </a>
                @endif

                @if(auth()->user()->role === 'field_staff' && auth()->id() === $job->assigned_to)
                    @if($job->status === 'assigned')
                        <a href="javascript:void(0)" class="startJobBtn" data-id="{{ $job->id }}" title="Start Job">
                            <i class="las la-play text-success fs-18"></i>
                        </a>
                    @endif

                    @if($job->status === 'in_progress')
                        <a href="javascript:void(0)" class="completeJobBtn" data-id="{{ $job->id }}" title="Complete Job">
                            <i class="las la-check-circle text-success fs-18"></i>
                        </a>
                    @endif
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->role === 'super_admin' ? '9' : '7' }}" class="text-center py-4">
            <p class="text-muted mb-0">No jobs found matching your filters</p>
        </td>
    </tr>
@endforelse
