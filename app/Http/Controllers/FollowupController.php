<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\JobFollowup;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // DEBUG: Log the request
        Log::info('Followups Index Request', [
            'is_ajax' => $request->ajax(),
            'filters' => $request->all()
        ]);

        // Get filter data for dropdowns
        $branches = Branch::where('is_active', true)->get();
        $users = $user->role === 'super_admin'
            ? User::where('is_active', true)->orderBy('name')->get()
            : collect();

        // If AJAX request, return JSON data
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getFollowupsData($request, $user);
        }

        // Initial page load - return view
        return view('followups.index', compact('branches', 'users'));
    }

    private function getFollowupsData(Request $request, $user)
    {
        try {
            // Get filter parameters
            $filters = [
                'type' => $request->get('type', 'all'),
                'status' => $request->get('status', 'pending'),
                'priority' => $request->get('priority', 'all'),
                'date_range' => $request->get('date_range', 'all'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'assigned_to' => $request->get('assigned_to'),
                'branch_id' => $request->get('branch_id'),
                'search' => $request->get('search'),
            ];

            // DEBUG: Log filters
            Log::info('Followups Filters', $filters);

            // Build Lead Followups Query
            $leadQuery = LeadFollowup::with(['lead.services', 'assignedToUser', 'lead.branch']);

            // Build Job Followups Query
            $jobQuery = JobFollowup::with(['job.customer', 'job.services', 'assignedTo', 'job.branch']);

            // Apply Role-based Filters
            if ($user->role !== 'super_admin') {
                $leadQuery->where('assigned_to', $user->id);
                $jobQuery->where('assigned_to', $user->id);
            }

            // Apply Branch Filter
            if (!empty($filters['branch_id'])) {
                $leadQuery->whereHas('lead', function($q) use ($filters) {
                    $q->where('branch_id', $filters['branch_id']);
                });
                $jobQuery->whereHas('job', function($q) use ($filters) {
                    $q->where('branch_id', $filters['branch_id']);
                });
            }

            // Apply Status Filter
            if ($filters['status'] !== 'all' && !empty($filters['status'])) {
                $leadQuery->where('status', $filters['status']);
                $jobQuery->where('status', $filters['status']);
            }

            // Apply Priority Filter
            if ($filters['priority'] !== 'all' && !empty($filters['priority'])) {
                $leadQuery->where('priority', $filters['priority']);
                $jobQuery->where('priority', $filters['priority']);
            }

            // Apply Date Range Filter
            switch ($filters['date_range']) {
                case 'overdue':
                    $leadQuery->where('status', 'pending')->whereDate('followup_date', '<', now()->toDateString());
                    $jobQuery->where('status', 'pending')->whereDate('followup_date', '<', now()->toDateString());
                    break;
                case 'today':
                    $leadQuery->where('status', 'pending')->whereDate('followup_date', today());
                    $jobQuery->where('status', 'pending')->whereDate('followup_date', today());
                    break;
                case 'this_week':
                    $leadQuery->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    $jobQuery->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $leadQuery->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    $jobQuery->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'custom':
                    if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                        $leadQuery->whereBetween('followup_date', [$filters['date_from'], $filters['date_to']]);
                        $jobQuery->whereBetween('followup_date', [$filters['date_from'], $filters['date_to']]);
                    }
                    break;
            }

            // Apply Assigned To Filter
            if (!empty($filters['assigned_to'])) {
                $leadQuery->where('assigned_to', $filters['assigned_to']);
                $jobQuery->where('assigned_to', $filters['assigned_to']);
            }

            // Apply Search Filter
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $leadQuery->where(function($q) use ($searchTerm) {
                    $q->whereHas('lead', function($lq) use ($searchTerm) {
                        $lq->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('phone', 'like', "%{$searchTerm}%")
                        ->orWhere('lead_code', 'like', "%{$searchTerm}%");
                    })->orWhere('notes', 'like', "%{$searchTerm}%");
                });

                $jobQuery->where(function($q) use ($searchTerm) {
                    $q->whereHas('job', function($jq) use ($searchTerm) {
                        $jq->where('job_code', 'like', "%{$searchTerm}%")
                        ->orWhereHas('customer', function($cq) use ($searchTerm) {
                            $cq->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('phone', 'like', "%{$searchTerm}%");
                        });
                    })->orWhere('notes', 'like', "%{$searchTerm}%");
                });
            }

            // Get followups based on type filter
            $leadFollowups = collect();
            $jobFollowups = collect();

            if ($filters['type'] === 'all' || $filters['type'] === 'lead' || empty($filters['type'])) {
                $leadFollowups = $leadQuery->get()->map(function($followup) {
                    $followup->source_type = 'lead';
                    return $followup;
                });
                Log::info('Lead Followups Count', ['count' => $leadFollowups->count()]);
            }

            if ($filters['type'] === 'all' || $filters['type'] === 'job' || empty($filters['type'])) {
                $jobFollowups = $jobQuery->get()->map(function($followup) {
                    $followup->source_type = 'job';
                    return $followup;
                });
                Log::info('Job Followups Count', ['count' => $jobFollowups->count()]);
            }

            // Merge and sort followups
            $followups = $leadFollowups->concat($jobFollowups)
                ->sortBy([
                    ['priority', function($a, $b) {
                        $order = ['high' => 1, 'medium' => 2, 'low' => 3];
                        return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
                    }],
                    ['followup_date', 'asc'],
                    ['followup_time', 'asc']
                ])
                ->values();

            Log::info('Total Merged Followups', ['count' => $followups->count()]);

            // Calculate stats
            $stats = $this->calculateStats($user, $filters['branch_id'] ?? null);

            // Paginate manually
            $perPage = 20;
            $currentPage = (int) $request->get('page', 1);
            $total = $followups->count();

            // Proper pagination calculation
            $offset = ($currentPage - 1) * $perPage;

            // FIXED: Format dates in the response using toArray() which respects date casting
            $paginatedFollowups = $followups->slice($offset, $perPage)->values()->map(function($followup) {
                // Convert to array - this will format dates according to model's $casts
                $data = $followup->toArray();

                // CRITICAL FIX: Extract just the date part (YYYY-MM-DD) from the datetime
                if (isset($data['followup_date'])) {
                    // Split by space to get just the date part
                    $data['followup_date'] = explode(' ', $data['followup_date'])[0];
                }

                // Add source_type back (it gets lost in toArray())
                $data['source_type'] = $followup->source_type;

                return $data;
            });

            Log::info('Pagination Debug', [
                'total' => $total,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'offset' => $offset,
                'paginated_count' => $paginatedFollowups->count(),
                'sample_date' => $paginatedFollowups->first()['followup_date'] ?? 'no data'
            ]);

            $response = [
                'success' => true,
                'followups' => $paginatedFollowups,
                'stats' => $stats,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'last_page' => $total > 0 ? (int) ceil($total / $perPage) : 0,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total),
                ]
            ];

            Log::info('Final Response', [
                'followups_count' => $paginatedFollowups->count(),
                'stats' => $stats,
                'pagination' => $response['pagination']
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Followups Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading followups: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateStats($user, $branchId = null)
    {
        $leadQuery = LeadFollowup::query();
        $jobQuery = JobFollowup::query();

        // Role-based filtering
        if ($user->role !== 'super_admin') {
            $leadQuery->where('assigned_to', $user->id);
            $jobQuery->where('assigned_to', $user->id);
        }

        // Branch filtering
        if ($branchId) {
            $leadQuery->whereHas('lead', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
            $jobQuery->whereHas('job', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // Get today's date as string for comparison
        $today = now()->format('Y-m-d');

        return [
            'total' => (clone $leadQuery)->count() + (clone $jobQuery)->count(),
            'pending' => (clone $leadQuery)->where('status', 'pending')->count() +
                        (clone $jobQuery)->where('status', 'pending')->count(),
            'completed' => (clone $leadQuery)->where('status', 'completed')->count() +
                        (clone $jobQuery)->where('status', 'completed')->count(),

            // FIXED: Overdue = status is pending AND date is BEFORE today (not including today)
            'overdue' => (clone $leadQuery)
                            ->where('status', 'pending')
                            ->whereDate('followup_date', '<', $today)
                            ->count() +
                        (clone $jobQuery)
                            ->where('status', 'pending')
                            ->whereDate('followup_date', '<', $today)
                            ->count(),

            // Today = status is pending AND date equals today
            'today' => (clone $leadQuery)
                        ->where('status', 'pending')
                        ->whereDate('followup_date', '=', $today)
                        ->count() +
                    (clone $jobQuery)
                        ->where('status', 'pending')
                        ->whereDate('followup_date', '=', $today)
                        ->count(),

            // High priority = status is pending AND priority is high
            'high_priority' => (clone $leadQuery)
                                ->where('status', 'pending')
                                ->where('priority', 'high')
                                ->count() +
                            (clone $jobQuery)
                                ->where('status', 'pending')
                                ->where('priority', 'high')
                                ->count(),
        ];
    }

}
