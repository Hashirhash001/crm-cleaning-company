<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LeadService extends Pivot
{
    protected $table = 'lead_service';

    protected $fillable = [
        'lead_id',
        'service_id',
    ];

    public $timestamps = true;

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
