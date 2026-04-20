<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebsiteLeadController extends Controller
{
    public function handle(Request $request)
    {
        // ── 1. Validate API key ────────────────────────────────────────────
        $expectedKey = config('services.website.webhook_key');
        if ($request->header('X-API-KEY') !== $expectedKey) {
            Log::warning('Website webhook: invalid API key');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ── 2. Validate required fields ────────────────────────────────────
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',   // ← location field
            'message' => 'nullable|string|max:1000',  // ← service field
        ]);

        // ── 3. Prevent duplicate phone ─────────────────────────────────────
        $existing = Lead::where('phone', $validated['phone'])
            ->where('branch_id', 2)                   // ← branch 2
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            Log::info('Website webhook: phone already exists', ['phone' => $validated['phone']]);
            return response()->json(['status' => 'existing_lead', 'lead_id' => $existing->id], 200);
        }

        // ── 4. Get website lead source ─────────────────────────────────────
        $source = LeadSource::where('code', 'website')->where('is_active', true)->first();

        if (!$source) {
            Log::error('Website webhook: lead source not found');
            return response()->json(['error' => 'Lead source not configured'], 500);
        }

        // ── 5. Create lead ─────────────────────────────────────────────────
        try {
            $lead = Lead::create([
                'name'           => $validated['name'],
                'phone'          => $validated['phone'],
                'email'          => $validated['email'] ?? null,
                'address'        => $validated['address'] ?? null,  // ← location
                'lead_source_id' => $source->id,
                'branch_id'      => 2,                              // ← branch 2
                'assigned_to'    => null,
                'status'         => 'pending',
                'description'    => $validated['message'] ?? null,  // ← service
            ]);

            Log::info('Website lead created', [
                'lead_id'   => $lead->id,
                'lead_code' => $lead->lead_code,
                'phone'     => $lead->phone,
                'address'   => $lead->address,
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
