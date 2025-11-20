<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'super_admin') {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $dailyBudget = Setting::get('daily_budget_limit', 100000);
        return view('settings.index', compact('dailyBudget'));
    }

    public function updateDailyBudget(Request $request)
    {
        try {
            $validated = $request->validate([
                'daily_budget_limit' => 'required|numeric|min:0',
            ]);

            Setting::set('daily_budget_limit', $validated['daily_budget_limit'], 'float');

            Log::info('Daily budget updated', [
                'new_limit' => $validated['daily_budget_limit'],
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Daily budget limit updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Update daily budget error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating daily budget'
            ], 500);
        }
    }
}
