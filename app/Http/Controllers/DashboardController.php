<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->query('branch_id');

        if ($user->role === 'super_admin') {
            $branches = Branch::where('is_active', true)->get();

            // Quick Stats - NOT filtered by branch (overall company stats)
            $totalCustomers = Customer::count();
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $pendingLeads = Lead::where('status', 'pending')->count();
            $confirmedLeads = Lead::where('status', 'confirmed')->count();
            $approvedLeads = Lead::where('status', 'approved')->count();
            $pendingWorkOrders = Job::where('status', 'pending')->count();
            $activeJobs = Job::whereIn('status', ['confirmed'])->count();

            // Budget - filtered by branch
            $dailyBudget = Setting::get('daily_budget_limit', 100000);
            $todayTotal = Lead::whereDate('approved_at', today())
                ->where('status', 'approved')
                ->when($branchId, function($q) use ($branchId) {
                    return $q->where('branch_id', $branchId);
                })
                ->sum('amount');
            $remaining = $dailyBudget - $todayTotal;
            $percentage = $dailyBudget > 0 ? ($todayTotal / $dailyBudget) * 100 : 0;

            // Sales - filtered by branch
            $approvedWeekly = Lead::when($branchId, function($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })->where('status', 'approved')
                ->whereBetween('approved_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount');

            $approvedMonthly = Lead::when($branchId, function($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })->where('status', 'approved')
                ->whereYear('approved_at', now()->year)
                ->whereMonth('approved_at', now()->month)
                ->sum('amount');

            // Followup Data - filtered by branch
            $followupData = $this->getFollowupData($user, $branchId);

            // Branch Statistics
            $branchStatistics = Branch::where('is_active', true)->get()->map(function($branch) {
                return [
                    'branch_name' => $branch->name,
                    'total_users' => $branch->users()->count(),
                    'active_users' => $branch->users()->where('is_active', true)->count(),
                    'super_admin_count' => $branch->users()->where('role', 'super_admin')->count(),
                    'lead_manager_count' => $branch->users()->where('role', 'lead_manager')->count(),
                    'field_staff_count' => $branch->users()->where('role', 'field_staff')->count(),
                    'telecallers_count' => $branch->users()->where('role', 'telecallers')->count(),
                ];
            });

            return view('dashboard', compact(
                'totalUsers',
                'activeUsers',
                'branches',
                'branchStatistics',
                'dailyBudget',
                'todayTotal',
                'remaining',
                'percentage',
                'followupData',
                'approvedWeekly',
                'approvedMonthly',
                'totalCustomers',
                'pendingLeads',
                'confirmedLeads',
                'approvedLeads',
                'pendingWorkOrders',
                'activeJobs'
            ));
        }

        if ($user->role === 'lead_manager') {
            $branches = Branch::where('is_active', true)->get();

            // Budget
            $dailyBudget = Setting::get('daily_budget_limit', 100000);
            $todayTotal = Lead::whereDate('approved_at', today())
                ->where('status', 'approved')
                ->where('created_by', $user->id)
                ->when($branchId, function($q) use ($branchId) {
                    return $q->where('branch_id', $branchId);
                })
                ->sum('amount');
            $remaining = $dailyBudget - $todayTotal;
            $percentage = $dailyBudget > 0 ? ($todayTotal / $dailyBudget) * 100 : 0;

            // Sales
            $approvedWeekly = Lead::where('created_by', $user->id)
                ->when($branchId, function($q) use ($branchId) {
                    return $q->where('branch_id', $branchId);
                })
                ->where('status', 'approved')
                ->whereBetween('approved_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount');

            $approvedMonthly = Lead::where('created_by', $user->id)
                ->when($branchId, function($q) use ($branchId) {
                    return $q->where('branch_id', $branchId);
                })
                ->where('status', 'approved')
                ->whereYear('approved_at', now()->year)
                ->whereMonth('approved_at', now()->month)
                ->sum('amount');

            $followupData = $this->getFollowupData($user, $branchId);

            return view('dashboard', compact(
                'dailyBudget',
                'todayTotal',
                'remaining',
                'percentage',
                'followupData',
                'approvedWeekly',
                'approvedMonthly',
                'branches'
            ));
        }

        if ($user->role === 'telecallers') {
            // Telecaller Lead Stats
            $telecallerStats = [
                'total' => Lead::where('assigned_to', $user->id)->count(),
                'pending' => Lead::where('assigned_to', $user->id)->where('status', 'pending')->count(),
                'site_visit' => Lead::where('assigned_to', $user->id)->where('status', 'site_visit')->count(),
                'confirmed' => Lead::where('assigned_to', $user->id)->where('status', 'confirmed')->count(),
                'approved' => Lead::where('assigned_to', $user->id)->where('status', 'approved')->count(),
            ];

            $followupData = $this->getFollowupData($user);

            return view('dashboard', compact('followupData', 'telecallerStats'));
        }

        if ($user->role === 'field_staff') {
            $followupData = $this->getFollowupData($user);

            // Add default values to prevent undefined variable errors
            $dailyBudget = 0;
            $todayTotal = 0;
            $remaining = 0;
            $percentage = 0;
            $approvedWeekly = 0;
            $approvedMonthly = 0;

            return view('dashboard', compact(
                'followupData',
                'dailyBudget',
                'todayTotal',
                'remaining',
                'percentage',
                'approvedWeekly',
                'approvedMonthly'
            ));
        }

        return view('dashboard');
    }

    private function getFollowupData($user, $branchId = null, $dateFrom = null, $dateTo = null)
    {
        // ========================================
        // LEAD FOLLOWUPS QUERY
        // ========================================
        $leadQuery = LeadFollowup::with(['lead.services', 'assignedToUser'])
            ->where('status', 'pending');

        // Role-based filtering for leads - ONLY ASSIGNED FOR NON-SUPER_ADMIN
        if ($user->role === 'super_admin') {
            if ($branchId) {
                $leadQuery->whereHas('lead', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        } else {
            // All other roles: ONLY show followups assigned to them
            $leadQuery->where('assigned_to', $user->id);

            if ($branchId && $user->role === 'lead_manager') {
                $leadQuery->whereHas('lead', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        }

        // Date filter for leads
        if ($dateFrom && $dateTo) {
            $leadQuery->whereBetween('followup_date', [$dateFrom, $dateTo]);
        }

        // ========================================
        // JOB FOLLOWUPS QUERY
        // ========================================
        $jobQuery = \App\Models\JobFollowup::with(['job.customer', 'job.services', 'assignedTo'])
            ->where('status', 'pending');

        // Role-based filtering for jobs - ONLY ASSIGNED FOR NON-SUPER_ADMIN
        if ($user->role === 'super_admin') {
            if ($branchId) {
                $jobQuery->whereHas('job', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        } else {
            // All other roles: ONLY show followups assigned to them
            $jobQuery->where('assigned_to', $user->id);

            if ($branchId && $user->role === 'lead_manager') {
                $jobQuery->whereHas('job', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        }

        // Date filter for jobs
        if ($dateFrom && $dateTo) {
            $jobQuery->whereBetween('followup_date', [$dateFrom, $dateTo]);
        }

        // ========================================
        // COUNTS (Combined) - FIX: Count ONLY overdue, not today
        // ========================================
        $overdueLeads = (clone $leadQuery)->whereDate('followup_date', '<', now()->toDateString())->count();
        $overdueJobs = (clone $jobQuery)->whereDate('followup_date', '<', now()->toDateString())->count();
        $overdue = $overdueLeads + $overdueJobs;

        $todayLeads = (clone $leadQuery)->whereDate('followup_date', today())->count();
        $todayJobs = (clone $jobQuery)->whereDate('followup_date', today())->count();
        $today = $todayLeads + $todayJobs;

        $thisWeekLeads = (clone $leadQuery)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisWeekJobs = (clone $jobQuery)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisWeek = $thisWeekLeads + $thisWeekJobs;

        $thisMonthLeads = (clone $leadQuery)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $thisMonthJobs = (clone $jobQuery)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $thisMonth = $thisMonthLeads + $thisMonthJobs;

        // ========================================
        // IMMEDIATE FOLLOWUPS (Today + Overdue) - PRIORITY FIRST
        // ========================================
        $immediateLeads = (clone $leadQuery)
            ->where('followup_date', '<=', now()->toDateString())
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE WHEN followup_date < ? THEN 0 ELSE 1 END", [now()->toDateString()])
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->get()
            ->map(function($followup) {
                $followup->source_type = 'lead';
                return $followup;
            });

        $immediateJobs = (clone $jobQuery)
            ->where('followup_date', '<=', now()->toDateString())
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE WHEN followup_date < ? THEN 0 ELSE 1 END", [now()->toDateString()])
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->get()
            ->map(function($followup) {
                $followup->source_type = 'job';
                return $followup;
            });

        $immediate = $immediateLeads->concat($immediateJobs)
            ->sortBy([
                fn($a, $b) => ['high' => 1, 'medium' => 2, 'low' => 3][$a->priority] <=> ['high' => 1, 'medium' => 2, 'low' => 3][$b->priority],
                fn($a, $b) => ($a->followup_date < now()->toDateString() ? 0 : 1) <=> ($b->followup_date < now()->toDateString() ? 0 : 1),
                'followup_date',
                'followup_time'
            ])
            ->take(20)
            ->values();

        // ========================================
        // THIS WEEK'S FOLLOWUPS - PRIORITY FIRST
        // ========================================
        $thisWeekLeadsFollowups = (clone $leadQuery)
            ->where('followup_date', '>', now()->toDateString())
            ->whereBetween('followup_date', [now()->toDateString(), now()->endOfWeek()])
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->get()
            ->map(function($followup) {
                $followup->source_type = 'lead';
                return $followup;
            });

        $thisWeekJobsFollowups = (clone $jobQuery)
            ->where('followup_date', '>', now()->toDateString())
            ->whereBetween('followup_date', [now()->toDateString(), now()->endOfWeek()])
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->get()
            ->map(function($followup) {
                $followup->source_type = 'job';
                return $followup;
            });

        $thisWeekFollowups = $thisWeekLeadsFollowups->concat($thisWeekJobsFollowups)
            ->sortBy([
                fn($a, $b) => ['high' => 1, 'medium' => 2, 'low' => 3][$a->priority] <=> ['high' => 1, 'medium' => 2, 'low' => 3][$b->priority],
                'followup_date',
                'followup_time'
            ])
            ->take(20)
            ->values();

        // ========================================
        // THIS MONTH'S FOLLOWUPS - PRIORITY FIRST
        // ========================================
        $thisMonthLeadsFollowups = (clone $leadQuery)
            ->where('followup_date', '>', now()->endOfWeek())
            ->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->get()
            ->map(function($followup) {
                $followup->source_type = 'lead';
                return $followup;
            });

        $thisMonthJobsFollowups = (clone $jobQuery)
            ->where('followup_date', '>', now()->endOfWeek())
            ->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->get()
            ->map(function($followup) {
                $followup->source_type = 'job';
                return $followup;
            });

        $thisMonthFollowups = $thisMonthLeadsFollowups->concat($thisMonthJobsFollowups)
            ->sortBy([
                fn($a, $b) => ['high' => 1, 'medium' => 2, 'low' => 3][$a->priority] <=> ['high' => 1, 'medium' => 2, 'low' => 3][$b->priority],
                'followup_date',
                'followup_time'
            ])
            ->take(30)
            ->values();

        // ========================================
        // SITE VISITS - FOR SUPER_ADMIN AND TELECALLERS
        // ========================================
        $siteVisitsThisWeek = collect();
        if ($user->role === 'telecallers' || $user->role === 'super_admin') {
            $siteVisitsQuery = Lead::where('status', 'site_visit')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->with('services');

            // For telecallers, only their assigned leads
            if ($user->role === 'telecallers') {
                $siteVisitsQuery->where('assigned_to', $user->id);
            }

            // For super_admin, apply branch filter if set
            if ($user->role === 'super_admin' && $branchId) {
                $siteVisitsQuery->where('branch_id', $branchId);
            }

            $siteVisitsThisWeek = $siteVisitsQuery
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        return [
            'overdue' => $overdue,
            'today' => $today,
            'thisWeek' => $thisWeek,
            'thisMonth' => $thisMonth,
            'immediate' => $immediate,
            'thisWeekFollowups' => $thisWeekFollowups,
            'thisMonthFollowups' => $thisMonthFollowups,
            'siteVisitsThisWeek' => $siteVisitsThisWeek,
        ];
    }
}
