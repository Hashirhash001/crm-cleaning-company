@forelse($leads as $lead)
    <tr data-lead-id="{{ $lead->id }}"
        data-code="{{ $lead->lead_code }}"
        data-name="{{ $lead->name }}"
        data-phone="{{ $lead->phone }}"
        data-service="{{ $lead->service_type ?? 'N/A' }}"
        data-status="{{ $lead->status }}"
        data-source="{{ $lead->source->name }}"
        data-branch="{{ $lead->branch->name ?? 'N/A' }}"
        data-assigned="{{ $lead->assignedTo ? $lead->assignedTo->name : 'Unassigned' }}"
        data-created-by="{{ $lead->createdBy->name }}"
        data-date="{{ $lead->created_at->format('Y-m-d') }}"
        data-approved-date="{{ $lead->approved_at ? $lead->approved_at->format('Y-m-d') : 'N/A' }}">

        <!-- Checkbox for bulk selection -->
        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
        <td class="checkbox-col">
            {{-- REMOVED STATUS CHECK - Allow all leads to be selected --}}
            <div class="checkbox-wrapper">
                <input type="checkbox"
                       class="custom-checkbox lead-checkbox"
                       value="{{ $lead->id }}"
                       data-name="{{ $lead->name }}"
                       data-code="{{ $lead->lead_code }}"
                       data-branch="{{ $lead->branch_id }}"
                       data-status="{{ $lead->status }}"
                       title="Select {{ $lead->name }}">
            </div>
        </td>
        @endif

        <!-- Lead Code -->
        <td>
            <span class="badge bg-primary">{{ $lead->lead_code }}</span>
        </td>

        <!-- Name -->
        <td>
            <a href="{{ route('leads.show', $lead->id) }}" class="lead-name-link text-decoration-none">
                <h6 class="m-0 text-primary">{{ $lead->name }}</h6>
            </a>
        </td>

        <!-- Phone -->
        <td>{{ $lead->phone }}</td>

        <!-- Service -->
        <td>
            @php
                $serviceTypes = [
                    'cleaning' => 'Cleaning',
                    'pest_control' => 'Pest Control',
                    '' => 'Not Selected',
                ];
            @endphp

            <span class="badge bg-primary">
                {{ $serviceTypes[$lead->service_type] ?? ucfirst(str_replace('_', ' ', $lead->service_type)) }}
            </span>

        </td>

        <!-- Status -->
        <td>
            @php
            $statusColors = [
                'pending' => 'warning',
                'site_visit' => 'info',
                'not_accepting_tc' => 'danger',
                'they_will_confirm' => 'primary',
                'date_issue' => 'warning',
                'rate_issue' => 'warning',
                'service_not_provided' => 'secondary',
                'just_enquiry' => 'secondary',
                'immediate_service' => 'success',
                'no_response' => 'secondary',
                'location_not_available' => 'secondary',
                'night_work_demanded' => 'dark',
                'customisation' => 'info',
                'confirmed' => 'success',
                'approved' => 'success',
                'rejected' => 'danger',
            ];
            $color = $statusColors[$lead->status] ?? 'secondary';
            @endphp

            <span class="badge bg-{{ $color }}">
            {{ $lead->status_label }}
            </span>

        </td>

        <!-- Source -->
        <td>
            <span class="badge bg-secondary">{{ $lead->source->name }}</span>
        </td>

        <!-- Branch (Super Admin only) -->
        @if(auth()->user()->role === 'super_admin')
            <td>
                <span class="badge bg-dark">{{ $lead->branch->name ?? 'N/A' }}</span>
            </td>
        @endif

        <!-- Assigned To -->
        <td>
            @if($lead->assignedTo)
                <span class="badge bg-info">{{ $lead->assignedTo->name }}</span>
            @else
                <span class="text-muted">Unassigned</span>
            @endif
        </td>

        <!-- Created By (Super Admin only) -->
        @if(auth()->user()->role === 'super_admin')
            <td>{{ $lead->createdBy->name }}</td>
        @endif

        <!-- Created Date -->
        <td>{{ $lead->created_at->format('d-m-Y') }}</td>

        <!-- Approved Date -->
        <td>{{ $lead->approved_at ? $lead->approved_at->format('d-m-Y h:i') : 'N/A' }}</td>

        <!-- Action -->
        <td>
            <div class="action-icons d-flex gap-2">
                @php
                    $user = auth()->user();
                    $canEdit = false;
                    $canDelete = false;
                    $canApprove = false;
                    $canAssign = false;

                    // Super admin: can always edit/assign; approve only if not yet approved
                    if ($user->role === 'super_admin') {
                        $canEdit = true;          // even for approved
                        $canAssign = true;        // even for approved
                        $canDelete = $lead->status !== 'approved'; // or true if you want
                        $canApprove = $lead->status !== 'approved';
                    }
                    // Lead manager: only on non-approved
                    elseif ($user->role === 'lead_manager' && $lead->created_by === $user->id) {
                        $canEdit = $lead->status !== 'approved'; // can't edit approved
                        $canDelete = $lead->status !== 'approved';
                        $canApprove = $lead->status !== 'approved';
                        $canAssign = $lead->status !== 'approved';
                    }
                    // Telecaller: can edit own assigned leads (EVEN APPROVED)
                    elseif ($user->role === 'telecallers' && $lead->assigned_to === $user->id) {
                        $canEdit = true; // NOW INCLUDES APPROVED LEADS
                    }

                    // Rejected: delete allowed for super admin
                    if ($lead->status === 'rejected' && $user->role === 'super_admin') {
                        $canDelete = true;
                    }
                @endphp

                <!-- Assign Button -->
                @if($canAssign)
                    <a href="javascript:void(0)"
                    class="assignLeadBtn"
                    data-id="{{ $lead->id }}"
                    data-name="{{ $lead->name }}"
                    data-code="{{ $lead->lead_code }}"
                    data-branch="{{ $lead->branch_id }}"
                    title="Assign Lead">
                        <i class="las la-user-plus text-primary fs-18"></i>
                    </a>
                @endif

                @if($canEdit)
                    <a href="{{ route('leads.edit', $lead->id) }}" class="editLeadBtn" data-id="{{ $lead->id }}" title="Edit Lead">
                        <i class="las la-pen text-secondary fs-18"></i>
                    </a>
                @endif

                @if($canDelete)
                    <a href="javascript:void(0)" class="deleteLeadBtn" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" title="Delete Lead">
                        <i class="las la-trash-alt text-danger fs-18"></i>
                    </a>
                @endif

                @if($canApprove)
                    <a href="javascript:void(0)" class="approveLeadBtn" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" data-amount="{{ $lead->amount ?? 0 }}" title="Approve Lead">
                        <i class="las la-check-circle text-success fs-18"></i>
                    </a>
                    <a href="javascript:void(0)" class="rejectLeadBtn" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" title="Reject Lead">
                        <i class="las la-times-circle text-danger fs-18"></i>
                    </a>
                @endif

                @if(!$canEdit && !$canDelete && !$canApprove && !$canAssign)
                    <span class="text-muted">-</span>
                @endif
            </div>
        </td>

    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->role === 'super_admin' ? '12' : '10' }}" class="text-center py-4">
            <div class="text-center py-5">
                <i class="las la-inbox" style="font-size: 4rem; opacity: 0.2;"></i>
                <p class="text-muted mt-3 mb-0">No leads found</p>
            </div>
        </td>
    </tr>
@endforelse
