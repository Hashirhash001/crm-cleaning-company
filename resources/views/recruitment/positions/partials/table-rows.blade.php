@forelse($positions as $index => $position)
<tr>
    <td>{{ $positions->firstItem() + $index }}</td>
    <td><strong>{{ $position->name }}</strong></td>
    <td>
        <span class="badge bg-primary-subtle text-primary">{{ optional($position->department)->name ?? '—' }}</span>
    </td>
    <td>{{ Str::limit($position->description, 60) ?? '—' }}</td>
    <td><span class="badge bg-info-subtle text-info">{{ $position->candidates_count }}</span></td>
    <td>
        @if($position->is_active)
            <span class="badge bg-success-subtle text-success">Active</span>
        @else
            <span class="badge bg-danger-subtle text-danger">Inactive</span>
        @endif
    </td>
    <td class="text-end">
        <button class="btn btn-sm btn-outline-primary editPosBtn" data-id="{{ $position->id }}" title="Edit">
            <i class="las la-edit"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger deletePosBtn" data-id="{{ $position->id }}" data-name="{{ $position->name }}" title="Delete">
            <i class="las la-trash"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center text-muted py-4">
        <i class="las la-inbox" style="font-size: 2rem;"></i><br>
        No positions found.
    </td>
</tr>
@endforelse
