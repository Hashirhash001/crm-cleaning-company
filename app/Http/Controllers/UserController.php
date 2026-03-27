<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();

        $query = User::with('branch')
            ->where('id', '!=', auth()->id());

        // Role-based access control
        if ($currentUser->role === 'lead_manager') {
            // Lead managers can ONLY see telecallers from their branch
            $query->where('branch_id', $currentUser->branch_id)
                ->where('role', 'telecallers');
        } elseif ($currentUser->role === 'telecallers') {
            // Telecallers cannot access user management (optional - add abort if needed)
            abort(403, 'Unauthorized access');
        }
        // Super admin sees everyone (default - no additional filter)

        // Apply filters (note: role filter should respect lead_manager restrictions)
        if ($request->filled('role')) {
            // If lead_manager, ignore role filter since they can only see telecallers
            if ($currentUser->role !== 'lead_manager') {
                $query->where('role', $request->role);
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        $branches = Branch::where('is_active', true)->get();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('users.partials.table-rows', compact('users'))->render(),
                'pagination' => $users->links('pagination::bootstrap-5')->render(),
                'total' => $users->total()
            ]);
        }

        return view('users.index', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'branch_id' => in_array(request()->input('role'), ['supervisor', 'super_admin', 'worker', 'field_staff'])
                ? 'nullable|exists:branches,id'
                : 'required|exists:branches,id',

            'role' => 'required|in:super_admin,lead_manager,field_staff,telecallers,supervisor,worker',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!'
        ]);
    }

    public function edit(User $user)
    {
        // Prevent editing own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit your own account!'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Prevent updating own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot update your own account!'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'branch_id' => in_array(request()->input('role'), ['supervisor', 'super_admin', 'worker', 'field_staff'])
                ? 'nullable|exists:branches,id'
                : 'required|exists:branches,id',

            'role' => 'required|in:super_admin,lead_manager,field_staff,telecallers,supervisor,worker',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!'
        ]);
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }

    public function show(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'lead_manager') {
            if ($user->branch_id !== $currentUser->branch_id) {
                abort(403, 'You can only view users from your branch.');
            }
        }

        $user->load([
            'branch',
            'assignedJobs.customer',
            'assignedJobs.services',
            'assignedJobs.branch',
            'assignedJobs.lead',

            // Leads (make sure jobs are eager loaded for conversion counts)
            'createdLeads.services',
            'createdLeads.jobs',
            'assignedLeads.jobs',
            'assignedLeads.services',

            'createdJobs.services',
            'createdJobs.customer',
        ]);

        // ==================== JOB STATISTICS ====================

        $assignedJobs = $user->assignedJobs;
        $totalAssignedJobs = $assignedJobs->count();
        $pendingJobs = $assignedJobs->whereIn('status', ['pending', 'assigned'])->count();
        $confirmedJobs = $assignedJobs->where('status', 'confirmed')->count();
        $approvedJobs = $assignedJobs->where('status', 'approved')->count();
        $inProgressJobs = $assignedJobs->where('status', 'in_progress')->count();
        $completedJobs = $assignedJobs->where('status', 'completed')->count();
        $cancelledJobs = $assignedJobs->where('status', 'cancelled')->count();

        $approvedAndCompletedJobs = $assignedJobs->whereIn('status', ['approved', 'completed']);

        $totalJobsValue = $approvedAndCompletedJobs->sum('amount');
        $completedJobsValue = $approvedAndCompletedJobs->where('status', 'completed')->sum('amount');
        $approvedJobsValue = $approvedAndCompletedJobs->where('status', 'approved')->sum('amount');
        $totalAmountCollected = $approvedAndCompletedJobs->sum('amount_paid');
        $totalAddonValue      = $approvedAndCompletedJobs->sum('addon_price');

        $totalDueAmount = $approvedAndCompletedJobs->sum(function ($job) {
            return max(0, $job->amount - $job->amount_paid);
        });

        $pendingJobsList = $assignedJobs->whereIn('status', ['pending', 'assigned'])->take(20);
        $confirmedJobsList = $assignedJobs->where('status', 'confirmed')->take(20);
        $approvedJobsList = $assignedJobs->where('status', 'approved')->take(20);
        $completedJobsList = $assignedJobs->where('status', 'completed')->take(20);

        // ==================== ASSIGNED LEADS STATISTICS ====================

        $assignedLeads = $user->assignedLeads;
        $totalAssignedLeads = $assignedLeads->count();
        $assignedConfirmedLeads = $assignedLeads->where('status', 'confirmed')->count();

        // ✅ Converted = has at least 1 job (NOT job_id)
        $assignedLeadsConverted = $assignedLeads->filter(function ($lead) {
            return $lead->jobs && $lead->jobs->count() > 0;
        });

        $assignedLeadsConvertedToJobs = $assignedLeadsConverted->count();

        $assignedLeadsValue = $assignedLeads->sum('amount');
        $assignedConfirmedLeadsValue = $assignedLeads->where('status', 'confirmed')->sum('amount');
        $assignedApprovedLeadsValue = $assignedLeadsConverted->sum('amount');

        $assignedConversionRate = $totalAssignedLeads > 0
            ? round(($assignedLeadsConvertedToJobs / $totalAssignedLeads) * 100, 2)
            : 0;

        $assignedAllLeadsList = $assignedLeads->take(20);
        $assignedConfirmedLeadsList = $assignedLeads->where('status', 'confirmed')->take(20);
        $assignedConvertedLeadsList = $assignedLeadsConverted->take(20);

        // ==================== CREATED JOBS STATISTICS ====================

        $createdJobs = $user->createdJobs;
        $totalCreatedJobs = $createdJobs->count();
        $createdPendingJobs = $createdJobs->whereIn('status', ['pending', 'assigned'])->count();
        $createdConfirmedJobs = $createdJobs->where('status', 'confirmed')->count();
        $createdApprovedJobs = $createdJobs->where('status', 'approved')->count();
        $createdCompletedJobs = $createdJobs->where('status', 'completed')->count();

        $createdJobsValue = $createdJobs->sum('amount');
        $createdApprovedJobsValue = $createdJobs->where('status', 'approved')->sum('amount');
        $createdCompletedValue = $createdJobs->where('status', 'completed')->sum('amount');

        $createdPendingJobsList = $createdJobs->whereIn('status', ['pending', 'assigned'])->take(20);
        $createdConfirmedJobsList = $createdJobs->where('status', 'confirmed')->take(20);
        $createdApprovedJobsList = $createdJobs->where('status', 'approved')->take(20);
        $createdCompletedJobsList = $createdJobs->where('status', 'completed')->take(20);

        // ==================== CREATED LEADS STATISTICS ====================

        $createdLeads = $user->createdLeads;
        $totalCreatedLeads = $createdLeads->count();
        $pendingLeads = $createdLeads->where('status', 'pending')->count();
        $confirmedLeads = $createdLeads->where('status', 'confirmed')->count();
        $approvedLeads = $createdLeads->where('status', 'approved')->count();
        $rejectedLeads = $createdLeads->where('status', 'rejected')->count();

        $totalLeadsValue = $createdLeads->sum('amount');
        $confirmedLeadsValue = $createdLeads->where('status', 'confirmed')->sum('amount');

        // ✅ Converted = has at least 1 job (NOT job_id)
        $createdLeadsConverted = $createdLeads->filter(function ($lead) {
            return $lead->jobs && $lead->jobs->count() > 0;
        });

        $leadsConvertedToJobs = $createdLeadsConverted->count();
        $approvedLeadsValue = $createdLeadsConverted->sum('amount');

        $totalAdvancePaid = $createdLeads->sum('advance_paid_amount');

        $conversionRate = $totalCreatedLeads > 0
            ? round(($leadsConvertedToJobs / $totalCreatedLeads) * 100, 2)
            : 0;

        $pendingLeadsList = $createdLeads->where('status', 'pending')->take(20);
        $allCreatedLeadsList = $createdLeads->take(20);
        $confirmedLeadsList = $createdLeads->where('status', 'confirmed')->take(20);
        $approvedLeadsList = $createdLeads->where('status', 'approved')->take(20);
        $rejectedLeadsList = $createdLeads->where('status', 'rejected')->take(20);
        $convertedLeadsList = $createdLeadsConverted->take(20);

        // ==================== SUPERVISOR STATISTICS ====================
        $supervisorJobsCount  = 0;
        $supervisorJobsValue  = 0;
        $supervisorAddonValue = 0;

        if ($user->role === 'supervisor') {
            $supervisorJobIds = \App\Models\JobStaff::where('user_id', $user->id)
                ->whereHas('job', function ($q) {
                    $q->whereIn('status', ['completed', 'staff_pending_approval']);
                })
                ->pluck('job_id');

            $supervisorJobsCount  = $supervisorJobIds->count();
            $supervisorJobsValue  = \App\Models\Job::whereIn('id', $supervisorJobIds)->sum('amount');
            $supervisorAddonValue = \App\Models\Job::whereIn('id', $supervisorJobIds)->sum('addon_price');
        }

        return view('users.show', compact(
            'user',
            'totalAssignedJobs',
            'pendingJobs',
            'confirmedJobs',
            'approvedJobs',
            'inProgressJobs',
            'completedJobs',
            'cancelledJobs',
            'totalJobsValue',
            'completedJobsValue',
            'approvedJobsValue',
            'totalAmountCollected',
            'totalDueAmount',
            'approvedAndCompletedJobs',
            'pendingJobsList',
            'confirmedJobsList',
            'approvedJobsList',
            'completedJobsList',

            'totalAssignedLeads',
            'assignedConfirmedLeads',
            'assignedLeadsValue',
            'assignedConfirmedLeadsValue',
            'assignedApprovedLeadsValue',
            'assignedLeadsConvertedToJobs',
            'assignedConversionRate',
            'assignedAllLeadsList',
            'assignedConfirmedLeadsList',
            'assignedConvertedLeadsList',

            'totalCreatedJobs',
            'createdPendingJobs',
            'createdConfirmedJobs',
            'createdApprovedJobs',
            'createdCompletedJobs',
            'createdJobsValue',
            'createdApprovedJobsValue',
            'createdCompletedValue',
            'createdPendingJobsList',
            'createdConfirmedJobsList',
            'createdApprovedJobsList',
            'createdCompletedJobsList',

            'totalCreatedLeads',
            'pendingLeads',
            'confirmedLeads',
            'approvedLeads',
            'rejectedLeads',
            'totalLeadsValue',
            'confirmedLeadsValue',
            'approvedLeadsValue',
            'totalAdvancePaid',
            'leadsConvertedToJobs',
            'conversionRate',
            'pendingLeadsList',
            'allCreatedLeadsList',
            'confirmedLeadsList',
            'approvedLeadsList',
            'rejectedLeadsList',
            'convertedLeadsList',
            'totalAddonValue',
            'supervisorJobsCount',
            'supervisorJobsValue',
            'supervisorAddonValue'
        ));
    }

    public function getDetails(User $user, $type, Request $request)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'lead_manager') {
            if ($user->branch_id !== $currentUser->branch_id) {
                abort(403);
            }
        }

        $perPage = 15;
        $data = null;
        $view = '';

        switch ($type) {

            // ==================== ASSIGNED JOBS ====================

            case 'all':
                $data = $user->assignedJobs()
                    ->with(['customer', 'services'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'pending':
                $data = $user->assignedJobs()
                    ->with(['customer', 'services'])
                    ->whereIn('status', ['pending', 'assigned'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'confirmed':
                $data = $user->assignedJobs()
                    ->with(['customer', 'services'])
                    ->where('status', 'confirmed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'approved':
                $data = $user->assignedJobs()
                    ->with(['customer', 'services'])
                    ->where('status', 'approved')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'completed':
                $data = $user->assignedJobs()
                    ->with(['customer', 'services'])
                    ->where('status', 'completed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'due':
                $data = $user->assignedJobs()
                    ->with(['customer'])
                    ->where('status', 'approved')
                    ->whereRaw('amount > amount_paid')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.due-panel';
                break;

            case 'due_combined':
                $data = $user->assignedJobs()
                    ->with(['customer', 'services'])
                    ->whereIn('status', ['approved', 'completed'])
                    ->whereRaw('amount > amount_paid')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.due-panel';
                break;

            // ==================== ASSIGNED LEADS ====================

            case 'assigned_all_leads':
                $data = $user->assignedLeads()
                    ->with(['services', 'jobs'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'assigned_confirmed_leads':
                $data = $user->assignedLeads()
                    ->with(['services', 'jobs'])
                    ->where('status', 'confirmed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'assigned_converted_leads':
                $data = $user->assignedLeads()
                    ->with(['services', 'jobs'])
                    ->whereHas('jobs') // ✅ converted = has at least 1 job
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            // ==================== CREATED LEADS ====================

            case 'all_leads':
                $data = $user->createdLeads()
                    ->with(['services', 'jobs'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'pending_leads':
                $data = $user->createdLeads()
                    ->with(['services', 'jobs'])
                    ->where('status', 'pending')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'confirmed_leads':
                $data = $user->createdLeads()
                    ->with(['services', 'jobs'])
                    ->where('status', 'confirmed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'approved_leads':
                $data = $user->createdLeads()
                    ->with(['services', 'jobs'])
                    ->where('status', 'approved')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'rejected_leads':
                $data = $user->createdLeads()
                    ->with(['services', 'jobs'])
                    ->where('status', 'rejected')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            case 'converted_leads':
                $data = $user->createdLeads()
                    ->with(['services', 'jobs'])
                    ->whereHas('jobs') // ✅ converted = has at least 1 job
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;

            // ==================== CREATED JOBS ====================

            case 'all_created_jobs':
                $data = $user->createdJobs()
                    ->with(['customer', 'services'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'created_confirmed':
                $data = $user->createdJobs()
                    ->with(['customer', 'services'])
                    ->where('status', 'confirmed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'created_approved':
                $data = $user->createdJobs()
                    ->with(['customer', 'services'])
                    ->where('status', 'approved')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            case 'created_completed':
                $data = $user->createdJobs()
                    ->with(['customer', 'services'])
                    ->where('status', 'completed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.jobs-panel';
                break;

            default:
                abort(404);
        }

        if ($view === 'users.partials.leads-panel') {
            $html = view($view, ['leads' => $data])->render();
        } elseif ($view === 'users.partials.due-panel') {
            $html = view($view, ['jobs' => $data])->render();
        } else {
            $html = view($view, ['jobs' => $data])->render();
        }

        return response()->json(['html' => $html]);
    }

    public function performance()
    {
        return view('users.performance');
    }

    public function performanceData(Request $request)
    {
        $user = auth()->user();

        $period    = $request->get('period', 'month');
        $startDate = null;
        $endDate   = null;

        switch ($period) {
            case 'day':
                $startDate = Carbon::today();
                $endDate   = Carbon::now();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now();
                break;
            case 'lastmonth':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case '6months':
                $startDate = Carbon::now()->subMonths(6);
                $endDate   = Carbon::now();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate   = Carbon::now();
                break;
            case 'lastyear':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate   = Carbon::now()->subYear()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date')
                    ? Carbon::parse($request->get('start_date'))
                    : Carbon::now()->startOfMonth();
                $endDate   = $request->get('end_date')
                    ? Carbon::parse($request->get('end_date'))
                    : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now();
        }

        $usersQuery = User::where('is_active', true)
            ->whereNotIn('role', ['super_admin', 'lead_manager', 'worker']);

        if ($request->filled('branch_id')) {
            $usersQuery->where('branch_id', $request->branch_id);
        }

        if ($request->filled('role_filter')) {
            $usersQuery->where('role', $request->role_filter);
        }

        $users = $usersQuery->with('branch')->get();

        $leaderboard = $users->map(function ($user) use ($startDate, $endDate) {

            $role = $user->role;

            // ── Leads metrics (count only) ─────────────────────────────────────
            $leadsCreated   = 0;
            $leadsConverted = 0;
            $conversionRate = 0;

            if (in_array($role, ['telecallers', 'field_staff'])) {
                $leadsCreated = $user->createdLeads()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $leadsConverted = $user->createdLeads()
                    ->whereHas('jobs')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $conversionRate = $leadsCreated > 0
                    ? round($leadsConverted / $leadsCreated * 100, 2) : 0;
            }

            // ── Telecaller / Field Staff — assigned jobs ───────────────────────
            $jobsApproved    = 0;
            $jobsValue       = 0;
            $telecallerAddon = 0;

            if (in_array($role, ['telecallers', 'field_staff'])) {
                $jobDateFilter = function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('approved_at', [$startDate, $endDate])
                    ->orWhereBetween('completed_at', [$startDate, $endDate]);
                };

                $jobsApproved = $user->assignedJobs()
                    ->whereIn('status', ['approved', 'completed'])
                    ->where($jobDateFilter)
                    ->count();

                $jobsValue = $user->assignedJobs()
                    ->whereIn('status', ['approved', 'completed'])
                    ->where($jobDateFilter)
                    ->sum('amount');  // base amount only

                $telecallerAddon = $user->assignedJobs()
                    ->whereIn('status', ['approved', 'completed'])
                    ->where($jobDateFilter)
                    ->sum('addon_price');  // addon separate
            }

            // ── Supervisor — jobs from job_staff pivot ─────────────────────────
            $staffJobsCount  = 0;
            $staffJobsValue  = 0;
            $staffAddonValue = 0;
            $avgRating       = null;

            if (in_array($role, ['supervisor', 'worker'])) {
                $staffJobIds = \App\Models\JobStaff::where('user_id', $user->id)
                    ->whereHas('job', function ($q) use ($startDate, $endDate) {
                        $q->whereIn('status', ['completed', 'staff_pending_approval'])
                            ->whereBetween('completed_at', [$startDate, $endDate]);
                    })
                    ->pluck('job_id');

                $staffJobsCount  = $staffJobIds->count();
                $staffJobsValue  = \App\Models\Job::whereIn('id', $staffJobIds)->sum('amount');       // base only
                $staffAddonValue = \App\Models\Job::whereIn('id', $staffJobIds)->sum('addon_price');  // addon separate
                $avgRating       = \App\Models\JobRating::whereIn('job_id', $staffJobIds)->avg('rating');

                $jobsApproved = $staffJobsCount;
                $jobsValue    = $staffJobsValue;  // base only
            }

            // ── Addon and Total ────────────────────────────────────────────────
            $addonValue = in_array($role, ['telecallers', 'field_staff'])
                ? $telecallerAddon
                : $staffAddonValue;

            $totalValue = $jobsValue + $addonValue;  // base + addon

            return [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'role'            => $user->role,
                'branch'          => $user->branch->name ?? 'N/A',
                'leads_created'   => (int)   $leadsCreated,
                'leads_converted' => (int)   $leadsConverted,
                'conversion_rate' => (float) $conversionRate,
                'jobs_approved'   => (int)   $jobsApproved,
                'jobs_value'      => (float) $jobsValue,   // base amount only
                'addon_value'     => (float) $addonValue,  // addon only
                'leads_value'     => 0,
                'total_value'     => (float) $totalValue,  // base + addon
                'avg_rating'      => $avgRating ? (float) round($avgRating, 1) : null,
                'staff_jobs'      => (int)   $staffJobsCount,
            ];
        });

        $activeLeaderboard = $leaderboard->filter(function ($u) {
            return $u['total_value'] > 0
                || $u['leads_created'] > 0
                || $u['jobs_approved'] > 0;
        });

        $sorted = $activeLeaderboard->sortByDesc('total_value')->values();

        $ranked = $sorted->map(function ($u, $index) {
            $u['rank'] = $index + 1;
            return $u;
        });

        // ── Accurate summary stats ─────────────────────────────────────────────
        $totalLeadsCreated   = $ranked->sum('leads_created');
        $totalLeadsConverted = $ranked->sum('leads_converted');

        $summary = [
            'total_leads_created'   => $totalLeadsCreated,
            'total_leads_converted' => $totalLeadsConverted,
            'total_jobs_approved'   => $ranked->sum('jobs_approved'),
            'total_value'           => $ranked->sum('total_value'),
            'avg_conversion_rate'   => $totalLeadsCreated > 0
                ? round($totalLeadsConverted / $totalLeadsCreated * 100, 2)
                : 0,
        ];

        if ($request->get('export') === 'csv') {
            return $this->exportPerformanceCsv($ranked, $startDate, $endDate, $period);
        }

        return response()->json([
            'success'     => true,
            'leaderboard' => $ranked,
            'summary'     => $summary,
            'period'      => $period,
            'start_date'  => $startDate->format('Y-m-d'),
            'end_date'    => $endDate->format('Y-m-d'),
        ]);
    }

    // ── CSV Export helper ──────────────────────────────────────────────────────
    private function exportPerformanceCsv($leaderboard, $startDate, $endDate, $period)
    {
        $fileName = 'performance_' . $period . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($leaderboard, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Rank',
                'Name',
                'Email',
                'Role',
                'Branch',
                'Leads Created',
                'Leads Converted',
                'Conversion Rate (%)',
                'Jobs/Work Orders',
                'Jobs Value (₹)',
                'Addon Value (₹)',
                'Total Value (₹)',
                'Period Start',
                'Period End',
            ]);

            foreach ($leaderboard as $u) {
                fputcsv($file, [
                    $u['rank'],
                    $u['name'],
                    $u['email'],
                    ucfirst(str_replace('_', ' ', $u['role'])),
                    $u['branch'],
                    $u['leads_created'],
                    $u['leads_converted'],
                    $u['conversion_rate'],
                    $u['jobs_approved'],
                    number_format($u['jobs_value'],  2, '.', ''),
                    number_format($u['addon_value'], 2, '.', ''),
                    number_format($u['total_value'], 2, '.', ''),
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
