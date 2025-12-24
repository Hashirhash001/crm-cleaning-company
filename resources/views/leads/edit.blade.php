@extends('layouts.app')

@section('title', 'Edit Lead')

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

    /* Approved lead status styling */
    .bg-success.text-white {
        background-color: #28a745 !important;
    }

    .input-group .input-group-text.bg-success {
        border-color: #28a745;
    }

    .alert-warning.border-warning {
        border-left: 4px solid #ffc107 !important;
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
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="las la-edit me-2"></i>Edit Lead - {{ $lead->lead_code }}
                </h4>
            </div>
            <div class="card-body">
                <form id="UpdateLeadForm" method="POST" action="{{ route('leads.update', $lead) }}">
                    @csrf
                    @method('PUT')

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
                                       value="{{ old('name', $lead->name) }}"
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
                                       value="{{ old('email', $lead->email) }}"
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
                                       value="{{ old('phone', $lead->phone) }}"
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
                                       value="{{ old('phone_alternative', $lead->phone_alternative) }}"
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
                                       value="{{ old('address', $lead->address) }}"
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
                                    <option value="commercial" {{ old('property_type', $lead->property_type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                    <option value="residential" {{ old('property_type', $lead->property_type) == 'residential' ? 'selected' : '' }}>Residential</option>
                                </select>
                                @error('property_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sqft" class="form-label">SQFT Details</label>
                                <select class="form-select @error('sqft') is-invalid @enderror" id="sqft" name="sqft">
                                    <option value="">Select SQFT</option>
                                    @php
                                        $sqftValue = old('sqft', $lead->sqft);
                                        $isCustom = !in_array($sqftValue, ['', '0-700', '700-2000', '2100-3000', '3100-4000', '4100-5000', '5100-6000']);
                                        if ($isCustom && $sqftValue) {
                                            $customValue = $sqftValue;
                                            $sqftValue = 'custom';
                                        } else {
                                            $customValue = old('sqft_custom', '');
                                        }
                                    @endphp
                                    <option value="0-700" {{ $sqftValue == '0-700' ? 'selected' : '' }}>0-700</option>
                                    <option value="700-2000" {{ $sqftValue == '700-2000' ? 'selected' : '' }}>700-2000</option>
                                    <option value="2100-3000" {{ $sqftValue == '2100-3000' ? 'selected' : '' }}>2100-3000</option>
                                    <option value="3100-4000" {{ $sqftValue == '3100-4000' ? 'selected' : '' }}>3100-4000</option>
                                    <option value="4100-5000" {{ $sqftValue == '4100-5000' ? 'selected' : '' }}>4100-5000</option>
                                    <option value="5100-6000" {{ $sqftValue == '5100-6000' ? 'selected' : '' }}>5100-6000</option>
                                    <option value="custom" {{ $sqftValue == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                @error('sqft')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Custom SQFT Input -->
                                <input type="text"
                                       class="form-control mt-2 @error('sqft_custom') is-invalid @enderror"
                                       id="sqft_custom"
                                       name="sqft_custom"
                                       value="{{ $customValue }}"
                                       placeholder="Enter custom SQFT"
                                       style="{{ $sqftValue == 'custom' ? '' : 'display: none;' }}">
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
                                    <option value="cleaning" {{ old('service_type', $lead->service_type) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                    <option value="pest_control" {{ old('service_type', $lead->service_type) == 'pest_control' ? 'selected' : '' }}>Pest Control</option>
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
                                <label class="form-label required-field">Select Services (Multiple Selection Allowed)</label>
                                <div class="service-select-box @error('service_ids') is-invalid @enderror" id="servicesContainer">
                                    <p class="text-muted text-center my-5">
                                        <i class="las la-spinner la-spin" style="font-size: 2rem;"></i><br>
                                        Loading services...
                                    </p>
                                </div>
                                <small class="text-muted">Check services and quantity for each</small>
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
                                          placeholder="Enter detailed customer requirements">{{ old('description', $lead->description) }}</textarea>
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
                                       value="{{ old('amount', $lead->amount) }}"
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
                                       value="{{ old('advance_paid_amount', $lead->advance_paid_amount) }}"
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
                                    <option value="cash" {{ old('payment_mode', $lead->payment_mode) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="upi" {{ old('payment_mode', $lead->payment_mode) == 'upi' ? 'selected' : '' }}>UPI</option>
                                    <option value="card" {{ old('payment_mode', $lead->payment_mode) == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="bank_transfer" {{ old('payment_mode', $lead->payment_mode) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="neft" {{ old('payment_mode', $lead->payment_mode) == 'neft' ? 'selected' : '' }}>NEFT</option>
                                </select>
                                @error('payment_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Balance Amount (₹)</label>
                                <div class="form-control bg-light balance-amount" id="balance_amount">
                                    ₹ {{ number_format($lead->balance_amount ?? 0, 2) }}
                                </div>
                                <small class="text-muted">Auto-calculated: Total Cost - Advance Paid</small>
                            </div>
                        </div>
                    </div>

                    <!-- Lead Status Assignment Section -->
                    <div class="form-section">
                        <h5><i class="las la-tasks me-2"></i>Status of Lead & Assignment</h5>

                        <!-- Show alert if lead is approved -->
                        @if($lead->status === 'approved')
                            <div class="alert alert-warning border-warning mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="las la-info-circle me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>This lead is APPROVED.</strong><br>
                                        <small>
                                            - Status is locked and cannot be changed<br>
                                            - Name, phone, and email changes will sync to the customer<br>
                                            - Amount or service changes will update related jobs and require admin approval
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            @php
                                $user = auth()->user();
                            @endphp

                            <!-- Status Field -->
                            @if($lead->status === 'approved')
                                <!-- For approved leads: Keep status locked -->
                                <input type="hidden" name="status" value="approved">
                                <div class="col-md-6">
                                    <label class="form-label">Lead Status</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control bg-success text-white fw-bold"
                                            value="✓ Approved"
                                            disabled>
                                        <span class="input-group-text bg-success text-white">
                                            <i class="las la-lock"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Status is locked. Lead has been converted to customer.
                                    </small>
                                </div>
                            @else
                                <!-- For non-approved leads: Show status dropdown -->
                                <div class="col-md-6">
                                    <label for="status" class="form-label required-field">Lead Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ old('status', $lead->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="site_visit" {{ old('status', $lead->status) == 'site_visit' ? 'selected' : '' }}>Site Visit</option>
                                        <option value="not_accepting_tc" {{ old('status', $lead->status) == 'not_accepting_tc' ? 'selected' : '' }}>Not Accepting T&C</option>
                                        <option value="they_will_confirm" {{ old('status', $lead->status) == 'they_will_confirm' ? 'selected' : '' }}>They Will Confirm</option>
                                        <option value="date_issue" {{ old('status', $lead->status) == 'date_issue' ? 'selected' : '' }}>Date Issue</option>
                                        <option value="rate_issue" {{ old('status', $lead->status) == 'rate_issue' ? 'selected' : '' }}>Rate Issue</option>
                                        <option value="service_not_provided" {{ old('status', $lead->status) == 'service_not_provided' ? 'selected' : '' }}>Service Not Provided</option>
                                        <option value="just_enquiry" {{ old('status', $lead->status) == 'just_enquiry' ? 'selected' : '' }}>Just Enquiry</option>
                                        <option value="immediate_service" {{ old('status', $lead->status) == 'immediate_service' ? 'selected' : '' }}>Immediate Service</option>
                                        <option value="no_response" {{ old('status', $lead->status) == 'no_response' ? 'selected' : '' }}>No Response</option>
                                        <option value="location_not_available" {{ old('status', $lead->status) == 'location_not_available' ? 'selected' : '' }}>Location Not Available</option>
                                        <option value="night_work_demanded" {{ old('status', $lead->status) == 'night_work_demanded' ? 'selected' : '' }}>Night Work Demanded</option>
                                        <option value="customisation" {{ old('status', $lead->status) == 'customisation' ? 'selected' : '' }}>Customisation</option>

                                        @if($user->role === 'super_admin')
                                            <option value="approved" {{ old('status', $lead->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ old('status', $lead->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        @endif
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <!-- Branch Field -->
                            @if(auth()->user()->role === 'super_admin')
                                <div class="col-md-6">
                                    <label for="branch_id" class="form-label required-field">Branch</label>
                                    <select class="form-select @error('branch_id') is-invalid @enderror"
                                            id="branch_id"
                                            name="branch_id"
                                            required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                    {{ old('branch_id', $lead->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="branch_id" value="{{ $lead->branch_id }}">
                            @endif
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label">Assign To Telecaller</label>
                                @if($user->role === 'telecallers')
                                    <!-- Telecaller always self, no dropdown -->
                                    <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                                    <input type="hidden" name="assigned_to" value="{{ $user->id }}">
                                @else
                                    <!-- Admin/Lead manager can choose telecaller -->
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

    // Initialize Select2
    $('#district').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select district',
        allowClear: true,
        width: '100%'
    });

    const allTelecallers = @json($telecallers);
    const currentAssignedTo = {{ $lead->assigned_to ?? 'null' }};
    const leadServiceIds = @json($lead->services->pluck('id'));
    const leadServiceQuantities = @json($lead->services->mapWithKeys(function($s) { return [$s->id => $s->pivot->quantity]; }));

    // ============ SQFT Custom Field Toggle ============
    $('#sqft').on('change', function() {
        let sqftValue = $(this).val();
        if (sqftValue === 'custom') {
            $('#sqft_custom').show().focus();
        } else {
            $('#sqft_custom').hide();
        }
    });

    // Calculate balance amount
    function calculateBalance() {
        let totalAmount = parseFloat($('#amount').val()) || 0;
        let advancePaid = parseFloat($('#advance_paid_amount').val()) || 0;
        let balance = totalAmount - advancePaid;

        $('#balanceamount').text(balance.toFixed(2));

        if (advancePaid > totalAmount && totalAmount > 0) {
            $('#advance_paid_amount').addClass('is-invalid');
            $('#balanceamount').removeClass('text-success').addClass('text-danger');

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

    // Branch change handler
    $('#branch_id').on('change', function() {
        let selectedBranchId = $(this).val();
        let assignedToSelect = $('#assigned_to');
        let currentValue = assignedToSelect.val();

        assignedToSelect.find('option:not(:first)').remove();

        if (selectedBranchId) {
            let filteredTelecallers = allTelecallers.filter(function(telecaller) {
                return telecaller.branch_id == selectedBranchId;
            });

            filteredTelecallers.forEach(function(telecaller) {
                let isSelected = (telecaller.id == currentAssignedTo);
                assignedToSelect.append(`<option value="${telecaller.id}" ${isSelected ? 'selected' : ''}>${telecaller.name}</option>`);
            });

            if (filteredTelecallers.length === 0) {
                assignedToSelect.append(`<option value="" disabled>No telecallers in this branch</option>`);
            }
        }
    });

    // ============ UPDATED: Load services with quantities ============
    function loadServices(serviceType) {
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
                    let isChecked = leadServiceIds.includes(service.id);
                    let quantity = leadServiceQuantities[service.id] || 1;

                    html += `
                        <div class="service-checkbox-item">
                            <div class="service-checkbox-wrapper">
                                <input type="checkbox"
                                       name="service_ids[]"
                                       value="${service.id}"
                                       id="service_${service.id}"
                                       class="service-checkbox"
                                       data-service-id="${service.id}"
                                       ${isChecked ? 'checked' : ''}>
                                <label for="service_${service.id}">${service.name}</label>
                            </div>
                            <div class="service-quantity-wrapper">
                                <span class="quantity-label">Qty:</span>
                                <input type="number"
                                       name="service_quantities[${service.id}]"
                                       id="quantity_${service.id}"
                                       class="service-quantity-input"
                                       min="1"
                                       value="${quantity}"
                                       ${isChecked ? '' : 'disabled'}>
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
                        quantityInput.prop('disabled', true);
                    }
                });
            },
            error: function() {
                container.html('<p class="text-danger text-center my-3">Error loading services. Please try again.</p>');
            }
        });
    }

    $('#service_type').on('change', function() {
        loadServices($(this).val());
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        let errors = [];

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

        if ($('#status').is(':visible') && !$('#status').val()) {
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

        let totalAmount = parseFloat($('#amount').val()) || 0;
        let advancePaid = parseFloat($('#advance_paid_amount').val()) || 0;

        if (advancePaid > totalAmount && totalAmount > 0) {
            errors.push('Advance paid cannot exceed total service cost');
            $('#advance_paid_amount').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '<ul class="text-start">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>'
            });
        }

        return isValid;
    }

    // Clear validation errors on input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Form submission - AJAX to stay on page
    $('#UpdateLeadForm').on('submit', function(e) {
        e.preventDefault(); // Always prevent default

        // Validate
        if (!validateForm()) {
            return false;
        }

        // Show loading
        let submitBtn = $(this).find('button[type="submit"]');
        let originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Updating...');

        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);

                if (response.success) {
                    // Build success message
                    let successHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading">
                                <i class="las la-check-circle me-2"></i>
                                <strong>Lead Updated Successfully!</strong>
                            </h5>
                            <p class="mb-2"><strong>Lead Code:</strong> <span class="badge bg-primary">${response.lead_code}</span></p>
                            <p class="mb-0">${response.message}</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                    // Show success message at top of form
                    $('#UpdateLeadForm').prepend(successHtml);

                    // Scroll to success message
                    $('html, body').animate({
                        scrollTop: $('.alert-success').offset().top - 100
                    }, 500);

                    // Show SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `
                            <div class="text-start">
                                <p class="mb-2"><strong>Lead Code:</strong> <span class="badge bg-primary">${response.lead_code}</span></p>
                                <p class="mb-0">${response.message}</p>
                            </div>
                        `,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#10b981',
                        showCancelButton: true,
                        cancelButtonText: 'Go to Leads',
                        cancelButtonColor: '#3b82f6'
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            window.location.href = "{{ route('leads.index') }}";
                        }
                    });

                    // Remove any previous alerts
                    $('.alert-danger').remove();
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);

                let errors = [];
                let errorHtml = '';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    // Validation errors
                    errorHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                            <h5 class="alert-heading">
                                <i class="las la-exclamation-triangle me-2"></i>
                                <strong>Please fix the following errors:</strong>
                            </h5>
                            <ul class="mb-0">
                    `;

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
                            input.siblings('.invalid-feedback').remove();

                            if (input.parent().hasClass('service-select-box')) {
                                input.parent().after(`<div class="invalid-feedback d-block">${errorMessages[0]}</div>`);
                            } else {
                                input.after(`<div class="invalid-feedback d-block">${errorMessages[0]}</div>`);
                            }
                        }
                    });

                    errorHtml += `
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                    // Show error at top of form
                    $('.alert-success, .alert-danger').remove();
                    $('#UpdateLeadForm').prepend(errorHtml);

                    // Scroll to error
                    $('html, body').animate({
                        scrollTop: $('#errorAlert').offset().top - 100
                    }, 500);

                } else if (xhr.responseJSON?.message) {
                    errorHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="las la-exclamation-circle me-2"></i>
                            <strong>Error:</strong> ${xhr.responseJSON.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                    $('.alert-success, .alert-danger').remove();
                    $('#UpdateLeadForm').prepend(errorHtml);

                    $('html, body').animate({
                        scrollTop: $('.alert-danger').offset().top - 100
                    }, 500);
                } else {
                    errorHtml = '<p class="mb-0">An error occurred. Please try again.</p>';
                }

                // Also show SweetAlert for errors
                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: '<ul class="text-start mb-0">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        });
    });

    // Auto-scroll to error alert on page load
    @if($errors->any())
        $(window).on('load', function() {
            $('html, body').animate({
                scrollTop: $('#errorAlert').offset().top - 100
            }, 500);
        });
    @endif

    // Load services on page load
    @if($lead->service_type)
        loadServices('{{ $lead->service_type }}');
    @endif

    // Trigger branch change if super admin
    @if(auth()->user()->role === 'superadmin')
        $('#branch_id').trigger('change');
    @endif
});
</script>
@endsection

