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
        $query = Job::with(['customer', 'service', 'branch', 'assignedTo', 'createdBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by assigned to
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Filter by scheduled date range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // Search by title or job code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('job_code', 'like', "%{$search}%");
            });
        }

        // If user is field staff, only show their jobs
        if (auth()->user()->role === 'field_staff') {
            $query->where('assigned_to', auth()->id());
        }

        // Paginate instead of get()
        $jobs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get data for filters
        $branches = \App\Models\Branch::all();
        $customers = \App\Models\Customer::all();
        $services = \App\Models\Service::all();
        $field_staff = \App\Models\User::where('role', 'field_staff')->get();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('jobs.partials.table-rows', compact('jobs'))->render(),
                'pagination' => $jobs->links('pagination::bootstrap-5')->render()
            ]);
        }

        return view('jobs.index', compact('jobs', 'branches', 'customers', 'services', 'field_staff'));
    }


    public function store(Request $request)
    {
        try {
            if (auth()->user()->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'customer_id' => 'nullable|exists:customers,id',
                'service_id' => 'nullable|exists:services,id',
                'description' => 'nullable|string',
                'customer_instructions' => 'nullable|string',
                'branch_id' => 'required|exists:branches,id',
                'location' => 'nullable|string',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable|date_format:H:i',
            ]);

            // Generate unique job code
            $jobCount = Job::count();
            $jobCode = 'JOB' . str_pad($jobCount + 1, 3, '0', STR_PAD_LEFT);

            $job = Job::create([
                'job_code' => $jobCode,
                'title' => $validated['title'],
                'customer_id' => $validated['customer_id'] ?? null,
                'service_id' => $validated['service_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'customer_instructions' => $validated['customer_instructions'] ?? null,
                'branch_id' => $validated['branch_id'],
                'location' => $validated['location'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

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
        $job->load([
            'branch',
            'lead',
            'customer.customerNotes.createdBy',
            'customer.customerNotes.job',
            'service',
            'assignedTo',
            'createdBy'
        ]);

        return view('jobs.show', compact('job'));
    }

    public function edit(Job $job)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'super_admin' && $user->role !== 'branch_manager') {
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

            // Super admin has full access, no restrictions
            if ($user->role !== 'super_admin' && $user->role !== 'branch_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'customer_id' => 'nullable|exists:customers,id',
                'service_id' => 'nullable|exists:services,id',
                'branch_id' => 'required|exists:branches,id',
                'assigned_to' => 'nullable|exists:users,id',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable',
                'location' => 'nullable|string',
                'description' => 'nullable|string',
                'customer_instructions' => 'nullable|string',
            ]);

            $job->update($validated);

            Log::info('Job updated', ['job_id' => $job->id, 'updated_by' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Job updated successfully!'
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
                'status' => 'assigned'
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
                'service_id' => 'required|exists:services,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'customer_instructions' => 'nullable|string',
                'branch_id' => 'required|exists:branches,id',
                'location' => 'nullable|string',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable|date_format:H:i',
            ]);

            // Generate unique job code
            $jobCount = Job::count();
            $jobCode = 'JOB' . str_pad($jobCount + 1, 3, '0', STR_PAD_LEFT);

            $job = Job::create([
                'job_code' => $jobCode,
                'customer_id' => $validated['customer_id'],
                'service_id' => $validated['service_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'customer_instructions' => $validated['customer_instructions'] ?? null,
                'branch_id' => $validated['branch_id'],
                'location' => $validated['location'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

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
