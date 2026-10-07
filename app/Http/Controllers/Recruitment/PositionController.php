<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentDepartment;
use App\Models\RecruitmentPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,lead_manager,telecallers');
    }

    /**
     * Display listing of positions.
     */
    public function index(Request $request)
    {
        $query = RecruitmentPosition::with('department')
            ->withCount('candidates');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('recruitment_department_id', $request->department_id);
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

        $positions = $query->paginate(15);
        $departments = RecruitmentDepartment::active()->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('recruitment.positions.partials.table-rows', compact('positions'))->render(),
                'pagination' => $positions->links('pagination::bootstrap-5')->render(),
                'total' => $positions->total(),
            ]);
        }

        return view('recruitment.positions.index', compact('positions', 'departments'));
    }

    /**
     * Store a newly created position.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'recruitment_department_id' => 'required|exists:recruitment_departments,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check unique name within department
        $exists = RecruitmentPosition::where('name', $request->name)
            ->where('recruitment_department_id', $request->recruitment_department_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['name' => ['A position with this name already exists in the selected department.']]
            ], 422);
        }

        $position = RecruitmentPosition::create($validator->validated());

        Log::info('Recruitment position created', ['id' => $position->id, 'name' => $position->name]);

        return response()->json([
            'success' => true,
            'message' => 'Position created successfully.',
        ]);
    }

    /**
     * Show position for editing (AJAX).
     */
    public function edit(RecruitmentPosition $position)
    {
        $position->load('department');
        return response()->json([
            'success' => true,
            'position' => $position,
        ]);
    }

    /**
     * Update position.
     */
    public function update(Request $request, RecruitmentPosition $position)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'recruitment_department_id' => 'required|exists:recruitment_departments,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check unique name within department (excluding current)
        $exists = RecruitmentPosition::where('name', $request->name)
            ->where('recruitment_department_id', $request->recruitment_department_id)
            ->where('id', '!=', $position->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['name' => ['A position with this name already exists in the selected department.']]
            ], 422);
        }

        $position->update($validator->validated());

        Log::info('Recruitment position updated', ['id' => $position->id, 'name' => $position->name]);

        return response()->json([
            'success' => true,
            'message' => 'Position updated successfully.',
        ]);
    }

    /**
     * Delete position (only if safe).
     */
    public function destroy(RecruitmentPosition $position)
    {
        if ($position->candidates()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete position. It has ' . $position->candidates()->count() . ' candidate(s) linked.',
            ], 422);
        }

        $position->delete();

        Log::info('Recruitment position deleted', ['id' => $position->id, 'name' => $position->name]);

        return response()->json([
            'success' => true,
            'message' => 'Position deleted successfully.',
        ]);
    }

    /**
     * Get positions by department (AJAX for dependent dropdown).
     */
    public function getByDepartment(Request $request)
    {
        $positions = RecruitmentPosition::where('recruitment_department_id', $request->department_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($positions);
    }
}
