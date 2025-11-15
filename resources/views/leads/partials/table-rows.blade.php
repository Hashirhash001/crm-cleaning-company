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
            <a href="{{ route('leads.show', $lead->id) }}" class="lead-name-link">
                <h6 class="m-0">{{ $lead->name }}</h6>
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
        @if(auth()->user()->role === 'super_admin')
            <td>{{ $lead->createdBy->name }}</td>
        @endif
        <td>{{ $lead->created_at->format('d M Y') }}</td>
        <td>
            <div class="action-icons">
                @if($lead->isPending())
                    @if((auth()->user()->role === 'lead_manager' && auth()->id() === $lead->created_by) || auth()->user()->role === 'super_admin')
                        <a href="javascript:void(0)" class="editLeadBtn" data-id="{{ $lead->id }}" title="Edit">
                            <i class="las la-pen text-secondary fs-18"></i>
                        </a>
                        <a href="javascript:void(0)" class="deleteLeadBtn" data-id="{{ $lead->id }}" title="Delete">
                            <i class="las la-trash-alt text-danger fs-18"></i>
                        </a>
                    @endif
                    @if(auth()->user()->role === 'super_admin')
                        <a href="javascript:void(0)" class="approveLeadBtn" data-id="{{ $lead->id }}" title="Approve">
                            <i class="las la-check text-success fs-18"></i>
                        </a>
                        <a href="javascript:void(0)" class="rejectLeadBtn" data-id="{{ $lead->id }}" title="Reject">
                            <i class="las la-times text-danger fs-18"></i>
                        </a>
                    @endif
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ auth()->user()->role === 'super_admin' ? '11' : '9' }}" class="text-center py-4">
            <p class="text-muted mb-0">No leads found</p>
        </td>
    </tr>
@endforelse
