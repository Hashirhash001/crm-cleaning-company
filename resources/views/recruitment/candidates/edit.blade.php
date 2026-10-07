@extends('layouts.app')

@section('title', 'Edit Candidate')

@section('extra-css')
<style>
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .form-section h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
    .required-field::after {
        content: " *";
        color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Edit Candidate</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Recruitment</li>
                    <li class="breadcrumb-item"><a href="{{ route('recruitment.candidates.index') }}">Candidates</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="las la-user-edit me-2"></i>Edit: {{ $candidate->full_name }}
                </h4>
                <a href="{{ route('recruitment.candidates.show', $candidate) }}" class="btn btn-sm btn-outline-info">
                    <i class="las la-eye me-1"></i> View
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('recruitment.candidates.update', $candidate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- SECTION 1: Personal Information --}}
                    <div class="form-section">
                        <h5><i class="las la-user me-2"></i>Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label required-field">Full Name</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                       id="full_name" name="full_name" value="{{ old('full_name', $candidate->full_name) }}" required>
                                @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label required-field">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $candidate->phone) }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $candidate->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="place" class="form-label">Place</label>
                                <input type="text" class="form-control @error('place') is-invalid @enderror"
                                       id="place" name="place" value="{{ old('place', $candidate->place) }}" placeholder="Enter place">
                                @error('place') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="district" class="form-label">District</label>
                                <input type="text" class="form-control @error('district') is-invalid @enderror"
                                       id="district" name="district" value="{{ old('district', $candidate->district) }}" placeholder="Enter district">
                                @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       id="date_of_birth" name="date_of_birth"
                                       value="{{ old('date_of_birth', $candidate->date_of_birth ? $candidate->date_of_birth->format('Y-m-d') : '') }}">
                                @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="calculated_age" class="form-label">Age</label>
                                <input type="text" class="form-control bg-light fw-bold text-primary"
                                       id="calculated_age" readonly placeholder="Auto"
                                       value="{{ $candidate->age ? $candidate->age . ' yrs' : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="driving_skill" class="form-label required-field">Driving Skill</label>
                                <select class="form-select @error('driving_skill') is-invalid @enderror"
                                        id="driving_skill" name="driving_skill" required>
                                    <option value="Yes" {{ old('driving_skill', $candidate->driving_skill) == 'Yes' ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ old('driving_skill', $candidate->driving_skill ?? 'No') == 'No' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('driving_skill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: Position & Department --}}
                    <div class="form-section">
                        <h5><i class="las la-briefcase me-2"></i>Position & Department</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="recruitment_department_id" class="form-label required-field">Department</label>
                                <select class="form-select @error('recruitment_department_id') is-invalid @enderror"
                                        id="recruitment_department_id" name="recruitment_department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                                {{ old('recruitment_department_id', $candidate->recruitment_department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('recruitment_department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="recruitment_position_id" class="form-label required-field">Position Applied</label>
                                <select class="form-select @error('recruitment_position_id') is-invalid @enderror"
                                        id="recruitment_position_id" name="recruitment_position_id" required>
                                    <option value="">Select Position</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}" data-dept="{{ $pos->recruitment_department_id }}"
                                                {{ old('recruitment_position_id', $candidate->recruitment_position_id) == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('recruitment_position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="current_company" class="form-label">Current Company</label>
                                <input type="text" class="form-control @error('current_company') is-invalid @enderror"
                                       id="current_company" name="current_company" value="{{ old('current_company', $candidate->current_company) }}">
                                @error('current_company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: Interview Details --}}
                    <div class="form-section">
                        <h5><i class="las la-calendar-check me-2"></i>Interview Details</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="interview_date" class="form-label">Interview Date</label>
                                <input type="date" class="form-control @error('interview_date') is-invalid @enderror"
                                       id="interview_date" name="interview_date"
                                       value="{{ old('interview_date', $candidate->interview_date ? $candidate->interview_date->format('Y-m-d') : '') }}">
                                @error('interview_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="interview_time" class="form-label">Interview Time</label>
                                <input type="time" class="form-control @error('interview_time') is-invalid @enderror"
                                       id="interview_time" name="interview_time" value="{{ old('interview_time', $candidate->interview_time) }}">
                                @error('interview_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="interviewer" class="form-label">Interviewer</label>
                                <input type="text" class="form-control @error('interviewer') is-invalid @enderror"
                                       id="interviewer" name="interviewer" value="{{ old('interviewer', $candidate->interviewer) }}" placeholder="Enter interviewer name">
                                @error('interviewer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="interview_status" class="form-label required-field">Interview Status</label>
                                <select class="form-select @error('interview_status') is-invalid @enderror"
                                        id="interview_status" name="interview_status" required>
                                    @foreach(\App\Models\RecruitmentCandidate::INTERVIEW_STATUSES as $status)
                                        <option value="{{ $status }}"
                                                {{ old('interview_status', $candidate->interview_status) == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interview_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: Interview Result --}}
                    <div class="form-section">
                        <h5><i class="las la-poll me-2"></i>Interview Result</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="interview_result" class="form-label required-field">Interview Result</label>
                                <select class="form-select @error('interview_result') is-invalid @enderror"
                                        id="interview_result" name="interview_result" required>
                                    @foreach(\App\Models\RecruitmentCandidate::INTERVIEW_RESULTS as $result)
                                        <option value="{{ $result }}"
                                                {{ old('interview_result', $candidate->interview_result) == $result ? 'selected' : '' }}>
                                            {{ $result }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interview_result') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 5: Joining Details --}}
                    <div class="form-section">
                        <h5><i class="las la-door-open me-2"></i>Joining Details</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="joining_status" class="form-label required-field">Joining Status</label>
                                <select class="form-select @error('joining_status') is-invalid @enderror"
                                        id="joining_status" name="joining_status" required>
                                    @foreach(\App\Models\RecruitmentCandidate::JOINING_STATUSES as $status)
                                        <option value="{{ $status }}"
                                                {{ old('joining_status', $candidate->joining_status) == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('joining_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="expected_join_date" class="form-label">Expected Joining Date</label>
                                <input type="date" class="form-control @error('expected_join_date') is-invalid @enderror"
                                       id="expected_join_date" name="expected_join_date"
                                       value="{{ old('expected_join_date', $candidate->expected_join_date ? $candidate->expected_join_date->format('Y-m-d') : '') }}">
                                @error('expected_join_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="joined_date" class="form-label">Joined Date</label>
                                <input type="date" class="form-control @error('joined_date') is-invalid @enderror"
                                       id="joined_date" name="joined_date"
                                       value="{{ old('joined_date', $candidate->joined_date ? $candidate->joined_date->format('Y-m-d') : '') }}">
                                @error('joined_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="follow_up_date" class="form-label">Follow-up Date</label>
                                <input type="date" class="form-control @error('follow_up_date') is-invalid @enderror"
                                       id="follow_up_date" name="follow_up_date"
                                       value="{{ old('follow_up_date', $candidate->follow_up_date ? $candidate->follow_up_date->format('Y-m-d') : '') }}">
                                @error('follow_up_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 6: Decision & Notes --}}
                    <div class="form-section">
                        <h5><i class="las la-clipboard me-2"></i>Decision & Notes</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="decision_reason" class="form-label">Decision Reason</label>
                                <select class="form-select @error('decision_reason') is-invalid @enderror"
                                        id="decision_reason" name="decision_reason">
                                    <option value="">Select Reason</option>
                                    @foreach(\App\Models\RecruitmentCandidate::DECISION_REASONS as $reason)
                                        <option value="{{ $reason }}"
                                                {{ old('decision_reason', $candidate->decision_reason) == $reason ? 'selected' : '' }}>
                                            {{ $reason }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('decision_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="reference" class="form-label">Reference</label>
                                <select class="form-select @error('reference') is-invalid @enderror"
                                        id="reference" name="reference">
                                    <option value="">Select Reference</option>
                                    @foreach(\App\Models\RecruitmentCandidate::REFERENCES as $ref)
                                        <option value="{{ $ref }}"
                                                {{ old('reference', $candidate->reference) == $ref ? 'selected' : '' }}>
                                            {{ $ref }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror"
                                          id="remarks" name="remarks" rows="3" placeholder="Additional notes...">{{ old('remarks', $candidate->remarks) }}</textarea>
                                @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('recruitment.candidates.index') }}" class="btn btn-secondary">
                            <i class="las la-arrow-left me-1"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-save me-1"></i> Update Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script>
$(document).ready(function() {
    $('#recruitment_department_id').on('change', function() {
        let deptId = $(this).val();
        let $posSelect = $('#recruitment_position_id');
        let currentPos = $posSelect.val();
        $posSelect.find('option').each(function() {
            let $opt = $(this);
            if ($opt.val() === '') return;
            if (deptId === '' || $opt.data('dept') == deptId) {
                $opt.show();
            } else {
                $opt.hide();
                if ($opt.val() == currentPos) $posSelect.val('');
            }
        });
    });
    if ($('#recruitment_department_id').val()) {
        $('#recruitment_department_id').trigger('change');
    }

    // Automatic Age Calculation from Date of Birth
    function updateAge() {
        let dobVal = $('#date_of_birth').val();
        if (!dobVal) {
            $('#calculated_age').val('');
            return;
        }
        let dob = new Date(dobVal);
        let today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        let m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        if (age >= 0 && !isNaN(age)) {
            $('#calculated_age').val(age + ' yrs');
        } else {
            $('#calculated_age').val('');
        }
    }

    $('#date_of_birth').on('change input', updateAge);
    if ($('#date_of_birth').val() && !$('#calculated_age').val()) {
        updateAge();
    }
});
</script>
@endsection
