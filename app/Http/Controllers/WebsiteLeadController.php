<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Services\BranchResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebsiteLeadController extends Controller
{
    protected $branchResolver;

    public function __construct(BranchResolver $branchResolver)
    {
        $this->branchResolver = $branchResolver;
    }

    public function handle(Request $request, $routeBranchId = null)
    {
        // ── 1. Validate API key ────────────────────────────────────────────
        $expectedKey = config('services.website.webhook_key');
        if ($request->header('X-API-KEY') !== $expectedKey) {
            Log::warning('Website webhook: invalid API key');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ── 2. Resolve Branch dynamically using BranchResolver ─────────────
        $resolution = $this->branchResolver->resolve($request, $routeBranchId);
        $branch = $resolution['branch'];
        $branchId = $resolution['branch_id'];

        if (!$branch || !$branchId) {
            Log::error('Website webhook: branch resolution failed', [
                'resolution' => $resolution,
                'payload'    => $request->all(),
            ]);
            return response()->json(['error' => 'Invalid or missing branch'], 400);
        }

        // ── 3. Validate fields ─────────────────────────────────────────────
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:1000',
        ]);

        // ── 4. Detailed pre-save logging ──────────────────────────────────
        Log::info('Website lead pre-save details', [
            'request_origin'    => $resolution['request_origin'],
            'request_referer'   => $resolution['request_referer'],
            'request_host'      => $resolution['request_host'],
            'website'           => $resolution['request_referer'] ?: $resolution['request_origin'],
            'incoming_payload'  => $request->all(),
            'incoming_branch'   => $resolution['incoming_branch'],
            'resolution_method' => $resolution['resolution_method'],
            'resolved_branch'   => $resolution['branch_name'],
            'branch_id'         => $branchId,
        ]);

        // ── 5. Prevent duplicate phone or email in same branch ───────────────
        $existingQuery = Lead::where('branch_id', $branchId)->whereNull('deleted_at');

        $existing = (clone $existingQuery)->where('phone', $validated['phone'])->first();

        if (!$existing && !empty($validated['email'])) {
            $existing = (clone $existingQuery)->where('email', $validated['email'])->first();
        }

        if ($existing) {
            Log::info('Website webhook: phone/email already exists, appending note', [
                'phone'     => $validated['phone'],
                'email'     => $validated['email'] ?? null,
                'branch_id' => $branchId,
                'lead_id'   => $existing->id,
            ]);

            if (!empty($validated['message'])) {
                \App\Models\LeadNote::create([
                    'lead_id'    => $existing->id,
                    'created_by' => 1, // SuperAdmin / System ID
                    'note'       => 'New Website Enquiry: ' . $validated['message'],
                ]);
            }

            return response()->json([
                'status'    => 'existing_lead',
                'lead_id'   => $existing->id,
                'lead_code' => $existing->lead_code,
                'message'   => 'Note added to existing lead',
            ], 200);
        }

        // ── 6. Get website lead source ─────────────────────────────────────
        $source = LeadSource::where('code', 'website')->where('is_active', true)->first();

        if (!$source) {
            Log::error('Website webhook: lead source not found');
            return response()->json(['error' => 'Lead source not configured'], 500);
        }

        // ── 7. Create lead ─────────────────────────────────────────────────
        try {
            $message = $validated['message'] ?? '';
            
            // Extract SQFT from message (e.g. "1000-2000 sq.ft" or "Upto 1000 sq.ft" or "1000-2000")
            $sqft = null;
            if (preg_match('/(\d+(?:-\d+)?)\s*sq\.?ft/i', $message, $m)) {
                $sqft = $m[1];
            } elseif (preg_match('/upto\s*(\d+)/i', $message, $m)) {
                $sqft = '0-' . $m[1];
            }

            // Determine service_type
            $serviceType = 'cleaning'; // default for website leads
            if (stripos($message, 'pest') !== false || stripos($message, 'termite') !== false || stripos($message, 'bed bug') !== false || stripos($message, 'mosquito') !== false) {
                $serviceType = 'pest_control';
            }

            $lead = Lead::create([
                'name'           => $validated['name'],
                'phone'          => $validated['phone'],
                'email'          => $validated['email'] ?? null,
                'address'        => $validated['address'] ?? null,
                'lead_source_id' => $source->id,
                'branch_id'      => $branchId,
                'assigned_to'    => null,
                'status'         => 'pending',
                'sqft'           => $sqft,
                'description'    => $message ?: null,
                'created_by'     => null,
                'service_type'   => $serviceType,
            ]);

            // Match and attach services to lead_service pivot
            $matchedServices = \App\Models\Service::where('is_active', true)->get()->filter(function($s) use ($message) {
                return stripos($message, $s->name) !== false || stripos($s->name, 'Deep Cleaning Full House') !== false && (stripos($message, 'Full House') !== false || stripos($message, 'Deep Cleaning') !== false);
            });

            if ($matchedServices->isEmpty() && $serviceType === 'cleaning') {
                $matchedServices = \App\Models\Service::where('name', 'Deep Cleaning Full House')->get();
            }

            foreach ($matchedServices as $service) {
                $lead->services()->attach($service->id, ['quantity' => 1]);
            }

            Log::info('Website lead created successfully', [
                'lead_id'            => $lead->id,
                'lead_code'          => $lead->lead_code,
                'branch_id'          => $branchId,
                'final_db_branch_id' => $lead->branch_id,
            ]);

            return response()->json([
                'status'    => 'success',
                'lead_id'   => $lead->id,
                'lead_code' => $lead->lead_code,
                'branch_id' => $lead->branch_id,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Website webhook: lead creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create lead'], 500);
        }
    }
}

