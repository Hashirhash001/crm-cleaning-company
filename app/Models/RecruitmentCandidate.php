<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentCandidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recruitment_candidates';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'place',
        'district',
        'date_of_birth',
        'driving_skill',
        'recruitment_position_id',
        'recruitment_department_id',
        'current_company',
        'interview_date',
        'interview_time',
        'interviewer',
        'interview_status',
        'interview_result',
        'joining_status',
        'joined_date',
        'expected_join_date',
        'follow_up_date',
        'decision_reason',
        'remarks',
        'reference',
        'created_by',
    ];

    /**
     * Get candidate age from date of birth.
     */
    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }

    protected $casts = [
        'date_of_birth' => 'date',
        'interview_date' => 'date',
        'joined_date' => 'date',
        'expected_join_date' => 'date',
        'follow_up_date' => 'date',
    ];

    // ─── Status Constants ────────────────────────────────────

    const DRIVING_SKILLS = ['Yes', 'No'];

    const INTERVIEW_STATUSES = ['Scheduled', 'Completed', 'Pending', 'Cancelled', 'Rescheduled'];

    const INTERVIEW_RESULTS = ['Selected', 'Rejected', 'Hold', 'Pending'];

    const JOINING_STATUSES = ['Joined', 'Pending', 'Hold', 'Not Joined', 'Cancelled'];

    const DECISION_REASONS = ['No Response', 'Not Interested', 'Rejected', 'Selected', 'Hold', 'By Company', 'Other'];

    const REFERENCES = ['WhatsApp', 'Friends', 'Employee Referral', 'Website', 'Indeed', 'LinkedIn', 'Other'];

    // ─── Badge Color Maps ────────────────────────────────────

    public static function interviewStatusBadge($status)
    {
        return match ($status) {
            'Scheduled'    => 'primary',
            'Completed'    => 'success',
            'Pending'      => 'warning',
            'Cancelled'    => 'danger',
            'Rescheduled'  => 'info',
            default        => 'secondary',
        };
    }

    public static function interviewResultBadge($result)
    {
        return match ($result) {
            'Selected' => 'success',
            'Rejected' => 'danger',
            'Hold'     => 'warning',
            'Pending'  => 'secondary',
            default    => 'secondary',
        };
    }

    public static function joiningStatusBadge($status)
    {
        return match ($status) {
            'Joined'     => 'success',
            'Pending'    => 'warning',
            'Hold'       => 'info',
            'Not Joined' => 'danger',
            'Cancelled'  => 'dark',
            default      => 'secondary',
        };
    }

    // ─── Relationships ───────────────────────────────────────

    /**
     * Get the position applied for.
     */
    public function position()
    {
        return $this->belongsTo(RecruitmentPosition::class, 'recruitment_position_id');
    }

    /**
     * Get the department.
     */
    public function department()
    {
        return $this->belongsTo(RecruitmentDepartment::class, 'recruitment_department_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
