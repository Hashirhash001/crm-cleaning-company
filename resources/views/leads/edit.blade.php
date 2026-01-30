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

                            <div class="col-md-6">
                                <label for="service_type" class="form-label">Type of Service</label>
                                <select class="form-select @error('service_type') is-invalid @enderror"
                                        id="service_type"
                                        name="service_type">
                                    <option value="">Select Service Type</option>
                                    @foreach($serviceTypes as $type)
                                        <option value="{{ $type }}"
                                            {{ old('service_type', $lead->service_type ?? '') == $type ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Select Services (Multiple Selection from Any Type)</label>

                                <!-- Search Box for Services -->
                                <div class="mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="las la-search"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control"
                                            id="serviceSearchInput"
                                            placeholder="Search services by name...">
                                        <button class="btn btn-outline-secondary" type="button" id="clearServiceSearch">
                                            <i class="las la-times"></i> Clear
                                        </button>
                                    </div>
                                </div>

                                <div class="service-select-box @error('service_ids') is-invalid @enderror" id="servicesContainer">
                                    <p class="text-muted text-center my-5">
                                        <i class="las la-spinner la-spin" style="font-size: 2rem;"></i><br>
                                        Loading services...
                                    </p>
                                </div>
                                <small class="text-muted">
                                    <i class="las la-info-circle"></i>
                                    You can select services from different types. Use the "Service Type" filter or search box above.
                                </small>
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
                                    <option value="qrcode" {{ old('payment_mode') == 'qrcode' ? 'selected' : '' }}>QR Code</option>
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
    const allServices = @json($services);
    const currentAssignedTo = {{ $lead->assigned_to ?? 'null' }};
    const leadServiceIds = @json($lead->services->pluck('id'));
    const leadServiceQuantities = @json($lead->services->mapWithKeys(function($s) { return [$s->id => $s->pivot->quantity]; }));

    let currentFilterType = null;
    let currentSearchTerm = '';

    // ============ PERSISTENT STATE FOR SELECTED SERVICES ============
    let selectedServices = {}; // { serviceId: { name: 'Service Name', quantity: 1, type: 'cleaning' } }

    // Initialize selected services from existing lead data
    leadServiceIds.forEach(function(serviceId) {
        let service = allServices.find(s => s.id == serviceId);
        if (service) {
            selectedServices[serviceId] = {
                name: service.name,
                quantity: leadServiceQuantities[serviceId] || 1,
                type: service.service_type
            };
        }
    });

    console.log('Initial selected services:', selectedServices);

    // ============ SQFT Custom Field Toggle ============
    $('#sqft').on('change', function() {
        let sqftValue = $(this).val();
        if (sqftValue === 'custom') {
            $('#sqft_custom').show().focus();
        } else {
            $('#sqft_custom').hide();
        }
    });

    // ============ Calculate balance amount ============
    function calculateBalance() {
        let totalAmount = parseFloat($('#amount').val()) || 0;
        let advancePaid = parseFloat($('#advance_paid_amount').val()) || 0;
        let balance = totalAmount - advancePaid;

        // Try different possible IDs for balance display
        let balanceElement = $('#balance_amount').length ? $('#balance_amount') :
                            $('#balanceamount').length ? $('#balanceamount') :
                            $('.balance-amount');

        if (balanceElement.length) {
            balanceElement.text('₹ ' + balance.toFixed(2));
        }

        if (advancePaid > totalAmount && totalAmount > 0) {
            $('#advance_paid_amount').addClass('is-invalid');
            if (balanceElement.length) {
                balanceElement.removeClass('text-success').addClass('text-danger');
            }

            if (!$('#advanceerror').length) {
                $('#advance_paid_amount').after('<div id="advanceerror" class="invalid-feedback d-block">Advance paid cannot be greater than total service cost</div>');
            }
        } else {
            $('#advance_paid_amount').removeClass('is-invalid');
            if (balanceElement.length) {
                balanceElement.removeClass('text-danger').addClass('text-success');
            }
            $('#advanceerror').remove();
        }
    }

    // Bind to amount fields
    $('#amount, #advance_paid_amount').on('input change keyup', function() {
        calculateBalance();
    });

    // Branch change handler
    $('#branch_id').on('change', function() {
        let selectedBranchId = $(this).val();
        let assignedToSelect = $('#assigned_to');

        assignedToSelect.find('option:not(:first)').remove();

        if (selectedBranchId) {
            let filteredTelecallers = allTelecallers.filter(function(telecaller) {
                return telecaller.branch_id == selectedBranchId;
            });

            filteredTelecallers.forEach(function(telecaller) {
                let isSelected = telecaller.id == currentAssignedTo;
                assignedToSelect.append(`<option value="${telecaller.id}" ${isSelected ? 'selected' : ''}>${telecaller.name}</option>`);
            });

            if (filteredTelecallers.length === 0) {
                assignedToSelect.append('<option value="" disabled>No telecallers in this branch</option>');
            }
        }
    });

    // ============ Save only VISIBLE selections ============
    function saveCurrentSelections() {
        console.log('Saving selections... Current DOM checkboxes:', $('.service-checkbox').length);

        // Only update services that are currently visible in DOM
        let visibleServiceIds = [];

        $('.service-checkbox').each(function() {
            let serviceId = $(this).data('service-id');
            visibleServiceIds.push(serviceId);

            let isChecked = $(this).is(':checked');

            if (isChecked) {
                let quantity = parseInt($(`#quantity_${serviceId}`).val()) || 1;
                let serviceName = $(this).data('service-name');
                let serviceType = $(this).data('service-type');

                selectedServices[serviceId] = {
                    name: serviceName,
                    quantity: quantity,
                    type: serviceType
                };

                console.log('Saved service:', serviceId, selectedServices[serviceId]);
            } else {
                // Only remove if this service is visible AND unchecked
                if (selectedServices.hasOwnProperty(serviceId)) {
                    delete selectedServices[serviceId];
                    console.log('Removed service (unchecked):', serviceId);
                }
            }
        });

        console.log('Visible service IDs:', visibleServiceIds);
        console.log('Total selected services:', Object.keys(selectedServices).length, selectedServices);
    }

    // ============ FIXED: Inject all selected services as hidden inputs ============
    function injectSelectedServicesIntoForm() {
        // Remove any previously injected hidden inputs
        $('#UpdateLeadForm').find('.injected-service-input').remove();

        // IMPORTANT: Disable all visible service checkboxes so they don't get submitted
        $('.service-checkbox').prop('disabled', true);
        $('.service-quantity-input').prop('disabled', true);

        console.log('Injecting selected services into form:', selectedServices);

        // Inject hidden inputs for all selected services
        Object.entries(selectedServices).forEach(([serviceId, data]) => {
            // Add service ID checkbox (checked)
            $('#UpdateLeadForm').append(`
                <input type="checkbox"
                    name="service_ids[]"
                    value="${serviceId}"
                    checked
                    class="injected-service-input"
                    style="display:none;">
            `);

            // Add quantity input
            $('#UpdateLeadForm').append(`
                <input type="number"
                    name="service_quantities[${serviceId}]"
                    value="${data.quantity}"
                    class="injected-service-input"
                    style="display:none;">
            `);

            console.log(`Injected service ${serviceId}: ${data.name} (qty: ${data.quantity})`);
        });

        console.log('Total injected inputs:', $('#UpdateLeadForm').find('.injected-service-input').length);
    }

    // ============ Load services with persistent selection ============
    function loadAllServices(filterByType = null, searchTerm = '') {
        console.log('Loading services... Filter:', filterByType, 'Search:', searchTerm);
        console.log('Current selections before load:', selectedServices);

        let container = $('#servicesContainer');

        // Apply type filter
        let servicesToShow = filterByType
            ? allServices.filter(s => s.service_type === filterByType)
            : allServices;

        // Apply search filter
        if (searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            servicesToShow = servicesToShow.filter(s =>
                s.name.toLowerCase().includes(searchTerm)
            );
        }

        if (servicesToShow.length === 0) {
            let message = searchTerm
                ? `No services found matching "<strong>${searchTerm}</strong>"`
                : 'No services available';

            container.html(`
                <p class="text-muted text-center my-5">
                    <i class="las la-search" style="font-size: 2rem;"></i><br>
                    ${message}
                </p>
            `);

            updateSelectedServicesDisplay();
            return;
        }

        // Group services by type
        let grouped = {};
        servicesToShow.forEach(function(service) {
            let type = service.service_type || 'other';
            if (!grouped[type]) {
                grouped[type] = [];
            }
            grouped[type].push(service);
        });

        let html = '';

        // Display grouped services with headers
        Object.keys(grouped).sort().forEach(function(type) {
            let typeName = type.replace(/_/g, ' ')
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ') + ' Services';

            let typeColor = type === 'cleaning' ? '#3b82f6' :
                        type === 'pest_control' ? '#10b981' :
                        '#6b7280';

            html += `
                <div style="margin-top: ${html ? '15px' : '0'}; padding: 8px 10px; background: ${typeColor}15; border-left: 3px solid ${typeColor}; border-radius: 4px;">
                    <strong style="color: ${typeColor}; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        ${typeName} <span style="font-size: 0.8rem; font-weight: 400;">(${grouped[type].length})</span>
                    </strong>
                </div>
            `;

            // Add services for this type
            grouped[type].forEach(function(service) {
                // Check persistent state for selection
                let isChecked = selectedServices.hasOwnProperty(service.id);
                let quantity = isChecked ? selectedServices[service.id].quantity : 1;

                console.log(`Service ${service.id} (${service.name}): checked=${isChecked}, qty=${quantity}`);

                html += `
                    <div class="service-checkbox-item">
                        <div class="service-checkbox-wrapper">
                            <input type="checkbox"
                                   name="service_ids[]"
                                   value="${service.id}"
                                   id="service_${service.id}"
                                   class="service-checkbox"
                                   data-service-id="${service.id}"
                                   data-service-name="${service.name}"
                                   data-service-type="${service.service_type}"
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
                                   ${!isChecked ? 'disabled' : ''}>
                        </div>
                    </div>
                `;
            });
        });

        // Show search result count if searching
        if (searchTerm) {
            let totalCount = servicesToShow.length;
            let prependHtml = `
                <div class="alert alert-info py-2 px-3 mb-2" style="font-size: 0.85rem;">
                    <i class="las la-info-circle"></i>
                    Found <strong>${totalCount}</strong> service${totalCount !== 1 ? 's' : ''} matching "<strong>${searchTerm}</strong>"
                </div>
            `;
            html = prependHtml + html;
        }

        container.html(html);

        console.log('DOM rendered. Checkboxes found:', $('.service-checkbox').length);
        console.log('Checked checkboxes:', $('.service-checkbox:checked').length);

        updateSelectedServicesDisplay();
        bindServiceEvents();
    }

    // ============ BIND EVENT HANDLERS TO SERVICE CHECKBOXES ============
    function bindServiceEvents() {
        $('.service-checkbox').off('change').on('change', function() {
            let serviceId = $(this).data('service-id');
            let serviceName = $(this).data('service-name');
            let serviceType = $(this).data('service-type');
            let quantityInput = $(`#quantity_${serviceId}`);

            if ($(this).is(':checked')) {
                quantityInput.prop('disabled', false);
                if (!quantityInput.val() || quantityInput.val() < 1) {
                    quantityInput.val(1);
                }

                selectedServices[serviceId] = {
                    name: serviceName,
                    quantity: parseInt(quantityInput.val()),
                    type: serviceType
                };

                console.log('Checkbox checked - added to selection:', serviceId, selectedServices[serviceId]);
            } else {
                quantityInput.prop('disabled', true);
                delete selectedServices[serviceId];
                console.log('Checkbox unchecked - removed from selection:', serviceId);
            }

            updateSelectedServicesDisplay();
        });

        $('.service-quantity-input').off('change').on('change', function() {
            let serviceId = $(this).attr('id').replace('quantity_', '');
            if (selectedServices.hasOwnProperty(serviceId)) {
                selectedServices[serviceId].quantity = parseInt($(this).val()) || 1;
                console.log('Quantity updated:', serviceId, selectedServices[serviceId].quantity);
                updateSelectedServicesDisplay();
            }
        });
    }

    // ============ DISPLAY SELECTED SERVICES WITH NAMES ============
    function updateSelectedServicesDisplay() {
        let selectedCount = Object.keys(selectedServices).length;
        let $existingBadge = $('#selectedServicesBadge');

        console.log('Updating display. Selected count:', selectedCount);

        if (selectedCount > 0) {
            let byType = {};

            Object.entries(selectedServices).forEach(([id, data]) => {
                let type = data.type || 'other';
                if (!byType[type]) {
                    byType[type] = [];
                }
                byType[type].push(`${data.name} (x${data.quantity})`);
            });

            let servicesList = [];
            Object.keys(byType).sort().forEach(type => {
                let icon = type === 'cleaning' ? '🧹' :
                        type === 'pest_control' ? '🐛' :
                        '📦';

                let color = type === 'cleaning' ? '#3b82f6' :
                        type === 'pest_control' ? '#10b981' :
                        '#6b7280';

                servicesList.push(`<span style="color: ${color};">${icon} ${byType[type].join(', ')}</span>`);
            });

            let displayList = servicesList.join(' | ');

            let badgeHtml = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <i class="las la-check-circle"></i>
                        <strong>${selectedCount}</strong> service${selectedCount !== 1 ? 's' : ''} selected
                        <br>
                        <small style="font-size: 0.75rem;">${displayList}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearAllSelections">
                        <i class="las la-times"></i> Clear All
                    </button>
                </div>
            `;

            if ($existingBadge.length === 0) {
                $('#servicesContainer').before(`
                    <div id="selectedServicesBadge" class="alert alert-success py-2 px-3 mb-2" style="font-size: 0.85rem;">
                        ${badgeHtml}
                    </div>
                `);
            } else {
                $existingBadge.html(badgeHtml);
            }

            $('#clearAllSelections').off('click').on('click', function () {
                Swal.fire({
                    title: 'Clear all selected services?',
                    text: 'This will remove all selected services from the list.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, clear',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        selectedServices = {};
                        console.log('All selections cleared');
                        loadAllServices($('#servicetype').val());

                        // Swal.fire({
                        //     title: 'Cleared!',
                        //     text: 'All selected services were cleared.',
                        //     icon: 'success',
                        //     timer: 1200,
                        //     showConfirmButton: false
                        // });
                    }
                });
            });
        } else {
            $existingBadge.remove();
        }
    }

    // ============ Service Type Filter ============
    $('#service_type').on('change', function() {
        currentFilterType = $(this).val();
        console.log('Filter changed to:', currentFilterType);
        saveCurrentSelections();
        loadAllServices(currentFilterType, currentSearchTerm);
    });

    // ============ Service Search Handler ============
    $('#serviceSearchInput').on('input', function() {
        currentSearchTerm = $(this).val().trim();
        console.log('Search changed to:', currentSearchTerm);
        saveCurrentSelections();
        loadAllServices(currentFilterType, currentSearchTerm);
    });

    $('#clearServiceSearch').on('click', function() {
        $('#serviceSearchInput').val('');
        currentSearchTerm = '';
        saveCurrentSelections();
        loadAllServices(currentFilterType, currentSearchTerm);
        $('#serviceSearchInput').focus();
    });

    $('#serviceSearchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            currentSearchTerm = $(this).val().trim();
            saveCurrentSelections();
            loadAllServices(currentFilterType, currentSearchTerm);
        }
    });

    // Load all services on page load
    loadAllServices();

    // Calculate balance after page loads
    setTimeout(function() {
        calculateBalance();
        console.log('Initial balance calculated');
    }, 100);

    // Form validation
    function validateForm() {
        let isValid = true;
        let errors = [];

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

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

        if (!$('#lead_source_id').val()) {
            errors.push('Lead source is required');
            $('#lead_source_id').addClass('is-invalid');
            isValid = false;
        }

        if ($('#status').is(':visible') && !$('#status').val()) {
            errors.push('Lead status is required');
            $('#status').addClass('is-invalid');
            isValid = false;
        }

        if ($('#sqft').val() === 'custom' && !$('#sqft_custom').val().trim()) {
            errors.push('Please enter custom SQFT value');
            $('#sqft_custom').addClass('is-invalid');
            isValid = false;
        }

        @if(auth()->user()->role === 'super_admin')
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
                html: `<ul class="text-start">${errors.map(e => `<li>${e}</li>`).join('')}</ul>`
            });
        }

        return isValid;
    }

    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // ============ UPDATED: Form submission with proper cleanup ============
    $('#UpdateLeadForm').on('submit', function(e) {
        e.preventDefault();

        // Save current visible selections
        saveCurrentSelections();

        // Inject ALL selected services and disable visible ones
        injectSelectedServicesIntoForm();

        console.log('Form submitting with selections:', selectedServices);

        if (!validateForm()) {
            // Re-enable checkboxes if validation fails
            $('.service-checkbox').prop('disabled', false);
            $('.service-checkbox:checked').each(function() {
                let serviceId = $(this).data('service-id');
                $(`#quantity_${serviceId}`).prop('disabled', false);
            });
            return false;
        }

        let submitBtn = $(this).find('button[type="submit"]');
        let originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Updating...');

        let formData = new FormData(this);

        // Log what's being sent
        console.log('FormData contents:');
        let serviceIdsCount = 0;
        let serviceQuantitiesCount = 0;
        for (let pair of formData.entries()) {
            if (pair[0] === 'service_ids[]') {
                serviceIdsCount++;
                console.log(`service_ids[]: ${pair[1]}`);
            } else if (pair[0].startsWith('service_quantities[')) {
                serviceQuantitiesCount++;
                console.log(`${pair[0]}: ${pair[1]}`);
            }
        }
        console.log(`Total service_ids: ${serviceIdsCount}, Total quantities: ${serviceQuantitiesCount}`);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);

                // Re-enable checkboxes
                $('.service-checkbox').prop('disabled', false);
                $('.service-checkbox:checked').each(function() {
                    let serviceId = $(this).data('service-id');
                    $(`#quantity_${serviceId}`).prop('disabled', false);
                });

                // Remove injected inputs
                $('.injected-service-input').remove();

                if (response.success) {
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

                    $('#UpdateLeadForm').prepend(successHtml);

                    $('html, body').animate({
                        scrollTop: $('.alert-success').offset().top - 100
                    }, 500);

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
                        } else {
                            // Optionally reload the page to show updated data
                            location.reload();
                        }
                    });

                    $('.alert-danger').remove();
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);

                // Re-enable checkboxes
                $('.service-checkbox').prop('disabled', false);
                $('.service-checkbox:checked').each(function() {
                    let serviceId = $(this).data('service-id');
                    $(`#quantity_${serviceId}`).prop('disabled', false);
                });

                // Remove injected inputs
                $('.injected-service-input').remove();

                let errors = [];
                let errorHtml = '';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
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

                    $('.alert-success, .alert-danger').remove();
                    $('#UpdateLeadForm').prepend(errorHtml);

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
                }

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

    @if($errors->any())
    $(window).on('load', function() {
        $('html, body').animate({
            scrollTop: $('#errorAlert').offset().top - 100
        }, 500);
    });
    @endif

    @if(auth()->user()->role === 'super_admin')
    $('#branch_id').trigger('change');
    @endif
});
</script>

@endsection

