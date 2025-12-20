<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_code',
        'branch_id',
        'assigned_to',
        'created_by',
        'lead_source_id',
        'service_id',
        'service_type',
        'name',
        'email',
        'phone',
        'phone_alternative',
        'address',
        'district',
        'property_type',
        'sqft',
        'description',
        'amount',
        'advance_payment',
        'advance_paid_amount',
        'payment_mode',
        'amount_updated_at',
        'amount_updated_by',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'job_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'advance_paid_amount' => 'decimal:2',
        'amount_updated_at' => 'datetime',
    ];

    // Many-to-many relationship with services
    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'lead_service')->withTimestamps();
    // }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'lead_service')
            ->using(LeadService::class)
            ->withTimestamps();
    }

    // Get all service names as comma-separated string
    public function getServicesListAttribute()
    {
        return $this->services->pluck('name')->join(', ');
    }

    // Accessor for balance amount
    public function getBalanceAmountAttribute()
    {
        return $this->amount - $this->advance_paid_amount;
    }

    // Status labels
    public static function getStatusLabels()
    {
        return [
            'pending' => 'Pending',
            'site_visit' => 'Site Visit',
            'not_accepting_tc' => 'Not Accepting T&C',
            'they_will_confirm' => 'They Will Confirm',
            'date_issue' => 'Date Issue',
            'rate_issue' => 'Rate Issue',
            'service_not_provided' => 'Service Not Provided',
            'just_enquiry' => 'Just Enquiry',
            'immediate_service' => 'Immediate Service',
            'no_response' => 'No Response',
            'location_not_available' => 'Location Not Available',
            'night_work_demanded' => 'Night Work Demanded',
            'customisation' => 'Customisation',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    public function getStatusLabelAttribute()
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst($this->status);
    }

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function amountUpdatedBy()
    {
        return $this->belongsTo(User::class, 'amount_updated_by');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'lead_id');
    }

    public function job()
    {
        return $this->hasOne(Job::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'lead_id')->orderBy('created_at', 'desc');
    }

    public function calls()
    {
        return $this->hasMany(LeadCall::class)->latest();
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class);
    }

    public function pendingFollowups()
    {
        return $this->hasMany(LeadFollowup::class)->where('status', 'pending');
    }

    public function approvals()
    {
        return $this->hasMany(LeadApproval::class);
    }

    // Status Checks
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Boot method for auto-generating lead code and cascading deletes
    protected static function boot()
    {
        parent::boot();

        // Auto-generate lead_code
        static::creating(function ($lead) {
            if (empty($lead->lead_code)) {
                $lead->lead_code = self::generateLeadCode();
            }
        });

        // Clean up related data on delete (works with soft deletes too)
        static::deleting(function ($lead) {
            // Load FULL collections (bypassing latest() scope)
            $lead->load(['calls', 'notes', 'followups', 'approvals']);

            // Delete calls
            $lead->calls->each->delete();

            // Delete notes
            $lead->notes->each->delete();

            // Delete followups
            $lead->followups->each->delete();

            // Delete approvals
            $lead->approvals->each->delete();

            // Delete lead_service pivot records
            LeadService::where('lead_id', $lead->id)->delete();

            // Delete customer if exists and not used elsewhere
            if ($lead->customer) {
                if ($lead->customer->jobs()->count() === 0) {
                    $lead->customer->delete();
                }
            }

            // Delete pending jobs
            $lead->jobs()->where('status', 'pending')->delete();
        });
    }

    public static function generateLeadCode()
    {
        $lastLead = self::withTrashed()->orderBy('id', 'desc')->first();

        if ($lastLead && $lastLead->lead_code) {
            $lastNumber = (int) filter_var($lastLead->lead_code, FILTER_SANITIZE_NUMBER_INT);
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }

        do {
            $code = 'LEAD' . str_pad($number, 3, '0', STR_PAD_LEFT);
            $exists = self::withTrashed()->where('lead_code', $code)->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Scope for dynamic sorting
     */
    public function scopeSort(Builder $query, $column, $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        switch ($column) {
            case 'code':
            case 'lead_code':
                return $query->orderBy('lead_code', $direction);

            case 'name':
                return $query->orderBy('name', $direction);

            case 'email':
                return $query->orderBy('email', $direction);

            case 'phone':
                return $query->orderBy('phone', $direction);

            case 'service':
            case 'servicetype':
                return $query->orderBy('service_type', $direction);

            case 'status':
                // Custom status ordering: pending > approved > rejected
                $order = $direction === 'asc'
                    ? "FIELD(status, 'pending', 'site_visit', 'approved', 'rejected')"
                    : "FIELD(status, 'rejected', 'approved', 'site_visit', 'pending')";
                return $query->orderByRaw($order);

            case 'source':
                // Sort by lead source name
                return $query->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
                    ->orderBy('lead_sources.name', $direction)
                    ->select('leads.*');

            case 'branch':
                // Sort by branch name
                return $query->leftJoin('branches', 'leads.branch_id', '=', 'branches.id')
                    ->orderBy('branches.name', $direction)
                    ->select('leads.*');

            case 'assigned':
            case 'assigned_to':
                // Sort by assigned user name
                return $query->leftJoin('users as assigned_users', 'leads.assigned_to', '=', 'assigned_users.id')
                    ->orderBy('assigned_users.name', $direction)
                    ->select('leads.*');

            case 'created-by':
            case 'created_by':
                // Sort by creator name
                return $query->leftJoin('users as creator_users', 'leads.created_by', '=', 'creator_users.id')
                    ->orderBy('creator_users.name', $direction)
                    ->select('leads.*');

            case 'date':
            case 'created_at':
                return $query->orderBy('created_at', $direction);

            case 'amount':
                return $query->orderBy('amount', $direction);

            default:
                return $query->orderBy('created_at', 'desc');
        }
    }

}
