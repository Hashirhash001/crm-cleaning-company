<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Get the branch that the user belongs to
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get jobs assigned to this user (for field staff)
     */
    public function assignedJobs()
    {
        return $this->hasMany(Job::class, 'assigned_to');
    }

    /**
     * Get leads created by this user (for lead managers)
     */
    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    /**
     * Get jobs created by this user
     */
    public function createdJobs()
    {
        return $this->hasMany(Job::class, 'created_by');
    }

    // Role helper methods

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isLeadManager()
    {
        return $this->role === 'lead_manager';
    }

    public function isFieldStaff()
    {
        return $this->role === 'field_staff';
    }

    public function isReportingUser()
    {
        return $this->role === 'reporting_user';
    }

    // Status helper
    public function isActive()
    {
        return $this->is_active;
    }

}
