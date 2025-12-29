@extends('layouts.app')

@section('title', 'Create Customer')

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
    .error-text {
        display: none;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .is-invalid {
        border-color: #dc3545;
    }
    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Create New Customer</h4>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
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
                    <i class="las la-user-plus me-2"></i>Customer Information
                </h4>
            </div>
            <div class="card-body">
                <form id="customerForm">
                    @csrf

                    <!-- Branch Selection (Super Admin Only) -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="form-section">
                        <h5><i class="las la-building me-2"></i>Branch Assignment</h5>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="branch_id" class="form-label required-field">Branch</label>
                                <select class="form-select" id="branch_id" name="branch_id" required>
                                    <option value="">Select Branch</option>
                                    @foreach(\App\Models\Branch::where('is_active', true)->orderBy('name')->get() as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error-text text-danger branch_id_error"></span>
                                <small class="text-muted">
                                    <i class="las la-info-circle"></i>
                                    Customer will be unique to this branch. Same phone/email can exist in different branches.
                                </small>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Hidden input for non-super-admin users -->
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">

                    <!-- Display current branch info -->
                    <div class="alert alert-info mb-3">
                        <i class="las la-building me-2"></i>
                        <strong>Branch:</strong> {{ auth()->user()->branch->name ?? 'N/A' }}
                        <small class="d-block mt-1 text-muted">
                            Customer will be created in your branch
                        </small>
                    </div>
                    @endif

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h5><i class="las la-user me-2"></i>Personal Information</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label required-field">Name</label>
                                <input type="text"
                                       class="form-control"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Enter customer name">
                                <span class="error-text text-danger name_error"></span>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Enter email address (optional)">
                                <span class="error-text text-danger email_error"></span>
                                <small class="text-muted">Email must be unique within this branch</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label required-field">Phone</label>
                                <input type="text"
                                       class="form-control"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="Enter phone number">
                                <span class="error-text text-danger phone_error"></span>
                                <small class="text-muted">Phone must be unique within this branch</small>
                            </div>

                            <div class="col-md-6">
                                <label for="priority" class="form-label required-field">Priority</label>
                                <select class="form-select"
                                        id="priority"
                                        name="priority">
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium (Default)</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                <span class="error-text text-danger priority_error"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control"
                                          id="address"
                                          name="address"
                                          rows="3"
                                          placeholder="Enter customer address">{{ old('address') }}</textarea>
                                <span class="error-text text-danger address_error"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control"
                                          id="notes"
                                          name="notes"
                                          rows="3"
                                          placeholder="Enter any notes about the customer">{{ old('notes') }}</textarea>
                                <span class="error-text text-danger notes_error"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-end mt-4">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary me-2">
                            <i class="las la-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="las la-save me-1"></i> Create Customer
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

        // Remove error styling when user types
        $('input, select, textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
            let fieldName = $(this).attr('name');
            $('.' + fieldName + '_error').text('').hide();
        });

        // AJAX Form Submission
        $('#customerForm').on('submit', function(e) {
            e.preventDefault();

            // Clear all previous errors
            $('.error-text').text('').hide();
            $('input, select, textarea').removeClass('is-invalid');

            let formData = new FormData(this);
            let submitBtn = $('#submitBtn');

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Creating...');

            $.ajax({
                url: '{{ route("customers.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Customer Created!',
                            html: `<p>${response.message}</p>
                                ${response.customer.customer_code ? `<p><strong>Customer Code:</strong> <span class="badge bg-primary">${response.customer.customer_code}</span></p>` : ''}
                                ${response.customer.name ? `<p><strong>Name:</strong> ${response.customer.name}</p>` : ''}`,
                            timer: 3000,
                            showConfirmButton: true,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="las la-save me-1"></i> Create Customer');

                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(field, messages) {
                            // Add is-invalid class to input
                            $('#' + field).addClass('is-invalid');

                            // Show error message
                            $('.' + field + '_error').text(messages[0]).show();
                        });

                        // Scroll to first error
                        let firstError = $('.is-invalid').first();
                        if (firstError.length) {
                            $('html, body').animate({
                                scrollTop: firstError.offset().top - 100
                            }, 500);
                        }
                    } else if (xhr.status === 403) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unauthorized',
                            text: xhr.responseJSON.message || 'You do not have permission to perform this action.',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'An error occurred while creating the customer.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            });
        });
    });
</script>
@endsection
