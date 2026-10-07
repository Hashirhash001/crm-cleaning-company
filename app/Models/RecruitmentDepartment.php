<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentDepartment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recruitment_departments';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get positions under this department.
     */
    public function positions()
    {
        return $this->hasMany(RecruitmentPosition::class, 'recruitment_department_id');
    }

    /**
     * Get candidates under this department.
     */
    public function candidates()
    {
        return $this->hasMany(RecruitmentCandidate::class, 'recruitment_department_id');
    }

    /**
     * Scope: only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
