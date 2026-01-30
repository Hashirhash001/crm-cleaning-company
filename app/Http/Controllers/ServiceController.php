<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of services
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Service::with('createdBy'); // Load creator relationship

        // Filter by service type
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
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
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['name', 'service_type', 'price', 'is_active', 'created_at'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $services = $query->paginate(15);

        // Get distinct service types from database
        $serviceTypes = Service::select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type')
            ->toArray();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('services.partials.table-rows', compact('services'))->render(),
                'pagination' => $services->links('pagination::bootstrap-5')->render(),
                'total' => $services->total(),
                'serviceTypes' => $serviceTypes
            ]);
        }

        return view('services.index', compact('services', 'serviceTypes'));
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:services,name',
                'service_type' => 'required|string|max:50',
                'description' => 'nullable|string|max:1000',
                'price' => 'nullable|numeric|min:0',
                'is_active' => 'nullable|in:0,1,true,false',
            ], [
                'name.required' => 'Service name is required',
                'name.unique' => 'This service name already exists',
                'service_type.required' => 'Service type is required',
                'service_type.in' => 'Invalid service type selected',
                'price.numeric' => 'Price must be a number',
                'price.min' => 'Price cannot be negative',
            ]);

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['created_by'] = auth()->id(); // Track creator

            $service = Service::create($validated);

            Log::info('Service created', [
                'service_id' => $service->id,
                'name' => $service->name,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service created successfully!',
                'service' => $service
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Service creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified service
     */
    public function edit(Service $service)
    {
        $user = auth()->user();

        // Check if user can edit (super_admin or creator)
        if ($user->role !== 'super_admin' && $service->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only edit services you created.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'service' => $service
        ]);
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, Service $service)
    {
        try {
            $user = auth()->user();

            // Check if user can update (super_admin or creator)
            if ($user->role !== 'super_admin' && $service->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit services you created.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:services,name,' . $service->id,
                'service_type' => 'required|string|max:50',
                'description' => 'nullable|string|max:1000',
                'price' => 'nullable|numeric|min:0',
                'is_active' => 'nullable|in:0,1,true,false',
            ]);

            $validated['is_active'] = $request->has('is_active') ? true : false;

            $service->update($validated);

            Log::info('Service updated', [
                'service_id' => $service->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully!',
                'service' => $service
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Service update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating service'
            ], 500);
        }
    }

    /**
     * Remove the specified service
     */
    public function destroy(Service $service)
    {
        try {
            $user = auth()->user();

            // Check if user can delete (super_admin or creator)
            if ($user->role !== 'super_admin' && $service->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete services you created.'
                ], 403);
            }

            // Check if service is used in leads or jobs
            $leadsCount = $service->leads()->count();

            if ($leadsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete service. It is being used in {$leadsCount} lead(s)."
                ], 400);
            }

            $serviceName = $service->name;
            $service->delete();

            Log::info('Service deleted', [
                'service_name' => $serviceName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Service deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting service'
            ], 500);
        }
    }
}
