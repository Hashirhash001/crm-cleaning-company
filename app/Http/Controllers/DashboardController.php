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
        $dateFrom = $request->query('date_from', now()->startOfWeek()->toDateString());
        $dateTo = $request->query('date_to', now()->endOfWeek()->toDateString());
        $selectedBranchName = 'All Branches';

        // Budget Information
        $dailyBudget = Setting::get('daily_budget_limit', 100000);
        $todayTotal = Lead::whereDate('approved_at', today())
            ->where('status', 'approved')
            ->sum('amount');
        $remaining = $dailyBudget - $todayTotal;
        $percentage = $dailyBudget > 0 ? ($todayTotal / $dailyBudget) * 100 : 0;

        // Followup Data based on user role with date filter
        $followupData = $this->getFollowupData($user, $branchId, $dateFrom, $dateTo);

        if ($user->role === 'super_admin') {
            $branches = Branch::where('is_active', true)->get();

            if ($branchId) {
                $totalUsers = User::where('branch_id', $branchId)->count();
                $activeUsers = User::where('branch_id', $branchId)->where('is_active', true)->count();
                $selectedBranch = Branch::find($branchId);
                $selectedBranchName = $selectedBranch ? $selectedBranch->name : 'All Branches';
            } else {
                $totalUsers = User::count();
                $activeUsers = User::where('is_active', true)->count();
                $selectedBranchName = 'All Branches';
            }

            $branchStatistics = Branch::where('is_active', true)->get()->map(function($branch) {
                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_users' => User::where('branch_id', $branch->id)->count(),
                    'active_users' => User::where('branch_id', $branch->id)->where('is_active', true)->count(),
                    'super_admin_count' => User::where('branch_id', $branch->id)->where('role', 'super_admin')->count(),
                    'lead_manager_count' => User::where('branch_id', $branch->id)->where('role', 'lead_manager')->count(),
                    'field_staff_count' => User::where('branch_id', $branch->id)->where('role', 'field_staff')->count(),
                    'telecallers_count' => User::where('branch_id', $branch->id)->where('role', 'telecallers')->count(),
                ];
            });

            // Sales with date filter
            $approvedWeekly = Lead::where('status', 'approved')
                ->whereBetween('approved_at', [$dateFrom, $dateTo])
                ->sum('amount');

            $approvedMonthly = Lead::where('status', 'approved')
                ->whereYear('approved_at', now()->year)
                ->whereMonth('approved_at', now()->month)
                ->sum('amount');

            return view('dashboard', compact(
                'totalUsers', 'activeUsers', 'branches', 'branchStatistics', 'selectedBranchName',
                'dailyBudget', 'todayTotal', 'remaining', 'percentage', 'followupData',
                'approvedWeekly', 'approvedMonthly', 'dateFrom', 'dateTo'
            ));
        } else {
            $totalUsers = User::where('branch_id', $user->branch_id)->count();
            $activeUsers = User::where('branch_id', $user->branch_id)->where('is_active', true)->count();
            $branches = [];
            $branchStatistics = [];

            $approvedWeekly = Lead::where('status', 'approved')
                ->whereBetween('approved_at', [$dateFrom, $dateTo])
                ->sum('amount');

            $approvedMonthly = Lead::where('status', 'approved')
                ->whereYear('approved_at', now()->year)
                ->whereMonth('approved_at', now()->month)
                ->sum('amount');

            return view('dashboard', compact(
                'totalUsers', 'activeUsers', 'branches', 'branchStatistics', 'selectedBranchName',
                'dailyBudget', 'todayTotal', 'remaining', 'percentage', 'followupData',
                'approvedWeekly', 'approvedMonthly', 'dateFrom', 'dateTo'
            ));
        }
    }

    private function getFollowupData($user, $branchId = null, $dateFrom = null, $dateTo = null)
    {
        $query = LeadFollowup::with(['lead', 'assignedToUser'])->where('status', 'pending');

        // Role-based filtering
        if ($user->role === 'super_admin') {
            if ($branchId) {
                $query->whereHas('lead', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        } elseif ($user->role === 'lead_manager') {
            $query->whereHas('lead', function($q) use ($user) {
                $q->where('created_by', $user->id);
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

        $overdue = (clone $query)->where('followup_date', '<', now()->toDateString())->count();
        $today = (clone $query)->whereDate('followup_date', today())->count();
        $thisWeek = (clone $query)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = (clone $query)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $weeklyFollowups = (clone $query)
            ->whereBetween('followup_date', [$dateFrom ?: now()->startOfWeek(), $dateTo ?: now()->endOfWeek()])
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(10)
            ->get();

        $overdueFollowups = (clone $query)
            ->where('followup_date', '<', now()->toDateString())
            ->orderBy('followup_date')
            ->limit(5)
            ->get();

        return [
            'overdue' => $overdue,
            'today' => $today,
            'thisWeek' => $thisWeek,
            'thisMonth' => $thisMonth,
            'weeklyFollowups' => $weeklyFollowups,
            'overdueFollowups' => $overdueFollowups,
        ];
    }

}
