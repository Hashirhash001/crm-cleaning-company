<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
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

        // Allow telecallers, lead_manager, super_admin, and field_staff to view jobs
        if (!in_array($user->role, ['super_admin', 'lead_manager', 'field_staff', 'telecallers'])) {
            return back()->with('error', 'Unauthorized');
        }

        $query = Job::with(['customer', 'services', 'service', 'branch', 'assignedTo', 'createdBy', 'lead']);

        // Filter by status (including 'confirmed')
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by service - check both single service and multiple services
        if ($request->filled('service_id')) {
            $serviceId = $request->service_id;
            $query->where(function($q) use ($serviceId) {
                // Check single service_id field
                $q->where('service_id', $serviceId)
                // OR check in the many-to-many relationship
                ->orWhereHas('services', function($subQuery) use ($serviceId) {
                    $subQuery->where('services.id', $serviceId);
                });
            });
        }

        // Filter by scheduled date range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // ============================================
        // ENHANCED SEARCH FUNCTIONALITY
        // ============================================
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function($q) use ($search) {
                // Search by job title
                $q->where('title', 'like', "%{$search}%")

                // Search by job code
                ->orWhere('job_code', 'like', "%{$search}%")

                // Search by location
                ->orWhere('location', 'like', "%{$search}%")

                // Search by customer name
                ->orWhereHas('customer', function($customerQuery) use ($search) {
                    $customerQuery->where('name', 'like', "%{$search}%")
                        // Search by customer code
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        // Search by customer phone
                        ->orWhere('phone', 'like', "%{$search}%");
                })

                // Search by lead code (if job has a lead)
                ->orWhereHas('lead', function($leadQuery) use ($search) {
                    $leadQuery->where('lead_code', 'like', "%{$search}%");
                })

                // Search by assigned user name
                ->orWhereHas('assignedTo', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });

            Log::info('Job search query', [
                'search_term' => $search,
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
        }

        // If user is field staff, only show their jobs
        if ($user->role === 'field_staff') {
            $query->where('assigned_to', $user->id);
        }

        // If user is telecaller, show jobs from their assigned leads
        if ($user->role === 'telecallers') {
            $query->whereHas('lead', function($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }

        // ============================================
        // APPLY SORTING
        // ============================================
        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Use the scope for sorting
        $query->sort($sortColumn, $sortDirection);

        // Paginate instead of get()
        $jobs = $query->paginate(15);

        // Get data for filters
        $branches = Branch::all();
        $customers = Customer::all();
        $services = Service::orderBy('name')->get();

        // Fetch telecallers and field staff separately
        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        $field_staff = User::where('role', 'field_staff')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('jobs.partials.table-rows', compact('jobs'))->render(),
                'pagination' => $jobs->links('pagination::bootstrap-5')->render(),
                'total' => $jobs->total(),
                'current_sort' => [
                    'column' => $sortColumn,
                    'direction' => $sortDirection,
                ],
            ]);
        }

        return view('jobs.index', compact('jobs', 'branches', 'customers', 'services', 'field_staff', 'telecallers'));
    }

    public function store(Request $request)
    {
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
                'service_type' => 'required|in:cleaning,pest_control,other',
                'service_ids' => 'required|array|min:1',
                'service_ids.*' => 'exists:services,id',
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
                'job_code' => $jobCode,
                'title' => $validated['title'],
                'customer_id' => $validated['customer_id'] ?? null,
                'service_id' => $validated['service_ids'][0] ?? null, // First service for backward compatibility
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

            // Attach selected services
            $job->services()->sync($validated['service_ids']);

            Log::info('Job created', ['job_id' => $job->id, 'job_code' => $jobCode]);

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

        // Allow telecallers to view jobs
        if (!in_array($user->role, ['super_admin', 'lead_manager', 'field_staff', 'telecallers'])) {
            return back()->with('error', 'Unauthorized');
        }

        $job->load([
            'branch',
            'lead.services',
            'customer.customerNotes.createdBy',
            'customer.customerNotes.job',
            'services', // Load job services
            'service',
            'assignedTo',
            'createdBy'
        ]);

        // Fetch telecallers and field staff separately for assignment
        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        $field_staff = User::where('role', 'field_staff')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        return view('jobs.show', compact('job', 'telecallers', 'field_staff'));
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

            // Get service type from lead or job
            if ($job->lead && $job->lead->service_type) {
                $jobData['service_type'] = $job->lead->service_type;
            } else {
                // Try to infer from services
                $firstService = $job->services->first();
                if ($firstService) {
                    $jobData['service_type'] = $firstService->service_type;
                }
            }

            // Get service IDs
            $jobData['service_ids'] = $job->services->pluck('id')->toArray();

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
                'service_type' => 'nullable|in:cleaning,pest_control,other',
                'service_ids' => 'nullable|array',
                'service_ids.*' => 'exists:services,id',
                'status' => 'nullable|in:pending,confirmed,in_progress,completed,cancelled',
            ]);

            // Track if amount or services changed
            $amountChanged = isset($validated['amount']) && $validated['amount'] != $job->amount;

            // Track if services changed
            $servicesChanged = false;
            if (isset($validated['service_ids'])) {
                $oldServiceIds = $job->services->pluck('id')->sort()->values()->toArray();
                $newServiceIds = collect($validated['service_ids'])->sort()->values()->toArray();
                $servicesChanged = $oldServiceIds != $newServiceIds;
            }

            // Determine new status
            $newStatus = $validated['status'] ?? $job->status;

            // If amount or services changed and job has a lead and is confirmed, set to pending
            if (($amountChanged || $servicesChanged) && $job->lead_id) {
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

            $job->update($updateData);

            // Sync services if provided
            if (isset($validated['service_ids']) && count($validated['service_ids']) > 0) {
                $job->services()->sync($validated['service_ids']);

                // Update single service_id field (use first service for backward compatibility)
                $job->update(['service_id' => $validated['service_ids'][0]]);
            }

            // If job has a related lead, sync changes back to the lead
            if ($job->lead_id && ($amountChanged || $servicesChanged)) {
                $lead = $job->lead;

                $leadUpdateData = [];

                // Update lead amount if job amount changed
                if ($amountChanged && isset($validated['amount'])) {
                    $leadUpdateData['amount'] = $validated['amount'];
                    $leadUpdateData['amount_updated_at'] = now();
                    $leadUpdateData['amount_updated_by'] = $user->id;
                }

                // Update lead if there are changes
                if (!empty($leadUpdateData)) {
                    $lead->update($leadUpdateData);
                }

                // Sync lead services if job services changed
                if ($servicesChanged && isset($validated['service_ids'])) {
                    $lead->services()->sync($validated['service_ids']);
                    $lead->update(['service_id' => $validated['service_ids'][0] ?? null]);
                }

                Log::info('Job edited - related lead updated', [
                    'job_id' => $job->id,
                    'lead_id' => $lead->id,
                    'amount_changed' => $amountChanged,
                    'services_changed' => $servicesChanged,
                ]);
            }

            Log::info('Job updated', [
                'job_id' => $job->id,
                'updated_by' => $user->id,
                'amount_changed' => $amountChanged,
                'services_changed' => $servicesChanged,
                'status_changed_to_pending' => $newStatus === 'pending',
            ]);

            $message = 'Job updated successfully!';
            if (($amountChanged || $servicesChanged) && $job->lead_id) {
                $message .= ' Job status changed to pending for admin approval. Related lead has also been updated.';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Job update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating job: ' . $e->getMessage()
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
                    'message' => 'Only super admin can delete jobs'
                ], 403);
            }

            $jobCode = $job->job_code;
            $job->delete();

            Log::info('Job deleted', ['job_code' => $jobCode, 'deleted_by' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job delete error: ' . $e->getMessage());
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
                'message' => 'Job assigned to ' . $assignedUser->name . ' successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning job'
            ], 500);
        }
    }

    // Confirm job status (admin only)
    public function confirmStatus(Job $job)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admin can confirm job status'
                ], 403);
            }

            $job->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => $user->id,
            ]);

            Log::info('Job status confirmed', [
                'job_id' => $job->id,
                'confirmed_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job status confirmed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Job confirm status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error confirming job status'
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

    public function completeJob(Request $request, Job $job)
    {
        try {
            if (auth()->user()->role !== 'field_staff' || auth()->id() !== $job->assigned_to) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($job->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Job is not in progress'
                ], 400);
            }

            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Job completed', ['job_id' => $job->id]);

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
                'service_type' => 'required|in:cleaning,pest_control,other',
                'service_ids' => 'required|array|min:1',
                'service_ids.*' => 'exists:services,id',
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
                'job_code' => $jobCode,
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

            // Attach selected services
            $job->services()->sync($validated['service_ids']);

            Log::info('Job created for existing customer', [
                'job_id' => $job->id,
                'customer_id' => $validated['customer_id']
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
}
