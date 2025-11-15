@forelse($customers as $customer)
    <tr data-priority="{{ $customer->priority }}"
        data-name="{{ strtolower($customer->name) }}"
        data-email="{{ strtolower($customer->email) }}"
        data-code="{{ $customer->customer_code }}"
        data-total-jobs="{{ $customer->jobs->count() }}"
        data-completed-jobs="{{ $customer->completed_jobs_count ?? 0 }}">
        <td><span class="badge bg-primary">{{ $customer->customer_code }}</span></td>
        <td>
            <a href="{{ route('customers.show', $customer->id) }}" class="customer-name-link">
                <h6 class="m-0">{{ $customer->name }}</h6>
            </a>
        </td>
        <td><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></td>
        <td>{{ $customer->phone ?? 'N/A' }}</td>
        <td>
            @if($customer->priority === 'high')
                <span class="badge bg-danger">High Priority</span>
            @elseif($customer->priority === 'medium')
                <span class="badge bg-warning">Medium</span>
            @else
                <span class="badge bg-success">Low</span>
            @endif
        </td>
        <td><span class="badge bg-info">{{ $customer->jobs->count() }}</span></td>
        <td><span class="badge bg-success">{{ $customer->completed_jobs_count ?? 0 }}</span></td>
        <td class="text-end">
            <a href="javascript:void(0)" class="editCustomerBtn" data-id="{{ $customer->id }}" title="Edit">
                <i class="las la-pen text-secondary fs-18"></i>
            </a>
            <a href="javascript:void(0)" class="addNoteBtn" data-id="{{ $customer->id }}" title="Add Note">
                <i class="las la-comment text-info fs-18 ms-2"></i>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4">
            <p class="text-muted">No customers found</p>
        </td>
    </tr>
@endforelse
