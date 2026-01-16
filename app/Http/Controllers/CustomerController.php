<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized');
        }

        // Select only needed columns
        $query = Customer::select([
                'id', 'customer_code', 'name', 'email', 'phone',
                'address', 'priority', 'branch_id', 'is_active', 'created_at'
            ])
            ->with([
                'branch:id,name',
                'lead:id,lead_code,customer_id'
            ])
            ->withCount('jobs')
            ->withCount([
                'jobs as completed_jobs_count' => function ($q) {
                    $q->whereIn('status', ['completed', 'approved']);
                }
            ])
            ->withSum([
                'jobs as total_value' => function ($q) {
                    $q->whereIn('status', ['completed', 'approved']);
                }
            ], 'amount');

        // Role-based access
        if ($user->role !== 'super_admin') {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply filters
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort_column', 'completed-jobs');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->sort($sortColumn, $sortDirection);

        // Paginate
        $customers = $query->paginate(15);

        // AJAX response (no stats calculation)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('customers.partials.table-rows', compact('customers'))->render(),
                'pagination' => $customers->links('pagination::bootstrap-5')->render(),
                'total' => $customers->total(),
                'current_sort' => [
                    'column' => $sortColumn,
                    'direction' => $sortDirection,
                ],
            ]);
        }

        // Calculate stats ONLY on initial page load with caching
        $cacheKey = 'customer_stats_' . $user->id . '_' . $user->branch_id;
        $stats = cache()->remember($cacheKey, 600, function () use ($user) {
            $statsQuery = Customer::query();

            if ($user->role !== 'super_admin') {
                $statsQuery->where('branch_id', $user->branch_id);
            }

            return [
                'total_revenue' => DB::table('customers')
                    ->join('jobs', 'customers.id', '=', 'jobs.customer_id')
                    ->whereIn('jobs.status', ['completed', 'approved'])
                    ->when($user->role !== 'super_admin', fn($q) =>
                        $q->where('customers.branch_id', $user->branch_id))
                    ->sum('jobs.amount'),
                'total_customers' => $statsQuery->count(),
                'active_customers' => (clone $statsQuery)->has('jobs')->count()
            ];
        });

        $branches = Branch::where('is_active', true)->select('id', 'name')->get();

        $totalRevenue = $stats['total_revenue'];
        $totalCustomers = $stats['total_customers'];
        $activeCustomers = $stats['active_customers'];

        return view('customers.index', compact(
            'customers',
            'branches',
            'totalRevenue',
            'totalCustomers',
            'activeCustomers'
        ));
    }

    public function create()
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            return back()->with('error', 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can create customers.');
        }

        return view('customers.create');
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can create customers.'
                    ], 403);
                }
                return back()->with('error', 'Unauthorized.');
            }

            // Determine branch_id
            $branchId = $user->role === 'super_admin'
                ? $request->branch_id
                : $user->branch_id;

            // Validation with composite unique constraints (email and phone unique per branch)
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    // Unique email per branch
                    Rule::unique('customers', 'email')->where(function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId);
                    }),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    // Unique phone per branch
                    Rule::unique('customers', 'phone')->where(function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId);
                    }),
                ],
                'address' => 'nullable|string|max:500',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string|max:1000',
                'branch_id' => $user->role === 'super_admin' ? 'required|exists:branches,id' : 'nullable',
            ], [
                'name.required' => 'Customer name is required',
                'email.email' => 'Please enter a valid email address',
                'email.unique' => 'This email is already registered with another customer in this branch',
                'phone.required' => 'Phone number is required',
                'phone.unique' => 'This phone number is already registered with another customer in this branch',
                'priority.required' => 'Priority level is required',
                'priority.in' => 'Please select a valid priority level',
                'branch_id.required' => 'Branch is required',
                'branch_id.exists' => 'Selected branch is invalid',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
                'branch_id' => $branchId,
                'is_active' => true,
            ]);

            Log::info('Customer created manually', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'branch_id' => $branchId,
                'created_by' => auth()->id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created successfully!',
                    'customer' => $customer,
                    'redirect' => route('customers.index')
                ]);
            }

            return redirect()->route('customers.index')
                ->with('success', json_encode([
                    'title' => 'Customer Created Successfully!',
                    'message' => "Customer has been created successfully.",
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name
                ]));
        } catch (\Exception $e) {
            Log::error('Customer creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating customer: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Error creating customer: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $user = auth()->user();

        // Authorization check based on role
        if ($user->role === 'telecallers' || $user->role === 'lead_manager') {
            // Branch-based access control for non-super-admin users
            if ($customer->branch_id !== $user->branch_id) {
                abort(403, 'Unauthorized. You can only view customers from your branch.');
            }
        }

        $branches = Branch::all();

        // Load relationships
        $customer->load(['jobs', 'customerNotes.createdBy', 'customerNotes.job', 'completedJobs', 'lead', 'branch']);

        return view('customers.show', compact('customer', 'branches'));
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can update customers.'
                ], 403);
            }

            // Authorization: Check if user can update this customer (branch-based)
            if ($user->role !== 'super_admin' && $customer->branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update customers from your branch'
                ], 403);
            }

            // Validation with composite unique (excluding current customer)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    // Unique email per branch, excluding this customer
                    Rule::unique('customers', 'email')
                        ->where('branch_id', $customer->branch_id)
                        ->ignore($customer->id),
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    // Unique phone per branch, excluding this customer
                    Rule::unique('customers', 'phone')
                        ->where('branch_id', $customer->branch_id)
                        ->ignore($customer->id),
                ],
                'address' => 'nullable|string',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string',
            ]);

            $customer->update($validated);

            Log::info('Customer updated', [
                'customer_id' => $customer->id,
                'branch_id' => $customer->branch_id,
                'updated_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Customer update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Customer $customer)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized. Only Super Admin, Lead Manager and Telecallers can edit customers.');
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'customer' => $customer
            ]);
        }

        return view('customers.edit', compact('customer'));
    }

    public function addNote(Request $request, Customer $customer)
    {
        try {
            $user = auth()->user();

            if ($user->role === 'telecallers') {
                $hasAccess = $customer->jobs()->where('assigned_to', $user->id)->exists()
                    || ($customer->lead && $customer->lead->assigned_to == $user->id);

                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only add notes to customers assigned to you.'
                    ], 403);
                }
            }

            $validated = $request->validate([
                'note' => 'required|string',
                'job_id' => 'nullable|exists:jobs,id',
            ]);

            $note = CustomerNote::create([
                'customer_id' => $customer->id,
                'job_id' => $validated['job_id'] ?? null,
                'created_by' => auth()->id(),
                'note' => $validated['note'],
            ]);

            Log::info('Customer note added', [
                'customer_id' => $customer->id,
                'note_id' => $note->id,
                'added_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!',
                'note' => $note->load('createdBy', 'job')
            ]);
        } catch (\Exception $e) {
            Log::error('Add note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding note'
            ], 500);
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            if (auth()->user()->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin can delete customers.'
                ], 403);
            }

            if ($customer->jobs()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete customer with existing jobs. Please remove or reassign jobs first.'
                ], 400);
            }

            $customerCode = $customer->customer_code;
            $customer->delete();

            Log::info('Customer deleted', [
                'customer_code' => $customerCode,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Customer deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a customer note
     * Only super_admin or the note creator can delete
     */
    public function deleteNote(Customer $customer, CustomerNote $note)
    {
        try {
            $user = auth()->user();

            // Authorization: Super admin can delete any note, others only their own
            if ($user->role !== 'super_admin' && $note->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete notes you created.'
                ], 403);
            }

            // Verify note belongs to this customer
            if ($note->customer_id !== $customer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note does not belong to this customer.'
                ], 400);
            }

            $note->delete();

            Log::info('Customer note deleted', [
                'customer_id' => $customer->id,
                'note_id' => $note->id,
                'deleted_by' => $user->id,
                'note_creator' => $note->created_by
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting note'
            ], 500);
        }
    }

    /**
     * Get customer jobs (for note modal dropdown)
     * API endpoint for AJAX calls
     */
    public function getCustomerJobs(Customer $customer)
    {
        try {
            $user = auth()->user();

            // Authorization check for telecallers
            if ($user->role === 'telecallers') {
                $hasAccess = $customer->jobs()->where('assigned_to', $user->id)->exists()
                    || ($customer->lead && $customer->lead->assigned_to == $user->id);

                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }

            // Load customer jobs with service relationship
            $jobs = $customer->jobs()
                ->with('service:id,name')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'job_code', 'title', 'status', 'amount', 'service_id'])
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'job_code' => $job->job_code,
                        'title' => $job->title,
                        'status' => $job->status,
                        'status_label' => ucfirst(str_replace('_', ' ', $job->status)),
                        'amount' => $job->amount,
                        'service_name' => $job->service->name ?? 'N/A'
                    ];
                });

            return response()->json($jobs);
        } catch (\Exception $e) {
            Log::error('Get customer jobs error: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading customer jobs'
            ], 500);
        }
    }

    /**
     * Get customer notes (for view notes modal)
     * API endpoint for AJAX calls
     */
    public function getCustomerNotes(Customer $customer)
    {
        try {
            $user = auth()->user();

            // Authorization check for telecallers
            if ($user->role === 'telecallers') {
                $hasAccess = $customer->jobs()->where('assigned_to', $user->id)->exists()
                    || ($customer->lead && $customer->lead->assigned_to == $user->id);

                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }

            // Load customer notes with relationships
            $notes = $customer->customerNotes()
                ->with([
                    'createdBy:id,name',
                    'job:id,job_code,title,status,amount'
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($note) use ($user) {
                    return [
                        'id' => $note->id,
                        'note' => $note->note,
                        'created_by' => $note->created_by,
                        'created_by_name' => $note->createdBy->name ?? 'Unknown',
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $note->created_at->diffForHumans(),
                        'can_delete' => $user->role === 'super_admin' || $note->created_by === $user->id,
                        'job' => $note->job ? [
                            'id' => $note->job->id,
                            'job_code' => $note->job->job_code,
                            'title' => $note->job->title,
                            'status' => $note->job->status,
                            'status_label' => ucfirst(str_replace('_', ' ', $note->job->status)),
                            'amount' => $note->job->amount,
                            'amount_formatted' => $note->job->amount ?
                                '₹' . number_format($note->job->amount, 2) : null
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'notes' => $notes,
                'total' => $notes->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Get customer notes error: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading customer notes'
            ], 500);
        }
    }

    public function byBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id'
        ]);

        $customers = Customer::where('branch_id', $request->branch_id)
            ->orderBy('name')
            ->get(['id', 'name', 'customer_code', 'phone']);

        return response()->json($customers);
    }

    public function export(Request $request)
    {
        try {
            $user = auth()->user();

            // Build query with same filters as index
            $query = Customer::with(['branch', 'lead'])
                ->withCount('jobs')
                ->withCount([
                    'jobs as completed_jobs_count' => function ($q) {
                        $q->whereIn('status', ['completed', 'approved']);
                    }
                ])
                ->withSum([
                    'jobs as total_value' => function ($q) {
                        $q->whereIn('status', ['completed', 'approved']);
                    }
                ], 'amount');

            // ROLE-BASED ACCESS CONTROL
            if ($user->role === 'super_admin') {
                // Super admin sees all customers
            } elseif (in_array($user->role, ['lead_manager', 'telecallers'])) {
                // Branch users see only their branch customers
                $query->where('branch_id', $user->branch_id);
            } else {
                abort(403, 'Unauthorized');
            }

            // APPLY FILTERS (same as index method)
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%");
                });
            }

            // Check total count
            $totalCount = $query->count();
            $exportLimit = 10000;

            Log::info('Customer export started', [
                'user_id' => $user->id,
                'filters' => $request->all(),
                'total_customers' => $totalCount,
                'exported_customers' => min($totalCount, $exportLimit)
            ]);

            if ($totalCount > $exportLimit) {
                session()->flash('warning', "Only the first {$exportLimit} customers will be exported. Total: {$totalCount}.");
            }

            // Generate filename
            $fileName = 'customers_export_' . now()->format('Y-m-d_His') . '.csv';

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
                    'Customer Code',
                    'Name',
                    'Email',
                    'Phone',
                    'Address',
                    'Priority',
                    'Branch',
                    'Total Jobs',
                    'Completed Jobs',
                    'Total Revenue',
                    'Status',
                    'Lead Code',
                    'Created At',
                    'Notes'
                ]);

                // Data rows with limit
                $query->limit($exportLimit)->chunk(1000, function($customers) use ($file) {
                    foreach ($customers as $customer) {
                        fputcsv($file, [
                            $customer->customer_code,
                            $customer->name,
                            $customer->email ?? '',
                            $customer->phone,
                            $customer->address ?? '',
                            ucfirst($customer->priority),
                            optional($customer->branch)->name ?? '',
                            $customer->jobs_count ?? 0,
                            $customer->completed_jobs_count ?? 0,
                            $customer->total_value ? number_format($customer->total_value, 2) : '0.00',
                            $customer->is_active ? 'Active' : 'Inactive',
                            optional($customer->lead)->lead_code ?? '',
                            $customer->created_at->format('Y-m-d H:i:s'),
                            $customer->notes ?? ''
                        ]);
                    }
                });

                fclose($file);
            };

            return response()->streamDownload($callback, $fileName, $headers);

        } catch (\Exception $e) {
            \Log::error('Customer export error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withError('Error exporting customers: ' . $e->getMessage());
        }
    }

}
