@forelse($users as $user)
    <tr data-role="{{ $user->role }}" data-status="{{ $user->is_active ? '1' : '0' }}">
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1 text-truncate">
                    <a href="{{ route('users.show', $user->id) }}" class="user-name-link">
                        <h6 class="m-0">{{ $user->name }}</h6>
                    </a>
                </div>
            </div>
        </td>
        <td><a href="mailto:{{ $user->email }}" class="text-body text-decoration-underline">{{ $user->email }}</a></td>
        <td>{{ $user->phone ?? 'N/A' }}</td>
        <td><span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span></td>
        <td>{{ $user->branch->name ?? 'N/A' }}</td>
        <td>{{ $user->created_at->format('d M Y') }}</td>
        <td>
            @if($user->is_active)
                <span class="badge rounded text-success bg-success-subtle">Active</span>
            @else
                <span class="badge rounded text-secondary bg-secondary-subtle">Inactive</span>
            @endif
        </td>
        <td class="text-end">
            {{-- Only super admins can edit/delete users --}}
            @if(auth()->user()->role === 'super_admin')
                <a href="javascript:void(0)" class="editBtn" data-id="{{ $user->id }}" title="Edit">
                    <i class="las la-pen text-secondary fs-18"></i>
                </a>
                <a href="javascript:void(0)" class="deleteBtn" data-id="{{ $user->id }}" title="Delete">
                    <i class="las la-trash-alt text-danger fs-18 ms-2"></i>
                </a>
            @else
                {{-- Lead managers can only view --}}
                <a href="{{ route('users.show', $user->id) }}" title="View Details">
                    <i class="las la-eye text-info fs-18"></i>
                </a>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4">
            <p class="text-muted">No users found</p>
        </td>
    </tr>
@endforelse
