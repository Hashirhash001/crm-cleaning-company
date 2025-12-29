<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobFollowup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'assigned_to',
        'created_by',
        'followup_date',
        'followup_time',
        'callback_time_preference',
        'priority',
        'notes',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCallbackTimePreferenceLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->callback_time_preference));
    }
}
