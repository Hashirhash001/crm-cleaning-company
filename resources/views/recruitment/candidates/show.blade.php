@extends('layouts.app')

@section('title', 'Candidate Details')

@section('extra-css')
<style>
    .detail-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .detail-section h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
    .detail-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .detail-value {
        font-size: 1rem;
        color: #212529;
        margin-bottom: 16px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Candidate Details</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Recruitment</li>
                    <li class="breadcrumb-item"><a href="{{ route('recruitment.candidates.index') }}">Candidates</a></li>
                    <li class="breadcrumb-item active">{{ $candidate->full_name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="las la-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title mb-0">
                    <i class="las la-user me-2"></i>{{ $candidate->full_name }}
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('recruitment.candidates.edit', $candidate) }}" class="btn btn-sm btn-primary">
                        <i class="las la-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('recruitment.candidates.index') }}" class="btn btn-sm btn-secondary">
                        <i class="las la-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">

                {{-- Personal Information --}}
                <div class="detail-section">
                    <h5><i class="las la-user me-2"></i>Personal Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value">{{ $candidate->full_name }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">{{ $candidate->phone }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">{{ $candidate->email ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Place</div>
                            <div class="detail-value">{{ $candidate->place ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="detail-label">District</div>
                            <div class="detail-value">{{ $candidate->district ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value">
                                {{ $candidate->date_of_birth ? $candidate->date_of_birth->format('d M Y') : '—' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Age</div>
                            <div class="detail-value">
                                @if($candidate->age)
                                    <span class="fw-bold text-primary">{{ $candidate->age }} years</span>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Driving Skill</div>
                            <div class="detail-value">
                                <span class="badge bg-{{ $candidate->driving_skill === 'Yes' ? 'success' : 'secondary' }}-subtle text-{{ $candidate->driving_skill === 'Yes' ? 'success' : 'secondary' }} px-3 py-2">
                                    {{ $candidate->driving_skill ?? 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="detail-label">Current Company</div>
                            <div class="detail-value">{{ $candidate->current_company ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Position --}}
                <div class="detail-section">
                    <h5><i class="las la-briefcase me-2"></i>Position</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Position Applied</div>
                            <div class="detail-value">{{ optional($candidate->position)->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Department</div>
                            <div class="detail-value">{{ optional($candidate->department)->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Interview --}}
                <div class="detail-section">
                    <h5><i class="las la-calendar-check me-2"></i>Interview</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="detail-label">Interview Date</div>
                            <div class="detail-value">{{ $candidate->interview_date ? $candidate->interview_date->format('d M Y') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Interview Time</div>
                            <div class="detail-value">{{ $candidate->interview_time ? \Carbon\Carbon::parse($candidate->interview_time)->format('h:i A') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Interviewer</div>
                            <div class="detail-value">{{ $candidate->interviewer ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Interview Status</div>
                            <div class="detail-value">
                                <span class="badge bg-{{ \App\Models\RecruitmentCandidate::interviewStatusBadge($candidate->interview_status) }}-subtle text-{{ \App\Models\RecruitmentCandidate::interviewStatusBadge($candidate->interview_status) }} px-3 py-2">
                                    {{ $candidate->interview_status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="detail-label">Interview Result</div>
                            <div class="detail-value">
                                <span class="badge bg-{{ \App\Models\RecruitmentCandidate::interviewResultBadge($candidate->interview_result) }}-subtle text-{{ \App\Models\RecruitmentCandidate::interviewResultBadge($candidate->interview_result) }} px-3 py-2">
                                    {{ $candidate->interview_result }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Joining --}}
                <div class="detail-section">
                    <h5><i class="las la-door-open me-2"></i>Joining</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="detail-label">Joining Status</div>
                            <div class="detail-value">
                                <span class="badge bg-{{ \App\Models\RecruitmentCandidate::joiningStatusBadge($candidate->joining_status) }}-subtle text-{{ \App\Models\RecruitmentCandidate::joiningStatusBadge($candidate->joining_status) }} px-3 py-2">
                                    {{ $candidate->joining_status }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Expected Joining Date</div>
                            <div class="detail-value">{{ $candidate->expected_join_date ? $candidate->expected_join_date->format('d M Y') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Joined Date</div>
                            <div class="detail-value">{{ $candidate->joined_date ? $candidate->joined_date->format('d M Y') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Follow-up Date</div>
                            <div class="detail-value">{{ $candidate->follow_up_date ? $candidate->follow_up_date->format('d M Y') : '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Decision --}}
                <div class="detail-section">
                    <h5><i class="las la-clipboard me-2"></i>Decision</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="detail-label">Decision Reason</div>
                            <div class="detail-value">{{ $candidate->decision_reason ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Reference</div>
                            <div class="detail-value">{{ $candidate->reference ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Created By</div>
                            <div class="detail-value">{{ optional($candidate->creator)->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="detail-label">Remarks</div>
                            <div class="detail-value">{{ $candidate->remarks ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Timestamps --}}
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="las la-clock me-1"></i>
                            Created: {{ $candidate->created_at->format('d M Y, h:i A') }}
                            @if($candidate->updated_at->ne($candidate->created_at))
                                | Updated: {{ $candidate->updated_at->format('d M Y, h:i A') }}
                            @endif
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
