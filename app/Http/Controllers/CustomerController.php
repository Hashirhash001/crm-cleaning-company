<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Customer::with(['jobs', 'customerNotes'])
            ->withCount([
                'jobs as total_jobs_count',
                'jobs as completed_jobs_count' => function($query) {
                    $query->where('status', 'completed');
                }
            ]);

        // Apply filters
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        if ($request->filled('sort_by')) {
            $sort = explode('-', $request->sort_by);
            $column = $sort[0];
            $direction = $sort[1] ?? 'asc';

            switch($column) {
                case 'name':
                    $query->orderBy('name', $direction);
                    break;
                case 'priority':
                    $priorityOrder = "CASE
                        WHEN priority = 'high' THEN 1
                        WHEN priority = 'medium' THEN 2
                        WHEN priority = 'low' THEN 3
                        END";
                    $query->orderByRaw("$priorityOrder " . ($direction === 'asc' ? 'ASC' : 'DESC'));
                    break;
                case 'jobs':
                    $query->orderBy('total_jobs_count', $direction);
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        $customers = $query->paginate(15);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('customers.partials.table-rows', compact('customers'))->render(),
                'pagination' => $customers->links('pagination::bootstrap-5')->render(),
                'total' => $customers->total()
            ]);
        }

        return view('customers.index', compact('customers'));
    }


    public function show(Customer $customer)
    {
        $customer->load(['jobs', 'customerNotes.createdBy', 'customerNotes.job', 'completedJobs', 'lead']);

        // Always return the full page (remove AJAX/modal logic)
        return view('customers.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email,' . $customer->id,
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
}
