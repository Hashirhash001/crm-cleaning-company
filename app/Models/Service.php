<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'service_type',
        'description',
        'price',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationship with leads (many-to-many)
    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_service');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope for cleaning services
    public function scopeCleaning($query)
    {
        return $query->where('service_type', 'cleaning');
    }

    // Scope for pest control services
    public function scopePestControl($query)
    {
        return $query->where('service_type', 'pest_control');
    }

    // Get service type label
    public function getServiceTypeLabelAttribute()
    {
        return $this->service_type === 'cleaning' ? 'Cleaning' : 'Pest Control';
    }
}
