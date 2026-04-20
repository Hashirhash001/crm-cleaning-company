<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAdsLeadController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Google Ads webhook received', $payload);

        // ── 1. Validate webhook key ────────────────────────────────────────
        $expectedKey = config('services.google_ads.webhook_key');
        if (($payload['google_key'] ?? null) !== $expectedKey) {
            Log::warning('Google Ads webhook: invalid key');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ── 2. Skip test leads ─────────────────────────────────────────────
        if (!empty($payload['is_test'])) {
            Log::info('Google Ads webhook: test lead ignored');
            return response()->json(['status' => 'test_ignored'], 200);
        }

        // ── 3. Require lead_id ─────────────────────────────────────────────
        $leadId = $payload['lead_id'] ?? null;
        if (!$leadId) {
            return response()->json(['error' => 'Missing lead_id'], 400);
        }

        // ── 4. Prevent duplicates by google_lead_id ────────────────────────
        if (Lead::where('google_lead_id', $leadId)->exists()) {
            Log::info('Google Ads webhook: duplicate lead ignored', ['lead_id' => $leadId]);
            return response()->json(['status' => 'duplicate'], 200);
        }

        // ── 5. Extract user_column_data fields ─────────────────────────────
        $fields = [];
        foreach ($payload['user_column_data'] ?? [] as $col) {
            $fields[$col['column_id']] = $col['string_value'] ?? null;
        }

        $name  = $fields['FULL_NAME'] ?? ($fields['FIRST_NAME'] ?? 'Google Ads Lead');
        $email = $fields['EMAIL'] ?? null;
        $phone = $fields['PHONE_NUMBER'] ?? null;

        // ── 6. Get Google Ads lead source ──────────────────────────────────
        $source = LeadSource::where('code', 'google_ads')->where('is_active', true)->first();

        if (!$source) {
            Log::error('Google Ads webhook: lead source not found');
            return response()->json(['error' => 'Lead source not configured'], 500);
        }

        // ── 7. Prevent duplicate phone in branch ───────────────────────────
        if ($phone) {
            $existing = Lead::where('phone', $phone)
                ->where('branch_id', 1)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                Log::info('Google Ads webhook: phone already exists', ['phone' => $phone]);
                if (!$existing->google_lead_id) {
                    $existing->update(['google_lead_id' => $leadId]);
                }
                return response()->json(['status' => 'existing_lead', 'lead_id' => $existing->id], 200);
            }
        }

        // ── 8. Create lead ─────────────────────────────────────────────────
        try {
            $lead = Lead::create([
                'google_lead_id' => $leadId,
                'name'           => $name,
                'email'          => $email,
                'phone'          => $phone ?? 'N/A',
                'lead_source_id' => $source->id,
                'branch_id'      => 1,
                'assigned_to'    => null,
                'status'         => 'pending',
                'description'    => $this->buildDescription($payload, $fields),
            ]);

            Log::info('Google Ads lead created', [
                'lead_id'        => $lead->id,
                'lead_code'      => $lead->lead_code,
                'google_lead_id' => $leadId,
            ]);

            return response()->json([
                'status'    => 'success',
                'lead_id'   => $lead->id,
                'lead_code' => $lead->lead_code,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Google Ads webhook: lead creation failed', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
            return response()->json(['error' => 'Failed to create lead'], 500);
        }
    }

    // ── Build description from payload ─────────────────────────────────────
    private function buildDescription(array $payload, array $fields): string
    {
        $parts = ['📢 Source: Google Ads'];

        if (!empty($payload['campaign_id']))  $parts[] = '📋 Campaign ID: ' . $payload['campaign_id'];
        if (!empty($payload['adgroup_id']))   $parts[] = '🎯 Ad Group ID: ' . $payload['adgroup_id'];
        if (!empty($payload['form_id']))      $parts[] = '📝 Form ID: ' . $payload['form_id'];
        if (!empty($fields['CITY']))          $parts[] = '📍 City: ' . $fields['CITY'];
        if (!empty($fields['ZIP_CODE']))      $parts[] = '📮 ZIP: ' . $fields['ZIP_CODE'];

        return implode(' | ', $parts);
    }

}
