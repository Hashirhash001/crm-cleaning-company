@forelse($candidates as $index => $candidate)
<tr>
    <td>
        <input type="checkbox" class="form-check-input candidate-select-checkbox" value="{{ $candidate->id }}">
    </td>
    <td>{{ $candidates->firstItem() + $index }}</td>
    <td>
        <a href="{{ route('recruitment.candidates.show', $candidate) }}" class="fw-semibold text-primary text-decoration-none">
            {{ $candidate->full_name }}
        </a>
    </td>
    <td>{{ $candidate->phone }}</td>
    <td>{{ optional($candidate->position)->name ?? '—' }}</td>
    <td>{{ optional($candidate->department)->name ?? '—' }}</td>
    <td>{{ $candidate->interview_date ? $candidate->interview_date->format('d M Y') : '—' }}</td>
    <td>{{ $candidate->interview_time ? \Carbon\Carbon::parse($candidate->interview_time)->format('h:i A') : '—' }}</td>
    <td>{{ $candidate->interviewer ?? '—' }}</td>
    <td>
        <span class="badge bg-{{ \App\Models\RecruitmentCandidate::interviewStatusBadge($candidate->interview_status) }}-subtle text-{{ \App\Models\RecruitmentCandidate::interviewStatusBadge($candidate->interview_status) }}">
            {{ $candidate->interview_status }}
        </span>
    </td>
    <td>
        <span class="badge bg-{{ \App\Models\RecruitmentCandidate::interviewResultBadge($candidate->interview_result) }}-subtle text-{{ \App\Models\RecruitmentCandidate::interviewResultBadge($candidate->interview_result) }}">
            {{ $candidate->interview_result }}
        </span>
    </td>
    <td>
        <span class="badge bg-{{ \App\Models\RecruitmentCandidate::joiningStatusBadge($candidate->joining_status) }}-subtle text-{{ \App\Models\RecruitmentCandidate::joiningStatusBadge($candidate->joining_status) }}">
            {{ $candidate->joining_status }}
        </span>
    </td>
    <td>{{ $candidate->expected_join_date ? $candidate->expected_join_date->format('d M Y') : '—' }}</td>
    <td>{{ $candidate->joined_date ? $candidate->joined_date->format('d M Y') : '—' }}</td>
    <td>{{ $candidate->follow_up_date ? $candidate->follow_up_date->format('d M Y') : '—' }}</td>
    <td>{{ $candidate->decision_reason ?? '—' }}</td>
    <td>{{ Str::limit($candidate->remarks, 30) ?? '—' }}</td>
    <td>{{ $candidate->reference ?? '—' }}</td>
    <td class="text-end text-nowrap">
        <a href="{{ route('recruitment.candidates.show', $candidate) }}" class="btn btn-sm btn-outline-info" title="View">
            <i class="las la-eye"></i>
        </a>
        <a href="{{ route('recruitment.candidates.edit', $candidate) }}" class="btn btn-sm btn-outline-primary" title="Edit">
            <i class="las la-edit"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger deleteCandidateBtn" data-id="{{ $candidate->id }}" data-name="{{ $candidate->full_name }}" title="Delete">
            <i class="las la-trash"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="18" class="text-center text-muted py-4">
        <i class="las la-inbox" style="font-size: 2rem;"></i><br>
        No candidates found. <a href="{{ route('recruitment.candidates.create') }}">Add your first candidate</a>.
    </td>
</tr>
@endforelse
