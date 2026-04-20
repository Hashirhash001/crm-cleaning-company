<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebsiteLeadController extends Controller
{
    public function handle(Request $request, $branchId = 2)  // ← default branch 2
    {
        // ── 1. Validate API key ────────────────────────────────────────────
        $expectedKey = config('services.website.webhook_key');
        if ($request->header('X-API-KEY') !== $expectedKey) {
            Log::warning('Website webhook: invalid API key');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ── 2. Validate branch exists ──────────────────────────────────────
        $branchId = (int) $branchId;
        if (!in_array($branchId, [1, 2])) {  // whitelist allowed branch IDs
            return response()->json(['error' => 'Invalid branch'], 400);
        }

        // ── 3. Validate fields ─────────────────────────────────────────────
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:1000',
        ]);

        // ── 4. Prevent duplicate phone in same branch ──────────────────────
        $existing = Lead::where('phone', $validated['phone'])
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            Log::info('Website webhook: phone already exists', [
                'phone'     => $validated['phone'],
                'branch_id' => $branchId,
            ]);
            return response()->json(['status' => 'existing_lead', 'lead_id' => $existing->id], 200);
        }

        // ── 5. Get website lead source ─────────────────────────────────────
        $source = LeadSource::where('code', 'website')->where('is_active', true)->first();

        if (!$source) {
            Log::error('Website webhook: lead source not found');
            return response()->json(['error' => 'Lead source not configured'], 500);
        }

        // ── 6. Create lead ─────────────────────────────────────────────────
        try {
            $lead = Lead::create([
                'name'           => $validated['name'],
                'phone'          => $validated['phone'],
                'email'          => $validated['email'] ?? null,
                'address'        => $validated['address'] ?? null,
                'lead_source_id' => $source->id,
                'branch_id'      => $branchId,             // ← dynamic
                'assigned_to'    => null,
                'status'         => 'pending',
                'description'    => $validated['message'] ?? null,
            ]);

            Log::info('Website lead created', [
                'lead_id'   => $lead->id,
                'lead_code' => $lead->lead_code,
                'branch_id' => $branchId,
            ]);

            return response()->json([
                'status'    => 'success',
                'lead_id'   => $lead->id,
                'lead_code' => $lead->lead_code,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Website webhook: lead creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create lead'], 500);
        }
    }
}
