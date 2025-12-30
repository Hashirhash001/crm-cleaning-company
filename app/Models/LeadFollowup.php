<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadFollowup extends Model
{
    protected $fillable = [
        'lead_id',
        'assigned_to',
        'followup_date',
        'followup_time',
        'callback_time_preference',
        'priority',
        'status',
        'notes',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'completed_at' => 'datetime',
    ];

    // Add accessor for readable callback time preference
    public function getCallbackTimePreferenceLabelAttribute()
    {
        $labels = [
            'morning' => 'Morning (9 AM - 12 PM)',
            'afternoon' => 'Afternoon (12 PM - 4 PM)',
            'evening' => 'Evening (4 PM - 8 PM)',
            'anytime' => 'Anytime',
        ];

        return $labels[$this->callback_time_preference] ?? 'Not specified';
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getSourceTypeAttribute()
    {
        return 'lead';
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('followup_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('followup_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('followup_date', '<', now()->toDateString())
            ->where('status', 'pending');
    }
}
