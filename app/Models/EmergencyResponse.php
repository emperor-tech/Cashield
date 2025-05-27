<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyResponse extends Model
{
    protected $fillable = [
        'report_id',
        'responder_id',
        'status',
        'eta_minutes',
        'action_taken',
        'location_lat',
        'location_lng',
        'resources_needed'
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
        'eta_minutes' => 'integer',
        'resources_needed' => 'array'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ResponseUpdate::class)->orderBy('created_at', 'desc');
    }
}