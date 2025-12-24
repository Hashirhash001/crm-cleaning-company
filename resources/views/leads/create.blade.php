@extends('layouts.app')

@section('title', 'Create New Lead')

@section('extra-css')
<style>
    .select2-container--bootstrap-5 .select2-selection {
        font-size: 0.8rem !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option {
        font-size: 0.8rem !important;
    }
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
    .balance-amount {
        font-size: 1.2rem;
        font-weight: 600;
        color: #28a745;
    }
    .service-select-box {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
        min-height: 150px;
        max-height: 200px;
        overflow-y: auto;
    }
    .service-checkbox-item {
        padding: 8px 10px;
        margin: 5px 0;
        border-radius: 5px;
        transition: background 0.2s;
        display: flex;
        align-items: center;
    }
    .service-checkbox-item:hover {
        background: #f8f9fa;
    }
    .service-checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
    }
    .service-checkbox-item label {
        cursor: pointer;
        margin: 0;
        font-weight: 500;
    }

    .service-checkbox-item {
        padding: 8px 10px;
        margin: 5px 0;
        border-radius: 5px;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .service-checkbox-item:hover {
        background: #f8f9fa;
    }

    .service-checkbox-wrapper {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .service-checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
    }

    .service-checkbox-item label {
        cursor: pointer;
        margin: 0;
        font-weight: 500;
        flex: 1;
    }

    .service-quantity-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .service-quantity-input {
        width: 80px;
        padding: 4px 8px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        text-align: center;
        font-size: 0.875rem;
    }

    .service-quantity-input:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .quantity-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
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
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="las la-user-plus me-2"></i>Lead Information
                </h4>
            </div>
            <div class="card-body">
                <form id="createLeadForm" method="POST" action="{{ route('leads.store') }}">
                    @csrf

                    {{-- Display ALL Validation Errors at Top --}}
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                        <h5 class="alert-heading">
                            <i class="las la-exclamation-triangle me-2"></i>
                            <strong>Please fix the following {{ $errors->count() }} error(s):</strong>
                        </h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- Display General Error Message --}}
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="las la-exclamation-circle me-2"></i>
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <!-- Client Information Section -->
                    <div class="form-section">
                        <h5><i class="las la-user me-2"></i>Client Information</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label required-field">Name of the Client</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Enter client name"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label required-field">Phone Number 1</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="Enter primary phone number"
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone_alternative" class="form-label">Phone Number 2 (Alternative)</label>
                                <input type="text"
                                       class="form-control @error('phone_alternative') is-invalid @enderror"
                                       id="phone_alternative"
                                       name="phone_alternative"
                                       value="{{ old('phone_alternative') }}"
                                       placeholder="Enter alternative phone number">
                                @error('phone_alternative')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="address" class="form-label">Place/Address</label>
                                <input type="text"
                                       class="form-control @error('address') is-invalid @enderror"
                                       id="address"
                                       name="address"
                                       value="{{ old('address') }}"
                                       placeholder="Enter address">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="district" class="form-label">District</label>
                                <select class="form-select @error('district') is-invalid @enderror"
                                        id="district"
                                        name="district"
                                        data-placeholder="Select District">
                                    <option value="">Select District</option>

                                    <!-- Kerala Districts -->
                                    <optgroup label="Kerala">
                                        <option value="Thiruvananthapuram" {{ old('district') == 'Thiruvananthapuram' ? 'selected' : '' }}>Thiruvananthapuram</option>
                                        <option value="Kollam" {{ old('district') == 'Kollam' ? 'selected' : '' }}>Kollam</option>
                                        <option value="Pathanamthitta" {{ old('district') == 'Pathanamthitta' ? 'selected' : '' }}>Pathanamthitta</option>
                                        <option value="Alappuzha" {{ old('district') == 'Alappuzha' ? 'selected' : '' }}>Alappuzha</option>
                                        <option value="Kottayam" {{ old('district') == 'Kottayam' ? 'selected' : '' }}>Kottayam</option>
                                        <option value="Idukki" {{ old('district') == 'Idukki' ? 'selected' : '' }}>Idukki</option>
                                        <option value="Ernakulam" {{ old('district') == 'Ernakulam' ? 'selected' : '' }}>Ernakulam</option>
                                        <option value="Thrissur" {{ old('district') == 'Thrissur' ? 'selected' : '' }}>Thrissur</option>
                                        <option value="Palakkad" {{ old('district') == 'Palakkad' ? 'selected' : '' }}>Palakkad</option>
                                        <option value="Malappuram" {{ old('district') == 'Malappuram' ? 'selected' : '' }}>Malappuram</option>
                                        <option value="Kozhikode" {{ old('district') == 'Kozhikode' ? 'selected' : '' }}>Kozhikode</option>
                                        <option value="Wayanad" {{ old('district') == 'Wayanad' ? 'selected' : '' }}>Wayanad</option>
                                        <option value="Kannur" {{ old('district') == 'Kannur' ? 'selected' : '' }}>Kannur</option>
                                        <option value="Kasaragod" {{ old('district') == 'Kasaragod' ? 'selected' : '' }}>Kasaragod</option>
                                    </optgroup>

                                    <!-- Tamil Nadu Districts -->
                                    <optgroup label="Tamil Nadu">
                                        <option value="Ariyalur" {{ old('district') == 'Ariyalur' ? 'selected' : '' }}>Ariyalur</option>
                                        <option value="Chengalpattu" {{ old('district') == 'Chengalpattu' ? 'selected' : '' }}>Chengalpattu</option>
                                        <option value="Chennai" {{ old('district') == 'Chennai' ? 'selected' : '' }}>Chennai</option>
                                        <option value="Coimbatore" {{ old('district') == 'Coimbatore' ? 'selected' : '' }}>Coimbatore</option>
                                        <option value="Cuddalore" {{ old('district') == 'Cuddalore' ? 'selected' : '' }}>Cuddalore</option>
                                        <option value="Dharmapuri" {{ old('district') == 'Dharmapuri' ? 'selected' : '' }}>Dharmapuri</option>
                                        <option value="Dindigul" {{ old('district') == 'Dindigul' ? 'selected' : '' }}>Dindigul</option>
                                        <option value="Erode" {{ old('district') == 'Erode' ? 'selected' : '' }}>Erode</option>
                                        <option value="Kallakurichi" {{ old('district') == 'Kallakurichi' ? 'selected' : '' }}>Kallakurichi</option>
                                        <option value="Kanchipuram" {{ old('district') == 'Kanchipuram' ? 'selected' : '' }}>Kanchipuram</option>
                                        <option value="Kanyakumari" {{ old('district') == 'Kanyakumari' ? 'selected' : '' }}>Kanyakumari</option>
                                        <option value="Karur" {{ old('district') == 'Karur' ? 'selected' : '' }}>Karur</option>
                                        <option value="Krishnagiri" {{ old('district') == 'Krishnagiri' ? 'selected' : '' }}>Krishnagiri</option>
                                        <option value="Madurai" {{ old('district') == 'Madurai' ? 'selected' : '' }}>Madurai</option>
                                        <option value="Mayiladuthurai" {{ old('district') == 'Mayiladuthurai' ? 'selected' : '' }}>Mayiladuthurai</option>
                                        <option value="Nagapattinam" {{ old('district') == 'Nagapattinam' ? 'selected' : '' }}>Nagapattinam</option>
                                        <option value="Namakkal" {{ old('district') == 'Namakkal' ? 'selected' : '' }}>Namakkal</option>
                                        <option value="Nilgiris" {{ old('district') == 'Nilgiris' ? 'selected' : '' }}>Nilgiris</option>
                                        <option value="Perambalur" {{ old('district') == 'Perambalur' ? 'selected' : '' }}>Perambalur</option>
                                        <option value="Pudukkottai" {{ old('district') == 'Pudukkottai' ? 'selected' : '' }}>Pudukkottai</option>
                                        <option value="Ramanathapuram" {{ old('district') == 'Ramanathapuram' ? 'selected' : '' }}>Ramanathapuram</option>
                                        <option value="Ranipet" {{ old('district') == 'Ranipet' ? 'selected' : '' }}>Ranipet</option>
                                        <option value="Salem" {{ old('district') == 'Salem' ? 'selected' : '' }}>Salem</option>
                                        <option value="Sivaganga" {{ old('district') == 'Sivaganga' ? 'selected' : '' }}>Sivaganga</option>
                                        <option value="Tenkasi" {{ old('district') == 'Tenkasi' ? 'selected' : '' }}>Tenkasi</option>
                                        <option value="Thanjavur" {{ old('district') == 'Thanjavur' ? 'selected' : '' }}>Thanjavur</option>
                                        <option value="Theni" {{ old('district') == 'Theni' ? 'selected' : '' }}>Theni</option>
                                        <option value="Thoothukudi" {{ old('district') == 'Thoothukudi' ? 'selected' : '' }}>Thoothukudi (Tuticorin)</option>
                                        <option value="Tiruchirappalli" {{ old('district') == 'Tiruchirappalli' ? 'selected' : '' }}>Tiruchirappalli (Trichy)</option>
                                        <option value="Tirunelveli" {{ old('district') == 'Tirunelveli' ? 'selected' : '' }}>Tirunelveli</option>
                                        <option value="Tirupathur" {{ old('district') == 'Tirupathur' ? 'selected' : '' }}>Tirupathur</option>
                                        <option value="Tiruppur" {{ old('district') == 'Tiruppur' ? 'selected' : '' }}>Tiruppur</option>
                                        <option value="Tiruvallur" {{ old('district') == 'Tiruvallur' ? 'selected' : '' }}>Tiruvallur</option>
                                        <option value="Tiruvannamalai" {{ old('district') == 'Tiruvannamalai' ? 'selected' : '' }}>Tiruvannamalai</option>
                                        <option value="Tiruvarur" {{ old('district') == 'Tiruvarur' ? 'selected' : '' }}>Tiruvarur</option>
                                        <option value="Vellore" {{ old('district') == 'Vellore' ? 'selected' : '' }}>Vellore</option>
                                        <option value="Viluppuram" {{ old('district') == 'Viluppuram' ? 'selected' : '' }}>Viluppuram</option>
                                        <option value="Virudhunagar" {{ old('district') == 'Virudhunagar' ? 'selected' : '' }}>Virudhunagar</option>
                                    </optgroup>
                                </select>
                                @error('district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <!-- Property & Service Details Section -->
                    <div class="form-section">
                        <h5><i class="las la-building me-2"></i>Property & Service Details</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="property_type" class="form-label">Property Type</label>
                                <select class="form-select @error('property_type') is-invalid @enderror"
                                        id="property_type"
                                        name="property_type">
                                    <option value="">Select Property Type</option>
                                    <option value="commercial" {{ old('property_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                    <option value="residential" {{ old('property_type') == 'residential' ? 'selected' : '' }}>Residential</option>
                                </select>
                                @error('property_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sqft" class="form-label">SQFT Details</label>
                                <select class="form-select @error('sqft') is-invalid @enderror" id="sqft" name="sqft">
                                    <option value="">Select SQFT</option>
                                    <option value="0-700" {{ old('sqft') == '0-700' ? 'selected' : '' }}>0-700</option>
                                    <option value="700-2000" {{ old('sqft') == '700-2000' ? 'selected' : '' }}>700-2000</option>
                                    <option value="2100-3000" {{ old('sqft') == '2100-3000' ? 'selected' : '' }}>2100-3000</option>
                                    <option value="3100-4000" {{ old('sqft') == '3100-4000' ? 'selected' : '' }}>3100-4000</option>
                                    <option value="4100-5000" {{ old('sqft') == '4100-5000' ? 'selected' : '' }}>4100-5000</option>
                                    <option value="5100-6000" {{ old('sqft') == '5100-6000' ? 'selected' : '' }}>5100-6000</option>
                                    <option value="custom" {{ old('sqft') == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                @error('sqft')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Custom SQFT Input (hidden by default) -->
                                <input type="text"
                                       class="form-control mt-2 @error('sqft_custom') is-invalid @enderror"
                                       id="sqft_custom"
                                       name="sqft_custom"
                                       value="{{ old('sqft_custom') }}"
                                       placeholder="Enter custom SQFT"
                                       style="display: none;">
                                @error('sqft_custom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="service_type" class="form-label required-field">Type of Service</label>
                                <select class="form-select @error('service_type') is-invalid @enderror"
                                        id="service_type"
                                        name="service_type"
                                        required>
                                    <option value="">Select Service Type</option>
                                    <option value="cleaning" {{ old('service_type') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                    <option value="pest_control" {{ old('service_type') == 'pest_control' ? 'selected' : '' }}>Pest Control</option>
                                    <option value="other" {{ old('service_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lead_source_id" class="form-label required-field">Source of the Lead</label>
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
                                <label class="form-label required-field">Select Services (Multiple Selection Allowed)</label>
                                <div class="service-select-box @error('service_ids') is-invalid @enderror" id="servicesContainer">
                                    <p class="text-muted text-center my-5">
                                        <i class="las la-arrow-up" style="font-size: 2rem;"></i><br>
                                        Please select a service type first
                                    </p>
                                </div>
                                <small class="text-muted">Check services and specify quantity for each</small>
                                @error('service_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="description" class="form-label">Description of Customer Requirement</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="4"
                                          placeholder="Enter detailed customer requirements">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Payment Section -->
                    <div class="form-section">
                        <h5><i class="las la-rupee-sign me-2"></i>Price Details</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Total Service Cost (₹)</label>
                                <input type="number"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       id="amount"
                                       name="amount"
                                       value="{{ old('amount') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="Enter total cost">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="advance_paid_amount" class="form-label">Advance Paid (₹)</label>
                                <input type="number"
                                       class="form-control @error('advance_paid_amount') is-invalid @enderror"
                                       id="advance_paid_amount"
                                       name="advance_paid_amount"
                                       value="{{ old('advance_paid_amount', 0) }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="Enter paid amount">
                                @error('advance_paid_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="payment_mode" class="form-label">Mode of Payment</label>
                                <select class="form-select @error('payment_mode') is-invalid @enderror"
                                        id="payment_mode"
                                        name="payment_mode">
                                    <option value="">Select Payment Mode</option>
                                    <option value="gpay" {{ old('payment_mode') == 'gpay' ? 'selected' : '' }}>Gpay</option>
                                    <option value="phonepe" {{ old('payment_mode') == 'phonepe' ? 'selected' : '' }}>Phonepe</option>
                                    <option value="paytm" {{ old('payment_mode') == 'paytm' ? 'selected' : '' }}>Paytm</option>
                                    <option value="amazonpay" {{ old('payment_mode') == 'amazonpay' ? 'selected' : '' }}>Amazonpay</option>
                                    <option value="cash" {{ old('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="upi" {{ old('payment_mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                                    <option value="card" {{ old('payment_mode') == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="bank_transfer" {{ old('payment_mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="neft" {{ old('payment_mode') == 'neft' ? 'selected' : '' }}>NEFT</option>
                                </select>
                                @error('payment_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Balance Amount (₹)</label>
                                <div class="form-control bg-light balance-amount" id="balance_amount">
                                    ₹ 0.00
                                </div>
                                <small class="text-muted">Auto-calculated: Total Cost - Advance Paid</small>
                            </div>
                        </div>
                    </div>

                    <!-- Lead Status & Assignment Section -->
                    <div class="form-section">
                        <h5><i class="las la-tasks me-2"></i>Status of Lead & Assignment</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label required-field">Lead Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="site_visit" {{ old('status') == 'site_visit' ? 'selected' : '' }}>Site Visit</option>
                                    <option value="not_accepting_tc" {{ old('status') == 'not_accepting_tc' ? 'selected' : '' }}>Not Accepting T&C</option>
                                    <option value="they_will_confirm" {{ old('status') == 'they_will_confirm' ? 'selected' : '' }}>They Will Confirm</option>
                                    <option value="date_issue" {{ old('status') == 'date_issue' ? 'selected' : '' }}>Date Issue</option>
                                    <option value="rate_issue" {{ old('status') == 'rate_issue' ? 'selected' : '' }}>Rate Issue</option>
                                    <option value="service_not_provided" {{ old('status') == 'service_not_provided' ? 'selected' : '' }}>Service We Do Not Provide</option>
                                    <option value="just_enquiry" {{ old('status') == 'just_enquiry' ? 'selected' : '' }}>Just Enquiry</option>
                                    <option value="immediate_service" {{ old('status') == 'immediate_service' ? 'selected' : '' }}>Immediate Service</option>
                                    <option value="no_response" {{ old('status') == 'no_response' ? 'selected' : '' }}>No Response</option>
                                    <option value="location_not_available" {{ old('status') == 'location_not_available' ? 'selected' : '' }}>Location Not Available</option>
                                    <option value="night_work_demanded" {{ old('status') == 'night_work_demanded' ? 'selected' : '' }}>Night Work Demanded</option>
                                    <option value="customisation" {{ old('status') == 'customisation' ? 'selected' : '' }}>Customisation</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(auth()->user()->role === 'super_admin')
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
                            @else
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>

                        <div class="row mb-3">
                            @php $user = auth()->user(); @endphp

                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label">Assign To Telecaller</label>

                                @if($user->role === 'telecallers')
                                    {{-- Telecaller: always self, no dropdown --}}
                                    <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                                    <input type="hidden" name="assigned_to" value="{{ $user->id }}">
                                @else
                                    {{-- Admin / Lead manager: can choose telecaller --}}
                                    <select class="form-select @error('assigned_to') is-invalid @enderror"
                                            id="assigned_to"
                                            name="assigned_to">
                                        <option value="">Select Telecaller (Optional)</option>
                                        @foreach($telecallers as $telecaller)
                                            <option value="{{ $telecaller->id }}"
                                                {{ old('assigned_to', $lead->assigned_to ?? null) == $telecaller->id ? 'selected' : '' }}>
                                                {{ $telecaller->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-end mt-4">
                        <a href="{{ route('leads.index') }}" class="btn btn-secondary me-2">
                            <i class="las la-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                            <i class="las la-save me-1"></i> Create Lead
                        </button>
                        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                            <button type="button" class="btn btn-success" id="createAndConvertBtn">
                                <i class="las la-briefcase me-1"></i> Create & Convert to Work Order
                            </button>
                        @endif
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize Select2 on district dropdown
        $('#district').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select district',
            allowClear: true,
            width: '100%'
        });

        const allTelecallers = @json($telecallers);

        // ============ NEW: SQFT Custom Field Toggle ============
        $('#sqft').on('change', function() {
            let sqftValue = $(this).val();
            if (sqftValue === 'custom') {
                $('#sqft_custom').show().focus();
            } else {
                $('#sqft_custom').hide().val('');
            }
        });

        // Trigger on page load if custom is selected
        @if(old('sqft') === 'custom')
            $('#sqft_custom').show();
        @endif

        // Calculate balance amount
        function calculateBalance() {
            let totalAmount = parseFloat($('#amount').val()) || 0;
            let advancePaid = parseFloat($('#advance_paid_amount').val()) || 0;
            let balance = totalAmount - advancePaid;

            // Update display
            $('#balanceamount').text(balance.toFixed(2));

            // Add validation styling
            if (advancePaid > totalAmount && totalAmount > 0) {
                $('#advance_paid_amount').addClass('is-invalid');
                $('#balanceamount').removeClass('text-success').addClass('text-danger');

                // Show error message
                if (!$('#advanceerror').length) {
                    $('#advance_paid_amount').after('<div id="advanceerror" class="invalid-feedback d-block">Advance paid cannot be greater than total service cost</div>');
                }
            } else {
                $('#advance_paid_amount').removeClass('is-invalid');
                $('#balanceamount').removeClass('text-danger').addClass('text-success');
                $('#advanceerror').remove();
            }
        }

        $('#amount, #advance_paid_amount').on('input', calculateBalance);

        // Branch change handler - filter telecallers
        $('#branch_id').on('change', function() {
            let selectedBranchId = $(this).val();
            let assignedToSelect = $('#assigned_to');

            assignedToSelect.find('option:not(:first)').remove();

            if (selectedBranchId) {
                let filteredTelecallers = allTelecallers.filter(function(telecaller) {
                    return telecaller.branch_id == selectedBranchId;
                });

                filteredTelecallers.forEach(function(telecaller) {
                    assignedToSelect.append(`<option value="${telecaller.id}">${telecaller.name}</option>`);
                });

                if (filteredTelecallers.length === 0) {
                    assignedToSelect.append(`<option value="" disabled>No telecallers in this branch</option>`);
                }
            }
        });

        $('#service_type').on('change', function() {
            let serviceType = $(this).val();
            let container = $('#servicesContainer');

            if (!serviceType) {
                container.html('<p class="text-muted text-center my-5"><i class="las la-arrow-up" style="font-size: 2rem;"></i><br> Please select a service type first</p>');
                return;
            }

            container.html('<p class="text-center my-3"><i class="las la-spinner la-spin"></i> Loading services...</p>');

            $.ajax({
                url: "{{ route('leads.servicesByType') }}",
                type: 'GET',
                data: { service_type: serviceType },
                success: function(services) {
                    if (services.length === 0) {
                        container.html('<p class="text-muted text-center my-3">No services available for this type</p>');
                        return;
                    }

                    let html = '';
                    services.forEach(function(service) {
                        html += `
                            <div class="service-checkbox-item">
                                <div class="service-checkbox-wrapper">
                                    <input type="checkbox"
                                        name="service_ids[]"
                                        value="${service.id}"
                                        id="service_${service.id}"
                                        class="service-checkbox"
                                        data-service-id="${service.id}">
                                    <label for="service_${service.id}">${service.name}</label>
                                </div>
                                <div class="service-quantity-wrapper">
                                    <span class="quantity-label">Qty:</span>
                                    <input type="number"
                                        name="service_quantities[${service.id}]"
                                        id="quantity_${service.id}"
                                        class="service-quantity-input"
                                        min="1"
                                        value="1"
                                        disabled>
                                </div>
                            </div>
                        `;
                    });

                    container.html(html);

                    // Enable/disable quantity input based on checkbox
                    $('.service-checkbox').on('change', function() {
                        let serviceId = $(this).data('service-id');
                        let quantityInput = $(`#quantity_${serviceId}`);

                        if ($(this).is(':checked')) {
                            quantityInput.prop('disabled', false);
                            if (!quantityInput.val() || quantityInput.val() < 1) {
                                quantityInput.val(1);
                            }
                        } else {
                            quantityInput.prop('disabled', true).val(1);
                        }
                    });

                    // Restore old values if any
                    @if(old('service_ids'))
                        let oldServiceIds = @json(old('service_ids'));
                        let oldQuantities = @json(old('service_quantities', []));

                        oldServiceIds.forEach(function(id) {
                            let checkbox = $(`#service_${id}`);
                            checkbox.prop('checked', true);

                            let quantityInput = $(`#quantity_${id}`);
                            quantityInput.prop('disabled', false);

                            if (oldQuantities[id]) {
                                quantityInput.val(oldQuantities[id]);
                            }
                        });
                    @endif
                },
                error: function() {
                    container.html('<p class="text-danger text-center my-3">Error loading services. Please try again.</p>');
                }
            });
        });

        // Trigger service type change if old value exists
        @if(old('service_type'))
            $('#service_type').trigger('change');
        @endif

        // Form validation helper function
        function validateForm() {
            let isValid = true;
            let errors = [];

            // Clear previous validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Required fields
            if (!$('#name').val().trim()) {
                errors.push('Client name is required');
                $('#name').addClass('is-invalid');
                isValid = false;
            }

            if (!$('#phone').val().trim()) {
                errors.push('Phone number is required');
                $('#phone').addClass('is-invalid');
                isValid = false;
            }

            if (!$('#service_type').val()) {
                errors.push('Service type is required');
                $('#service_type').addClass('is-invalid');
                isValid = false;
            }

            if (!$('#lead_source_id').val()) {
                errors.push('Lead source is required');
                $('#lead_source_id').addClass('is-invalid');
                isValid = false;
            }

            // Check if at least one service is selected
            if ($('.service-checkbox:checked').length === 0) {
                errors.push('Please select at least one service');
                $('#servicesContainer').addClass('is-invalid');
                isValid = false;
            }

            // Validate SQFT custom field
            if ($('#sqft').val() === 'custom' && !$('#sqft_custom').val().trim()) {
                errors.push('Please enter custom SQFT value');
                $('#sqft_custom').addClass('is-invalid');
                isValid = false;
            }

            if (!$('#status').val()) {
                errors.push('Lead status is required');
                $('#status').addClass('is-invalid');
                isValid = false;
            }

            @if(auth()->user()->role === 'superadmin')
            if (!$('#branch_id').val()) {
                errors.push('Branch is required');
                $('#branch_id').addClass('is-invalid');
                isValid = false;
            }
            @endif

            // Validate advance amount
            let totalAmount = parseFloat($('#amount').val()) || 0;
            let advancePaid = parseFloat($('#advance_paid_amount').val()) || 0;

            if (advancePaid > totalAmount && totalAmount > 0) {
                errors.push('Advance paid cannot exceed total service cost');
                $('#advance_paid_amount').addClass('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                // Show error summary
                let errorHtml = '<ul class="text-start mb-0">';
                errors.forEach(e => errorHtml += `<li>${e}</li>`);
                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorHtml,
                    confirmButtonColor: '#ef4444'
                });

                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $('.is-invalid:first').offset().top - 100
                }, 500);
            }

            return isValid;
        }

        // Clear validation errors on input
        $('input, select, textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        });

        // Regular form submission - Create Lead button
        $('#createLeadForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default submission

            // Validate
            if (!validateForm()) {
                return false;
            }

            // Show loading
            let submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Creating...');

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('leads.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Lead created successfully!',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.href = "{{ route('leads.index') }}";
                        });
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="las la-save me-1"></i> Create Lead');

                    let errors = [];
                    let errorHtml = '';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        // Validation errors
                        errorHtml = '<div class="text-start"><ul class="mb-0">';

                        Object.keys(xhr.responseJSON.errors).forEach(function(field) {
                            let errorMessages = xhr.responseJSON.errors[field];

                            errorMessages.forEach(function(error) {
                                errors.push(error);
                                errorHtml += `<li>${error}</li>`;
                            });

                            // Show inline errors
                            let input = $(`[name="${field}"], [name="${field}[]"]`).first();
                            if (input.length) {
                                input.addClass('is-invalid');

                                // Remove existing error message
                                input.siblings('.invalid-feedback').remove();

                                // Add new error message
                                if (input.parent().hasClass('service-select-box')) {
                                    input.parent().after(`<div class="invalid-feedback d-block">${errorMessages[0]}</div>`);
                                } else {
                                    input.after(`<div class="invalid-feedback d-block">${errorMessages[0]}</div>`);
                                }
                            }
                        });

                        errorHtml += '</ul></div>';
                    } else if (xhr.responseJSON?.message) {
                        errorHtml = `<p class="mb-0">${xhr.responseJSON.message}</p>`;
                    } else {
                        errorHtml = '<p class="mb-0">An error occurred. Please try again.</p>';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error Creating Lead',
                        html: errorHtml,
                        confirmButtonColor: '#ef4444',
                        width: '600px'
                    });

                    // Scroll to first error
                    if (errors.length > 0) {
                        $('html, body').animate({
                            scrollTop: $('.is-invalid:first').offset().top - 100
                        }, 500);
                    }
                }
            });
        });

        // Create & Convert to Job button
        $('#createAndConvertBtn').on('click', function() {
            // Validate form first
            if (!validateForm()) {
                return;
            }

            // Check if amount is set
            let amount = parseFloat($('#amount').val());
            let advancePaid = parseFloat($('#advance_paid_amount').val()) || 0;

            if (!amount || amount <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Amount Required',
                    text: 'Please enter the total service cost before converting to Work Order.',
                    confirmButtonColor: '#ef4444'
                });
                $('#amount').focus().addClass('is-invalid');
                return;
            }

            // Confirm conversion
            Swal.fire({
                title: 'Create Lead & Convert to Work Order?',
                html: `
                    <div class="text-start">
                        <p class="mb-3">This will:</p>
                        <ul class="mb-0">
                            <li>Create the lead</li>
                            <li>Create a customer record</li>
                            <li>Create a job/work order</li>
                        </ul>
                        <div class="alert alert-info mt-3 mb-0">
                            <strong>Amount:</strong> ₹${amount.toFixed(2)}
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="las la-check me-2"></i>Yes, Create & Convert',
                confirmButtonColor: '#10b981',
                cancelButtonText: 'Cancel',
                width: '550px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Creating lead and converting to work order',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit form via AJAX
                    let formData = new FormData($('#createLeadForm')[0]);

                    $.ajax({
                        url: "{{ route('leads.store') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            // Lead created successfully, now convert to job
                            let leadId = response.lead_id || response.id;

                            if (!leadId) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Lead created but failed to get lead ID'
                                });
                                return;
                            }

                            // Call approve endpoint to convert to job
                            $.ajax({
                                url: `/leads/${leadId}/approve`,
                                type: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    approval_notes: 'Auto-converted during lead creation'
                                },
                                success: function(approveResponse) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        html: `
                                            <div class="text-start">
                                                <p class="mb-3">Lead created and converted successfully!</p>
                                                <div class="alert alert-success mb-3">
                                                    <p class="mb-2"><strong>Lead Code:</strong> <span class="badge bg-primary">${response.lead_code}</span></p>
                                                    <p class="mb-2"><strong>Customer Code:</strong> <span class="badge bg-success">${approveResponse.customer_code}</span></p>
                                                    <p class="mb-0"><strong>Amount:</strong> <span class="text-success fw-bold">₹${approveResponse.amount}</span></p>
                                                </div>
                                                <div class="d-flex gap-2 mt-3">
                                                    <a href="/leads/${leadId}" class="btn btn-primary btn-sm flex-fill">
                                                        <i class="las la-file-alt me-1"></i> View Lead
                                                    </a>
                                                    <a href="/customers/${approveResponse.customer_id}" class="btn btn-success btn-sm flex-fill">
                                                        <i class="las la-user me-1"></i> View Customer
                                                    </a>
                                                </div>
                                            </div>
                                        `,
                                        confirmButtonText: 'Go to Leads',
                                        confirmButtonColor: '#10b981',
                                        showCancelButton: true,
                                        cancelButtonText: 'Create Another Lead',
                                        width: '600px'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = "{{ route('leads.index') }}";
                                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                                            window.location.href = "{{ route('leads.create') }}";
                                        }
                                    });
                                },
                                error: function(xhr) {
                                    let message = xhr.responseJSON?.message || 'Failed to convert to work order';
                                    let budgetInfo = xhr.responseJSON?.budgetinfo || null;
                                    let html = `<p class="mb-3">${message}</p>`;

                                    if (budgetInfo) {
                                        html += `
                                            <div class="alert alert-danger text-start mb-0">
                                                <p class="mb-1"><strong>Daily Limit:</strong> ₹${budgetInfo.dailylimit}</p>
                                                <p class="mb-1"><strong>Used Today:</strong> ₹${budgetInfo.todaytotal}</p>
                                                <p class="mb-1"><strong>Remaining:</strong> ₹${budgetInfo.remaining}</p>
                                                <p class="mb-0 text-danger"><strong>Excess:</strong> ₹${budgetInfo.excess}</p>
                                            </div>
                                        `;
                                    }

                                    html += `
                                        <div class="mt-3">
                                            <p class="text-muted mb-0">The lead was created successfully but couldn't be converted to a work order. You can convert it manually from the leads page.</p>
                                        </div>
                                    `;

                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Lead Created but Conversion Failed',
                                        html: html,
                                        confirmButtonText: 'Go to Leads',
                                        confirmButtonColor: '#3b82f6',
                                        width: '550px'
                                    }).then(() => {
                                        window.location.href = "{{ route('leads.index') }}";
                                    });
                                }
                            });
                        },
                        error: function(xhr) {
                            let message = 'Failed to create lead';
                            let errors = [];

                            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                                Object.values(xhr.responseJSON.errors).forEach(function(errorArray) {
                                    errors = errors.concat(errorArray);
                                });
                                message = '<ul class="text-start mb-0">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
                            } else if (xhr.responseJSON?.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error Creating Lead',
                                html: message,
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'OK'
                            });

                            // Show inline validation errors
                            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                                Object.keys(xhr.responseJSON.errors).forEach(function(field) {
                                    let input = $(`[name="${field}"], [name="${field}[]"]`).first();
                                    if (input.length) {
                                        input.addClass('is-invalid');
                                        input.after(`<div class="invalid-feedback d-block">${xhr.responseJSON.errors[field][0]}</div>`);
                                    }
                                });

                                // Scroll to first error
                                $('html, body').animate({
                                    scrollTop: $('.is-invalid:first').offset().top - 100
                                }, 500);
                            }
                        }
                    });
                }
            });
        });

        // Auto-scroll to error alert on page load
        @if($errors->any())
            $('html, body').animate({
                scrollTop: $('#errorAlert').offset().top - 100
            }, 500);
        @endif
    });
</script>

@endsection
