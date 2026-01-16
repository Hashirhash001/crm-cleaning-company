@forelse($services as $service)
    <tr>
        <td>
            <strong>{{ $service->name }}</strong>
            @if($service->createdBy)
                <br><small class="text-muted">
                    <i class="las la-user"></i> {{ $service->createdBy->name }}
                </small>
            @endif
        </td>
        <td>
            @php
                $typeColors = [
                    'cleaning' => 'primary',
                    'pest_control' => 'success',
                    'other' => 'secondary'
                ];
                $color = $typeColors[$service->service_type] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $color }}">
                {{ ucfirst(str_replace('_', ' ', $service->service_type)) }}
            </span>
        </td>
        <td>
            <small class="text-muted">{{ Str::limit($service->description ?? 'No description', 50) }}</small>
        </td>
        <td>
            @if($service->price)
                <span class="fw-bold text-success">₹{{ number_format($service->price, 0) }}</span>
            @else
                <span class="text-muted">Variable</span>
            @endif
        </td>
        <td>
            @if($service->is_active)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-danger">Inactive</span>
            @endif
        </td>
        <td class="text-end">
            @php
                $canEdit = auth()->user()->role === 'super_admin' || $service->created_by === auth()->id();
            @endphp

            @if($canEdit)
                <button type="button" class="btn btn-sm btn-soft-primary editServiceBtn"
                        data-id="{{ $service->id }}" title="Edit">
                    <i class="las la-pen"></i>
                </button>
                <button type="button" class="btn btn-sm btn-soft-danger deleteServiceBtn"
                        data-id="{{ $service->id }}"
                        data-name="{{ $service->name }}"
                        title="Delete">
                    <i class="las la-trash"></i>
                </button>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4">
            <i class="las la-inbox" style="font-size: 4rem; opacity: 0.2;"></i>
            <p class="text-muted mt-3 mb-0">No services found</p>
        </td>
    </tr>
@endforelse
