<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    public function index(Request $request)
    {
        $query = User::with('branch')
            ->where('id', '!=', auth()->id());

        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        $branches = Branch::where('is_active', true)->get();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('users.partials.table-rows', compact('users'))->render(),
                'pagination' => $users->links('pagination::bootstrap-5')->render(),
                'total' => $users->total()
            ]);
        }

        return view('users.index', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|in:super_admin,lead_manager,field_staff,telecallers',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!'
        ]);
    }

    public function edit(User $user)
    {
        // Prevent editing own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit your own account!'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Prevent updating own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot update your own account!'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|in:super_admin,lead_manager,field_staff,telecallers',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!'
        ]);
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'branch',
            'assignedJobs.customer',
            'assignedJobs.service',
            'assignedJobs.branch',
            'createdLeads',
            'createdJobs'
        ]);

        // Get job statistics
        $totalJobs = $user->assignedJobs->count();
        $completedJobs = $user->assignedJobs->where('status', 'completed')->count();
        $inProgressJobs = $user->assignedJobs->where('status', 'in_progress')->count();
        $pendingJobs = $user->assignedJobs->whereIn('status', ['pending', 'assigned'])->count();

        return view('users.show', compact('user', 'totalJobs', 'completedJobs', 'inProgressJobs', 'pendingJobs'));
    }

}
