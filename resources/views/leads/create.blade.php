@extends('layouts.app')

@section('title', 'Create New Lead')

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
    .duplicate-alert {
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Create New Lead</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
                    <i class="las la-user-plus me-2"></i>Lead Information
                </h4>
            </div>
            <div class="card-body">
                <form id="createLeadForm" method="POST" action="{{ route('leads.store') }}">
                    @csrf

                    <!-- Duplicate Alert Container -->
                    <div id="duplicateAlertContainer"></div>

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
                                       value="{{ old('name') }}"
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
                                       value="{{ old('phone') }}"
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
                                       value="{{ old('email') }}"
                                       placeholder="Enter email address">
                                <small class="text-muted">We'll check for duplicates automatically</small>
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
                                       value="{{ old('amount') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="Enter amount">
                                <small class="text-muted">You can add this later</small>
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
                                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
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
                                        <option value="{{ $source->id }}" {{ old('lead_source_id') == $source->id ? 'selected' : '' }}>
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
                                          placeholder="Enter lead description or notes">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Branch Information (Hidden for non-super-admin) -->
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
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                                        <option value="{{ $telecaller->id }}" {{ old('assigned_to') == $telecaller->id ? 'selected' : '' }}>
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
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    @endif

                    <!-- Action Buttons -->
                    <div class="text-end mt-4">
                        <a href="{{ route('leads.index') }}" class="btn btn-secondary me-2">
                            <i class="las la-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="las la-save me-1"></i> Create Lead
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

    let duplicateCheckTimeout;
    let isDuplicateFound = false;
    let currentCustomerData = null;

    // All telecallers data from server
    const allTelecallers = @json($telecallers);

    // Branch change handler - filter telecallers
    $('#branch_id').on('change', function() {
        let selectedBranchId = $(this).val();
        let assignedToSelect = $('#assigned_to');

        // Clear current options except the first one
        assignedToSelect.find('option:not(:first)').remove();

        if (selectedBranchId) {
            // Filter telecallers by selected branch
            let filteredTelecallers = allTelecallers.filter(function(telecaller) {
                return telecaller.branch_id == selectedBranchId;
            });

            // Add filtered telecallers to dropdown
            filteredTelecallers.forEach(function(telecaller) {
                assignedToSelect.append(
                    $('<option>', {
                        value: telecaller.id,
                        text: telecaller.name
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

    // Real-time Duplicate Detection
    $('#email, #phone').on('keyup', function() {
        clearTimeout(duplicateCheckTimeout);

        let email = $('#email').val().trim();
        let phone = $('#phone').val().trim();

        // Clear previous alerts
        $('#duplicateAlertContainer').html('');
        isDuplicateFound = false;
        currentCustomerData = null;
        $('#submitBtn').prop('disabled', false).html('<i class="las la-save me-1"></i> Create Lead');

        // Only check if email or phone has reasonable length
        if (email.length < 5 && phone.length < 8) {
            return;
        }

        duplicateCheckTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("leads.checkDuplicate") }}',
                type: 'POST',
                data: {
                    email: email,
                    phone: phone
                },
                success: function(response) {
                    if (response.exists) {
                        isDuplicateFound = true;
                        currentCustomerData = response.data;
                        showDuplicateAlert(response);
                        $('#submitBtn').prop('disabled', true).html('<i class="las la-ban me-1"></i> Cannot Create - Duplicate Found');
                    }
                }
            });
        }, 600);
    });

    function showDuplicateAlert(response) {
        let alertHtml = '';

        if (response.type === 'customer') {
            let data = response.data;
            let priorityClass = data.priority === 'high' ? 'danger' : (data.priority === 'medium' ? 'warning' : 'success');

            alertHtml = `
                <div class="alert alert-${priorityClass} duplicate-alert">
                    <h5 class="alert-heading">
                        <i class="las la-exclamation-triangle"></i> Existing Customer Detected!
                    </h5>
                    <hr>
                    <p><strong>Name:</strong> ${data.name}</p>
                    <p><strong>Customer Code:</strong> <span class="badge bg-primary">${data.customer_code}</span></p>
                    <p><strong>Total Jobs:</strong> ${data.total_jobs}</p>
                    <hr>
                    <p class="mb-0">
                        <a href="/customers/${data.customer_id}" class="btn btn-sm btn-info" target="_blank">
                            <i class="las la-eye"></i> View Customer
                        </a>
                    </p>
                </div>
            `;
        } else if (response.type === 'lead') {
            let data = response.data;

            alertHtml = `
                <div class="alert alert-warning duplicate-alert">
                    <h5 class="alert-heading">
                        <i class="las la-exclamation-circle"></i> Duplicate Lead Detected!
                    </h5>
                    <hr>
                    <p><strong>Name:</strong> ${data.name}</p>
                    <p><strong>Lead Code:</strong> <span class="badge bg-primary">${data.lead_code}</span></p>
                    <p><strong>Status:</strong> <span class="badge bg-warning">${data.status}</span></p>
                    <hr>
                    <p class="mb-0">
                        <a href="/leads/${data.lead_id}" class="btn btn-sm btn-info" target="_blank">
                            <i class="las la-eye"></i> View Lead
                        </a>
                    </p>
                </div>
            `;
        }

        $('#duplicateAlertContainer').html(alertHtml);
    }

    // Form Submit
    $('#createLeadForm').on('submit', function(e) {
        if (isDuplicateFound) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Cannot Create Lead',
                text: 'A duplicate customer or lead was detected. Please check the warning above.',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }
    });
});
</script>
@endsection

