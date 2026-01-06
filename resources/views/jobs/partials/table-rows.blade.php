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

        <!-- Payment Details Column -->
        <td>
            @if($job->amount)
                <div>
                    <small class="d-block text-muted">
                        Paid: ₹{{ number_format($job->amount_paid ?? 0, 2) }}
                    </small>
                    @php
                        $balance = $job->amount - ($job->amount_paid ?? 0);
                    @endphp
                    @if($balance > 0)
                        <small class="d-block text-danger">
                            Balance: ₹{{ number_format($balance, 2) }}
                        </small>
                    @else
                        <span class="badge bg-success">Fully Paid</span>
                    @endif
                </div>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>

        <!-- Status -->
        <td>
            <span class="badge badge-{{ $job->status }}">
                {{ ucfirst(str_replace('_', ' ', $job->status)) }}
            </span>
        </td>

        <!-- Branch -->
        <td>{{ $job->branch->name ?? 'N/A' }}</td>

        {{-- Show Assigned To and Scheduled Date for super_admin, lead_manager, and telecallers --}}
        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']))
            <!-- Assigned To -->
            <td>{{ $job->assignedTo->name ?? 'Unassigned' }}</td>

            <!-- Scheduled Date & Time -->
            <td>
                @if($job->scheduled_date)
                    {{ \Carbon\Carbon::parse($job->scheduled_date)->format('d M Y') }}
                    @if($job->scheduled_time)
                        <br><small class="text-muted">{{ $job->scheduled_time }}</small>
                    @endif
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </td>
        @endif

        <!-- Actions -->
        <td style="text-align: center; min-width: 200px;">
            <div class="action-icons" style="justify-content: center;">
                @php
                    $user = auth()->user();
                @endphp

                {{-- View Button - Everyone --}}
                <a href="{{ route('jobs.show', $job->id) }}" class="text-info" title="View Details">
                    <i class="las la-eye fs-20"></i>
                </a>

                {{-- CONFIRM Button - ONLY Telecallers can confirm PENDING jobs --}}
                @if($user->role === 'telecallers' && $job->status === 'pending')
                    <a href="javascript:void(0);"
                       class="text-purple confirmJobBtn"
                       data-id="{{ $job->id }}"
                       title="Confirm Job">
                        <i class="las la-check-circle text-success fs-18"></i>
                    </a>
                @endif

                {{-- APPROVE Button - ONLY Super Admin approves CONFIRMED jobs --}}
                @if($user->role === 'super_admin' && $job->status === 'confirmed')
                    <a href="javascript:void(0);"
                       class="text-primary approveJobBtn"
                       data-id="{{ $job->id }}"
                       title="Approve Job">
                        <i class="las la-check-circle text-success fs-18"></i>
                    </a>
                @endif

                {{-- COMPLETE Button - Only for APPROVED jobs --}}
                @if($job->status === 'approved' &&
                    ($user->role === 'super_admin' ||
                     $user->role === 'telecallers' ||
                     ($user->role === 'field_staff' && $user->id === $job->assigned_to)))
                    <a href="javascript:void(0);"
                       class="text-success completeJobBtn"
                       data-id="{{ $job->id }}"
                       title="Complete Job">
                        <i class="las la-check-circle fs-20"></i>
                    </a>
                @endif

                {{-- START Button - Field Staff can start APPROVED jobs --}}
                {{-- @if($user->role === 'field_staff' &&
                    $user->id === $job->assigned_to &&
                    $job->status === 'approved')
                    <a href="javascript:void(0);"
                       class="text-warning startJobBtn"
                       data-id="{{ $job->id }}"
                       title="Start Job">
                        <i class="las la-play-circle fs-20"></i>
                    </a>
                @endif --}}

                {{-- Edit Button - Available for super_admin, lead_manager, and telecallers --}}
                @if(in_array($user->role, ['super_admin', 'lead_manager', 'telecallers']))
                    <a href="javascript:void(0);"
                       class="text-secondary editJobBtn"
                       data-id="{{ $job->id }}"
                       title="Edit">
                        <i class="las la-pen fs-20"></i>
                    </a>
                @endif

                {{-- Re-assign Button - Super Admin & Lead Manager --}}
                @if(in_array($user->role, ['super_admin', 'lead_manager']))
                    <a href="javascript:void(0);"
                       class="text-info assignJobBtn"
                       data-id="{{ $job->id }}"
                       title="Re-assign">
                        <i class="las la-user-check fs-20"></i>
                    </a>
                @endif

                {{-- Delete Button - Super Admin Only --}}
                @if($user->role === 'super_admin')
                    <a href="javascript:void(0);"
                       class="text-danger deleteJobBtn"
                       data-id="{{ $job->id }}"
                       title="Delete">
                        <i class="las la-trash-alt fs-20"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        {{-- Update colspan based on role --}}
        <td colspan="{{ in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']) ? '11' : '9' }}" class="text-center py-4">
            <div class="text-center py-5">
                <i class="las la-briefcase" style="font-size: 4rem; opacity: 0.2;"></i>
                <p class="text-muted mt-3 mb-0">No jobs found matching your filters</p>
            </div>
        </td>
    </tr>
@endforelse
