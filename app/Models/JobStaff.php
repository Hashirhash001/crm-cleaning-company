<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobStaff extends Model
{
    protected $fillable = [
        'job_id', 'user_id', 'temp_name', 'temp_phone',
        'role', 'staff_type', 'notes', 'added_by',
        'approval_status', 'approved_by', 'approved_at', 'approval_notes',
    ];

    protected $casts = ['approved_at' => 'datetime'];

    public function job()      { return $this->belongsTo(Job::class); }
    public function user()     { return $this->belongsTo(User::class, 'user_id'); }
    public function addedBy()  { return $this->belongsTo(User::class, 'added_by'); }
    public function approvedBy(){ return $this->belongsTo(User::class, 'approved_by'); }

    public function getDisplayNameAttribute(): string
    {
        return $this->staff_type === 'registered'
            ? ($this->user?->name ?? 'Unknown')
            : ($this->temp_name ?? 'Unnamed');
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->staff_type === 'registered'
            ? ($this->user?->phone ?? null)
            : $this->temp_phone;
    }
}
