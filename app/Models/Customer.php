<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'address',
        'priority',
        'notes',
        'lead_id',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship to Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = self::generateCustomerCode();
            }
        });
    }

    public static function generateCustomerCode()
    {
        $lastCustomer = self::withTrashed()->orderBy('id', 'desc')->first();

        if ($lastCustomer && $lastCustomer->customer_code) {
            $lastNumber = (int)filter_var($lastCustomer->customer_code, FILTER_SANITIZE_NUMBER_INT);
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }

        do {
            $code = 'CUS' . str_pad($number, 3, '0', STR_PAD_LEFT);
            $exists = self::withTrashed()->where('customer_code', $code)->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function completedJobs()
    {
        return $this->jobs()->where('status', 'completed')->orwhere('status', 'confirmed');
    }

    // Priority helpers
    public function isHighPriority()
    {
        return $this->priority === 'high';
    }

    public function isMediumPriority()
    {
        return $this->priority === 'medium';
    }

    public function isLowPriority()
    {
        return $this->priority === 'low';
    }

    // Accessor for completed jobs count
    public function getCompletedJobsCountAttribute()
    {
        return $this->completedJobs()->count();
    }

    // ============================================
    // SCOPE FOR SORTING
    // ============================================

    /**
     * Scope for dynamic sorting
     */
    public function scopeSort(Builder $query, $column, $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        switch ($column) {
            case 'code':
            case 'customer_code':
                return $query->orderBy('customer_code', $direction);

            case 'name':
                return $query->orderBy('name', $direction);

            case 'email':
                return $query->orderBy('email', $direction);

            case 'phone':
                return $query->orderBy('phone', $direction);

            case 'priority':
                // Sort by priority: high > medium > low
                $order = $direction === 'asc'
                    ? "FIELD(priority, 'low', 'medium', 'high')"
                    : "FIELD(priority, 'high', 'medium', 'low')";
                return $query->orderByRaw($order);

            case 'total_jobs':
            case 'total-jobs':
                // Sort by total jobs count using withCount
                return $query->withCount('jobs')->orderBy('jobs_count', $direction);

            case 'completed_jobs':
            case 'completed-jobs':
                // Sort by completed jobs count
                return $query->withCount(['jobs as completed_jobs_count' => function ($query) {
                    $query->where('status', 'completed');
                }])->orderBy('completed_jobs_count', $direction);

            case 'created_at':
            case 'date':
                return $query->orderBy('created_at', $direction);

            default:
                return $query->orderBy('created_at', 'desc');
        }
    }
}
