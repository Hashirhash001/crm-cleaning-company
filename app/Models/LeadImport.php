<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadImport extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'errors',
        'failed_rows_data', // ← ADD THIS LINE!
    ];

    protected $casts = [
        'errors' => 'array',
        'failed_rows_data' => 'array',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
