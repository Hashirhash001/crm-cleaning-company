<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadCall extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'user_id',
        'call_date',
        'duration',
        'outcome',
        'notes',
    ];

    protected $casts = [
        'call_date' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
