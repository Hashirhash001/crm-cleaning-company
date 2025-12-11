<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            $totalCustomers = \App\Models\Customer::count();
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $pendingLeads = Lead::where('status', 'pending')->count();
            $activeJobs = \App\Models\Job::whereIn('status', ['pending', 'assigned', 'in_progress'])->count();

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
                'confirmed' => Lead::where('assigned_to', $user->id)->where('status', 'they_will_confirm')->count(),
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
        $query = LeadFollowup::with(['lead.services', 'assignedToUser'])->where('status', 'pending');

        // Role-based filtering
        if ($user->role === 'super_admin') {
            if ($branchId) {
                $query->whereHas('lead', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        } elseif ($user->role === 'lead_manager') {
            $query->whereHas('lead', function($q) use ($user, $branchId) {
                $q->where('created_by', $user->id);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            });
        } elseif ($user->role === 'telecallers') {
            $query->where('assigned_to', $user->id);
        } elseif ($user->role === 'field_staff') {
            $query->where('assigned_to', $user->id);
        }

        // Date filter
        if ($dateFrom && $dateTo) {
            $query->whereBetween('followup_date', [$dateFrom, $dateTo]);
        }

        // Counts
        $overdue = (clone $query)->where('followup_date', '<', now()->toDateString())->count();
        $today = (clone $query)->whereDate('followup_date', today())->count();
        $thisWeek = (clone $query)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = (clone $query)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count();

        // Immediate Followups (Today + Overdue) - for telecallers priority view
        $immediate = (clone $query)
            ->where('followup_date', '<=', now()->toDateString())
            ->orderByRaw("CASE WHEN followup_date < ? THEN 0 ELSE 1 END", [now()->toDateString()])
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(20)
            ->get();

        // This Week's Followups (excluding today and overdue)
        $thisWeekFollowups = (clone $query)
            ->where('followup_date', '>', now()->toDateString())
            ->whereBetween('followup_date', [now()->toDateString(), now()->endOfWeek()])
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(20)
            ->get();

        // This Month's Followups (excluding this week)
        $thisMonthFollowups = (clone $query)
            ->where('followup_date', '>', now()->endOfWeek())
            ->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(30)
            ->get();

        // Weekly Followups - for admin/lead_manager view
        $weeklyFollowups = (clone $query)
            ->whereBetween('followup_date', [$dateFrom ?: now()->startOfWeek(), $dateTo ?: now()->endOfWeek()])
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(50)
            ->get();

        // Overdue Followups
        $overdueFollowups = (clone $query)
            ->where('followup_date', '<', now()->toDateString())
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(50)
            ->get();

        // Site Visits This Week - for telecallers
        $siteVisitsThisWeek = collect();
        if ($user->role === 'telecallers') {
            $siteVisitsThisWeek = Lead::where('assigned_to', $user->id)
                ->where('status', 'site_visit')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->with('services')
                ->orderBy('created_at', 'desc')
                ->limit(10)
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
            'weeklyFollowups' => $weeklyFollowups,
            'overdueFollowups' => $overdueFollowups,
        ];
    }
}
