<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class JobService extends Pivot
{
    protected $table = 'job_service';

    protected $fillable = [
        'job_id',
        'service_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public $timestamps = true;

    // Relationships
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
