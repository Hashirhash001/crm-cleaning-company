@forelse($jobs as $job)
<tr data-job-id="{{ $job->id }}" data-status="{{ $job->status }}">

    {{-- Job Code --}}
    <td>
        <span class="badge bg-primary" style="font-size:0.78rem;letter-spacing:0.3px;">
            {{ $job->job_code }}
        </span>
    </td>

    {{-- Title / Branch --}}
    <td>
        <a href="{{ route('jobs.show', $job->id) }}" class="job-title-link">
            {{ Str::limit($job->title, 35) }}
        </a>
        @if($job->branch)
            <br><small class="text-muted" style="font-size:0.75rem;">
                <i class="ph ph-map-pin"></i> {{ $job->branch->name }}
            </small>
        @endif
    </td>

    {{-- Customer --}}
    <td>
        @if($job->customer)
            <a href="{{ route('customers.show', $job->customer_id) }}" class="fw-semibold text-primary text-decoration-none">
                {{ $job->customer->name }}
            </a>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- Staff --}}
    <td style="min-width:160px;">
        @php
            $supervisors = $job->staff->where('role', 'supervisor');
            $workers     = $job->staff->where('role', 'worker');
        @endphp
        @if($job->staff->count())
            @if($supervisors->count())
                <div class="mb-1 d-flex align-items-start gap-1 flex-wrap">
                    <span class="badge" style="background:#dbeafe;color:#1d4ed8;font-size:0.68rem;padding:3px 7px;border-radius:5px;white-space:nowrap;">
                        <i class="ph ph-user-circle-gear"></i> SUP
                    </span>
                    <small class="text-dark">{{ $supervisors->pluck('display_name')->join(', ') }}</small>
                </div>
            @endif
            @if($workers->count())
                <div class="d-flex align-items-start gap-1 flex-wrap">
                    <span class="badge" style="background:#f3e8ff;color:#7c3aed;font-size:0.68rem;padding:3px 7px;border-radius:5px;white-space:nowrap;">
                        <i class="ph ph-hard-hat"></i> WRK
                    </span>
                    <small class="text-dark">{{ $workers->pluck('display_name')->join(', ') }}</small>
                </div>
            @endif
        @else
            <span class="text-muted fst-italic" style="font-size:0.82rem;">No staff assigned</span>
        @endif
    </td>

    {{-- Rating --}}
    <td>
        @if($job->rating)
            <div class="d-flex align-items-center gap-1" style="white-space:nowrap;">
                @for($i = 1; $i <= 5; $i++)
                    <i class="ph{{ $i <= $job->rating->rating ? ' ph-star-fill' : ' ph-star' }}"
                       style="color:#f59e0b;font-size:0.95rem;"></i>
                @endfor
                <span class="fw-semibold ms-1" style="font-size:0.82rem;color:#92400e;">
                    {{ $job->rating->rating }}/5
                </span>
            </div>
        @else
            <span class="text-muted" style="font-size:0.82rem;">—</span>
        @endif
    </td>

    {{-- Completed At --}}
    <td>
        <span class="fw-semibold" style="font-size:0.88rem;">
            {{ $job->completed_at?->format('d M Y') ?? '—' }}
        </span>
    </td>

    {{-- Status --}}
    <td>
        @if($job->status === 'staff_pending_approval')
            <span class="status-pill status-pending-staff">
                <i class="ph ph-clock"></i> Staff Pending
            </span>
        @else
            <span class="status-pill status-completed">
                <i class="ph ph-check-circle"></i> Completed
            </span>
        @endif
    </td>

    {{-- Actions --}}
    <td style="min-width:130px;">
        <div class="d-flex align-items-center gap-2">

            {{-- View --}}
            <a href="{{ route('jobs.show', $job->id) }}"
               title="View Details"
               class="action-btn" style="background:#eff6ff;color:#2563eb;">
                <i class="ph ph-eye"></i>
            </a>

            {{-- Add Staff --}}
            @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']))
            <button type="button"
                class="action-btn addStaffInlineBtn"
                data-id="{{ $job->id }}"
                data-title="{{ $job->title }}"
                data-code="{{ $job->job_code }}"
                title="Add Supervisor / Worker"
                style="background:#f3e8ff;color:#7c3aed;">
                <i class="ph ph-user-plus"></i>
            </button>
            @endif

            {{-- Quick Approve --}}
            @if($job->status === 'staff_pending_approval' && auth()->user()->role === 'super_admin')
            <button type="button"
                class="action-btn quickApproveBtn"
                data-id="{{ $job->id }}"
                data-action="approve"
                title="Approve Staff"
                style="background:#dcfce7;color:#16a34a;">
                <i class="ph ph-check-fat"></i>
            </button>

            {{-- Quick Reject --}}
            <button type="button"
                class="action-btn quickApproveBtn"
                data-id="{{ $job->id }}"
                data-action="reject"
                title="Reject Staff"
                style="background:#fee2e2;color:#dc2626;">
                <i class="ph ph-x-circle"></i>
            </button>
            @endif

        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <div style="padding:2.5rem 1rem;">
            <div style="width:72px;height:72px;border-radius:50%;background:#f1f5f9;
                        display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <i class="ph ph-check-circle" style="font-size:2.2rem;color:#cbd5e1;"></i>
            </div>
            <p class="fw-semibold mb-1" style="color:#475569;font-size:1rem;">No completed work orders found</p>
            <p class="text-muted mb-0" style="font-size:0.85rem;">Try adjusting your filters or search query</p>
        </div>
    </td>
</tr>
@endforelse
