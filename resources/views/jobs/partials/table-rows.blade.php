@forelse($jobs as $job)
    <tr data-job-id="{{ $job->id }}" data-status="{{ $job->status }}" data-branch="{{ $job->branch_id }}">
        <!-- Job Code -->
        <td><span class="badge bg-primary">{{ $job->job_code }}</span></td>

        <!-- Job Title -->
        <td>
            <a href="{{ route('jobs.show', $job->id) }}" class="job-title-link">
                {{ Str::limit($job->title, 40) }}
            </a>
        </td>

        <!-- Customer -->
        <td>
            @if($job->customer)
                <a href="{{ route('customers.show', $job->customer_id) }}" class="text-primary">
                    {{ $job->customer->name }}
                </a>
            @else
                <span class="text-muted">No Customer</span>
            @endif
        </td>

        <!-- Services -->
        <td>
            @if($job->services && $job->services->count() > 0)
                @foreach($job->services->take(2) as $service)
                    <span class="badge bg-info">{{ $service->name }}</span>
                @endforeach
                @if($job->services->count() > 2)
                    <span class="badge bg-secondary">+{{ $job->services->count() - 2 }} more</span>
                @endif
            @elseif($job->service)
                <span class="badge bg-info">{{ $job->service->name }}</span>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>

        <!-- Amount -->
        <td>
            @if($job->amount)
                <strong class="text-success">₹{{ number_format($job->amount, 2) }}</strong>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>

        <!-- Status -->
        <td><span class="badge badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span></td>

        <!-- Branch -->
        <td>{{ $job->branch->name }}</td>

        {{-- Show Assigned To and Scheduled Date for super_admin, lead_manager, and telecallers --}}
        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']))
            <!-- Assigned To -->
            <td>{{ $job->assignedTo->name ?? 'Unassigned' }}</td>

            <!-- Scheduled Date & Time -->
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

        <!-- Actions -->
        <td style="text-align: center;">
            <div class="action-icons" style="justify-content: center;">
                @php
                    $user = auth()->user();
                    $canEdit = in_array($user->role, ['super_admin', 'lead_manager', 'telecallers']);
                @endphp

                {{-- Edit Button - Available for super_admin, lead_manager, and telecallers --}}
                @if($canEdit)
                    <a href="javascript:void(0)" class="editJobBtn" data-id="{{ $job->id }}" title="Edit">
                        <i class="las la-pen text-secondary fs-18"></i>
                    </a>
                @endif

                {{-- Delete Button - Super Admin Only --}}
                @if(auth()->user()->role === 'super_admin')
                    <a href="javascript:void(0)" class="deleteJobBtn" data-id="{{ $job->id }}" title="Delete">
                        <i class="las la-trash-alt text-danger fs-18"></i>
                    </a>
                    <a href="javascript:void(0)" class="assignJobBtn" data-id="{{ $job->id }}" title="Re-assign">
                        <i class="las la-user-check text-info fs-18"></i>
                    </a>
                @endif

                {{-- Confirm Button - Super Admin Only for Pending Jobs --}}
                @if(auth()->user()->role === 'super_admin' && $job->status === 'pending')
                    <button type="button"
                            class="btn btn-sm confirmJobBtn"
                            data-id="{{ $job->id }}"
                            title="Confirm Job"
                            style="padding: 0; border: none; background: transparent;">
                        <i class="las la-check-circle text-success fs-18"></i>
                    </button>
                @endif

                {{-- Field Staff Actions --}}
                @if(auth()->user()->role === 'field_staff' && auth()->id() === $job->assigned_to)
                    @if(in_array($job->status, ['assigned', 'confirmed']))
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
        {{-- Update colspan based on role --}}
        <td colspan="{{ in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']) ? '10' : '8' }}" class="text-center py-4">
            <div class="text-center py-5">
                <i class="las la-briefcase" style="font-size: 4rem; opacity: 0.2;"></i>
                <p class="text-muted mt-3 mb-0">No jobs found matching your filters</p>
            </div>
        </td>
    </tr>
@endforelse
