<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\LeadCall;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadApproval;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::with(['branch', 'services', 'source', 'createdBy', 'assignedTo']);

        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->select('id', 'name', 'branch_id')
            ->orderBy('name')
            ->get();

        // Role-based access
        if ($user->role === 'super_admin') {
            // Super admin sees all leads
        } elseif ($user->role === 'lead_manager') {
            $query->where('created_by', $user->id);
        } elseif ($user->role === 'telecallers') {
            $query->where('assigned_to', $user->id);
        } else {
            abort(403, 'Unauthorized');
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Open leads (anything not approved)
        if ($request->input('mode') === 'open') {
            $query->where('status', '!=', 'approved');
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('lead_source_id') && $request->lead_source_id !== '0') {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        // Filter by service - check the many-to-many relationship
        if ($request->filled('service_id')) {
            $serviceId = $request->service_id;
            $query->whereHas('services', function($subQuery) use ($serviceId) {
                $subQuery->where('services.id', $serviceId);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        // ============================================
        // APPLY SORTING
        // ============================================
        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Use the scope for sorting
        $query->sort($sortColumn, $sortDirection);

        // Paginate results
        $leads = $query->paginate(15);

        // Calculate pending count based on role
        if ($user->role === 'super_admin') {
            $pendingcount = Lead::where('status', 'pending')->count();
        } elseif ($user->role === 'lead_manager') {
            $pendingcount = Lead::where('status', 'pending')
                ->where('created_by', $user->id)
                ->count();
        } else {
            $pendingcount = 0;
        }

        $branches = Branch::where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $lead_sources = LeadSource::where('is_active', true)->get();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
                'current_sort' => [
                    'column' => $sortColumn,
                    'direction' => $sortDirection,
                ],
            ]);
        }

        return view('leads.index', compact('leads', 'pendingcount', 'branches', 'services', 'lead_sources', 'telecallers'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized to create leads');
        }

        $services = Service::where('is_active', true)->get();
        $lead_sources = LeadSource::where('is_active', true)->get();

        if ($user->role === 'super_admin') {
            $telecallers = User::where('role', 'telecallers')
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('is_active', true)->get();
        } else {
            $telecallers = User::where('role', 'telecallers')
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        return view('leads.create', compact('services', 'lead_sources', 'telecallers', 'branches'));
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return back()->with('error', 'Unauthorized');
            }

            // Validate
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:leads,email',
                'phone' => 'required|string|max:20|unique:leads,phone',
                'phone_alternative' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'district' => 'nullable|string|max:100',
                'property_type' => 'nullable|in:commercial,residential',
                'sqft' => 'nullable|string|max:100',
                'service_type' => 'required|in:cleaning,pest_control,other',
                'service_ids' => 'required|array|min:1',
                'service_ids.*' => 'exists:services,id',
                'lead_source_id' => 'required|exists:lead_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'amount' => 'nullable|numeric|min:0',
                'advance_paid_amount' => 'nullable|numeric|min:0|lte:amount',
                'payment_mode' => 'nullable|in:cash,upi,card,bank_transfer,neft,gpay,phonepe,paytm,amazonpay',
                'branch_id' => $user->role === 'super_admin' ? 'required|exists:branches,id' : 'nullable',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation',
            ]);

            $branchId = $user->role === 'super_admin' ? $validated['branch_id'] : $user->branch_id;

            if (!$branchId) {
                return back()->withInput()->with('error', 'Error: Branch not found for your account.');
            }

            $assignedTo = $validated['assigned_to'] ?? null;
            if ($user->role === 'telecallers' && !$assignedTo) {
                $assignedTo = $user->id;
            }

            $lead = Lead::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'phone_alternative' => $validated['phone_alternative'] ?? null,
                'address' => $validated['address'] ?? null,
                'district' => $validated['district'] ?? null,
                'service_type' => $validated['service_type'],
                'property_type' => $validated['property_type'] ?? null,
                'sqft' => $validated['sqft'] ?? null,
                'lead_source_id' => $validated['lead_source_id'],
                'assigned_to' => $assignedTo,
                'amount' => $validated['amount'] ?? null,
                'advance_paid_amount' => $validated['advance_paid_amount'] ?? 0,
                'payment_mode' => $validated['payment_mode'] ?? null,
                'amount_updated_at' => $validated['amount'] ? now() : null,
                'amount_updated_by' => $validated['amount'] ? $user->id : null,
                'branch_id' => $branchId,
                'description' => $validated['description'] ?? null,
                'created_by' => $user->id,
                'status' => $validated['status'],
            ]);

            // Attach selected services
            $lead->services()->attach($validated['service_ids']);

            Log::info('Lead created successfully', [
                'lead_id' => $lead->id,
                'lead_code' => $lead->lead_code,
                'services' => $validated['service_ids'],
                'created_by' => $user->id,
            ]);

            // Check if this is an AJAX request (from "Create & Convert" button)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead created successfully',
                    'lead_id' => $lead->id,
                    'lead_code' => $lead->lead_code,
                    'name' => $lead->name,
                ]);
            }

            return redirect()->route('leads.index')
                ->with('success', json_encode([
                    'title' => 'Lead Created Successfully!',
                    'lead_id' => $lead->id,
                    'message' => "Lead {$lead->lead_code} has been created.",
                    'leadcode' => $lead->lead_code,
                    'name' => $lead->name
                ]));

        } catch (\Exception $e) {
            Log::error('Lead creation error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating lead: ' . $e->getMessage());
        }
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
            abort(403, 'You can only edit your own leads');
        }

        if ($user->role === 'telecallers' && $lead->assigned_to !== $user->id) {
            abort(403, 'You can only edit leads assigned to you');
        }

        $services = Service::where('is_active', true)->get();
        $lead_sources = LeadSource::where('is_active', true)->get();

        if ($user->role === 'super_admin') {
            $telecallers = User::where('role', 'telecallers')
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('is_active', true)->get();
        } else {
            $telecallers = User::where('role', 'telecallers')
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        return view('leads.edit', compact('lead', 'services', 'lead_sources', 'telecallers', 'branches'));
    }

    public function update(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            // Allow super_admin, lead_manager, and telecallers to edit leads
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return back()->with('error', 'Unauthorized');
            }

            // Telecallers can only edit assigned leads
            if ($user->role === 'telecallers' && $lead->assigned_to !== $user->id) {
                return back()->with('error', 'You can only edit your assigned leads');
            }

            // Lead managers can only edit their own leads
            if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
                return back()->with('error', 'You can only edit your own leads');
            }

            // Determine if the lead is currently approved
            $isApprovedLead = $lead->status === 'approved';

            // Build validation rules
            $validationRules = [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:leads,email,' . $lead->id,
                'phone' => 'required|string|max:20|unique:leads,phone,' . $lead->id,
                'phone_alternative' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'district' => 'nullable|string|max:100',
                'property_type' => 'nullable|in:commercial,residential',
                'sqft' => 'nullable|string|max:100',
                'service_type' => 'required|in:cleaning,pest_control,other',
                'service_ids' => 'required|array|min:1',
                'service_ids.*' => 'exists:services,id',
                'lead_source_id' => 'required|exists:lead_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'amount' => 'nullable|numeric|min:0',
                'advance_paid_amount' => 'nullable|numeric|min:0|lte:amount',
                'payment_mode' => 'nullable|in:cash,upi,card,bank_transfer,neft,gpay,phonepe,paytm,amazonpay',
                'branch_id' => $user->role === 'super_admin' ? 'required|exists:branches,id' : 'nullable',
                'description' => 'nullable|string',
            ];

            // Status validation: required for super_admin or non-approved leads
            // For telecallers/lead_managers editing approved leads, status is sent as hidden field
            if ($user->role === 'super_admin' || !$isApprovedLead) {
                $validationRules['status'] = 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation,approved,rejected';
            } else {
                // For telecallers/lead_managers, status should be 'approved' (sent as hidden)
                $validationRules['status'] = 'required|in:approved';
            }

            $validated = $request->validate($validationRules);

            // Force telecaller self-assignment
            if ($user->role === 'telecallers') {
                $validated['assigned_to'] = $user->id;
            }

            $branchId = $user->role === 'super_admin' ? $validated['branch_id'] : $user->branch_id;

            // Track changes
            $amountChanged = isset($validated['amount']) && $validated['amount'] != $lead->amount;

            // Track if services changed
            $oldServiceIds = $lead->services->pluck('id')->sort()->values()->toArray();
            $newServiceIds = collect($validated['service_ids'])->sort()->values()->toArray();
            $servicesChanged = $oldServiceIds != $newServiceIds;

            // Determine the new status
            $newStatus = $validated['status'];

            // Track if status actually changed
            $statusChanged = $newStatus !== $lead->status;

            // Update lead
            $lead->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'phone_alternative' => $validated['phone_alternative'] ?? null,
                'address' => $validated['address'] ?? null,
                'district' => $validated['district'] ?? null,
                'service_type' => $validated['service_type'],
                'property_type' => $validated['property_type'] ?? null,
                'sqft' => $validated['sqft'] ?? null,
                'lead_source_id' => $validated['lead_source_id'],
                'assigned_to' => $validated['assigned_to'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'advance_paid_amount' => $validated['advance_paid_amount'] ?? 0,
                'payment_mode' => $validated['payment_mode'] ?? null,
                'amount_updated_at' => $amountChanged ? now() : $lead->amount_updated_at,
                'amount_updated_by' => $amountChanged ? $user->id : $lead->amount_updated_by,
                'branch_id' => $branchId,
                'description' => $validated['description'] ?? null,
                'status' => $newStatus,
            ]);

            // Sync lead services
            $lead->services()->sync($validated['service_ids']);

            // Update single service_id field (use first service for backward compatibility)
            $lead->update(['service_id' => $validated['service_ids'][0] ?? null]);

            // If lead was/is approved and has related jobs, sync changes to jobs
            if (($isApprovedLead || $newStatus === 'approved') && ($amountChanged || $servicesChanged)) {
                $relatedJobs = $lead->jobs()->whereIn('status', ['confirmed', 'pending', 'assigned', 'in_progress'])->get();

                foreach ($relatedJobs as $job) {
                    $updateData = [];

                    // Update job amount if changed
                    if ($amountChanged) {
                        $updateData['amount'] = $validated['amount'];
                    }

                    // Change job status to pending if it was confirmed (needs admin re-approval)
                    if ($job->status === 'confirmed' && ($amountChanged || $servicesChanged)) {
                        $updateData['status'] = 'pending';
                    }

                    // Update job if there are changes
                    if (!empty($updateData)) {
                        $job->update($updateData);
                    }

                    // Sync job services if changed
                    if ($servicesChanged) {
                        $job->services()->sync($validated['service_ids']);

                        // Update single service_id field
                        $job->update(['service_id' => $validated['service_ids'][0] ?? null]);
                    }
                }

                Log::info('Approved lead edited - related jobs updated', [
                    'lead_id' => $lead->id,
                    'jobs_updated' => $relatedJobs->count(),
                    'amount_changed' => $amountChanged,
                    'services_changed' => $servicesChanged,
                ]);
            }

            Log::info('Lead updated', [
                'lead_id' => $lead->id,
                'updated_by' => $user->id,
                'user_role' => $user->role,
                'amount_changed' => $amountChanged,
                'services_changed' => $servicesChanged,
                'status_changed' => $statusChanged,
                'old_status' => $lead->getOriginal('status'),
                'new_status' => $newStatus,
            ]);

            // Build success message
            $message = "Lead {$lead->lead_code} has been updated.";
            if (($isApprovedLead || $newStatus === 'approved') && ($amountChanged || $servicesChanged)) {
                $message .= ' Related jobs have been updated and set to pending status for admin approval.';
            }

            return redirect()->route('leads.index')
                ->with('success', json_encode([
                    'title' => 'Lead Updated Successfully!',
                    'message' => $message,
                    'leadcode' => $lead->lead_code,
                ]));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Lead update validation error', [
                'lead_id' => $lead->id,
                'errors' => $e->errors(),
                'user_role' => $user->role,
            ]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Lead update error: ' . $e->getMessage(), [
                'lead_id' => $lead->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error updating lead: ' . $e->getMessage());
        }
    }

    public function show(Lead $lead)
    {
        $user = auth()->user();

        if ($user->role === 'telecallers' && $lead->assigned_to !== $user->id) {
            abort(403, 'You can only view leads assigned to you.');
        }

        if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
            abort(403, 'You can only view your own leads.');
        }

        $lead->load([
            'branch',
            'source',
            'services',
            'createdBy',
            'assignedTo',
            'amountUpdatedBy',
            'calls.user',
            'notes.createdBy',
            'customer',
            'jobs.service',
            'jobs.assignedTo',
            'followups' => function($query) {
                $query->with('assignedToUser', 'createdBy')
                      ->orderBy('followup_date', 'asc')
                      ->orderBy('followup_time', 'asc');
            }
        ]);

        return view('leads.show', compact('lead'));
    }

    public function getServicesByType(Request $request)
    {
        $serviceType = $request->input('service_type');

        $services = Service::where('service_type', $serviceType)
                           ->where('is_active', true)
                           ->orderBy('name')
                           ->get(['id', 'name']);

        return response()->json($services);
    }

    public function assignLead(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'notes' => 'nullable|string'
            ]);

            $lead->update([
                'assigned_to' => $validated['assigned_to']
            ]);

            // Optionally add note
            if ($request->filled('notes')) {
                LeadNote::create([
                    'lead_id' => $lead->id,
                    'created_by' => auth()->id(),
                    'note' => 'Assignment Note: ' . $validated['notes']
                ]);
            }

            $telecaller = User::find($validated['assigned_to']);

            Log::info('Lead assigned', [
                'lead_id' => $lead->id,
                'assigned_to' => $telecaller->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Lead assigned to {$telecaller->name} successfully!"
            ]);

        } catch (\Exception $e) {
            Log::error('Lead assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning lead'
            ], 500);
        }
    }

    public function bulkAssign(Request $request)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'lead_ids' => 'required|array',
                'lead_ids.*' => 'exists:leads,id',
                'assigned_to' => 'required|exists:users,id',
                'notes' => 'nullable|string'
            ]);

            $telecaller = User::find($validated['assigned_to']);
            $count = 0;

            foreach ($validated['lead_ids'] as $leadId) {
                $lead = Lead::find($leadId);

                // Update all selected leads, no status restriction
                if ($lead) {
                    $lead->update(['assigned_to' => $validated['assigned_to']]);

                    if ($request->filled('notes')) {
                        LeadNote::create([
                            'lead_id' => $lead->id,
                            'created_by' => $user->id,
                            'note' => 'Bulk Assignment Note: ' . $validated['notes']
                        ]);
                    }

                    $count++;
                }
            }

            Log::info('Bulk lead assignment', [
                'count' => $count,
                'assigned_to' => $telecaller->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bulk assignment completed!",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk assignment'
            ], 500);
        }
    }

    public function addCall(Request $request, Lead $lead)
    {
        try {
            $validated = $request->validate([
                'call_date' => 'required|date',
                'duration' => 'nullable|integer|min:0',
                'outcome' => 'required|in:interested,not_interested,callback,no_answer,wrong_number',
                'notes' => 'nullable|string',
                // Followup fields (optional)
                'followup_date' => 'nullable|required_if:outcome,callback,interested|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'callback_time_preference' => 'nullable|in:morning,afternoon,evening,anytime',
                'followup_priority' => 'nullable|in:low,medium,high',
                'followup_notes' => 'nullable|string',
            ]);

            // Create call log
            $call = LeadCall::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'call_date' => $validated['call_date'],
                'duration' => $validated['duration'] ?? null,
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $followupCreated = false;

            // Create followup if outcome is callback or interested
            if (in_array($validated['outcome'], ['callback', 'interested']) && isset($validated['followup_date'])) {
                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'followup_date' => $validated['followup_date'],
                    'followup_time' => $validated['followup_time'] ?? null,
                    'callback_time_preference' => $validated['callback_time_preference'] ?? 'anytime',
                    'priority' => $validated['followup_priority'] ?? 'medium',
                    'notes' => $validated['followup_notes'] ?? null,
                    'assigned_to' => $lead->assigned_to ?? auth()->id(),
                    'created_by' => auth()->id(),
                    'status' => 'pending',
                ]);

                $followupCreated = true;
            }

            Log::info('Call logged successfully', [
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'followup_created' => $followupCreated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully!',
                'followup_created' => $followupCreated,
            ]);

        } catch (\Exception $e) {
            Log::error('Add call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging call: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addFollowup(Request $request, Lead $lead)
    {
        try {
            $validated = $request->validate([
                'followup_date' => 'required|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'callback_time_preference' => 'nullable|in:morning,afternoon,evening,anytime',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string|max:1000',
            ]);

            LeadFollowup::create([
                'lead_id' => $lead->id,
                'followup_date' => $validated['followup_date'],
                'followup_time' => $validated['followup_time'] ?? null,
                'callback_time_preference' => $validated['callback_time_preference'] ?? 'anytime',
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
                'assigned_to' => $lead->assigned_to ?? auth()->id(),
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            Log::info('Followup scheduled', [
                'lead_id' => $lead->id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup scheduled successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Add followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling followup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function complete(LeadFollowup $followup)
    {
        if ($followup->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already completed']);
        }
        // Own, assigned, or admin-only logic here if needed...
        $followup->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        return response()->json(['success' => true, 'message' => 'Follow-up marked as completed!']);
    }

    public function addNote(Request $request, Lead $lead)
    {
        try {
            $validated = $request->validate([
                'note' => 'required|string',
            ]);

            $note = LeadNote::create([
                'lead_id' => $lead->id,
                'created_by' => auth()->id(),
                'note' => $validated['note'],
            ]);

            Log::info('Note added to lead', ['lead_id' => $lead->id, 'note_id' => $note->id]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Add note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding note'
            ], 500);
        }
    }

    public function destroy(Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (in_array($lead->status, ['approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only delete unapproved leads'
                ], 403);
            }

            if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own leads'
                ], 403);
            }

            if ($user->role === 'telecallers' && $lead->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own leads'
                ], 403);
            }

            $leadName = $lead->name;
            $lead->delete();

            Log::info('Lead deleted', ['lead_name' => $leadName]);

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lead delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lead'
            ], 500);
        }
    }

    public function approve(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            // Allow super_admin, lead_manager, and telecallers
            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin and Lead Manager can approve leads.'
                ], 403);
            }

            // If telecaller, only allow approval of their own assigned leads
            if ($user->role === 'telecallers' && $lead->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only approve leads assigned to you.'
                ], 403);
            }

            // Check if amount is set
            if (!$lead->amount || $lead->amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead amount must be set before approval. Please add the amount first.'
                ], 400);
            }

            // Check daily budget limit
            $budgetCheck = $this->checkDailyBudget($lead->amount);

            if (!$budgetCheck['can_approve']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily budget limit exceeded!',
                    'budget_info' => [
                        'daily_limit' => '₹' . number_format($budgetCheck['daily_limit'], 2),
                        'today_total' => '₹' . number_format($budgetCheck['today_total'], 2),
                        'remaining' => '₹' . number_format($budgetCheck['remaining'], 2),
                        'requested' => '₹' . number_format($budgetCheck['requested_amount'], 2),
                        'excess' => '₹' . number_format($budgetCheck['requested_amount'] - $budgetCheck['remaining'], 2)
                    ]
                ], 400);
            }

            $validated = $request->validate([
                'approval_notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                // Create Customer from Lead - Handle null email
                $customer = Customer::create([
                    'name' => $lead->name,
                    'email' => $lead->email ?? null, // Handle null email
                    'phone' => $lead->phone,
                    'address' => $lead->address ?? null,
                    'priority' => 'medium',
                    'notes' => $lead->description,
                    'lead_id' => $lead->id,
                    'is_active' => true,
                ]);

                // Generate UNIQUE job code
                $jobCode = $this->generateUniqueJobCode();

                // Auto-create Job from approved lead
                $job = Job::create([
                    'job_code' => $jobCode,
                    'title' => ($lead->service ? $lead->service->name : 'Cleaning Service') . ' - ' . $lead->name,
                    'customer_id' => $customer->id,
                    'lead_id' => $lead->id,
                    'service_id' => $lead->services->first()->id ?? null, // First service for backward compatibility
                    'branch_id' => $lead->branch_id,
                    'assigned_to' => $lead->assigned_to,
                    'amount' => $lead->amount,
                    'scheduled_date' => null,
                    'status' => 'confirmed',
                    'created_by' => auth()->id(),
                    'notes' => 'Auto-created from approved lead: ' . $lead->lead_code . "\n" .
                            'Amount: ₹' . number_format($lead->amount, 2) . "\n" .
                            ($validated['approval_notes'] ?? ''),
                ]);

                // Sync all services from lead to job
                if ($lead->services->isNotEmpty()) {
                    $job->services()->sync($lead->services->pluck('id'));
                }

                // Update Lead with approval details
                $lead->update([
                    'status' => 'approved',
                    'customer_id' => $customer->id,
                    'approval_notes' => $validated['approval_notes'] ?? null,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Create approval record
                LeadApproval::create([
                    'lead_id' => $lead->id,
                    'super_admin_id' => auth()->id(),
                    'action' => 'approve',
                    'comment' => $validated['approval_notes'] ?? 'Lead approved and customer/job created',
                    'reviewed_at' => now(),
                ]);

                DB::commit();

                // Calculate new remaining budget
                $newRemaining = $budgetCheck['remaining'] - $lead->amount;

                Log::info('Lead approved with budget check', [
                    'lead_id' => $lead->id,
                    'amount' => $lead->amount,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'remaining_budget' => $newRemaining,
                    'approved_by' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lead converted successfully! Customer and Work order created.',
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'job_id' => $job->id,
                    'job_code' => $job->job_code,
                    'amount' => '₹' . number_format($lead->amount, 2),
                    'remaining_budget' => '₹' . number_format($newRemaining, 2)
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Lead approval error: ' . $e->getMessage(), [
                'lead_id' => $lead->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role === 'telecallers' && $lead->assigned_to !== $user->id) {
            return response()->json(['message' => 'You can only update status of your assigned leads'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation,approved,rejected',
        ]);

        if ($validated['status'] === 'approved') {
            return response()->json(['message' => 'Not allowed to approve leads'], 403);
        }

        $lead->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'status' => $lead->status,
            'status_label' => $lead->status_label,
        ]);
    }

    /**
     * Generate a unique job code with database locking to prevent race conditions
     */
    private function generateUniqueJobCode()
    {
        // Use database transaction with locking to prevent race conditions
        return DB::transaction(function () {
            // Lock the table to prevent concurrent inserts
            $lastJob = Job::lockForUpdate()->orderBy('id', 'desc')->first();

            if ($lastJob && preg_match('/JOB(\d+)/', $lastJob->job_code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $jobCode = 'JOB' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Double-check if code exists (extra safety)
            $attempts = 0;
            while (Job::where('job_code', $jobCode)->exists() && $attempts < 10) {
                $nextNumber++;
                $jobCode = 'JOB' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                $attempts++;
            }

            return $jobCode;
        });
    }

    public function reject(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            // Security check: Only super_admin can reject
            if ($user->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Admin can reject leads'
                ], 403);
            }

            // Check if lead is pending
            if ($lead->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead is not pending'
                ], 400);
            }

            $validated = $request->validate([
                'rejection_reason' => 'required|string',
            ]);

            DB::beginTransaction();

            try {
                // Update lead status
                $lead->update([
                    'status' => 'rejected',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approval_notes' => $validated['rejection_reason'],
                ]);

                // Create rejection record
                LeadApproval::create([
                    'lead_id' => $lead->id,
                    'super_admin_id' => $user->id,
                    'action' => 'reject',
                    'comment' => $validated['rejection_reason'],
                    'reviewed_at' => now(),
                ]);

                DB::commit();

                Log::info('Lead rejected', [
                    'lead_id' => $lead->id,
                    'rejected_by' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lead rejected successfully!'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Lead reject error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkDuplicate(Request $request)
    {
        try {
            $email = $request->input('email');
            $phone = $request->input('phone');

            $result = [
                'exists' => false,
                'type' => null,
                'data' => null
            ];

            // Check in Customers first
            $customer = Customer::where(function($query) use ($email, $phone) {
                    if ($email) {
                        $query->where('email', $email);
                    }
                    if ($phone) {
                        $query->orWhere('phone', $phone);
                    }
                })
                ->with(['jobs', 'completedJobs'])
                ->first();

            if ($customer) {
                $lastJob = $customer->jobs()->latest()->first();

                $result = [
                    'exists' => true,
                    'type' => 'customer',
                    'message' => 'This email/phone belongs to an existing customer',
                    'data' => [
                        'customer_code' => $customer->customer_code,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'priority' => $customer->priority,
                        'total_jobs' => $customer->jobs->count(),
                        'completed_jobs' => $customer->completedJobs->count(),
                        'pending_jobs' => $customer->jobs->where('status', 'pending')->count(),
                        'last_job_date' => $lastJob ? $lastJob->created_at->format('d M Y') : 'N/A',
                        'last_service' => $lastJob && $lastJob->service ? $lastJob->service->name : 'N/A',
                        'customer_id' => $customer->id,
                        'customer_since' => $customer->created_at->format('d M Y')
                    ]
                ];

                return response()->json($result);
            }

            // Check in Leads
            $lead = Lead::where(function($query) use ($email, $phone) {
                    if ($email) {
                        $query->where('email', $email);
                    }
                    if ($phone) {
                        $query->orWhere('phone', $phone);
                    }
                })
                ->first();

            if ($lead) {
                $result = [
                    'exists' => true,
                    'type' => 'lead',
                    'message' => 'This email/phone already exists as a lead',
                    'data' => [
                        'lead_code' => $lead->lead_code,
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'phone' => $lead->phone,
                        'status' => $lead->status,
                        'service' => $lead->service ? $lead->service->name : 'N/A',
                        'created_at' => $lead->created_at->format('d M Y'),
                        'lead_id' => $lead->id
                    ]
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Duplicate check error: ' . $e->getMessage());
            return response()->json(['exists' => false], 500);
        }
    }

    /**
     * Get today's total approved amount
     */
    private function getTodayApprovedAmount()
    {
        return Lead::whereDate('approved_at', today())
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * Check if amount exceeds daily budget
     */
    private function checkDailyBudget($amount)
    {
        $dailyLimit = Setting::get('daily_budget_limit', 100000);
        $todayTotal = $this->getTodayApprovedAmount();
        $remaining = $dailyLimit - $todayTotal;

        return [
            'can_approve' => $amount <= $remaining,
            'daily_limit' => $dailyLimit,
            'today_total' => $todayTotal,
            'remaining' => $remaining,
            'requested_amount' => $amount
        ];
    }

    /**
     * Quick search for telecallers - search leads and customers by phone/email
     */
    public function quickSearch(Request $request)
    {
        $user = auth()->user();

        // Only telecallers, super_admin and lead_manager can use this
        if (!in_array($user->role, ['telecallers', 'super_admin', 'lead_manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->input('query');

        if (strlen($query) < 3) {
            return response()->json(['leads' => [], 'customers' => []]);
        }

        // Search Leads
        $leadsQuery = Lead::query();

        // Telecallers can only search their assigned leads
        if ($user->role === 'telecallers') {
            $leadsQuery->where('assigned_to', $user->id);
        }
        // Super admin and lead manager can search all leads

        $leads = $leadsQuery->where(function($q) use ($query) {
                $q->where('phone', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('lead_code', 'like', "%{$query}%");
            })
            ->with(['service'])
            ->limit(5)
            ->get()
            ->map(function($lead) {
                return [
                    'id' => $lead->id,
                    'lead_code' => $lead->lead_code,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'status' => $lead->status,
                    'service' => $lead->service->name ?? 'N/A'
                ];
            });

        // Search Customers - All roles can search all customers
        $customers = Customer::where(function($q) use ($query) {
                $q->where('phone', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('customer_code', 'like', "%{$query}%");
            })
            ->withCount('jobs')
            ->limit(5)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'priority' => $customer->priority,
                    'total_jobs' => $customer->jobs_count
                ];
            });

        return response()->json([
            'leads' => $leads,
            'customers' => $customers
        ]);
    }

    /**
     * Display WhatsApp leads for telecallers
     */
    public function whatsappLeads(Request $request)
    {
        $user = auth()->user();

        // Only telecallers can access
        if ($user->role !== 'telecallers') {
            abort(403, 'Unauthorized access');
        }

        // Get WhatsApp source
        $whatsappSource = LeadSource::where('name', 'WhatsApp')->first();

        if (!$whatsappSource) {
            return redirect()->route('leads.index')
                ->with('error', 'WhatsApp lead source not configured');
        }

        // Build query
        $query = Lead::with(['branch', 'service', 'source', 'createdBy', 'assignedTo'])
            ->where('assigned_to', $user->id)
            ->where('lead_source_id', $whatsappSource->id);

        // Apply additional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('datefrom')) {
            $query->whereDate('created_at', '>=', $request->datefrom);
        }

        if ($request->filled('dateto')) {
            $query->whereDate('created_at', '<=', $request->dateto);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(20);
        $pendingcount = Lead::where('assigned_to', $user->id)
            ->where('lead_source_id', $whatsappSource->id)
            ->where('status', 'pending')
            ->count();

        $services = Service::where('is_active', true)->get();

        // Return JSON for AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
            ]);
        }

        return view('leads.whatsapp', compact('leads', 'pendingcount', 'services'));
    }

    /**
     * Display Google Ads leads for telecallers
     */
    public function googleAdsLeads(Request $request)
    {
        $user = auth()->user();

        // Only telecallers can access
        if ($user->role !== 'telecallers') {
            abort(403, 'Unauthorized access');
        }

        // Get Google Ads source
        $googleAdsSource = LeadSource::where('name', 'Google Ads')->first();

        if (!$googleAdsSource) {
            return redirect()->route('leads.index')
                ->with('error', 'Google Ads lead source not configured');
        }

        // Build query
        $query = Lead::with(['branch', 'service', 'source', 'createdBy', 'assignedTo'])
            ->where('assigned_to', $user->id)
            ->where('lead_source_id', $googleAdsSource->id);

        // Apply additional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('datefrom')) {
            $query->whereDate('created_at', '>=', $request->datefrom);
        }

        if ($request->filled('dateto')) {
            $query->whereDate('created_at', '<=', $request->dateto);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(20);
        $pendingcount = Lead::where('assigned_to', $user->id)
            ->where('lead_source_id', $googleAdsSource->id)
            ->where('status', 'pending')
            ->count();

        $services = Service::where('is_active', true)->get();

        // Return JSON for AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
            ]);
        }

        return view('leads.google-ads', compact('leads', 'pendingcount', 'services'));
    }

    /**
     * Delete a followup
     */
    public function deleteFollowup(LeadFollowup $followup)
    {
        try {
            $user = auth()->user();

            // Authorization: super_admin, lead_manager, or creator/assignee
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $followup->created_by !== $user->id &&
                $followup->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this followup'
                ], 403);
            }

            // Don't allow deletion of completed followups (optional - remove if you want)
            if ($followup->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete completed followups'
                ], 400);
            }

            $followup->delete();

            Log::info('Followup deleted', [
                'followup_id' => $followup->id,
                'lead_id' => $followup->lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting followup'
            ], 500);
        }
    }

    /**
     * Delete a call log
     */
    public function deleteCall(LeadCall $call)
    {
        try {
            $user = auth()->user();

            // Authorization: super_admin, lead_manager, or creator
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $call->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this call log'
                ], 403);
            }

            $call->delete();

            Log::info('Call log deleted', [
                'call_id' => $call->id,
                'lead_id' => $call->lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call log deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting call log'
            ], 500);
        }
    }

    /**
     * Delete a note
     */
    public function deleteNote(LeadNote $note)
    {
        try {
            $user = auth()->user();

            // Authorization: super_admin, lead_manager, or creator
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $note->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this note'
                ], 403);
            }

            $note->delete();

            Log::info('Note deleted', [
                'note_id' => $note->id,
                'lead_id' => $note->lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting note'
            ], 500);
        }
    }

}
