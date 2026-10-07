@forelse($departments as $index => $department)
<tr>
    <td>{{ $departments->firstItem() + $index }}</td>
    <td><strong>{{ $department->name }}</strong></td>
    <td>{{ Str::limit($department->description, 60) ?? '—' }}</td>
    <td><span class="badge bg-info-subtle text-info">{{ $department->positions_count }}</span></td>
    <td><span class="badge bg-primary-subtle text-primary">{{ $department->candidates_count }}</span></td>
    <td>
        @if($department->is_active)
            <span class="badge bg-success-subtle text-success">Active</span>
        @else
            <span class="badge bg-danger-subtle text-danger">Inactive</span>
        @endif
    </td>
    <td class="text-end">
        <button class="btn btn-sm btn-outline-primary editDeptBtn" data-id="{{ $department->id }}" title="Edit">
            <i class="las la-edit"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger deleteDeptBtn" data-id="{{ $department->id }}" data-name="{{ $department->name }}" title="Delete">
            <i class="las la-trash"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center text-muted py-4">
        <i class="las la-inbox" style="font-size: 2rem;"></i><br>
        No departments found.
    </td>
</tr>
@endforelse
