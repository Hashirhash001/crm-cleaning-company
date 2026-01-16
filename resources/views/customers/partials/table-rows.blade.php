@forelse($customers as $customer)
    <tr data-priority="{{ $customer->priority }}"
        data-name="{{ strtolower($customer->name) }}"
        data-email="{{ strtolower($customer->email) }}"
        data-code="{{ $customer->customer_code }}"
        data-total-jobs="{{ $customer->jobs_count ?? 0 }}"
        data-completed-jobs="{{ $customer->completed_jobs_count ?? 0 }}">
        <td><span class="badge bg-primary">{{ $customer->customer_code }}</span></td>
        <td>
            <a href="{{ route('customers.show', $customer->id) }}" class="customer-name-link">
                <h6 class="m-0">{{ $customer->name }}</h6>
            </a>
        </td>
        <td>{{ $customer->phone ?? 'N/A' }}</td>
        @if(auth()->user()->role === 'super_admin')
        <td>
            <span class="badge bg-info">
                {{ $customer->branch->name ?? 'N/A' }}
            </span>
        </td>
        @endif
        <td>
            @if($customer->priority === 'high')
                <span class="badge bg-danger">High Priority</span>
            @elseif($customer->priority === 'medium')
                <span class="badge bg-warning">Medium</span>
            @else
                <span class="badge bg-success">Low</span>
            @endif
        </td>
        {{-- <td><span class="badge bg-info">{{ $customer->jobs_count ?? 0 }}</span></td> --}}
        <td><span class="badge bg-success">{{ $customer->completed_jobs_count ?? 0 }}</span></td>
        <td>
            <span class="badge bg-primary" style="font-size: 0.9rem;">
                ₹{{ number_format($customer->total_value ?? 0) }}
            </span>
        </td>
        <td class="text-end">
            @if(auth()->user()->role === 'super_admin')
                <a href="javascript:void(0)" class="editCustomerBtn" data-id="{{ $customer->id }}" title="Edit">
                    <i class="las la-pen text-secondary fs-18"></i>
                </a>
            @endif
            <a href="javascript:void(0)" class="addNoteBtn" data-id="{{ $customer->id }}"
                data-name="{{ $customer->name }}"
                data-code="{{ $customer->customer_code }}"
                title="Add Note">
                <i class="las la-comment text-info fs-18 {{ auth()->user()->role === 'super_admin' ? 'ms-2' : '' }}"></i>
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
