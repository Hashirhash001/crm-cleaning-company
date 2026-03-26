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
        <td>
            @php
                $roleConfig = [
                    'super_admin'  => ['bg' => '#1e40af', 'light' => '#eff6ff', 'icon' => 'la-shield-alt',   'label' => 'Super Admin'],
                    'lead_manager' => ['bg' => '#065f46', 'light' => '#ecfdf5', 'icon' => 'la-user-tie',      'label' => 'Lead Manager'],
                    'telecallers' => ['bg' => '#92400e', 'light' => '#fffbeb', 'icon' => 'la-phone',         'label' => 'Telecaller'],
                    'field_staff'  => ['bg' => '#1d4ed8', 'light' => '#eff6ff', 'icon' => 'la-hard-hat',      'label' => 'Field Staff'],
                    'supervisor'  => ['bg' => '#6d28d9', 'light' => '#f5f3ff', 'icon' => 'la-user-cog',      'label' => 'Supervisor'],
                    'worker'      => ['bg' => '#be185d', 'light' => '#fdf2f8', 'icon' => 'la-tools',         'label' => 'Worker'],
                ];
                $rc = $roleConfig[$user->role] ?? ['bg' => '#475569', 'light' => '#f1f5f9', 'icon' => 'la-user', 'label' => ucfirst($user->role)];
            @endphp
            <span style="
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                background: {{ $rc['light'] }};
                color: {{ $rc['bg'] }};
                border: 1px solid {{ $rc['bg'] }}22;
                padding: 0.3rem 0.75rem;
                border-radius: 999px;
                font-size: 0.78rem;
                font-weight: 600;
                letter-spacing: 0.2px;
                white-space: nowrap;
            ">
                <i class="las {{ $rc['icon'] }}" style="font-size:0.92rem;"></i>
                {{ $rc['label'] }}
            </span>
        </td>
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
