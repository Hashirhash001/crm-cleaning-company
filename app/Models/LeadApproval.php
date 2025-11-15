<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadApproval extends Model
{
    protected $fillable = [
        'lead_id',
        'super_admin_id',
        'action',
        'comment',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }
}
