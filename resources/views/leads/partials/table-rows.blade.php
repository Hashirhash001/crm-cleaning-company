@forelse($leads as $lead)
    <tr data-lead-id="{{ $lead->id }}"
        data-status="{{ $lead->status }}"
        data-source="{{ $lead->lead_source_id }}"
        data-branch="{{ $lead->branch_id }}"
        data-date="{{ $lead->created_at->format('Y-m-d') }}">
        <td>
            <span class="badge bg-primary">{{ $lead->lead_code }}</span>
        </td>
        <td>
            <a href="{{ route('leads.show', $lead->id) }}" class="lead-name-link text-decoration-none">
                <h6 class="m-0 text-primary">{{ $lead->name }}</h6>
            </a>
        </td>
        <td>
            <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
        </td>
        <td>{{ $lead->phone }}</td>
        <td>
            <span class="badge bg-info">{{ $lead->service->name ?? 'N/A' }}</span>
        </td>
        <td>
            <span class="badge bg-secondary">{{ $lead->source->name }}</span>
        </td>
        @if(auth()->user()->role === 'super_admin')
            <td>
                <span class="badge bg-dark">{{ $lead->branch->name ?? 'N/A' }}</span>
            </td>
        @endif
        <td>
            <span class="badge badge-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
        </td>
        <td>
            @if($lead->assignedTo)
                <span class="badge bg-info">{{ $lead->assignedTo->name }}</span>
            @else
                <span class="text-muted">Unassigned</span>
            @endif
        </td>
        @if(auth()->user()->role === 'super_admin')
            <td>{{ $lead->createdBy->name }}</td>
        @endif
        <td>{{ $lead->created_at->format('d M Y') }}</td>
        <td>
            <div class="action-icons d-flex gap-2">
                @php
                    $user = auth()->user();
                    $canEdit = false;
                    $canDelete = false;
                    $canApprove = false;

                    if ($lead->status === 'pending') {
                        // Super Admin can edit, delete, approve all
                        if ($user->role === 'super_admin') {
                            $canEdit = true;
                            $canDelete = true;
                            $canApprove = true;
                        }
                        // Lead Manager can edit/delete their own leads
                        elseif ($user->role === 'lead_manager' && $lead->created_by === $user->id) {
                            $canEdit = true;
                            $canDelete = true;
                            $canApprove = true; // Lead managers can also approve
                        }
                        // Telecallers can edit their assigned leads
                        elseif ($user->role === 'telecallers' && $lead->assigned_to === $user->id) {
                            $canEdit = true;
                        }
                    }
                @endphp

                @if($canEdit)
                    <a href="javascript:void(0)" class="editLeadBtn" data-id="{{ $lead->id }}" title="Edit Lead">
                        <i class="las la-pen text-secondary fs-18"></i>
                    </a>
                @endif

                @if($canDelete)
                    <a href="javascript:void(0)" class="deleteLeadBtn" data-id="{{ $lead->id }}" title="Delete Lead">
                        <i class="las la-trash-alt text-danger fs-18"></i>
                    </a>
                @endif

                @if($canApprove)
                    <a href="javascript:void(0)" class="approveLeadBtn" data-id="{{ $lead->id }}" title="Approve Lead">
                        <i class="las la-check-circle text-success fs-18"></i>
                    </a>
                    <a href="javascript:void(0)" class="rejectLeadBtn" data-id="{{ $lead->id }}" title="Reject Lead">
                        <i class="las la-times-circle text-danger fs-18"></i>
                    </a>
                @endif

                @if(!$canEdit && !$canDelete && !$canApprove)
                    <span class="text-muted">-</span>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->role === 'super_admin' ? '12' : '10' }}" class="text-center py-4">
            <p class="text-muted mb-0">No leads found</p>
        </td>
    </tr>
@endforelse
