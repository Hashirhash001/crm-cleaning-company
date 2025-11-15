<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_code',
        'branch_id',
        'created_by',
        'lead_source_id',
        'service_id',
        'name',
        'email',
        'phone',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'job_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_code)) {
                $lead->lead_code = self::generateLeadCode();
            }
        });
    }

    public static function generateLeadCode()
    {
        // Get the last lead by ordering by ID in descending order
        $lastLead = self::withTrashed()->orderBy('id', 'desc')->first();

        if ($lastLead && $lastLead->lead_code) {
            // Extract number from lead code (e.g., LEAD002 -> 2)
            $lastNumber = (int)filter_var($lastLead->lead_code, FILTER_SANITIZE_NUMBER_INT);
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }

        // Keep trying until we find a unique code
        do {
            $code = 'LEAD' . str_pad($number, 3, '0', STR_PAD_LEFT);
            $exists = self::withTrashed()->where('lead_code', $code)->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function approvals()
    {
        return $this->hasMany(LeadApproval::class);
    }

    public function job()
    {
        return $this->hasOne(Job::class);
    }

    // ADD THIS RELATIONSHIP
    public function jobs()
    {
        return $this->hasMany(Job::class, 'lead_id')->orderBy('created_at', 'desc');
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

    public function calls()
    {
        return $this->hasMany(LeadCall::class)->latest();
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

}
