<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
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
        $selectedBranchName = 'All Branches';

        if ($user->role === 'super_admin') {
            // Super Admin - can see all branches
            $branches = Branch::where('is_active', true)->get();

            if ($branchId) {
                // Filter by selected branch
                $totalUsers = User::where('branch_id', $branchId)->count();
                $activeUsers = User::where('branch_id', $branchId)
                    ->where('is_active', true)->count();

                // Get the selected branch name
                $selectedBranch = Branch::find($branchId);
                $selectedBranchName = $selectedBranch ? $selectedBranch->name : 'All Branches';
            } else {
                // Show all branches
                $totalUsers = User::count();
                $activeUsers = User::where('is_active', true)->count();
                $selectedBranchName = 'All Branches';
            }

            // Get statistics for all branches
            $branchStatistics = Branch::where('is_active', true)->get()->map(function($branch) {
                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_users' => User::where('branch_id', $branch->id)->count(),
                    'active_users' => User::where('branch_id', $branch->id)
                        ->where('is_active', true)->count(),
                    'super_admin_count' => User::where('branch_id', $branch->id)
                        ->where('role', 'super_admin')->count(),
                    'lead_manager_count' => User::where('branch_id', $branch->id)
                        ->where('role', 'lead_manager')->count(),
                    'field_staff_count' => User::where('branch_id', $branch->id)
                        ->where('role', 'field_staff')->count(),
                    'reporting_user_count' => User::where('branch_id', $branch->id)
                        ->where('role', 'reporting_user')->count(),
                ];
            });

            return view('dashboard', compact(
                'totalUsers',
                'activeUsers',
                'branches',
                'branchStatistics',
                'selectedBranchName'
            ));
        } else {
            // Other roles - see only their branch
            $totalUsers = User::where('branch_id', $user->branch_id)->count();
            $activeUsers = User::where('branch_id', $user->branch_id)
                ->where('is_active', true)->count();

            $branches = [];
            $branchStatistics = [];

            return view('dashboard', compact(
                'totalUsers',
                'activeUsers',
                'branches',
                'branchStatistics',
                'selectedBranchName'
            ));
        }
    }
}
