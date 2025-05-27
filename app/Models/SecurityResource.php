<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityResource extends Model
{
    protected $fillable = [
        'name',
        'type',
        'available',
        'current_location_id',
        'last_used',
        'notes',
        'capabilities',
        'maintenance_due',
        'status'
    ];

    protected $casts = [
        'available' => 'boolean',
        'capabilities' => 'array',
        'last_used' => 'datetime',
        'maintenance_due' => 'datetime'
    ];

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', true)
                    ->where(function ($q) {
                        $q->whereNull('maintenance_due')
                          ->orWhere('maintenance_due', '>', now());
                    });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('maintenance_due', '<=', now());
    }

    public function markAsUnavailable(string $reason = null)
    {
        $this->update([
            'available' => false,
            'notes' => $reason,
            'last_used' => now()
        ]);
    }

    public function markAsAvailable()
    {
        $this->update([
            'available' => true,
            'notes' => null,
            'last_used' => null
        ]);
    }

    public function scheduleMaintenance(\DateTime $dueDate)
    {
        $this->update([
            'maintenance_due' => $dueDate,
            'available' => false,
            'notes' => 'Scheduled for maintenance'
        ]);
    }
}