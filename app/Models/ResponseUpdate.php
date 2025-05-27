<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseUpdate extends Model
{
    protected $fillable = [
        'emergency_response_id',
        'status',
        'message',
        'location_lat',
        'location_lng',
        'image_path',
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
    ];

    public function emergencyResponse(): BelongsTo
    {
        return $this->belongsTo(EmergencyResponse::class);
    }
}