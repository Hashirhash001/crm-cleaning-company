@extends('layouts.app')

@section('title', 'Edit Lead')

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
            <h4 class="page-title">Edit Lead</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="las la-edit me-2"></i>Edit Lead - {{ $lead->lead_code }}
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.update', $lead) }}">
                    @csrf
                    @method('PUT')

                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h5><i class="las la-user me-2"></i>Personal Information</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label required-field">Name</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $lead->name) }}"
                                       placeholder="Enter lead name"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label required-field">Phone</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $lead->phone) }}"
                                       placeholder="Enter phone number"
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $lead->email) }}"
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="amount" class="form-label">
                                    Lead Amount (₹)
                                    <small class="text-muted">(Optional)</small>
                                </label>
                                <input type="number"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       id="amount"
                                       name="amount"
                                       value="{{ old('amount', $lead->amount) }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="Enter amount">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Service Details Section -->
                    <div class="form-section">
                        <h5><i class="las la-briefcase me-2"></i>Service Details</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="service_id" class="form-label required-field">Service</label>
                                <select class="form-select @error('service_id') is-invalid @enderror"
                                        id="service_id"
                                        name="service_id"
                                        required>
                                    <option value="">Select Service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ old('service_id', $lead->service_id) == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lead_source_id" class="form-label required-field">Lead Source</label>
                                <select class="form-select @error('lead_source_id') is-invalid @enderror"
                                        id="lead_source_id"
                                        name="lead_source_id"
                                        required>
                                    <option value="">Select Source</option>
                                    @foreach($lead_sources as $source)
                                        <option value="{{ $source->id }}" {{ old('lead_source_id', $lead->lead_source_id) == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_source_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="4"
                                          placeholder="Enter lead description or notes">{{ old('description', $lead->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="form-section">
                        <h5><i class="las la-building me-2"></i>Branch & Assignment</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="branch_id" class="form-label required-field">Branch</label>
                                <select class="form-select @error('branch_id') is-invalid @enderror"
                                        id="branch_id"
                                        name="branch_id"
                                        required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $lead->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label">Assign To Telecaller</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror"
                                        id="assigned_to"
                                        name="assigned_to">
                                    <option value="">Select Telecaller (Optional)</option>
                                    @foreach($telecallers as $telecaller)
                                        <option value="{{ $telecaller->id }}" {{ old('assigned_to', $lead->assigned_to) == $telecaller->id ? 'selected' : '' }}>
                                            {{ $telecaller->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="branch_id" value="{{ $lead->branch_id }}">
                    @endif

                    <!-- Action Buttons -->
                    <div class="text-end mt-4">
                        <a href="{{ route('leads.index') }}" class="btn btn-secondary me-2">
                            <i class="las la-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-save me-1"></i> Update Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // All telecallers data from server
    const allTelecallers = @json($telecallers);
    const currentAssignedTo = {{ $lead->assigned_to ?? 'null' }};

    // Branch change handler - filter telecallers
    $('#branch_id').on('change', function() {
        let selectedBranchId = $(this).val();
        let assignedToSelect = $('#assigned_to');
        let currentValue = assignedToSelect.val();

        // Clear current options except the first one
        assignedToSelect.find('option:not(:first)').remove();

        if (selectedBranchId) {
            // Filter telecallers by selected branch
            let filteredTelecallers = allTelecallers.filter(function(telecaller) {
                return telecaller.branch_id == selectedBranchId;
            });

            // Add filtered telecallers to dropdown
            filteredTelecallers.forEach(function(telecaller) {
                let isSelected = (telecaller.id == currentAssignedTo);
                assignedToSelect.append(
                    $('<option>', {
                        value: telecaller.id,
                        text: telecaller.name,
                        selected: isSelected
                    })
                );
            });

            if (filteredTelecallers.length === 0) {
                assignedToSelect.append(
                    $('<option>', {
                        value: '',
                        text: 'No telecallers in this branch',
                        disabled: true
                    })
                );
            }
        }
    });

    // Trigger change on page load to populate telecallers for current branch
    @if(auth()->user()->role === 'super_admin')
        $('#branch_id').trigger('change');
    @endif

    // Show validation errors with SweetAlert
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
        });
    @endif
});
</script>
@endsection

