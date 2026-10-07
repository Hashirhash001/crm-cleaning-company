<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentPosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recruitment_positions';

    protected $fillable = [
        'name',
        'recruitment_department_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the department this position belongs to.
     */
    public function department()
    {
        return $this->belongsTo(RecruitmentDepartment::class, 'recruitment_department_id');
    }

    /**
     * Get candidates for this position.
     */
    public function candidates()
    {
        return $this->hasMany(RecruitmentCandidate::class, 'recruitment_position_id');
    }

    /**
     * Scope: only active positions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
