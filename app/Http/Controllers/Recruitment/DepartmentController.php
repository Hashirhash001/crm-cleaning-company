<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,lead_manager,telecallers');
    }

    /**
     * Display listing of departments.
     */
    public function index(Request $request)
    {
        $query = RecruitmentDepartment::withCount(['positions', 'candidates']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortColumn = $request->get('sort_column', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $allowedColumns = ['name', 'is_active', 'created_at'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        $departments = $query->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('recruitment.departments.partials.table-rows', compact('departments'))->render(),
                'pagination' => $departments->links('pagination::bootstrap-5')->render(),
                'total' => $departments->total(),
            ]);
        }

        return view('recruitment.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:recruitment_departments,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department = RecruitmentDepartment::create($validator->validated());

        Log::info('Recruitment department created', ['id' => $department->id, 'name' => $department->name]);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully.',
        ]);
    }

    /**
     * Show department for editing (AJAX).
     */
    public function edit(RecruitmentDepartment $department)
    {
        return response()->json([
            'success' => true,
            'department' => $department,
        ]);
    }

    /**
     * Update department.
     */
    public function update(Request $request, RecruitmentDepartment $department)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:recruitment_departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department->update($validator->validated());

        Log::info('Recruitment department updated', ['id' => $department->id, 'name' => $department->name]);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully.',
        ]);
    }

    /**
     * Delete department (only if safe).
     */
    public function destroy(RecruitmentDepartment $department)
    {
        // Check for linked positions or candidates
        if ($department->positions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department. It has ' . $department->positions()->count() . ' position(s) linked.',
            ], 422);
        }

        if ($department->candidates()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department. It has ' . $department->candidates()->count() . ' candidate(s) linked.',
            ], 422);
        }

        $department->delete();

        Log::info('Recruitment department deleted', ['id' => $department->id, 'name' => $department->name]);

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully.',
        ]);
    }
}
