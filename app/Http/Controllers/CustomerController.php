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

        // Allow super_admin, lead_manager AND telecallers
        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized');
        }

        $query = Customer::with(['lead', 'jobs', 'completedJobs']);

        // Telecallers can only see customers from their assigned jobs/leads
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

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);

        // Return JSON for AJAX - Check both ways
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('customers.partials.table-rows', compact('customers'))->render(),
                'pagination' => $customers->links('pagination::bootstrap-5')->render(),
                'total' => $customers->total()
            ]);
        }

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        // Only super_admin, lead_manager and telecallers can create customers
        if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            return back()->with('error', 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can create customers.');
        }

        return view('customers.create');
    }

    public function store(Request $request)
    {
        try {
            // Authorization check
            if (!in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. Only Super Admin, Lead Manager or Telecallers can create customers.'
                    ], 403);
                }
                return back()->with('error', 'Unauthorized.');
            }

            // Validate request
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

            // Check if validation fails
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

            // Create customer
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

            // Return JSON for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created successfully!',
                    'customer' => $customer,
                    'redirect' => route('customers.index')
                ]);
            }

            // Return with success message for normal form submission
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

        // Authorization check
        if ($user->role === 'telecallers') {
            // Telecallers can only view customers assigned to them
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
            // Only super_admin and lead_manager can update customers
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
        // Only super_admin and lead_manager can edit customers
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

            // Authorization: Telecallers can only add notes to their assigned customers
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
            // Only super_admin can delete customers
            if (auth()->user()->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin can delete customers.'
                ], 403);
            }

            // Check if customer has jobs
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
}
