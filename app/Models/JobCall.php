<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCall extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'user_id',
        'call_date',
        'duration',
        'outcome',
        'notes',
    ];

    protected $casts = [
        'call_date' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
