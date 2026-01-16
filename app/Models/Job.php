<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
        'amount_paid',
        'addon_price',
        'addon_price_comments',
        'customer_instructions',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
        'location',
        'scheduled_date',
        'scheduled_time',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_date' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'addon_price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    // NEW: Calculate balance amount
    public function getBalanceAmountAttribute()
    {
        return $this->amount - $this->amount_paid;
    }

    // NEW: Check if fully paid
    public function isFullyPaid()
    {
        return $this->amount_paid >= $this->amount;
    }

    // NEW: Get payment status
    public function getPaymentStatusAttribute()
    {
        if ($this->amount_paid <= 0) {
            return 'unpaid';
        } elseif ($this->amount_paid >= $this->amount) {
            return 'paid';
        } else {
            return 'partial';
        }
    }

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

    // Generate unique job code (includes soft-deleted + race condition handling)
    public static function generateJobCode()
    {
        // Use a database lock to prevent race conditions
        return DB::transaction(function () {
            // Get the highest job number from existing codes (including soft-deleted)
            $maxNumber = self::withTrashed()
                ->selectRaw('MAX(CAST(SUBSTRING(job_code, 4) AS UNSIGNED)) as max_number')
                ->value('max_number');

            // Start from the next number
            $nextNumber = $maxNumber ? $maxNumber + 1 : 1;

            // Keep trying until we find a unique code
            $attempts = 0;
            do {
                $jobCode = 'JOB' . $nextNumber;
                $exists = self::withTrashed()->where('job_code', $jobCode)->exists();

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
        return $this->belongsToMany(Service::class, 'job_service')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // Helper to get comma-separated service names
    public function getServicesListAttribute()
    {
        return $this->services->pluck('name')->join(', ');
    }

    public function followups()
    {
        return $this->hasMany(JobFollowup::class)->latest();
    }

    public function pendingFollowups()
    {
        return $this->hasMany(JobFollowup::class)->where('status', 'pending')->latest();
    }

    public function calls()
    {
        return $this->hasMany(JobCall::class)->latest();
    }

    public function notes()
    {
        return $this->hasMany(JobNote::class)->latest();
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
                    // Sort numerically by extracting the number from JOB0001, JOB0002, etc.
                    return $query->orderByRaw(
                        "CAST(SUBSTRING(job_code, 4) AS UNSIGNED) {$direction}"
                    );

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
