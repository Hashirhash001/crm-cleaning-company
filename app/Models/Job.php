<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
    public function services()
    {
        return $this->belongsToMany(Service::class, 'job_service')->withTimestamps();
    }

    // Helper to get comma-separated service names
    public function getServicesListAttribute()
    {
        return $this->services->pluck('name')->join(', ');
    }

    /**
     * Scope for dynamic sorting
     */
    public function scopeSort(Builder $query, $column, $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        switch ($column) {
            case 'code':
            case 'job_code':
                return $query->orderBy('job_code', $direction);

            case 'title':
                return $query->orderBy('title', $direction);

            case 'customer':
            case 'customer_name':
                // Sort by customer name
                return $query->leftJoin('customers', 'jobs.customer_id', '=', 'customers.id')
                    ->orderBy('customers.name', $direction)
                    ->select('jobs.*');

            case 'service':
            case 'service_name':
                // Sort by service name
                return $query->leftJoin('services', 'jobs.service_id', '=', 'services.id')
                    ->orderBy('services.name', $direction)
                    ->select('jobs.*');

            case 'branch':
            case 'branch_name':
                // Sort by branch name
                return $query->leftJoin('branches', 'jobs.branch_id', '=', 'branches.id')
                    ->orderBy('branches.name', $direction)
                    ->select('jobs.*');

            case 'status':
                // Custom status ordering
                $order = $direction === 'asc'
                    ? "FIELD(status, 'pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'cancelled')"
                    : "FIELD(status, 'cancelled', 'completed', 'in_progress', 'assigned', 'confirmed', 'pending')";
                return $query->orderByRaw($order);

            case 'assigned':
            case 'assigned_to':
                // Sort by assigned user name
                return $query->leftJoin('users as assigned_users', 'jobs.assigned_to', '=', 'assigned_users.id')
                    ->orderBy('assigned_users.name', $direction)
                    ->select('jobs.*');

            case 'scheduled_date':
            case 'date':
                return $query->orderBy('scheduled_date', $direction);

            case 'amount':
                return $query->orderBy('amount', $direction);

            case 'created_at':
                return $query->orderBy('created_at', $direction);

            default:
                return $query->orderBy('created_at', 'desc');
        }
    }
}
