<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use App\Models\LeadCall;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadApproval;
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

        $query = Lead::with(['branch', 'service', 'source', 'createdBy']);

        // Role-based access
        if ($user->role === 'super_admin') {
            // Super admin sees all leads
            $pending_count = Lead::where('status', 'pending')->count();
        } elseif ($user->role === 'lead_manager') {
            $query->where('created_by', $user->id);
            $pending_count = 0;
        } else {
            abort(403, 'Unauthorized');
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('lead_source_id')) {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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

        // Paginate results
        $leads = $query->orderBy('created_at', 'desc')->paginate(15);

        $pending_count = Lead::where('status', 'pending')->count();
        $branches = \App\Models\Branch::all();
        $services = \App\Models\Service::all();
        $lead_sources = \App\Models\LeadSource::all();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total()
            ]);
        }

        return view('leads.index', compact('leads', 'pending_count', 'branches', 'services', 'lead_sources'));
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Validate with proper error messages
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:leads,email',
                'phone' => 'required|string|max:20|unique:leads,phone',
                'service_id' => 'required|exists:services,id',
                'lead_source_id' => 'required|exists:lead_sources,id',
                'branch_id' => $user->role === 'super_admin'
                    ? 'required|exists:branches,id'
                    : 'nullable',
                'description' => 'nullable|string',
            ], [
                'email.unique' => 'This email address is already registered as a lead.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'name.required' => 'Lead name is required.',
                'name.max' => 'Lead name must not exceed 255 characters.',
                'phone.required' => 'Phone number is required.',
                'phone.unique' => 'This phone number is already registered as a lead.',
                'phone.max' => 'Phone number must not exceed 20 characters.',
                'lead_source_id.required' => 'Please select a lead source.',
                'lead_source_id.exists' => 'Selected lead source does not exist.',
                'branch_id.required' => 'Please select a branch.',
                'branch_id.exists' => 'Selected branch does not exist.',
            ]);

            $branchId = $user->role === 'super_admin'
                ? $validated['branch_id']
                : $user->branch_id;

            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Branch not found for your account. Please contact administrator.'
                ], 422);
            }

            // Check if branch exists
            $branch = Branch::find($branchId);
            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected branch does not exist.'
                ], 422);
            }

            $lead = Lead::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'service_id' => $validated['service_id'],
                'lead_source_id' => $validated['lead_source_id'],
                'branch_id' => $branchId,
                'description' => $validated['description'] ?? null,
                'created_by' => $user->id,
                'status' => 'pending',
            ]);

            Log::info('Lead created successfully', [
                'lead_id' => $lead->id,
                'created_by' => $user->id,
                'name' => $lead->name,
                'email' => $lead->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully! Pending approval from admin.'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lead creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Lead $lead)
    {
        $lead->load([
            'branch',
            'source',
            'service',
            'createdBy',
            'calls.user',
            'notes.createdBy',
            'customer',
            'jobs.service',
            'jobs.assignedTo'
        ]);

        return view('leads.show', compact('lead'));
    }

    public function addCall(Request $request, Lead $lead)
    {
        try {
            $validated = $request->validate([
                'call_date' => 'required|date',
                'duration' => 'nullable|integer|min:0',
                'outcome' => 'required|in:interested,not_interested,callback,no_answer,wrong_number',
                'notes' => 'nullable|string',
            ]);

            $call = LeadCall::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'call_date' => $validated['call_date'],
                'duration' => $validated['duration'] ?? null,
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Call logged for lead', ['lead_id' => $lead->id, 'call_id' => $call->id]);

            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Add call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging call'
            ], 500);
        }
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

    public function edit(Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($lead->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only edit pending leads'
                ], 403);
            }

            if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own leads'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'lead' => $lead
            ]);

        } catch (\Exception $e) {
            Log::error('Lead edit error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading lead'
            ], 500);
        }
    }

    public function update(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($lead->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only edit pending leads'
                ], 403);
            }

            if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own leads'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:leads,email,' . $lead->id,
                'phone' => 'required|string|max:20',
                'lead_source_id' => 'required|exists:lead_sources,id',
                'branch_id' => $user->role === 'super_admin'
                    ? 'required|exists:branches,id'
                    : 'nullable',
                'description' => 'nullable|string',
            ], [
                'email.unique' => 'This email address is already registered as a lead.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'name.required' => 'Lead name is required.',
                'phone.required' => 'Phone number is required.',
                'lead_source_id.required' => 'Please select a lead source.',
                'branch_id.required' => 'Please select a branch.',
            ]);

            $branchId = $user->role === 'super_admin'
                ? $validated['branch_id']
                : $user->branch_id;

            $lead->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'lead_source_id' => $validated['lead_source_id'],
                'branch_id' => $branchId,
                'description' => $validated['description'] ?? null,
            ]);

            Log::info('Lead updated', ['lead_id' => $lead->id]);

            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lead update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($lead->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only delete pending leads'
                ], 403);
            }

            if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
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
            // Security check: Only super_admin can approve
            if (auth()->user()->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin can approve leads.'
                ], 403);
            }

            // Check if lead is pending
            if ($lead->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This lead has already been processed'
                ], 400);
            }

            $validated = $request->validate([
                'approval_notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                // Create Customer from Lead
                $customer = Customer::create([
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'address' => $lead->address ?? null,
                    'priority' => 'medium',
                    'notes' => $lead->description,
                    'lead_id' => $lead->id,
                    'is_active' => true,
                ]);

                // Generate UNIQUE job code - FIX FOR DUPLICATE ERROR
                $jobCode = $this->generateUniqueJobCode();

                // Auto-create Job from approved lead
                $job = Job::create([
                    'job_code' => $jobCode,
                    'title' => ($lead->service ? $lead->service->name : 'Cleaning Service') . ' - ' . $lead->name,
                    'customer_id' => $customer->id,
                    'lead_id' => $lead->id,
                    'service_id' => $lead->service_id,
                    'branch_id' => $lead->branch_id,
                    'assigned_to' => null,
                    'scheduled_date' => null,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                    'notes' => 'Auto-created from approved lead: ' . $lead->lead_code . "\n" . ($validated['approval_notes'] ?? ''),
                ]);

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

                Log::info('Lead approved, customer and job created', [
                    'lead_id' => $lead->id,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'job_code' => $jobCode,
                    'approved_by' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lead approved successfully! Customer and Job created.',
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'job_id' => $job->id,
                    'job_code' => $job->job_code
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
}
