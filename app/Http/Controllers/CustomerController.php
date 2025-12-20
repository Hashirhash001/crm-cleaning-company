<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Http\Request;
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

        // Authorization
        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized');
        }

        $query = Customer::with(['lead', 'jobs']);

        // Telecallers can only see their assigned customers
        if ($user->role === 'telecallers') {
            $query->where(function($q) use ($user) {
                $q->whereHas('jobs', function($jobQuery) use ($user) {
                    $jobQuery->where('assigned_to', $user->id);
                })->orWhereHas('lead', function($leadQuery) use ($user) {
                    $leadQuery->where('assigned_to', $user->id);
                });
            });
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $query->sort($sortColumn, $sortDirection);

        $customers = $query->paginate(15);

        // Return JSON for AJAX requests
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

        return view('customers.index', compact('customers'));
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
            if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can create customers.'
                    ], 403);
                }
                return back()->with('error', 'Unauthorized.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:customers,email',
                'phone' => 'required|string|max:20|unique:customers,phone',
                'address' => 'nullable|string|max:500',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Customer name is required',
                'email.email' => 'Please enter a valid email address',
                'email.unique' => 'This email is already registered with another customer',
                'phone.required' => 'Phone number is required',
                'phone.unique' => 'This phone number is already registered with another customer',
                'priority.required' => 'Priority level is required',
                'priority.in' => 'Please select a valid priority level',
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
                'is_active' => true,
            ]);

            Log::info('Customer created manually', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
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

        if ($user->role === 'telecallers') {
            $hasAccess = $customer->jobs()->where('assigned_to', $user->id)->exists()
                      || ($customer->lead && $customer->lead->assigned_to == $user->id);

            if (!$hasAccess) {
                abort(403, 'Unauthorized. You can only view customers assigned to you.');
            }
        }

        $customer->load(['jobs', 'customerNotes.createdBy', 'customerNotes.job', 'completedJobs', 'lead']);

        return view('customers.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can update customers.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:customers,email,' . $customer->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string',
            ]);

            $customer->update($validated);

            Log::info('Customer updated', ['customer_id' => $customer->id]);

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
                ->map(function($job) {
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
                ->map(function($note) use ($user) {
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

}
