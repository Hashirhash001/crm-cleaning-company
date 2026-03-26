<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Job;
use App\Models\JobCall;
use App\Models\JobFollowup;
use App\Models\JobNote;
use App\Models\JobRating;
use App\Models\JobStaff;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Allow telecallers, leadmanager, superadmin, and fieldstaff to view jobs
        if (!in_array($user->role, ['super_admin', 'lead_manager', 'field_staff', 'telecallers', 'supervisor', 'worker'])) {
            return back()->with('error', 'Unauthorized');
        }

        // IMPORTANT: Eager load relationships but don't join them in the main query
        $query = Job::with(['customer', 'services', 'service', 'branch', 'assignedTo', 'createdBy', 'lead']);

        // Handle quick filters
        $status = $request->input('status');

        if ($status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status === 'confirmed') {
            $query->where('status', 'confirmed');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status && $status !== '') {
            $query->where('status', $status);
        }

        // Filter by branch
        if ($request->filled('branchid')) {
            $query->where('branch_id', $request->branchid);
        }

        // Filter by service
        if ($request->filled('serviceid')) {
            $serviceId = $request->serviceid;
            $query->whereHas('services', function($subQuery) use ($serviceId) {
                $subQuery->where('services.id', $serviceId);
            });
        }

        // Filter by scheduled date range
        if ($request->filled('datefrom')) {
            $query->whereDate('scheduled_date', '>=', $request->datefrom);
        }

        if ($request->filled('dateto')) {
            $query->whereDate('scheduled_date', '<=', $request->dateto);
        }

        // ENHANCED SEARCH FUNCTIONALITY
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('job_code', 'like', $search)
                    ->orWhere('location', 'like', $search)
                    ->orWhereHas('customer', function($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', $search)
                            ->orWhere('customer_code', 'like', $search)
                            ->orWhere('phone', 'like', $search);
                    })
                    ->orWhereHas('lead', function($leadQuery) use ($search) {
                        $leadQuery->where('lead_code', 'like', $search);
                    })
                    ->orWhereHas('assignedTo', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', $search);
                    });
            });
        }

        // If user is field staff, only show their jobs
        if ($user->role === 'field_staff') {
            $query->where('assigned_to', $user->id);
        }

        // If user is telecaller, show jobs assigned to them
        if ($user->role === 'telecallers') {
            $query->where('assigned_to', $user->id);
        }

        if (in_array($user->role, ['supervisor', 'worker'])) {
            $query->whereHas('staff', fn($q) => $q->where('user_id', $user->id));
        }

        // APPLY SORTING - This must come AFTER all filters but BEFORE pagination
        $sortColumn = $request->get('sortcolumn', 'created_at');
        $sortDirection = $request->get('sortdirection', 'desc');

        // Log the sorting for debugging
        Log::info('Applying sort', [
            'column' => $sortColumn,
            'direction' => $sortDirection,
            'user' => $user->id
        ]);

        // Apply the sort scope
        $query->sort($sortColumn, $sortDirection);

        // Paginate - This must be LAST
        $jobs = $query->paginate(15);

        // Calculate pending counts
        if ($user->role === 'telecallers') {
            $pendingJobs = Job::where('status', 'pending')->where('assigned_to', $user->id)->count();
            $pendingApproval = Job::where('status', 'confirmed')->where('assigned_to', $user->id)->count();
        } elseif ($user->role === 'super_admin') {
            $pendingJobs = Job::where('status', 'pending')->count();
            $pendingApproval = Job::where('status', 'confirmed')->count();
        } else {
            $pendingApproval = 0;
            $pendingJobs = 0;
        }

        // Get data for filters
        $branches = Branch::all();
        $customers = Customer::all();
        $services = Service::orderBy('name')->get();
        // Get distinct service types from database
        $serviceTypes = Service::select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type')
            ->toArray();

        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        $field_staff = User::where('role', 'field_staff')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'branch_id']);
        $workers = User::where('role', 'worker')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'branch_id']);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('jobs.partials.table-rows', compact('jobs'))->render(),
                'pagination' => $jobs->links('pagination::bootstrap-5')->render(),
                'total' => $jobs->total(),
                'current_sort' => [
                    'column' => $sortColumn,
                    'direction' => $sortDirection
                ]
            ]);
        }

        return view('jobs.index', compact('jobs', 'pendingApproval', 'pendingJobs', 'branches', 'customers', 'services', 'serviceTypes', 'field_staff', 'telecallers', 'supervisors', 'workers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        try {
            // Allow super_admin, lead_manager, and telecallers to create jobs
            if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'customer_id' => 'nullable|exists:customers,id',
                // No service_type required - will auto-detect
                'service_ids' => 'required|array|min:1',
                'service_ids.*' => 'exists:services,id',
                'service_quantities' => 'nullable|array',
                'service_quantities.*' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'customer_instructions' => 'nullable|string',
                'branch_id' => 'required|exists:branches,id',
                'location' => 'nullable|string',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable|date_format:H:i',
                'amount' => 'nullable|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'addon_price' => 'nullable|numeric|min:0',
                'addon_price_comments' => 'nullable|string|max:1000',
                'confirm_on_creation' => 'nullable|boolean',
                'status' => 'nullable|in:pending,work_on_hold,postponed,cancelled',
            ]);

            if ($user->role === 'telecallers') {
                $validated['assigned_to'] = $user->id;
            }

            // Generate unique job code
            // $jobCount = Job::count();
            // $jobCode = Job::generateJobCode();

            // Determine initial status
            // If telecaller checked "confirm_on_creation", set status to "confirmed"
            $initialStatus = 'pending';
            // Check if confirm checkbox is checked (for telecallers)
            $confirmChecked = $user->role === 'telecallers' && $request->input('confirm_on_creation') == 1;

            // Priority Logic:
            // 1. If confirm checkbox is checked, ALWAYS use "confirmed" (checkbox has highest priority)
            // 2. Otherwise, use the status dropdown value if provided
            // 3. Otherwise, default to "pending"

            if ($confirmChecked) {
                $initialStatus = 'confirmed';
            } elseif ($request->filled('status')) {
                $initialStatus = $validated['status'];
            }

            $job = Job::create([
                // 'job_code' => $jobCode,
                'title' => $validated['title'],
                'customer_id' => $validated['customer_id'] ?? null,
                'service_id' => $validated['service_ids'][0] ?? null,
                'description' => $validated['description'] ?? null,
                'customer_instructions' => $validated['customer_instructions'] ?? null,
                'branch_id' => $validated['branch_id'],
                'location' => $validated['location'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'addon_price' => $validated['addon_price'] ?? 0,
                'addon_price_comments' => $validated['addon_price_comments'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => auth()->id(),
                'status' => $initialStatus,
            ]);

            // **ATTACH SELECTED SERVICES WITH QUANTITIES**
            $serviceData = [];
            foreach ($validated['service_ids'] as $serviceId) {
                $quantity = $validated['service_quantities'][$serviceId] ?? 1;
                $serviceData[$serviceId] = ['quantity' => $quantity];
            }
            $job->services()->attach($serviceData);

            Log::info('Job created', [
                'job_id' => $job->id,
                'services' => $serviceData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job created successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating job: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Job $job)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'field_staff', 'telecallers', 'supervisor', 'worker'])) {
            return back()->withError('Unauthorized');
        }

        $job->load([
            'branch', 'lead.services', 'customer.customerNotes.createdBy',
            'customer.customerNotes.job', 'services', 'service',
            'assignedTo', 'createdBy', 'followups.assignedTo',
            'followups.createdBy', 'calls.user', 'notes.createdBy',
            'staff.user', 'staff.addedBy',
            'rating.ratedBy',
        ]);

        $branches     = Branch::all();
        $services     = Service::orderBy('name')->get(['id', 'name', 'service_type']);
        $serviceTypes = Service::select('service_type')->distinct()->orderBy('service_type')
                            ->pluck('service_type')->toArray();

        $telecallers = User::where('role', 'telecallers')->where('is_active', true)
                            ->orderBy('name')->get(['id', 'name', 'branch_id']);

        $fieldstaff  = User::where('role', 'field_staff')->where('is_active', true)
                            ->orderBy('name')->get(['id', 'name', 'branch_id']);

        // for Add Staff modal
        $supervisors = User::where('role', 'supervisor')->where('is_active', true)
                            ->orderBy('name')->get(['id', 'name']);

        $workers     = User::where('role', 'worker')->where('is_active', true)
                            ->orderBy('name')->get(['id', 'name']);

        return view('jobs.show', compact(
            'job', 'telecallers', 'fieldstaff',
            'supervisors', 'workers',
            'branches', 'services', 'serviceTypes'
        ));
    }

    public function edit(Job $job)
    {
        try {
            $user = auth()->user();

            // Allow telecallers, lead_manager, and super_admin to edit jobs
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($user->role === 'telecallers' && $job->assigned_to !== $user->id) {
                abort(403, 'You can only edit jobs assigned to you.');
            }

            // Format the job data properly
            $jobData = $job->toArray();

            // Ensure scheduled_date is in YYYY-MM-DD format
            if ($job->scheduled_date) {
                $jobData['scheduled_date'] = $job->scheduled_date->format('Y-m-d');
            }

            // Ensure scheduled_time is in HH:MM format
            if ($job->scheduled_time) {
                $jobData['scheduled_time'] = substr($job->scheduled_time, 0, 5); // Get HH:MM
            }

            // Load services from job_service pivot table
            $services = $job->services;

            // Get service_type from the services table (not from leads)
            $firstService = $services->first();
            $jobData['service_type'] = $firstService ? $firstService->service_type : 'other';

            // Get service IDs
            $jobData['service_ids'] = $job->services->pluck('id')->toArray();

            $jobData['service_quantities'] = $services->pluck('pivot.quantity', 'id')->toArray();

            return response()->json([
                'success' => true,
                'job' => $jobData
            ]);

        } catch (\Exception $e) {
            Log::error('Job edit error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading job'
            ], 500);
        }
    }

    public function update(Request $request, Job $job)
    {
        try {
            $user = auth()->user();

            // Allow telecallers, lead_manager, and super_admin to edit jobs
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($user->role === 'telecallers' && $job->assigned_to !== $user->id) {
                abort(403, 'You can only update jobs assigned to you.');
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'customer_id' => 'nullable|exists:customers,id',
                'branch_id' => 'required|exists:branches,id',
                'assigned_to' => 'nullable|exists:users,id',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable',
                'location' => 'nullable|string',
                'description' => 'nullable|string',
                'customer_instructions' => 'nullable|string',
                'amount' => 'nullable|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'addon_price' => 'nullable|numeric|min:0',
                'addon_price_comments' => 'nullable|string|max:1000',
                'service_type' => 'nullable|string',
                'service_ids' => 'nullable|array',
                'service_ids.*' => 'exists:services,id',
                'service_quantities' => 'nullable|array',
                'service_quantities.*' => 'nullable|integer|min:1',
                'status' => 'nullable|in:pending,work_on_hold,postponed,cancelled',
                'confirm_on_creation' => 'nullable|boolean',
            ]);

            // Track if amount or services changed
            $amountChanged = isset($validated['amount']) && $validated['amount'] != $job->amount;
            $amountPaidChanged = isset($validated['amount_paid']) && $validated['amount_paid'] != $job->amount_paid;

            // Track if services changed
            $servicesChanged = false;
            if (isset($validated['service_ids'])) {
                $oldServiceIds = $job->services->pluck('id')->sort()->values()->toArray();
                $newServiceIds = collect($validated['service_ids'])->sort()->values()->toArray();
                $servicesChanged = $oldServiceIds != $newServiceIds;
            }

            // Determine new status
            $newStatus = $validated['status'] ?? $job->status;

            // Check if confirm checkbox is checked (for telecallers)
            $confirmChecked = $user->role === 'telecallers' && $request->input('confirm_on_creation') == 1;

            // If amount or services changed and job has a lead and is confirmed, set to pending
            if ($confirmChecked) {
                $newStatus = 'confirmed';
            } elseif ($request->filled('status')) {
                $newStatus = $validated['status'];
            } elseif (($amountChanged || $amountPaidChanged || $servicesChanged) && $job->lead_id) {
                $newStatus = 'pending';
            }

            // Prepare update data
            $updateData = [
                'title' => $validated['title'],
                'customer_id' => $validated['customer_id'] ?? $job->customer_id,
                'branch_id' => $validated['branch_id'],
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
                'customer_instructions' => $validated['customer_instructions'] ?? null,
                'addon_price' => $validated['addon_price'] ?? 0,
                'addon_price_comments' => $validated['addon_price_comments'] ?? null,
                'status' => $newStatus,
            ];

            // Add optional fields if provided
            if (isset($validated['assigned_to'])) {
                $updateData['assigned_to'] = $validated['assigned_to'];
            }

            if (isset($validated['scheduled_date'])) {
                $updateData['scheduled_date'] = $validated['scheduled_date'];
            }

            if (isset($validated['scheduled_time'])) {
                $updateData['scheduled_time'] = $validated['scheduled_time'];
            }

            if (isset($validated['amount'])) {
                $updateData['amount'] = $validated['amount'];
            }

            if (isset($validated['amount_paid'])) {
                $updateData['amount_paid'] = $validated['amount_paid'];
            }

            $job->update($updateData);

            // Sync services WITH QUANTITIES if provided
            if (isset($validated['service_ids']) && count($validated['service_ids']) > 0) {
                // Build service data with quantities
                $serviceData = [];
                $quantities = $validated['service_quantities'] ?? [];

                foreach ($validated['service_ids'] as $serviceId) {
                    $quantity = $quantities[$serviceId] ?? 1;
                    $serviceData[$serviceId] = ['quantity' => $quantity];
                }

                $job->services()->sync($serviceData);

                // Update single service_id field (use first service for backward compatibility)
                $job->update(['service_id' => $validated['service_ids'][0]]);
            }

            // If job has a related lead, sync changes back to the lead
            if ($job->lead_id && ($amountChanged || $amountPaidChanged || $servicesChanged)) {
                $lead = $job->lead;

                $leadUpdateData = [];

                // Update lead amount if job amount changed
                if ($amountChanged && isset($validated['amount'])) {
                    $leadUpdateData['amount'] = $validated['amount'];
                    $leadUpdateData['amount_updated_at'] = now();
                    $leadUpdateData['amount_updated_by'] = $user->id;
                }

                // Update lead amount paid if job amount paid changed
                if ($amountPaidChanged && isset($validated['amount_paid'])) {
                    $leadUpdateData['advance_paid_amount'] = $validated['amount_paid'];
                    $leadUpdateData['amount_updated_at'] = now();
                    $leadUpdateData['amount_updated_by'] = $user->id;
                }

                // Update lead if there are changes
                if (!empty($leadUpdateData)) {
                    $lead->update($leadUpdateData);
                }

                // **SYNC LEAD SERVICES WITH QUANTITIES if job services changed**
                if ($servicesChanged && isset($serviceData)) {
                    $lead->services()->sync($serviceData); // Use same serviceData with quantities
                    $lead->update(['service_id' => $validated['service_ids'][0] ?? null]);
                }

                Log::info('work order edited - related lead updated', [
                    'job_id' => $job->id,
                    'lead_id' => $lead->id,
                    'amount_changed' => $amountChanged,
                    'amount_paid_changed' => $amountPaidChanged,
                    'services_changed' => $servicesChanged,
                ]);
            }

            Log::info('work order updated', [
                'job_id' => $job->id,
                'updated_by' => $user->id,
                'amount_changed' => $amountChanged,
                'amount_paid_changed' => $amountPaidChanged,
                'services_changed' => $servicesChanged,
                'status_changed_to_pending' => $newStatus === 'pending',
            ]);

            $message = 'work order updated successfully!';
            if (($amountChanged || $amountPaidChanged || $servicesChanged) && $job->lead_id) {
                $message .= ' work order status changed to pending for admin approval. Related lead has also been updated.';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('work order update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating work order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Job $job)
    {
        try {
            $user = auth()->user();

            // Super admin can delete any job
            if ($user->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only super admin can delete work order'
                ], 403);
            }

            $jobCode = $job->job_code;
            $job->delete();

            Log::info('work order deleted', ['job_code' => $jobCode, 'deleted_by' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'work order deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('work order delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting job'
            ], 500);
        }
    }

    public function assign(Request $request, Job $job)
    {
        try {
            $user = auth()->user();

            // Only super admin can assign/re-assign
            if ($user->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'assigned_to' => 'required|exists:users,id'
            ]);

            $userToAssign = User::findOrFail($validated['assigned_to']);

            // Only enforce branch check if the user has a branch set
            if ($userToAssign->branch_id && (int)$userToAssign->branch_id !== (int)$job->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected staff does not belong to this branch.'
                ], 422);
            }

            $job->update([
                'assigned_to' => $validated['assigned_to'],
                'assigned_at' => now(),
            ]);

            $assignedUser = User::find($validated['assigned_to']);

            Log::info('Job assigned', [
                'job_id' => $job->id,
                'assigned_to' => $assignedUser->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'work order assigned to ' . $assignedUser->name . ' successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning job'
            ], 500);
        }
    }

    public function startJob(Job $job)
    {
        try {
            if (auth()->user()->role !== 'field_staff' || auth()->id() !== $job->assigned_to) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($job->status !== 'assigned') {
                return response()->json([
                    'success' => false,
                    'message' => 'Job is not assigned'
                ], 400);
            }

            $job->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            Log::info('Job started', ['job_id' => $job->id]);

            return response()->json([
                'success' => true,
                'message' => 'Job started successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job start error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error starting job'
            ], 500);
        }
    }

    /**
     * Telecallers confirm the job (change status to "confirmed")
     */
    public function confirmStatus(Request $request, Job $job)
    {
        try {
            $user = auth()->user();

            // Only telecallers can confirm
            if ($user->role !== 'telecallers') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only telecallers can confirm jobs'
                ], 403);
            }

            // Can only confirm if status is pending
            if (!in_array($job->status, ['pending', 'postponed', 'work_on_hold'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job is not pending'
                ], 400);
            }

            $job->update([
                'status' => 'confirmed',
            ]);

            Log::info('Job confirmed', [
                'job_id' => $job->id,
                'confirmed_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job confirmed successfully! Waiting for admin approval.'
            ]);

        } catch (\Exception $e) {
            Log::error('Job confirm error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error confirming job'
            ], 500);
        }
    }

    /**
     * Super admin approves confirmed jobs (change status to "approved")
     */
    public function approveJob(Request $request, Job $job)
    {
        try {
            $user = auth()->user();

            // Only super_admin can approve
            if ($user->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Admin can approve jobs'
                ], 403);
            }

            // Can only approve if status is confirmed
            if ($job->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Job must be in confirmed status to approve'
                ], 400);
            }

            $validated = $request->validate([
                'approval_notes' => 'nullable|string',
            ]);

            $job->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'notes' => $validated['approval_notes'] ?? $job->notes,
            ]);

            Log::info('Job approved', [
                'job_id' => $job->id,
                'approved_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job approved successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving job'
            ], 500);
        }
    }

    /**
     * Updated completeJob method - only allow completion of approved jobs
     */
    public function completeJob(Request $request, Job $job)
    {
        try {
            $user = auth()->user();

            $isAuthorized = (
                ($user->role === 'field_staff' && $user->id === $job->assigned_to) ||
                $user->role === 'telecallers' ||
                $user->role === 'super_admin'
            );

            if (!$isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to complete this job'
                ], 403);
            }

            // Only allow completion if status is "approved" (changed from "confirmed")
            if ($job->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Job must be approved before it can be marked as complete'
                ], 400);
            }

            // Check if there's any due amount
            $amountDue = ($job->amount ?? 0) - ($job->amount_paid ?? 0);

            if ($amountDue > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete job. Outstanding amount due: ₹' . number_format($amountDue, 2)
                ], 400);
            }

            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Job completed', [
                'job_id' => $job->id,
                'completed_by' => $user->id,
                'user_role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job completed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job complete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error completing job'
            ], 500);
        }
    }

    public function createForCustomer(Request $request)
    {
        try {
            if (auth()->user()->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                // No service_type required
                'service_ids' => 'required|array|min:1',
                'service_ids.*' => 'exists:services,id',
                'service_quantities' => 'nullable|array',
                'service_quantities.*' => 'nullable|integer|min:1',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'customer_instructions' => 'nullable|string',
                'branch_id' => 'required|exists:branches,id',
                'location' => 'nullable|string',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable|date_format:H:i',
                'amount' => 'nullable|numeric|min:0',
            ]);

            // Generate unique job code
            $jobCount = Job::count();
            $jobCode = 'JOB' . str_pad($jobCount + 1, 4, '0', STR_PAD_LEFT);

            $job = Job::create([
                // 'job_code' => $jobCode,
                'customer_id' => $validated['customer_id'],
                'service_id' => $validated['service_ids'][0] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'customer_instructions' => $validated['customer_instructions'] ?? null,
                'branch_id' => $validated['branch_id'],
                'location' => $validated['location'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            // **ATTACH SELECTED SERVICES WITH QUANTITIES**
            $serviceData = [];
            foreach ($validated['service_ids'] as $serviceId) {
                $quantity = $validated['service_quantities'][$serviceId] ?? 1;
                $serviceData[$serviceId] = ['quantity' => $quantity];
            }
            $job->services()->attach($serviceData);

            Log::info('Job created for existing customer', [
                'job_id' => $job->id,
                'customer_id' => $validated['customer_id'],
                'services' => $serviceData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job created successfully for existing customer!',
                'job_code' => $job->job_code
            ]);

        } catch (\Exception $e) {
            Log::error('Job creation for customer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating job: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add followup to job
     */
    public function addFollowup(Request $request, Job $job)
    {
        try {
            $validated = $request->validate([
                'followup_date' => 'required|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'callback_time_preference' => 'nullable|in:morning,afternoon,evening,anytime',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string|max:1000',
            ]);

            JobFollowup::create([
                'job_id' => $job->id,
                'followup_date' => $validated['followup_date'],
                'followup_time' => $validated['followup_time'] ?? null,
                'callback_time_preference' => $validated['callback_time_preference'] ?? 'anytime',
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
                'assigned_to' => $job->assigned_to ?? auth()->id(),
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            Log::info('Job followup scheduled', [
                'job_id' => $job->id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup scheduled successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Add job followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling followup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a followup
     */
    public function completeFollowup(JobFollowup $followup)
    {
        try {
            if ($followup->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Followup already completed'
                ]);
            }

            $followup->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            Log::info('Job followup completed', ['followup_id' => $followup->id]);

            return response()->json([
                'success' => true,
                'message' => 'Followup marked as completed!'
            ]);

        } catch (\Exception $e) {
            Log::error('Complete job followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error completing followup'
            ], 500);
        }
    }

    /**
     * Delete a followup
     */
    public function deleteFollowup(Job $job, JobFollowup $followup)
    {
        try {
            $user = auth()->user();

            // Only super_admin or creator can delete
            if ($user->role !== 'super_admin' && $followup->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($followup->job_id !== $job->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Followup does not belong to this job'
                ], 400);
            }

            $followup->delete();

            Log::info('Job followup deleted', [
                'job_id' => $job->id,
                'followup_id' => $followup->id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete job followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting followup'
            ], 500);
        }
    }

    /**
     * Add call log to job
     */
    public function addCall(Request $request, Job $job)
    {
        try {
            $validated = $request->validate([
                'call_date' => 'required|date',
                'duration' => 'nullable|integer|min:0',
                'outcome' => 'required|in:completed,rescheduled,issue_reported,follow_up_needed,no_answer,other',
                'notes' => 'nullable|string|max:1000',
                'followup_date' => 'nullable|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'callback_time_preference' => 'nullable|in:morning,afternoon,evening,anytime',
                'followup_priority' => 'nullable|in:low,medium,high',
                'followup_notes' => 'nullable|string|max:1000',
            ]);

            // Create call log
            JobCall::create([
                'job_id' => $job->id,
                'user_id' => auth()->id(),
                'call_date' => $validated['call_date'],
                'duration' => $validated['duration'] ?? null,
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $followupCreated = false;

            // Create followup if needed
            if (in_array($validated['outcome'], ['follow_up_needed', 'rescheduled']) && isset($validated['followup_date'])) {
                JobFollowup::create([
                    'job_id' => $job->id,
                    'followup_date' => $validated['followup_date'],
                    'followup_time' => $validated['followup_time'] ?? null,
                    'callback_time_preference' => $validated['callback_time_preference'] ?? 'anytime',
                    'priority' => $validated['followup_priority'] ?? 'medium',
                    'notes' => $validated['followup_notes'] ?? null,
                    'assigned_to' => $job->assigned_to ?? auth()->id(),
                    'created_by' => auth()->id(),
                    'status' => 'pending',
                ]);

                $followupCreated = true;
            }

            Log::info('Job call logged', [
                'job_id' => $job->id,
                'user_id' => auth()->id(),
                'followup_created' => $followupCreated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully!',
                'followup_created' => $followupCreated,
            ]);

        } catch (\Exception $e) {
            Log::error('Add job call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a call log
     */
    public function deleteCall(Job $job, JobCall $call)
    {
        try {
            $user = auth()->user();

            // Authorization
            if (!in_array($user->role, ['super_admin', 'lead_manager']) && $call->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($call->job_id !== $job->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call does not belong to this job'
                ], 400);
            }

            $call->delete();

            Log::info('Job call deleted', [
                'job_id' => $job->id,
                'call_id' => $call->id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call log deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete job call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting call log'
            ], 500);
            }
    }

    /**
     * Add note to job
     */
    public function addNote(Request $request, Job $job)
    {
        try {
            $validated = $request->validate([
                'note' => 'required|string|max:1000',
            ]);

            JobNote::create([
                'job_id' => $job->id,
                'created_by' => auth()->id(),
                'note' => $validated['note'],
            ]);

            Log::info('Job note added', [
                'job_id' => $job->id,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Add job note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a note
     */
    public function deleteNote(Job $job, JobNote $note)
    {
        try {
            $user = auth()->user();

            // Only super_admin or creator can delete
            if ($user->role !== 'super_admin' && $note->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($note->job_id !== $job->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note does not belong to this job'
                ], 400);
            }

            $note->delete();

            Log::info('Job note deleted', [
                'job_id' => $job->id,
                'note_id' => $note->id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete job note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting note'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $user = auth()->user();

            // Authorization check
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'field_staff', 'telecallers'])) {
                abort(403, 'Unauthorized');
            }

            // Build query with same filters as index
            $query = Job::with(['customer', 'services', 'service', 'branch', 'assignedTo', 'createdBy', 'lead']);

            // APPLY ALL FILTERS (same as index method)
            $status = $request->input('status');

            if ($status === 'approved') {
                $query->where('status', 'approved');
            } elseif ($status === 'confirmed') {
                $query->where('status', 'confirmed');
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($status && $status !== '') {
                $query->where('status', $status);
            }

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->filled('service_id')) {
                $serviceId = $request->service_id;
                $query->whereHas('services', function($subQuery) use ($serviceId) {
                    $subQuery->where('services.id', $serviceId);
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('scheduled_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('scheduled_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('job_code', 'like', $search)
                        ->orWhere('location', 'like', $search)
                        ->orWhereHas('customer', function($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', $search)
                                ->orWhere('customer_code', 'like', $search)
                                ->orWhere('phone', 'like', $search);
                        });
                });
            }

            // Role-based filtering
            if ($user->role === 'field_staff') {
                $query->where('assigned_to', $user->id);
            }

            if ($user->role === 'telecallers') {
                $query->where('assigned_to', $user->id);
            }

            // Check total count
            $totalCount = $query->count();
            $exportLimit = 10000;

            Log::info('Job export started', [
                'user_id' => $user->id,
                'filters' => $request->all(),
                'total_jobs' => $totalCount,
                'exported_jobs' => min($totalCount, $exportLimit)
            ]);

            if ($totalCount > $exportLimit) {
                session()->flash('warning', "Only the first {$exportLimit} jobs will be exported. Total: {$totalCount}.");
            }

            // Generate filename
            $fileName = 'jobs_export_' . now()->format('Y-m-d_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($query, $exportLimit) {
                $file = fopen('php://output', 'w');

                // Add UTF-8 BOM for Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // CSV Headers
                fputcsv($file, [
                    'Job Code',
                    'Title',
                    'Customer Name',
                    'Customer Code',
                    'Customer Phone',
                    'Services',
                    'Service Type',
                    'Branch',
                    'Status',
                    'Amount',
                    'Amount Paid',
                    'Balance',
                    'Add-on Price',
                    'Location',
                    'Scheduled Date',
                    'Scheduled Time',
                    'Assigned To',
                    'Created By',
                    'Approved By',
                    'Approved At',
                    'Completed At',
                    'Created At',
                    'Description',
                    'Customer Instructions'
                ]);

                // Data rows with limit
                $query->limit($exportLimit)->chunk(1000, function($jobs) use ($file) {
                    foreach ($jobs as $job) {
                        // Get services list
                        $servicesList = $job->services->pluck('name')->join(', ');

                        // Calculate balance
                        $balance = ($job->amount ?? 0) - ($job->amount_paid ?? 0);

                        // Format status
                        $statusLabel = ucfirst(str_replace('_', ' ', $job->status));

                        fputcsv($file, [
                            $job->job_code,
                            $job->title,
                            optional($job->customer)->name ?? '',
                            optional($job->customer)->customer_code ?? '',
                            optional($job->customer)->phone ?? '',
                            $servicesList,
                            optional($job->service)->service_type ?? '',
                            optional($job->branch)->name ?? '',
                            $statusLabel,
                            $job->amount ? number_format($job->amount, 2) : '0.00',
                            $job->amount_paid ? number_format($job->amount_paid, 2) : '0.00',
                            number_format($balance, 2),
                            $job->addon_price ? number_format($job->addon_price, 2) : '0.00',
                            $job->location ?? '',
                            $job->scheduled_date ? $job->scheduled_date->format('Y-m-d') : '',
                            $job->scheduled_time ?? '',
                            optional($job->assignedTo)->name ?? 'Unassigned',
                            optional($job->createdBy)->name ?? '',
                            optional($job->approvedBy)->name ?? '',
                            $job->approved_at ? $job->approved_at->format('Y-m-d H:i:s') : '',
                            $job->completed_at ? $job->completed_at->format('Y-m-d H:i:s') : '',
                            $job->created_at->format('Y-m-d H:i:s'),
                            $job->description ?? '',
                            $job->customer_instructions ?? ''
                        ]);
                    }
                });

                fclose($file);
            };

            return response()->streamDownload($callback, $fileName, $headers);

        } catch (\Exception $e) {
            Log::error('Job export error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withError('Error exporting jobs: ' . $e->getMessage());
        }
    }

    // ─── ADD STAFF ─────────────────────────────────────────────────────────────

    public function addStaff(Request $request, Job $job)
    {
        try {
            if (!in_array($job->status, ['completed', 'staff_pending_approval'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff can only be added to completed work orders.'
                ], 400);
            }

            if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'staff_type' => 'required|in:registered,temporary',
                'role'       => 'required|in:supervisor,worker',
                'user_id'    => 'required_if:staff_type,registered|nullable|exists:users,id',
                'temp_name'  => 'required_if:staff_type,temporary|nullable|string|max:255',
                'temp_phone' => 'nullable|string|max:20',
                'notes'      => 'nullable|string|max:500',
            ]);

            // Validate the registered user actually has the correct role
            if ($validated['staff_type'] === 'registered' && !empty($validated['user_id'])) {
                $selectedUser = User::find($validated['user_id']);
                if ($selectedUser && $selectedUser->role !== $validated['role']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Selected user is not a {$validated['role']}."
                    ], 422);
                }
            }

            JobStaff::create([
                ...$validated,
                'job_id'   => $job->id,
                'added_by' => auth()->id(),
                // ✅ NO approval_status here — removed
            ]);

            // ✅ Flip the whole work order to pending approval
            $job->update(['status' => 'staff_pending_approval']);

            Log::info('Staff added to job — awaiting approval', [
                'job_id' => $job->id, 'added_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Staff added. Work order is now pending super admin approval.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── APPROVE STAFF ─────────────────────────────────────────────────────────

    public function approveStaff(Request $request, Job $job)
    {
        try {
            if (auth()->user()->role !== 'super_admin') {
                return response()->json(['success' => false, 'message' => 'Only Super Admin can approve.'], 403);
            }

            if ($job->status !== 'staff_pending_approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'This work order is not pending staff approval.'
                ], 400);
            }

            $validated = $request->validate([
                'action' => 'required|in:approve,reject',
            ]);

            if ($validated['action'] === 'approve') {
                $job->update([
                    'status'      => 'completed',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                $message = 'Staff approved. Work order restored to Completed.';
            } else {
                // Reject — wipe all staff and restore
                $job->staff()->delete();
                $job->update(['status' => 'completed']);
                $message = 'Staff rejected and removed. Work order restored to Completed.';
            }

            Log::info('Job staff approval', [
                'job_id' => $job->id, 'action' => $validated['action'], 'by' => auth()->id(),
            ]);

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── DELETE STAFF ───────────────────────────────────────────────────────────

    public function deleteStaff(Job $job, JobStaff $staff)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'super_admin' && $staff->added_by !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $staff->delete();

            // ✅ If no staff remain, restore completed status
            if ($job->status === 'staff_pending_approval' && $job->staff()->count() === 0) {
                $job->update(['status' => 'completed']);
            }

            return response()->json(['success' => true, 'message' => 'Staff removed successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── ADD / UPDATE RATING ───────────────────────────────────────────────────

    public function addRating(Request $request, Job $job)
    {
        try {
            if (!in_array($job->status, ['completed', 'staff_pending_approval'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating can only be added to completed work orders.'
                ], 400);
            }

            $validated = $request->validate([
                'rating'   => 'required|integer|min:1|max:5',
                'feedback' => 'nullable|string|max:1000',
            ]);

            $rating = JobRating::updateOrCreate(
                ['job_id' => $job->id],
                [
                    'customer_id'   => $job->customer_id,
                    'rating'        => $validated['rating'],
                    'feedback'      => $validated['feedback'] ?? null,
                    'rated_by_type' => 'admin',
                    'rated_by'      => auth()->id(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rating saved successfully!',
                'rating'  => $rating,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function completedOrders(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
            abort(403, 'Unauthorized');
        }

        $query = Job::with(['customer', 'services', 'branch', 'assignedTo', 'staff.user', 'rating'])
            ->whereIn('status', ['completed', 'staff_pending_approval']);

        if ($user->role !== 'super_admin') {
            $query->where('branch_id', $user->branch_id);
        }

        // Status filter
        $statusFilter = $request->input('status');
        if ($statusFilter === 'staff_pending_approval') {
            $query->where('status', 'staff_pending_approval');
        } elseif ($statusFilter === 'completed') {
            $query->where('status', 'completed');
        }

        // Branch filter
        if ($request->filled('branch_id') && $user->role === 'super_admin') {
            $query->where('branch_id', $request->branch_id);
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                ->orWhere('job_code', 'like', $search)
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', $search))
                ->orWhereHas('customer', fn($b) => $b->where('phone', 'like', $search));
            });
        }

        $query->orderByRaw("FIELD(status, 'staff_pending_approval', 'completed')")
            ->orderBy('completed_at', 'desc');

        $jobs = $query->paginate(15);

        $branches       = \App\Models\Branch::all();
        $staffPending   = \App\Models\Job::where('status', 'staff_pending_approval')->count();
        $totalCompleted = \App\Models\Job::where('status', 'completed')->count();

        // ── FIX: pass supervisors & workers so the modal dropdown works ──────────
        $supervisors = \App\Models\User::where('role', 'supervisor')
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'branch_id']);

        $workers = \App\Models\User::where('role', 'worker')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'branch_id']);

        // kept for any re-assign needs
        $fieldstaff  = \App\Models\User::where('role', 'field_staff')
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'branch_id']);

        $telecallers = \App\Models\User::where('role', 'telecallers')
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'branch_id']);

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'html'            => view('jobs.partials.completed-rows', compact('jobs'))->render(),
                'pagination'      => $jobs->links('pagination::bootstrap-5')->render(),
                'total'           => $jobs->total(),
                'staff_pending'   => $staffPending,
                'total_completed' => $totalCompleted,
            ]);
        }

        return view('jobs.completed', compact(
            'jobs',
            'branches',
            'staffPending',
            'totalCompleted',
            'supervisors',   // ← was missing
            'workers',       // ← was missing
            'fieldstaff',
            'telecallers'
        ));
    }

    public function bulkStore(Request $request, Job $job)
    {
        if (!in_array($job->status, ['completed', 'staff_pending_approval'])) {
            return response()->json(['success' => false, 'message' => 'Staff can only be added to completed work orders.'], 400);
        }

        if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'staff'              => 'required|array|min:1',
            'staff.*.role'       => 'required|in:supervisor,worker',
            'staff.*.staff_type' => 'required|in:registered,temporary',
            'staff.*.user_id'    => 'required_if:staff.*.staff_type,registered|nullable|exists:users,id',
            'staff.*.temp_name'  => 'required_if:staff.*.staff_type,temporary|nullable|string|max:255',
            'staff.*.notes'      => 'nullable|string|max:500',
        ]);

        foreach ($request->staff as $entry) {
            // Validate registered user actually has the correct role
            if ($entry['staff_type'] === 'registered' && !empty($entry['user_id'])) {
                $selectedUser = User::find($entry['user_id']);
                if ($selectedUser && $selectedUser->role !== $entry['role']) {
                    return response()->json([
                        'success' => false,
                        'message' => "User {$selectedUser->name} is not a {$entry['role']}."
                    ], 422);
                }
            }

            $job->staff()->create([
                'user_id'    => $entry['staff_type'] === 'registered' ? $entry['user_id'] : null,
                'temp_name'  => $entry['staff_type'] === 'temporary'  ? $entry['temp_name'] : null,
                'temp_phone' => $entry['staff_type'] === 'temporary'  ? $entry['temp_phone'] : null,
                'role'       => $entry['role'],
                'staff_type' => $entry['staff_type'],
                'notes'      => $entry['notes'] ?? null,
                'added_by'   => auth()->id(),
            ]);
        }

        // Flip the whole job to pending approval — same as single addStaff()
        $job->update(['status' => 'staff_pending_approval']);

        return response()->json([
            'success' => true,
            'message' => count($request->staff) . ' staff member(s) added. Work order is now pending super admin approval.',
        ]);
    }

}
