<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

    // THIS IS THE MISSING METHOD - ADD IT
    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function completedJobs()
    {
        return $this->jobs()->where('status', 'completed');
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
}
