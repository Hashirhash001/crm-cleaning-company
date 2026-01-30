<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\LeadCall;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadApproval;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Base query with relationships
        $query = Lead::with(['branch', 'services', 'source', 'createdBy', 'assignedTo']);

        // Get all telecallers for assignment dropdown
        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->select('id', 'name', 'branch_id')
            ->orderBy('name')
            ->get();

        // ============================================
        // ROLE-BASED ACCESS CONTROL
        // ============================================
        if ($user->role === 'super_admin') {
            // Super admin sees all leads
        } elseif ($user->role === 'lead_manager') {
            // Lead manager sees only their created leads
            $query->where('created_by', $user->id);
        } elseif ($user->role === 'telecallers') {
            // Telecallers see all leads in their branch
            $query->where('branch_id', $user->branch_id);
        } else {
            abort(403, 'Unauthorized');
        }

        // ============================================
        // APPLY FILTERS
        // ============================================

        // Mode filter (All Leads / Open Leads / Work Orders)
        $mode = $request->input('mode', '');

        if ($mode === 'open') {
            // Open leads = anything NOT approved
            $query->where('status', '!=', 'approved')
                ->where('status', '!=', 'confirmed');
        } elseif ($mode === 'approved' || $request->status === 'approved') {
            // Work Orders = approved leads only
            $query->where('status', 'approved');
        }
        // Default: show all leads (no additional filter)

        // Status filter (only apply if not using mode filter)
        if ($request->filled('status') && !$mode) {
            $query->where('status', $request->status);
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Lead source filter
        if ($request->filled('lead_source_id') && $request->lead_source_id != '0') {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        // Service filter - check the many-to-many relationship
        if ($request->filled('service_id')) {
            $serviceId = $request->service_id;
            $query->whereHas('services', function($subQuery) use ($serviceId) {
                $subQuery->where('services.id', $serviceId);
            });
        }

        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Assigned to filter
        if ($request->filled('assignedto')) {
            if ($user->role === 'telecallers') {
                // telecaller: only allow me/unassigned
                if ($request->assignedto === 'me') {
                    $query->where('assigned_to', $user->id);
                } elseif ($request->assignedto === 'unassigned') {
                    $query->whereNull('assigned_to');
                }
            } else {
                // admin/manager: allow unassigned or numeric telecaller id
                if ($request->assignedto === 'unassigned') {
                    $query->whereNull('assigned_to');
                } else {
                    $query->where('assigned_to', $request->assignedto);
                }
            }
        }

        // Search filter - Optimized version
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';

            $query->where(function($q) use ($search) {
                // Direct lead fields (fastest)
                $q->where('leads.name', 'like', $search)
                ->orWhere('leads.email', 'like', $search)
                ->orWhere('leads.phone', 'like', $search)
                ->orWhere('leads.lead_code', 'like', $search);
            })
            // Separate OR for related tables (better indexing)
            ->orWhere(function($q) use ($search) {
                $q->whereHas('customer', function($subQuery) use ($search) {
                    $subQuery->where('customer_code', 'like', $search);
                })
                ->orWhereHas('jobs', function($subQuery) use ($search) {
                    $subQuery->where('job_code', 'like', $search);
                });
            });
        }

        // ============================================
        // SORTING
        // ============================================
        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Use the scope for sorting (assuming you have a scope in Lead model)
        $query->sort($sortColumn, $sortDirection);

        // ============================================
        // PAGINATION WITH PER PAGE SELECTION
        // ============================================
        $perPage = $request->get('per_page', 15);

        // Validate per_page to prevent abuse
        $allowedPerPage = [15, 30, 50, 100, 500, 1000];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        // Paginate results
        $leads = $query->paginate($perPage);

        // ============================================
        // CALCULATE PENDING COUNT
        // ============================================
        if ($user->role === 'super_admin') {
            $pendingcount = Lead::where('status', 'pending')->count();
            $pendingApproval = Lead::where('status', 'confirmed')->count();
        } elseif ($user->role === 'lead_manager') {
            $pendingcount = Lead::where('status', 'pending')
                ->where('created_by', $user->id)
                ->count();

            $pendingApproval = Lead::where('status', 'confirmed')
                ->where('created_by', $user->id)
                ->count();
        } else {
            $pendingcount = 0;
            $pendingApproval = 0;
        }

        // Get filter dropdown data
        $branches = Branch::where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $lead_sources = LeadSource::where('is_active', true)->get();

        // ============================================
        // RETURN AJAX OR NORMAL VIEW
        // ============================================
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'from' => $leads->firstItem() ?? 0,
                'to' => $leads->lastItem() ?? 0,
                'current_sort' => [
                    'column' => $sortColumn,
                    'direction' => $sortDirection,
                ],
            ]);
        }

        return view('leads.index', compact('leads', 'pendingcount', 'pendingApproval', 'branches', 'services', 'lead_sources', 'telecallers'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized to create leads');
        }

        $services = Service::where('is_active', true)->get();
        // Get distinct service types from database
        $serviceTypes = Service::select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type')
            ->toArray();
        $lead_sources = LeadSource::where('is_active', true)->get();

        if ($user->role === 'super_admin') {
            $telecallers = User::where('role', 'telecallers')
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('is_active', true)->get();
        } else {
            $telecallers = User::where('role', 'telecallers')
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        return view('leads.create', compact('services', 'serviceTypes', 'lead_sources', 'telecallers', 'branches'));
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            // Authorization check
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized',
                        'errors' => ['authorization' => ['You are not authorized to create leads']]
                    ], 403);
                }
                return back()->withInput()->with('error', 'Unauthorized');
            }

            $branchId = $user->role === 'super_admin'
                ? $request->input('branch_id')
                : $user->branch_id;

            // Validate
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('leads', 'email')->where(fn ($q) => $q->where('branch_id', $branchId)),
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('leads', 'phone')->where(fn ($q) => $q->where('branch_id', $branchId)),
                ],
                'phone_alternative' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'district' => 'nullable|string|max:100',
                'property_type' => 'nullable|in:commercial,residential',
                'sqft' => 'nullable|string|max:100',
                'sqft_custom' => 'nullable|string|max:100',
                // No service_type required - will auto-detect
                'service_ids' => 'nullable|array',
                'service_ids.*' => 'nullable:services,id',
                'service_quantities' => 'nullable|array',
                'service_quantities.*' => 'nullable|integer|min:1',
                'lead_source_id' => 'required|exists:lead_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'amount' => 'nullable|numeric|min:0',
                'advance_paid_amount' => 'nullable|numeric|min:0|lte:amount',
                'payment_mode' => 'nullable|in:cash,upi,card,bank_transfer,neft,gpay,phonepe,paytm,amazonpay,qrcode',
                'branch_id' => $user->role === 'super_admin' ? 'required|exists:branches,id' : 'nullable',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation,confirmed',
            ],
            [
                'email.unique' => 'This email already exists in this branch.',
                'phone.unique' => 'This phone number already exists in this branch.',
            ]
            );

            // Auto-detect service_type from first selected service
            $serviceType = 'other';

            if (!empty($validated['service_ids']) && is_array($validated['service_ids'])) {
                $firstService = Service::find($validated['service_ids'][0]);
                $serviceType = $firstService ? $firstService->service_type : 'other';
            }

            // Determine branch
            $branchId = $user->role === 'super_admin' ? $validated['branch_id'] : $user->branch_id;

            if (!$branchId) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Branch Error',
                        'errors' => ['branch_id' => ['Branch not found for your account']]
                    ], 422);
                }
                return back()->withInput()->with('error', 'Error: Branch not found for your account.');
            }

            // Determine assigned_to
            $assignedTo = $validated['assigned_to'] ?? null;
            if ($user->role === 'telecallers' && !$assignedTo) {
                $assignedTo = $user->id;
            }

            // Handle SQFT: Use custom value if 'custom' is selected
            $sqftValue = $validated['sqft'] ?? null;
            if ($sqftValue === 'custom' && !empty($validated['sqft_custom'])) {
                $sqftValue = $validated['sqft_custom'];
            }

            // Create lead
            $lead = Lead::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'phone_alternative' => $validated['phone_alternative'] ?? null,
                'address' => $validated['address'] ?? null,
                'district' => $validated['district'] ?? null,
                'service_type' => $serviceType, // Auto-detected
                'property_type' => $validated['property_type'] ?? null,
                'sqft' => $sqftValue,
                'lead_source_id' => $validated['lead_source_id'],
                'assigned_to' => $assignedTo,
                'amount' => $validated['amount'] ?? null,
                'advance_paid_amount' => $validated['advance_paid_amount'] ?? 0,
                'payment_mode' => $validated['payment_mode'] ?? null,
                'amount_updated_at' => $validated['amount'] ? now() : null,
                'amount_updated_by' => $validated['amount'] ? $user->id : null,
                'branch_id' => $branchId,
                'description' => $validated['description'] ?? null,
                'created_by' => $user->id,
                'status' => $validated['status'],
            ]);

            // Attach selected services WITH quantities
            $serviceData = [];

            if (!empty($validated['service_ids']) && is_array($validated['service_ids'])) {
                foreach ($validated['service_ids'] as $serviceId) {
                    if (!$serviceId) continue;

                    $quantity = $validated['service_quantities'][$serviceId] ?? 1;
                    $serviceData[$serviceId] = ['quantity' => $quantity];
                }

                if (!empty($serviceData)) {
                    $lead->services()->attach($serviceData);
                    $lead->update(['service_id' => array_key_first($serviceData)]);
                }
            }

            Log::info('Lead created successfully', [
                'lead_id' => $lead->id,
                'lead_code' => $lead->lead_code,
                'services' => $serviceData,
                'created_by' => $user->id,
            ]);

            // Check if this is an AJAX request (from Create & Convert button)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead created successfully',
                    'lead_id' => $lead->id,
                    'lead_code' => $lead->lead_code,
                    'name' => $lead->name,
                ]);
            }

            // Regular form submission - redirect to leads index
            return redirect()->route('leads.index')->with('success', json_encode([
                'title' => 'Lead Created Successfully!',
                'lead_id' => $lead->id,
                'message' => "Lead {$lead->lead_code} has been created.",
                'lead_code' => $lead->lead_code,
                'name' => $lead->name,
            ]));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Lead validation error', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);

            $firstMessage = collect($e->errors())->flatten()->first() ?? 'Validation failed';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstMessage,
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Lead creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating lead: ' . $e->getMessage(),
                    'errors' => ['general' => ['Error creating lead: ' . $e->getMessage()]]
                ], 500);
            }

            return back()->withInput()->with('error', 'Error creating lead: ' . $e->getMessage());
        }
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
            abort(403, 'You can only edit your own leads');
        }

        if ($user->role === 'telecallers' && $lead->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }

        // Get ALL services - no filtering by type
        $services = Service::where('is_active', true)
            ->orderBy('service_type')
            ->orderBy('name')
            ->get();
        // Get distinct service types from database
        $serviceTypes = Service::select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type')
            ->toArray();

        $lead_sources = LeadSource::where('is_active', true)->get();

        if ($user->role === 'super_admin') {
            $telecallers = User::where('role', 'telecallers')
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('is_active', true)->get();
        } else {
            $telecallers = User::where('role', 'telecallers')
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->select('id', 'name', 'branch_id')
                ->get();
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        return view('leads.edit', compact('lead', 'services', 'serviceTypes', 'lead_sources', 'telecallers', 'branches'));
    }

    public function update(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            // Authorization
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized',
                        'errors' => ['authorization' => ['You are not authorized to update this lead']]
                    ], 403);
                }
                return back()->with('error', 'Unauthorized');
            }

            if ($user->role === 'telecallers' && $lead->branch_id !== $user->branch_id) {
                abort(403, 'Unauthorized');
            }

            if ($user->role === 'lead_manager' && $lead->created_by != $user->id) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized',
                        'errors' => ['authorization' => ['You can only edit your own leads']]
                    ], 403);
                }
                return back()->with('error', 'You can only edit your own leads');
            }

            $isApprovedLead = ($lead->status === 'approved');

            $branchId = $user->role === 'super_admin'
                ? $request->input('branch_id', $lead->branch_id)
                : $user->branch_id;

            // Build validation rules
            $validationRules = [
                'name' => 'required|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('leads', 'email')
                        ->where(fn ($q) => $q->where('branch_id', $branchId))
                        ->ignore($lead->id),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('leads', 'phone')
                        ->where(fn ($q) => $q->where('branch_id', $branchId))
                        ->ignore($lead->id),
                ],
                'phone_alternative' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'district' => 'nullable|string|max:100',
                'property_type' => 'nullable|in:commercial,residential',
                'sqft' => 'nullable|string|max:100',
                'sqft_custom' => 'nullable|string|max:100',
                // No service_type validation - will be auto-detected
                'service_ids' => 'nullable|array',
                'service_ids.*' => 'nullable|exists:services,id',
                'service_quantities' => 'nullable|array',
                'service_quantities.*' => 'nullable|integer|min:1',
                'lead_source_id' => 'required|exists:lead_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'amount' => 'nullable|numeric|min:0',
                'advance_paid_amount' => 'nullable|numeric|min:0|lte:amount',
                'payment_mode' => 'nullable|in:cash,upi,card,bank_transfer,neft,gpay,phonepe,paytm,amazonpay,qrcode',
                'branch_id' => $user->role === 'super_admin' ? 'required|exists:branches,id' : 'nullable',
                'description' => 'nullable|string',
            ];

            // Status validation
            if ($isApprovedLead) {
                $validationRules['status'] = 'required|in:approved';
            } else {
                if ($user->role === 'super_admin') {
                    $validationRules['status'] = 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation,approved,rejected,confirmed';
                } else {
                    $validationRules['status'] = 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation,confirmed';
                }
            }

            $validated = $request->validate($validationRules);

            // Auto-detect service_type from first selected service OR keep existing
            $serviceType = $lead->service_type; // Default to existing

            if (!empty($validated['service_ids']) && is_array($validated['service_ids'])) {
                $firstService = Service::find($validated['service_ids'][0]);
                $serviceType = $firstService ? $firstService->service_type : ($lead->service_type ?? 'other');
            }

            if ($user->role === 'telecallers') {
                $validated['assigned_to'] = $user->id;
            }

            $branchId = $user->role === 'super_admin' ? $validated['branch_id'] : $user->branch_id;

            $sqftValue = $validated['sqft'] ?? null;
            if ($sqftValue === 'custom' && !empty($validated['sqft_custom'])) {
                $sqftValue = $validated['sqft_custom'];
            }

            // Track changes
            $nameChanged = $validated['name'] != $lead->name;
            $phoneChanged = $validated['phone'] != $lead->phone;
            $emailChanged = ($validated['email'] ?? null) != $lead->email;
            $amountChanged = isset($validated['amount']) && $validated['amount'] != $lead->amount;
            $amountPaidChanged = isset($validated['advance_paid_amount']) && $validated['advance_paid_amount'] != $lead->advance_paid_amount;

            // Check service changes INCLUDING quantities
            $oldServiceData = $lead->services->mapWithKeys(function ($service) {
                return [$service->id => $service->pivot->quantity];
            })->toArray();

            // Build new service data with quantities
            $newServiceData = [];
            if (!empty($validated['service_ids']) && is_array($validated['service_ids'])) {
                // Remove duplicates and empty values
                $serviceIds = array_filter(array_unique($validated['service_ids']));

                foreach ($serviceIds as $serviceId) {
                    if ($serviceId && is_numeric($serviceId)) {
                        $quantity = isset($validated['service_quantities'][$serviceId])
                            ? (int)$validated['service_quantities'][$serviceId]
                            : 1;

                        $newServiceData[(int)$serviceId] = ['quantity' => $quantity];
                    }
                }
            }

            Log::info('Service data comparison', [
                'old' => $oldServiceData,
                'new' => $newServiceData,
                'validated_ids' => $validated['service_ids'] ?? [],
                'validated_quantities' => $validated['service_quantities'] ?? []
            ]);

            $servicesChanged = ($oldServiceData != $newServiceData);


            // Update lead
            $lead->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'phone_alternative' => $validated['phone_alternative'] ?? null,
                'address' => $validated['address'] ?? null,
                'district' => $validated['district'] ?? null,
                'service_type' => $serviceType, // Auto-detected or existing
                'property_type' => $validated['property_type'] ?? null,
                'sqft' => $sqftValue ?? null,
                'lead_source_id' => $validated['lead_source_id'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'advance_paid_amount' => $validated['advance_paid_amount'] ?? 0,
                'payment_mode' => $validated['payment_mode'] ?? null,
                'amount_updated_at' => $amountChanged ? now() : $lead->amount_updated_at,
                'amount_updated_by' => $amountChanged ? $user->id : $lead->amount_updated_by,
                'branch_id' => $branchId,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            // Sync services with quantities
            if (!empty($newServiceData)) {
                Log::info('Syncing services', ['data' => $newServiceData]);

                // Sync all services with quantities
                $lead->services()->sync($newServiceData);

                // Update service_id to first service (or keep existing logic)
                $firstServiceId = array_key_first($newServiceData);
                $lead->update(['service_id' => $firstServiceId]);

                Log::info('Services synced successfully', [
                    'lead_id' => $lead->id,
                    'service_count' => count($newServiceData),
                    'first_service_id' => $firstServiceId
                ]);
            } else {
                // If no services selected, detach all
                Log::info('No services selected, detaching all', ['lead_id' => $lead->id]);
                $lead->services()->detach();
                $lead->update(['service_id' => null]);
            }

            // Reload services to confirm
            $lead->load('services');
            Log::info('Final services after sync', [
                'lead_id' => $lead->id,
                'services' => $lead->services->pluck('name', 'id')->toArray()
            ]);


            // Sync to customer and jobs if approved
            $customerUpdated = false;
            $jobsUpdated = false;

            if ($isApprovedLead) {
                // Sync customer
                try {
                    $lead->load('customer');
                    $customer = $lead->customer;

                    if ($customer) {
                        $customerUpdateData = [];

                        if ($nameChanged) {
                            $customerUpdateData['name'] = $validated['name'];
                        }
                        if ($phoneChanged) {
                            $customerUpdateData['phone'] = $validated['phone'];
                        }
                        if ($emailChanged) {
                            $customerUpdateData['email'] = $validated['email'];
                        }

                        if (!empty($customerUpdateData)) {
                            $customer->update($customerUpdateData);
                            $customerUpdated = true;
                            Log::info('Customer synced from lead update', [
                                'lead_id' => $lead->id,
                                'customer_id' => $customer->id,
                                'changes' => $customerUpdateData
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error syncing customer: ' . $e->getMessage());
                }

                // Sync jobs
                try {
                    $lead->load('jobs');
                    $jobs = $lead->jobs;

                    if ($jobs->isNotEmpty()) {
                        foreach ($jobs as $job) {
                            $jobUpdateData = [];

                            if ($nameChanged) {
                                $jobUpdateData['title'] = $validated['name'];
                            }

                            if ($servicesChanged) {
                                // Sync services to job
                                $job->services()->sync($newServiceData);
                                if (!empty($newServiceData)) {
                                    $jobUpdateData['service_id'] = array_key_first($newServiceData);
                                }
                            }

                            if ($amountChanged || $amountPaidChanged) {
                                if ($amountChanged) {
                                    $jobUpdateData['amount'] = $validated['amount'] ?? 0;
                                }
                                if ($amountPaidChanged) {
                                    $jobUpdateData['amount_paid'] = $validated['advance_paid_amount'] ?? 0;
                                }

                                // Mark job as needing approval if amounts changed
                                $jobUpdateData['approval_status'] = 'pending';
                                $jobUpdateData['approval_notes'] = 'Amount updated from lead edit. Requires admin approval.';
                            }

                            if (!empty($jobUpdateData)) {
                                $job->update($jobUpdateData);
                                $jobsUpdated = true;

                                Log::info('Job synced from lead update', [
                                    'lead_id' => $lead->id,
                                    'job_id' => $job->id,
                                    'changes' => $jobUpdateData
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error syncing jobs: ' . $e->getMessage());
                }
            }

            Log::info('Lead updated successfully', [
                'lead_id' => $lead->id,
                'updated_by' => $user->id,
                'is_approved' => $isApprovedLead,
                'customer_synced' => $customerUpdated,
                'jobs_synced' => $jobsUpdated
            ]);

            // Build success message
            $message = 'Lead updated successfully!';
            if ($isApprovedLead) {
                if ($customerUpdated && $jobsUpdated) {
                    $message .= ' Customer and related jobs have been updated.';
                } elseif ($customerUpdated) {
                    $message .= ' Customer information has been updated.';
                } elseif ($jobsUpdated) {
                    $message .= ' Related jobs have been updated and marked for approval.';
                }
            }

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'lead_id' => $lead->id,
                    'lead_code' => $lead->lead_code,
                    'customer_updated' => $customerUpdated,
                    'jobs_updated' => $jobsUpdated,
                ]);
            }

            // Regular form submission - redirect to leads index or show page
            return redirect()->route('leads.show', $lead)
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Lead update validation error', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'lead_id' => $lead->id,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Lead update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'lead_id' => $lead->id,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating lead: ' . $e->getMessage(),
                    'errors' => ['general' => ['Error updating lead: ' . $e->getMessage()]]
                ], 500);
            }

            return back()->withInput()->with('error', 'Error updating lead: ' . $e->getMessage());
        }
    }

    public function show(Lead $lead)
    {
        $user = auth()->user();

        if ($user->role === 'telecallers' && $lead->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
            abort(403, 'You can only view your own leads.');
        }

        $lead->load([
            'branch',
            'source',
            'services',
            'createdBy',
            'assignedTo',
            'amountUpdatedBy',
            'calls.user',
            'notes.createdBy',
            'customer',
            'jobs.service',
            'jobs.assignedTo',
            'followups' => function($query) {
                $query->with('assignedToUser', 'createdBy')
                      ->orderBy('followup_date', 'asc')
                      ->orderBy('followup_time', 'asc');
            }
        ]);

        return view('leads.show', compact('lead'));
    }

    public function getServicesByType(Request $request)
    {
        $serviceType = $request->input('service_type');

        $services = Service::where('service_type', $serviceType)
                           ->where('is_active', true)
                           ->orderBy('name')
                           ->get(['id', 'name']);

        return response()->json($services);
    }

    public function assignLead(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'notes' => 'nullable|string'
            ]);

            $userToAssign = User::findOrFail($validated['assigned_to']);

            if ((int)$userToAssign->branch_id !== (int)$lead->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected staff does not belong to this branch.'
                ], 422);
            }

            $lead->update([
                'assigned_to' => $validated['assigned_to']
            ]);

            // Optionally add note
            if ($request->filled('notes')) {
                LeadNote::create([
                    'lead_id' => $lead->id,
                    'created_by' => auth()->id(),
                    'note' => 'Assignment Note: ' . $validated['notes']
                ]);
            }

            $telecaller = User::find($validated['assigned_to']);

            Log::info('Lead assigned', [
                'lead_id' => $lead->id,
                'assigned_to' => $telecaller->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Lead assigned to {$telecaller->name} successfully!"
            ]);

        } catch (\Exception $e) {
            Log::error('Lead assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning lead'
            ], 500);
        }
    }

    public function bulkAssign(Request $request)
    {
        try {
            $user = auth()->user();

            // Authorization check
            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Validate request
            $validated = $request->validate([
                'lead_ids' => 'required|array',
                'lead_ids.*' => 'exists:leads,id',
                'assigned_to' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $telecaller = User::find($validated['assigned_to']);
            $count = 0;
            $approvedCount = 0;

            $leads = Lead::whereIn('id', $validated['lead_ids'])->get(['id','branch_id']);

            $uniqueBranches = $leads->pluck('branch_id')->filter()->unique();

            if ($uniqueBranches->count() !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk assignment is allowed only for leads from the same branch.'
                ], 422);
            }

            if ((int)$telecaller->branch_id !== (int)$uniqueBranches[0]) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected staff does not belong to this branch.'
                ], 422);
            }

            foreach ($validated['lead_ids'] as $leadId) {
                $lead = Lead::find($leadId);

                if ($lead) {
                    // Count approved leads separately
                    if ($lead->status === 'approved') {
                        $approvedCount++;
                    }

                    // Update ALL leads regardless of status
                    $lead->update([
                        'assigned_to' => $validated['assigned_to']
                    ]);

                    // Add note if provided
                    if ($request->filled('notes')) {
                        LeadNote::create([
                            'lead_id' => $lead->id,
                            'created_by' => $user->id,
                            'note' => 'Bulk Assignment Note: ' . $validated['notes']
                        ]);
                    }

                    $count++;
                }
            }

            Log::info('Bulk lead assignment', [
                'count' => $count,
                'approved_count' => $approvedCount,
                'assigned_to' => $telecaller->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk assignment completed!',
                'count' => $count,
                'approved_count' => $approvedCount,
                'telecaller_name' => $telecaller->name
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk assignment'
            ], 500);
        }
    }

    public function addCall(Request $request, Lead $lead)
    {
        try {
            $validated = $request->validate([
                'call_date' => 'required|date',
                'duration' => 'nullable|integer|min:0',
                'outcome' => 'required|in:interested,not_interested,callback,no_answer,wrong_number',
                'notes' => 'nullable|string',
                // Followup fields (optional)
                'followup_date' => 'nullable|required_if:outcome,callback,interested|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'callback_time_preference' => 'nullable|in:morning,afternoon,evening,anytime',
                'followup_priority' => 'nullable|in:low,medium,high',
                'followup_notes' => 'nullable|string',
            ]);

            // Create call log
            $call = LeadCall::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'call_date' => $validated['call_date'],
                'duration' => $validated['duration'] ?? null,
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $followupCreated = false;

            // Create followup if outcome is callback or interested
            if (in_array($validated['outcome'], ['callback', 'interested']) && isset($validated['followup_date'])) {
                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'followup_date' => $validated['followup_date'],
                    'followup_time' => $validated['followup_time'] ?? null,
                    'callback_time_preference' => $validated['callback_time_preference'] ?? 'anytime',
                    'priority' => $validated['followup_priority'] ?? 'medium',
                    'notes' => $validated['followup_notes'] ?? null,
                    'assigned_to' => $lead->assigned_to ?? auth()->id(),
                    'created_by' => auth()->id(),
                    'status' => 'pending',
                ]);

                $followupCreated = true;
            }

            Log::info('Call logged successfully', [
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'followup_created' => $followupCreated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully!',
                'followup_created' => $followupCreated,
            ]);

        } catch (\Exception $e) {
            Log::error('Add call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging call: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addFollowup(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->role === 'telecallers' && $lead->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }
        try {
            $validated = $request->validate([
                'followup_date' => 'required|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'callback_time_preference' => 'nullable|in:morning,afternoon,evening,anytime',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string|max:1000',
            ]);

            LeadFollowup::create([
                'lead_id' => $lead->id,
                'followup_date' => $validated['followup_date'],
                'followup_time' => $validated['followup_time'] ?? null,
                'callback_time_preference' => $validated['callback_time_preference'] ?? 'anytime',
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
                'assigned_to' => $lead->assigned_to ?? auth()->id(),
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            Log::info('Followup scheduled', [
                'lead_id' => $lead->id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup scheduled successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Add followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling followup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function complete(LeadFollowup $followup)
    {
        if ($followup->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already completed']);
        }
        // Own, assigned, or admin-only logic here if needed...
        $followup->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        return response()->json(['success' => true, 'message' => 'Follow-up marked as completed!']);
    }

    public function addNote(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->role === 'telecallers' && $lead->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }

        try {
            $validated = $request->validate([
                'note' => 'required|string',
            ]);

            $note = LeadNote::create([
                'lead_id' => $lead->id,
                'created_by' => auth()->id(),
                'note' => $validated['note'],
            ]);

            Log::info('Note added to lead', ['lead_id' => $lead->id, 'note_id' => $note->id]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Add note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding note'
            ], 500);
        }
    }

    public function destroy(Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // if (in_array($lead->status, ['approved'])) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Can only delete unapproved leads'
            //     ], 403);
            // }

            if ($user->role === 'lead_manager' && $lead->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own leads'
                ], 403);
            }

            if ($user->role === 'telecallers' && $lead->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own leads'
                ], 403);
            }

            $leadName = $lead->name;
            $lead->delete();

            Log::info('Lead deleted', ['lead_name' => $leadName]);

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lead delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lead'
            ], 500);
        }
    }

    public function approve(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin and Lead Manager can approve leads.'
                ], 403);
            }

            if ($user->role === 'telecallers' && $lead->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only approve leads assigned to you.'
                ], 403);
            }

            if (!$lead->amount || $lead->amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead amount must be set before approval. Please add the amount first.'
                ], 400);
            }

            $budgetCheck = $this->checkDailyBudget($lead->amount);

            if (!$budgetCheck['can_approve']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily budget limit exceeded!',
                    'budget_info' => [
                        'daily_limit' => '₹' . number_format($budgetCheck['daily_limit'], 2),
                        'today_total' => '₹' . number_format($budgetCheck['today_total'], 2),
                        'remaining' => '₹' . number_format($budgetCheck['remaining'], 2),
                        'requested' => '₹' . number_format($budgetCheck['requested_amount'], 2),
                        'excess' => '₹' . number_format($budgetCheck['requested_amount'] - $budgetCheck['remaining'], 2)
                    ]
                ], 400);
            }

            $validated = $request->validate([
                'approval_notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                // Create Customer
                $customer = Customer::create([
                    'name' => $lead->name,
                    'email' => $lead->email ?? null,
                    'phone' => $lead->phone,
                    'address' => $lead->address ?? null,
                    'priority' => 'low',
                    'notes' => $lead->description,
                    'lead_id' => $lead->id,
                    'branch_id' => $lead->branch_id,
                    'is_active' => true,
                ]);

                // $jobCode = $this->generateUniqueJobCode();

                // Create Job
                $job = Job::create([
                    // 'job_code' => $jobCode,
                    'title' => ($lead->services->first() ? $lead->services->first()->name : 'Service') . ' - ' . $lead->name,
                    'customer_id' => $customer->id,
                    'lead_id' => $lead->id,
                    'service_id' => $lead->services->first()->id ?? null,
                    'branch_id' => $lead->branch_id,
                    'assigned_to' => $lead->assigned_to,
                    'amount' => $lead->amount,
                    'amount_paid' => $lead->advance_paid_amount ?? 0,
                    'scheduled_date' => null,
                    'status' => 'Approved',
                    'created_by' => auth()->id(),
                    'notes' => 'Auto-created from approved lead: ' . $lead->lead_code . "\n" .
                            'Amount: ₹' . number_format($lead->amount, 2) . "\n" .
                            ($validated['approval_notes'] ?? ''),
                ]);

                // **SYNC SERVICES WITH QUANTITIES TO JOB**
                if ($lead->services->isNotEmpty()) {
                    $serviceQuantities = [];
                    foreach ($lead->services as $service) {
                        $serviceQuantities[$service->id] = ['quantity' => $service->pivot->quantity];
                    }
                    $job->services()->sync($serviceQuantities);
                }

                // Update Lead
                $lead->update([
                    'status' => 'approved',
                    'customer_id' => $customer->id,
                    'approval_notes' => $validated['approval_notes'] ?? null,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                LeadApproval::create([
                    'lead_id' => $lead->id,
                    'super_admin_id' => auth()->id(),
                    'action' => 'approve',
                    'comment' => $validated['approval_notes'] ?? 'Lead approved and customer/job created',
                    'reviewed_at' => now(),
                ]);

                DB::commit();

                $newRemaining = $budgetCheck['remaining'] - $lead->amount;

                Log::info('Lead approved with budget check', [
                    'lead_id' => $lead->id,
                    'amount' => $lead->amount,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'remaining_budget' => $newRemaining,
                    'approved_by' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lead converted successfully! Customer and Work order created.',
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'job_id' => $job->id,
                    'job_code' => $job->job_code,
                    'amount' => '₹' . number_format($lead->amount, 2),
                    'remaining_budget' => '₹' . number_format($newRemaining, 2)
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Lead approval error: ' . $e->getMessage(), [
                'lead_id' => $lead->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role === 'telecallers' && $lead->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'You can only update status of leads assigned to your branch'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,site_visit,not_accepting_tc,they_will_confirm,date_issue,rate_issue,service_not_provided,just_enquiry,immediate_service,no_response,location_not_available,night_work_demanded,customisation,approved,rejected,confirmed',
        ]);

        if ($validated['status'] === 'approved') {
            return response()->json(['message' => 'Not allowed to approve leads'], 403);
        }

        $lead->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'status' => $lead->status,
            'status_label' => $lead->status_label,
        ]);
    }

    /**
     * Generate a unique job code (includes soft-deleted + race condition handling)
     */
    private function generateUniqueJobCode()
    {
        // Use database transaction with locking to prevent race conditions
        return DB::transaction(function () {
            // Get the highest job number from ALL records (including soft-deleted)
            $maxNumber = Job::withTrashed()
                ->selectRaw('MAX(CAST(SUBSTRING(job_code, 4) AS UNSIGNED)) as max_number')
                ->value('max_number');

            // Start from the next number
            $nextNumber = $maxNumber ? $maxNumber + 1 : 1;

            // Keep trying until we find a unique code
            $attempts = 0;
            do {
                $jobCode = 'JOB' . $nextNumber;

                // Check if code exists in both active AND soft-deleted records
                $exists = Job::withTrashed()->where('job_code', $jobCode)->exists();

                if ($exists) {
                    $nextNumber++;
                    $attempts++;

                    // Prevent infinite loop
                    if ($attempts > 100) {
                        throw new \Exception('Unable to generate unique job code after 100 attempts');
                    }
                }
            } while ($exists);

            return $jobCode;
        });
    }

    public function reject(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            // Security check: Only super_admin can reject
            if ($user->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Admin can reject leads'
                ], 403);
            }

            // Check if lead is pending
            if ($lead->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead is not pending'
                ], 400);
            }

            $validated = $request->validate([
                'rejection_reason' => 'required|string',
            ]);

            DB::beginTransaction();

            try {
                // Update lead status
                $lead->update([
                    'status' => 'rejected',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approval_notes' => $validated['rejection_reason'],
                ]);

                // Create rejection record
                LeadApproval::create([
                    'lead_id' => $lead->id,
                    'super_admin_id' => $user->id,
                    'action' => 'reject',
                    'comment' => $validated['rejection_reason'],
                    'reviewed_at' => now(),
                ]);

                DB::commit();

                Log::info('Lead rejected', [
                    'lead_id' => $lead->id,
                    'rejected_by' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lead rejected successfully!'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Lead reject error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkDuplicate(Request $request)
    {
        try {
            $email = $request->input('email');
            $phone = $request->input('phone');

            $result = [
                'exists' => false,
                'type' => null,
                'data' => null
            ];

            // Check in Customers first
            $customer = Customer::where(function($query) use ($email, $phone) {
                    if ($email) {
                        $query->where('email', $email);
                    }
                    if ($phone) {
                        $query->orWhere('phone', $phone);
                    }
                })
                ->with(['jobs', 'completedJobs'])
                ->first();

            if ($customer) {
                $lastJob = $customer->jobs()->latest()->first();

                $result = [
                    'exists' => true,
                    'type' => 'customer',
                    'message' => 'This email/phone belongs to an existing customer',
                    'data' => [
                        'customer_code' => $customer->customer_code,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'priority' => $customer->priority,
                        'total_jobs' => $customer->jobs->count(),
                        'completed_jobs' => $customer->completedJobs->count(),
                        'pending_jobs' => $customer->jobs->where('status', 'pending')->count(),
                        'last_job_date' => $lastJob ? $lastJob->created_at->format('d M Y') : 'N/A',
                        'last_service' => $lastJob && $lastJob->service ? $lastJob->service->name : 'N/A',
                        'customer_id' => $customer->id,
                        'customer_since' => $customer->created_at->format('d M Y')
                    ]
                ];

                return response()->json($result);
            }

            // Check in Leads
            $lead = Lead::where(function($query) use ($email, $phone) {
                    if ($email) {
                        $query->where('email', $email);
                    }
                    if ($phone) {
                        $query->orWhere('phone', $phone);
                    }
                })
                ->first();

            if ($lead) {
                $result = [
                    'exists' => true,
                    'type' => 'lead',
                    'message' => 'This email/phone already exists as a lead',
                    'data' => [
                        'lead_code' => $lead->lead_code,
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'phone' => $lead->phone,
                        'status' => $lead->status,
                        'service' => $lead->service ? $lead->service->name : 'N/A',
                        'created_at' => $lead->created_at->format('d M Y'),
                        'lead_id' => $lead->id
                    ]
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Duplicate check error: ' . $e->getMessage());
            return response()->json(['exists' => false], 500);
        }
    }

    /**
     * Get today's total approved amount
     */
    private function getTodayApprovedAmount()
    {
        return Lead::whereDate('approved_at', today())
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * Check if amount exceeds daily budget
     */
    private function checkDailyBudget($amount)
    {
        $dailyLimit = Setting::get('daily_budget_limit', 100000);
        $todayTotal = $this->getTodayApprovedAmount();
        $remaining = $dailyLimit - $todayTotal;

        return [
            'can_approve' => $amount <= $remaining,
            'daily_limit' => $dailyLimit,
            'today_total' => $todayTotal,
            'remaining' => $remaining,
            'requested_amount' => $amount
        ];
    }

    /**
     * Quick search for telecallers - search leads and customers by phone/email
     */
    public function quickSearch(Request $request)
    {
        $user = auth()->user();

        // Only telecallers, super_admin and lead_manager can use this
        if (!in_array($user->role, ['telecallers', 'super_admin', 'lead_manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->input('query');

        if (strlen($query) < 3) {
            return response()->json(['leads' => [], 'customers' => []]);
        }

        // Search Leads
        $leadsQuery = Lead::query();

        // Telecallers can only search their branch leads
        if ($user->role === 'telecallers') {
            $leadsQuery->where('branch_id', $user->branch_id);
        }
        // Super admin and lead manager can search all leads

        $leads = $leadsQuery->where(function($q) use ($query) {
                $q->where('phone', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('lead_code', 'like', "%{$query}%");
            })
            ->with(['service'])
            ->limit(5)
            ->get()
            ->map(function($lead) {
                return [
                    'id' => $lead->id,
                    'lead_code' => $lead->lead_code,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'status' => $lead->status,
                    'service' => $lead->service->name ?? 'N/A'
                ];
            });

        // Search Customers - All roles can search all customers
        $customers = Customer::where(function($q) use ($query) {
                $q->where('phone', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('customer_code', 'like', "%{$query}%");
            })
            ->withCount('jobs')
            ->limit(5)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'priority' => $customer->priority,
                    'total_jobs' => $customer->jobs_count
                ];
            });

        return response()->json([
            'leads' => $leads,
            'customers' => $customers
        ]);
    }

    /**
     * Display WhatsApp leads for telecallers
     */
    public function whatsappLeads(Request $request)
    {
        $user = auth()->user();

        // Only telecallers can access
        if ($user->role !== 'telecallers') {
            abort(403, 'Unauthorized access');
        }

        // Get WhatsApp source
        $whatsappSource = LeadSource::where('name', 'WhatsApp')->first();

        if (!$whatsappSource) {
            return redirect()->route('leads.index')
                ->with('error', 'WhatsApp lead source not configured');
        }

        // Build query
        $query = Lead::with(['branch', 'service', 'source', 'createdBy', 'assignedTo'])
            ->where('branch_id', $user->branch_id)
            ->where('lead_source_id', $whatsappSource->id);

        // Apply additional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('datefrom')) {
            $query->whereDate('created_at', '>=', $request->datefrom);
        }

        if ($request->filled('dateto')) {
            $query->whereDate('created_at', '<=', $request->dateto);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(20);
        $pendingcount = Lead::where('assigned_to', $user->id)
            ->where('lead_source_id', $whatsappSource->id)
            ->where('status', 'pending')
            ->count();

        $services = Service::where('is_active', true)->get();

        // Return JSON for AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
            ]);
        }

        return view('leads.whatsapp', compact('leads', 'pendingcount', 'services'));
    }

    /**
     * Display Google Ads leads for telecallers
     */
    public function googleAdsLeads(Request $request)
    {
        $user = auth()->user();

        // Only telecallers can access
        if ($user->role !== 'telecallers') {
            abort(403, 'Unauthorized access');
        }

        // Get Google Ads source
        $googleAdsSource = LeadSource::where('name', 'Google Ads')->first();

        if (!$googleAdsSource) {
            return redirect()->route('leads.index')
                ->with('error', 'Google Ads lead source not configured');
        }

        // Build query
        $query = Lead::with(['branch', 'service', 'source', 'createdBy', 'assignedTo'])
            ->where('branch_id', $user->branch_id)
            ->where('lead_source_id', $googleAdsSource->id);

        // Apply additional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('datefrom')) {
            $query->whereDate('created_at', '>=', $request->datefrom);
        }

        if ($request->filled('dateto')) {
            $query->whereDate('created_at', '<=', $request->dateto);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(20);
        $pendingcount = Lead::where('assigned_to', $user->id)
            ->where('lead_source_id', $googleAdsSource->id)
            ->where('status', 'pending')
            ->count();

        $services = Service::where('is_active', true)->get();

        // Return JSON for AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
            ]);
        }

        return view('leads.google-ads', compact('leads', 'pendingcount', 'services'));
    }

    /**
     * Delete a followup
     */
    public function deleteFollowup(LeadFollowup $followup)
    {
        try {
            $user = auth()->user();

            // Authorization: super_admin, lead_manager, or creator/assignee
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $followup->created_by !== $user->id &&
                $followup->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this followup'
                ], 403);
            }

            // Don't allow deletion of completed followups (optional - remove if you want)
            if ($followup->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete completed followups'
                ], 400);
            }

            $followup->delete();

            Log::info('Followup deleted', [
                'followup_id' => $followup->id,
                'lead_id' => $followup->lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting followup'
            ], 500);
        }
    }

    /**
     * Delete a call log
     */
    public function deleteCall(LeadCall $call)
    {
        try {
            $user = auth()->user();

            // Authorization: super_admin, lead_manager, or creator
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $call->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this call log'
                ], 403);
            }

            $call->delete();

            Log::info('Call log deleted', [
                'call_id' => $call->id,
                'lead_id' => $call->lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call log deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting call log'
            ], 500);
        }
    }

    /**
     * Delete a note
     */
    public function deleteNote(LeadNote $note)
    {
        try {
            $user = auth()->user();

            // Authorization: super_admin, lead_manager, or creator
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $note->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this note'
                ], 403);
            }

            $note->delete();

            Log::info('Note deleted', [
                'note_id' => $note->id,
                'lead_id' => $note->lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting note'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $user = auth()->user();

            // Build query with same filters as index
            $query = Lead::with(['branch', 'services', 'source', 'createdBy', 'assignedTo']);

            // ROLE-BASED ACCESS CONTROL
            if ($user->role === 'super_admin') {
                // Super admin sees all leads
            } elseif ($user->role === 'lead_manager') {
                $query->where('created_by', $user->id);
            } elseif ($user->role === 'telecallers') {
                $query->where('branch_id', $user->branch_id);
            } else {
                abort(403, 'Unauthorized');
            }

            // APPLY ALL YOUR FILTERS (keep existing filter code)
            $mode = $request->input('mode');

            if ($mode === 'open') {
                $query->where('status', '!=', 'approved')
                    ->where('status', '!=', 'confirmed');
            } elseif ($mode === 'approved' || $request->status === 'approved') {
                $query->where('status', 'approved');
            }

            if ($request->filled('status') && !$mode) {
                $query->where('status', $request->status);
            }

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->filled('lead_source_id') && $request->lead_source_id != 0) {
                $query->where('lead_source_id', $request->lead_source_id);
            }

            if ($request->filled('service_id')) {
                $serviceId = $request->service_id;
                $query->whereHas('services', function($subQuery) use ($serviceId) {
                    $subQuery->where('services.id', $serviceId);
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('assigned_to')) {
                if ($user->role === 'telecallers') {
                    if ($request->assigned_to === 'me') {
                        $query->where('assigned_to', $user->id);
                    } elseif ($request->assigned_to === 'unassigned') {
                        $query->whereNull('assigned_to');
                    }
                } else {
                    if ($request->assigned_to === 'unassigned') {
                        $query->whereNull('assigned_to');
                    } else {
                        $query->where('assigned_to', $request->assigned_to);
                    }
                }
            }

            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('leads.name', 'like', $search)
                    ->orWhere('leads.email', 'like', $search)
                    ->orWhere('leads.phone', 'like', $search)
                    ->orWhere('leads.lead_code', 'like', $search);
                });
            }

            // Check total count
            $totalCount = $query->count();
            $exportLimit = 10000;

            Log::info('Lead export started', [
                'user_id' => $user->id,
                'total_leads' => $totalCount,
                'exported_leads' => min($totalCount, $exportLimit)
            ]);

            if ($totalCount > $exportLimit) {
                session()->flash('warning', "Only the first {$exportLimit} leads will be exported. Total: {$totalCount}.");
            }

            // Generate filename - CSV format
            $fileName = 'leads_export_' . now()->format('Y-m-d_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($query, $exportLimit) {
                $file = fopen('php://output', 'w');

                // Add UTF-8 BOM for Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // CSV Headers - Excel will show first row bold by default
                fputcsv($file, [
                    'Lead Code',
                    'Name',
                    'Email',
                    'Phone',
                    'Phone Alternative',
                    'Address',
                    'District',
                    'Property Type',
                    'SQFT',
                    'Services',
                    'Service Type',
                    'Lead Source',
                    'Branch',
                    'Status',
                    'Amount',
                    'Advance Paid',
                    'Balance Amount',
                    'Payment Mode',
                    'Assigned To',
                    'Created By',
                    'Approved By',
                    'Approved At',
                    'Created At',
                    'Description'
                ]);

                // Data rows with limit
                $query->limit($exportLimit)->chunk(1000, function($leads) use ($file) {
                    foreach ($leads as $lead) {
                        fputcsv($file, [
                            $lead->lead_code,
                            $lead->name,
                            $lead->email ?? '',
                            $lead->phone,
                            $lead->phone_alternative ?? '',
                            $lead->address ?? '',
                            $lead->district ?? '',
                            $lead->property_type ?? '',
                            $lead->sqft ?? '',
                            $lead->services_list,
                            $lead->service_type ?? '',
                            optional($lead->source)->name ?? '',
                            optional($lead->branch)->name ?? '',
                            $lead->status_label,
                            $lead->amount ?? '',
                            $lead->advance_paid_amount ?? '',
                            $lead->balance_amount ?? '',
                            $lead->payment_mode ?? '',
                            optional($lead->assignedTo)->name ?? 'Unassigned',
                            optional($lead->createdBy)->name ?? '',
                            optional($lead->approvedBy)->name ?? '',
                            $lead->approved_at ? $lead->approved_at->format('Y-m-d H:i:s') : '',
                            $lead->created_at->format('Y-m-d H:i:s'),
                            $lead->description ?? ''
                        ]);
                    }
                });

                fclose($file);
            };

            return response()->streamDownload($callback, $fileName, $headers);

        } catch (\Exception $e) {
            Log::error('Lead export error: ' . $e->getMessage());
            return back()->withError('Error exporting leads: ' . $e->getMessage());
        }
    }

    /**
     * Confirm a lead (Telecallers only - no comments needed)
     */
    public function confirmLead(Request $request, Lead $lead)
    {
        try {
            $user = auth()->user();

            // Only telecallers can confirm
            if ($user->role !== 'telecallers') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only telecallers can confirm leads.'
                ], 403);
            }

            // Can only confirm leads assigned to them
            if ($lead->assigned_to != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only confirm leads assigned to you.'
                ], 403);
            }

            // Can't confirm if already confirmed or approved
            if (in_array($lead->status, ['confirmed', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This lead is already ' . $lead->status . '.'
                ], 400);
            }

            // Update status to confirmed
            $lead->update([
                'status' => 'confirmed'
            ]);

            Log::info('Lead confirmed by telecaller', [
                'lead_id' => $lead->id,
                'lead_code' => $lead->lead_code,
                'confirmed_by' => $user->id,
                'telecaller_name' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead confirmed successfully! Waiting for admin approval.',
                'status' => 'confirmed'
            ]);

        } catch (\Exception $e) {
            Log::error('Lead confirm error: ' . $e->getMessage(), [
                'lead_id' => $lead->id ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error confirming lead: ' . $e->getMessage()
            ], 500);
        }
    }



}
