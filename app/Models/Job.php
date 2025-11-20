<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_code',
        'lead_id',
        'customer_id',
        'service_id',
        'branch_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'amount',
        'customer_instructions',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
        'location',
        'scheduled_date',
        'scheduled_time',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_date' => 'date',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Status Checks
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->job_code)) {
                $job->job_code = self::generateJobCode();
            }
        });
    }

    public static function generateJobCode()
    {
        $lastJob = self::orderBy('id', 'desc')->first();
        $number = $lastJob ? (int)substr($lastJob->job_code, 3) + 1 : 1;
        return 'JOB' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class);
    }
}
