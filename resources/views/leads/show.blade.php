@extends('layouts.app')

@section('title', 'Lead Details - ' . $lead->name)

@section('extra-css')
<style>
    .lead-detail-container {
        background: #f8f9fb;
        min-height: calc(100vh - 100px);
        padding: 1.5rem 0;
    }

    .lead-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        color: #fff;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }

    .lead-status-badge {
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid #e8ecef;
        margin-bottom: 1.5rem;
        transition: all 0.3s;
    }

    .info-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }

    .info-card-header {
        border-bottom: 2px solid #f1f3f5;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 1.1rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.875rem 0;
        border-bottom: 1px dashed #e8ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #64748b;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .info-value {
        color: #1e293b;
        font-weight: 600;
        text-align: right;
    }

    .amount-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: #fff;
        text-align: center;
    }

    .amount-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .converted-customer-card {
        background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
        border: 2px solid #11998e;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .related-job-card {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
        border: 2px solid #3b82f6;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .view-profile-btn {
        background: #11998e;
        color: #fff;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
    }

    .view-profile-btn:hover {
        background: #0d7a6f;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
    }

    .view-job-btn {
        background: #3b82f6;
        color: #fff;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
    }

    .view-job-btn:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }

    .action-button {
        border-radius: 10px;
        padding: 0.625rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s;
        border: none;
    }

    .action-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .followup-timeline {
        position: relative;
        padding-left: 0;
    }

    .followup-item {
        position: relative;
        padding: 1.25rem;
        background: #f8f9fb;
        border-radius: 12px;
        margin-bottom: 1rem;
        border-left: 4px solid #667eea;
        transition: all 0.3s;
    }

    .followup-item:hover {
        background: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        transform: translateX(4px);
    }

    .followup-item.overdue {
        border-left-color: #dc3545;
        background: linear-gradient(90deg, rgba(220, 53, 69, 0.05), #f8f9fb);
    }

    .followup-item.completed {
        border-left-color: #28a745;
        background: linear-gradient(90deg, rgba(40, 167, 69, 0.05), #f8f9fb);
        opacity: 0.8;
    }

    .followup-item.today {
        border-left-color: #ffc107;
        background: linear-gradient(90deg, rgba(255, 193, 7, 0.05), #f8f9fb);
    }

    .call-log-item, .note-item {
        background: #fff;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 3px solid #667eea;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .note-item {
        background: #fffbea;
        border-left-color: #ffc107;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="lead-detail-container">
    <div class="container-fluid">
        <!-- Lead Header with Action Buttons -->
        <div class="lead-header-card">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <h2 class="mb-0 me-3">{{ $lead->name }}</h2>
                        <span class="lead-status-badge bg-{{ $lead->status === 'approved' ? 'success' : ($lead->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ strtoupper($lead->status) }}
                        </span>
                    </div>
                    <p class="mb-0 opacity-90">
                        <i class="las la-tag me-2"></i>{{ $lead->lead_code }} |
                        <i class="las la-calendar ms-3 me-2"></i>{{ $lead->created_at->format('d M Y') }}
                    </p>
                </div>
                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('leads.index') }}" class="btn btn-light action-button me-2">
                        <i class="las la-arrow-left me-2"></i>Back to Leads
                    </a>

                    @if($lead->status === 'pending')
                        <!-- Edit Button -->
                        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']) || (auth()->user()->role === 'telecallers' && $lead->assigned_to === auth()->id()))
                        <button type="button" class="btn btn-primary action-button me-2" onclick="editLeadFromShow()">
                            <i class="las la-edit me-2"></i>Edit
                        </button>
                        @endif

                        <!-- Approve Button -->
                        @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                        <button type="button" class="btn btn-success action-button me-2" onclick="approveLead()">
                            <i class="las la-check me-2"></i>Approve
                        </button>

                        <!-- Reject Button -->
                        @if(auth()->user()->role === 'super_admin')
                        <button type="button" class="btn btn-danger action-button" onclick="rejectLead()">
                            <i class="las la-times me-2"></i>Reject
                        </button>
                        @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">

                <!-- Converted to Customer Section -->
                @if($lead->status === 'approved' && $lead->customer)
                <div class="converted-customer-card">
                    <h6 class="mb-3" style="color: #11998e;">
                        <i class="las la-user-check me-2"></i>Converted to Customer
                    </h6>
                    <div class="mb-2">
                        <strong>Customer Code:</strong>
                        <span class="badge bg-success ms-2">{{ $lead->customer->customer_code }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Name:</strong> {{ $lead->customer->name }}
                    </div>
                    <a href="{{ route('customers.show', $lead->customer->id) }}" class="btn view-profile-btn">
                        <i class="las la-external-link-alt me-2"></i>View Customer Profile
                    </a>
                </div>
                @endif

                <!-- Related Job Section -->
                @if($lead->status === 'approved' && $lead->jobs && $lead->jobs->count() > 0)
                    @php $job = $lead->jobs->first(); @endphp
                    <div class="related-job-card">
                        <h6 class="mb-3" style="color: #3b82f6;">
                            <i class="las la-briefcase me-2"></i>Related Job
                        </h6>
                        <div class="mb-2">
                            <strong>Job Code:</strong>
                            <span class="badge bg-primary ms-2">{{ $job->job_code }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Title:</strong><br>
                            {{ $job->title }}
                        </div>
                        <div class="mb-2">
                            <strong>Service:</strong>
                            <span class="badge bg-info">{{ $job->service->name ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Amount:</strong>
                            <span class="text-success fw-bold">₹{{ number_format($job->amount, 2) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                        </div>
                        <a href="{{ route('jobs.show', $job->id) }}" class="btn view-job-btn">
                            <i class="las la-external-link-alt me-2"></i>View Job Details
                        </a>
                    </div>
                @endif

                <!-- Contact Information -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-user me-2"></i>Contact Information</h5>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $lead->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $lead->phone }}</span>
                    </div>
                    @if($lead->address)
                    <div class="info-row">
                        <span class="info-label">Address</span>
                        <span class="info-value">{{ $lead->address }}</span>
                    </div>
                    @endif
                </div>

                <!-- Lead Details -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-info-circle me-2"></i>Lead Details</h5>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Service</span>
                        <span class="info-value">{{ $lead->service->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Source</span>
                        <span class="info-value">{{ $lead->source->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Branch</span>
                        <span class="info-value">{{ $lead->branch->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created By</span>
                        <span class="info-value">{{ $lead->createdBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">{{ $lead->assignedTo->name ?? 'Unassigned' }}</span>
                    </div>
                </div>

                <!-- Amount Card -->
                @if($lead->amount)
                <div class="amount-card">
                    <p class="mb-1 opacity-90"><strong>Lead Amount:</strong></p>
                    <div class="amount-value">₹{{ number_format($lead->amount, 2) }}</div>
                    @if($lead->amountUpdatedBy && $lead->amount_updated_at)
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <small class="opacity-75 d-block">
                        <strong>Updated by</strong> {{ $lead->amountUpdatedBy->name }}<br>
                        {{ $lead->amount_updated_at->format('d M Y, h:i A') }}
                    </small>
                    @endif
                </div>
                @else
                <div class="info-card text-center">
                    <i class="las la-money-bill-wave" style="font-size: 3rem; opacity: 0.2;"></i>
                    <p class="text-muted mb-0">Amount not set</p>
                </div>
                @endif


            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
                <!-- Scheduled Followups -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-calendar-check me-2"></i>Scheduled Followups</h5>
                            @if($lead->status === 'pending')
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                                <i class="las la-plus me-1"></i>Add Followup
                            </button>
                            @endif
                        </div>
                    </div>

                    @if($lead->followups && $lead->followups->count() > 0)
                        <div class="followup-timeline">
                            @foreach($lead->followups as $followup)
                            <div class="followup-item {{ $followup->followup_date->isToday() ? 'today' : '' }} {{ $followup->followup_date->isPast() && $followup->status === 'pending' ? 'overdue' : '' }} {{ $followup->status === 'completed' ? 'completed' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-{{ $followup->priority === 'high' ? 'danger' : ($followup->priority === 'medium' ? 'warning' : 'info') }}">
                                            <i class="las la-flag me-1"></i>{{ ucfirst($followup->priority) }} Priority
                                        </span>
                                        @if($followup->followup_date->isToday())
                                            <span class="badge bg-warning ms-2">Today</span>
                                        @endif
                                        @if($followup->followup_date->isPast() && $followup->status === 'pending')
                                            <span class="badge bg-danger ms-2">Overdue</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $followup->status === 'completed' ? 'success' : ($followup->status === 'cancelled' ? 'secondary' : 'primary') }}">
                                        {{ ucfirst($followup->status) }}
                                    </span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong><i class="las la-calendar me-1"></i>Date:</strong>
                                            {{ $followup->followup_date->format('d M Y') }}
                                            @if($followup->followup_time)
                                                <br><strong><i class="las la-clock me-1"></i>Time:</strong>
                                                {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong><i class="las la-user me-1"></i>Assigned To:</strong>
                                            {{ $followup->assignedToUser->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                @if($followup->notes)
                                <div class="mt-2 p-2 bg-white rounded">
                                    <small class="text-muted">{{ $followup->notes }}</small>
                                </div>
                                @endif

                                @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to == auth()->id()))
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-success markFollowupComplete" data-id="{{ $followup->id }}">
                                        <i class="las la-check me-1"></i>Mark Complete
                                    </button>
                                </div>
                                @endif

                                @if($followup->status === 'completed' && $followup->completed_at)
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="las la-check-circle me-1"></i>
                                        Completed on {{ $followup->completed_at->format('d M Y, h:i A') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="las la-calendar-times"></i>
                            <p class="mb-0">No followups scheduled yet</p>
                        </div>
                    @endif
                </div>

                <!-- Call Logs -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-phone me-2"></i>Call Logs</h5>
                            @if($lead->status === 'pending')
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCallModal">
                                <i class="las la-plus me-1"></i>Add Call
                            </button>
                            @endif
                        </div>
                    </div>

                    @if($lead->calls && $lead->calls->count() > 0)
                        @foreach($lead->calls as $call)
                        <div class="call-log-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $call->user->name }}</strong>
                                    <span class="badge bg-{{ $call->outcome === 'interested' ? 'success' : ($call->outcome === 'not_interested' ? 'danger' : 'warning') }} ms-2">
                                        {{ ucfirst(str_replace('_', ' ', $call->outcome)) }}
                                    </span>
                                    <p class="text-muted small mb-1 mt-1">
                                        {{ \Carbon\Carbon::parse($call->call_date)->format('d M Y') }}
                                        @if($call->duration) | Duration: {{ $call->duration }} min @endif
                                    </p>
                                    @if($call->notes)
                                    <p class="mb-0 small">{{ $call->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="las la-phone-slash"></i>
                            <p class="mb-0">No call logs yet</p>
                        </div>
                    @endif
                </div>

                <!-- Notes -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-sticky-note me-2"></i>Notes</h5>
                            @if($lead->status === 'pending')
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="las la-plus me-1"></i>Add Note
                            </button>
                            @endif
                        </div>
                    </div>

                    @if($lead->notes && $lead->notes->count() > 0)
                        @foreach($lead->notes as $note)
                        <div class="note-item">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $note->createdBy->name }}</strong>
                                <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 mt-2">{{ $note->note }}</p>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="las la-comment-slash"></i>
                            <p class="mb-0">No notes yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="las la-edit me-2"></i>Edit Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editLeadForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="{{ $lead->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" value="{{ $lead->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" value="{{ $lead->phone }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Service *</label>
                            <select class="form-select" id="edit_service_id" name="service_id" required>
                                @foreach(\App\Models\Service::where('is_active', true)->get() as $service)
                                    <option value="{{ $service->id }}" {{ $lead->service_id == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lead Source *</label>
                            <select class="form-select" id="edit_lead_source_id" name="lead_source_id" required>
                                @foreach(\App\Models\LeadSource::where('is_active', true)->get() as $source)
                                    <option value="{{ $source->id }}" {{ $lead->lead_source_id == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" class="form-control" id="edit_amount" name="amount" value="{{ $lead->amount }}" step="0.01" min="0">
                        </div>
                        @if(auth()->user()->role === 'super_admin')
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch *</label>
                            <select class="form-select" id="edit_branch_id" name="branch_id" required>
                                @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                    <option value="{{ $branch->id }}" {{ $lead->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select" id="edit_assigned_to" name="assigned_to">
                                <option value="">Unassigned</option>
                                @foreach(\App\Models\User::where('role', 'telecallers')->where('is_active', true)->get() as $user)
                                    <option value="{{ $user->id }}" {{ $lead->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3">{{ $lead->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-2"></i>Update Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Followup Modal -->
<div class="modal fade" id="addFollowupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="las la-calendar-plus me-2"></i>Schedule Followup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFollowupForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Followup Date *</label>
                            <input type="date" class="form-control" name="followup_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Followup Time</label>
                            <input type="time" class="form-control" name="followup_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority *</label>
                        <select class="form-select" name="priority" required>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Schedule Followup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Call Modal -->
<div class="modal fade" id="addCallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="call_date" class="form-label">Call Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="call_date" name="call_date" required>
                            <span class="error-text call_date_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="0" placeholder="e.g., 5">
                            <span class="error-text duration_error text-danger d-block mt-1"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="outcome" class="form-label">Call Outcome <span class="text-danger">*</span></label>
                        <select class="form-select" id="outcome" name="outcome" required>
                            <option value="">Select Outcome</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="callback">Callback Required</option>
                            <option value="no_answer">No Answer</option>
                            <option value="wrong_number">Wrong Number</option>
                        </select>
                        <span class="error-text outcome_error text-danger d-block mt-1"></span>
                    </div>

                    <div class="mb-3">
                        <label for="call_notes" class="form-label">Call Notes</label>
                        <textarea class="form-control" id="call_notes" name="notes" rows="3" placeholder="Enter call details..."></textarea>
                    </div>

                    <!-- Followup Section - Shows when outcome is 'callback' or 'interested' -->
                    <div id="followupSection" style="display: none;">
                        <hr>
                        <h6 class="mb-3"><i class="las la-calendar-plus"></i> Schedule Followup</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="followup_date" class="form-label">Followup Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="followup_date" name="followup_date">
                                <span class="error-text followup_date_error text-danger d-block mt-1"></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="followup_time" class="form-label">Followup Time</label>
                                <input type="time" class="form-control" id="followup_time" name="followup_time">
                                <span class="error-text followup_time_error text-danger d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="followup_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="followup_priority" name="followup_priority">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                            <span class="error-text followup_priority_error text-danger d-block mt-1"></span>
                        </div>

                        <div class="mb-3">
                            <label for="followup_notes" class="form-label">Followup Notes</label>
                            <textarea class="form-control" id="followup_notes" name="followup_notes" rows="2" placeholder="What needs to be discussed in the followup?"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Call & Followup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="note" name="note" rows="4" required placeholder="Enter note..."></textarea>
                        <span class="error-text note_error text-danger d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
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

            // Set current datetime for call_date
            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            $('#call_date').val(now.toISOString().slice(0,16));

            // Set default followup date to tomorrow
            let tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            $('#manual_followup_date').val(tomorrow.toISOString().split('T')[0]);
            $('#manual_followup_date').attr('min', new Date().toISOString().split('T')[0]);

            // Show/Hide followup section based on outcome
            $('#outcome').on('change', function() {
                let outcome = $(this).val();
                if (outcome === 'callback' || outcome === 'interested') {
                    $('#followupSection').slideDown();

                    // Set default followup date to tomorrow
                    let tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    $('#followup_date').val(tomorrow.toISOString().split('T')[0]);

                    // Make followup fields required
                    $('#followup_date').prop('required', true);
                } else {
                    $('#followupSection').slideUp();
                    // Remove required attribute
                    $('#followup_date').prop('required', false);
                    // Clear followup fields
                    $('#followup_date').val('');
                    $('#followup_time').val('');
                    $('#followup_notes').val('');
                }
            });

            // Mark followup as complete
            $(document).on('click', '.markFollowupComplete', function() {
                var followupId = $(this).data('id');
                Swal.fire({
                    title: 'Complete Followup?',
                    text: 'Mark this followup as completed',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Complete',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/lead-followups/' + followupId + '/complete',
                            type: 'POST',
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Completed!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Could not update followup', 'error');
                            }
                        });
                    }
                });
            });

            // Submit Add Call Form with Followup
            $('#addCallForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('.error-text').text('');

                $.ajax({
                    url: '/leads/{{ $lead->id }}/calls',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addCallModal').modal('hide');

                        let message = response.message;
                        if (response.followup_created) {
                            message += '<br><small class="text-success">Followup scheduled successfully!</small>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to log call', 'error');
                        }
                    }
                });
            });

            // Submit Add Note Form
            $('#addNoteForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('.error-text').text('');

                $.ajax({
                    url: '/leads/{{ $lead->id }}/notes',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addNoteModal').modal('hide');
                        $('#note').val('');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to add note', 'error');
                        }
                    }
                });
            });

            // Submit Manual Followup Form
            $('#addFollowupForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('.error-text').text('');

                $.ajax({
                    url: '/leads/{{ $lead->id }}/followups',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addFollowupModal').modal('hide');
                        $('#addFollowupForm')[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to schedule followup', 'error');
                        }
                    }
                });
            });
        });

        // Edit Lead from Show Page
        function editLeadFromShow() {
            $('#edit_name').val('{{ $lead->name }}');
            $('#edit_email').val('{{ $lead->email }}');
            $('#edit_phone').val('{{ $lead->phone }}');
            $('#edit_service_id').val('{{ $lead->service_id }}');
            $('#edit_lead_source_id').val('{{ $lead->lead_source_id }}');
            $('#edit_assigned_to').val('{{ $lead->assigned_to }}');
            $('#edit_amount').val('{{ $lead->amount }}');
            $('#edit_description').val(`{{ $lead->description }}`);

            @if(auth()->user()->role === 'super_admin')
                $('#edit_branch_id').val('{{ $lead->branch_id }}');
            @endif

            $('.error-text').text('');
            $('#editLeadModal').modal('show');
        }

        // Submit Edit Lead Form
        $('#editLeadForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            $('.error-text').text('');

            $.ajax({
                url: '/leads/{{ $lead->id }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editLeadModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('.' + key + '_error').text(value[0]);
                        });
                    } else {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update lead', 'error');
                    }
                }
            });
        });

        // Approve Lead
        window.approveLead = function() {
            Swal.fire({
                title: 'Approve Lead?',
                html: `
                    <div class="text-start mt-3">
                        <label class="form-label fw-semibold">Approval Notes (Optional)</label>
                        <textarea id="approval_notes" class="form-control" rows="3" placeholder="Add any notes about this approval"></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="las la-check me-2"></i>Yes, Approve',
                confirmButtonColor: '#28a745',
                cancelButtonText: 'Cancel',
                width: '500px',
                preConfirm: () => {
                    return {
                        approval_notes: document.getElementById('approval_notes').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("leads.approve", $lead->id) }}',
                        type: 'POST',
                        data: result.value,
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Lead Approved!',
                                html: `
                                    <p class="mb-3">${response.message}</p>
                                    <div class="text-start">
                                        <p class="mb-2"><strong>Customer Code:</strong> <span class="badge bg-success">${response.customer_code}</span></p>
                                        <p class="mb-2"><strong>Job Code:</strong> <span class="badge bg-primary">${response.job_code}</span></p>
                                        <p class="mb-2"><strong>Amount:</strong> <span class="text-success">${response.amount}</span></p>
                                        <p class="mb-0"><strong>Remaining Budget:</strong> <span class="text-success">${response.remaining_budget}</span></p>
                                    </div>
                                `,
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'OK'
                            }).then(() => location.reload());
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON?.message || 'Failed to approve lead';
                            let budgetInfo = xhr.responseJSON?.budget_info || null;

                            let html = `<p>${message}</p>`;

                            if(budgetInfo) {
                                html += `
                                    <div class="mt-3 text-start alert alert-danger">
                                        <p class="mb-1"><strong>Daily Limit:</strong> ${budgetInfo.daily_limit}</p>
                                        <p class="mb-1"><strong>Used Today:</strong> ${budgetInfo.today_total}</p>
                                        <p class="mb-1"><strong>Remaining:</strong> ${budgetInfo.remaining}</p>
                                        <p class="mb-1"><strong>Requested:</strong> ${budgetInfo.requested}</p>
                                        <p class="mb-0 text-danger"><strong>Excess Amount:</strong> ${budgetInfo.excess}</p>
                                    </div>
                                `;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Cannot Approve Lead',
                                html: html,
                                width: '500px'
                            });
                        }
                    });
                }
            });
        };

        // Reject Lead
        window.rejectLead = function() {
            Swal.fire({
                title: 'Reject Lead?',
                html: `
                    <div class="text-start mt-3">
                        <label class="form-label fw-semibold">Rejection Reason *</label>
                        <textarea id="rejection_reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection" required></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="las la-times me-2"></i>Yes, Reject',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel',
                width: '500px',
                preConfirm: () => {
                    const val = document.getElementById('rejection_reason').value;
                    if(!val || val.trim() === '') {
                        Swal.showValidationMessage('Rejection reason is required');
                        return false;
                    }
                    return { rejection_reason: val };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: '{{ route("leads.reject", $lead->id) }}',
                        type: 'POST',
                        data: result.value,
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Lead Rejected',
                                text: response.message,
                                confirmButtonColor: '#dc3545'
                            }).then(() => location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reject lead', 'error');
                        }
                    });
                }
            });
        };

    </script>
@endsection
