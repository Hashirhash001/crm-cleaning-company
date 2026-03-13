<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRating extends Model
{
    protected $fillable = [
        'job_id', 'customer_id', 'rating',
        'feedback', 'rated_by_type', 'rated_by',
    ];

    public function job()      { return $this->belongsTo(Job::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function ratedBy()  { return $this->belongsTo(User::class, 'rated_by'); }
}
