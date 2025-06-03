<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftIncident extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'shift_id',
        'type',
        'description',
        'severity',
        'location',
        'metadata',
        'reported_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'location' => 'array',
        'metadata' => 'array',
        'reported_at' => 'datetime',
    ];

    /**
     * Get the shift this incident belongs to.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(SecurityShift::class, 'shift_id');
    }

    /**
     * Get the user who reported the incident.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the formatted location string.
     */
    public function getFormattedLocationAttribute(): string
    {
        if (!$this->location) {
            return 'Unknown location';
        }

        return sprintf(
            '%s, %s',
            $this->location['lat'] ?? 'Unknown latitude',
            $this->location['lng'] ?? 'Unknown longitude'
        );
    }

    /**
     * Get the severity badge class.
     */
    public function getSeverityBadgeClassAttribute(): string
    {
        return match ($this->severity) {
            'high' => 'admin-badge-red',
            'medium' => 'admin-badge-yellow',
            'low' => 'admin-badge-green',
            default => 'admin-badge-gray',
        };
    }

    /**
     * Get the type badge class.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'suspicious_activity' => 'admin-badge-yellow',
            'security_breach' => 'admin-badge-red',
            'maintenance' => 'admin-badge-blue',
            'assistance' => 'admin-badge-green',
            default => 'admin-badge-gray',
        };
    }

    /**
     * Get the formatted metadata.
     */
    public function getFormattedMetadataAttribute(): array
    {
        $formatted = [];

        if (!$this->metadata) {
            return $formatted;
        }

        foreach ($this->metadata as $key => $value) {
            $formatted[ucwords(str_replace('_', ' ', $key))] = is_array($value) ? json_encode($value) : $value;
        }

        return $formatted;
    }

    /**
     * Scope a query to only include incidents of a given severity.
     */
    public function scopeOfSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope a query to only include incidents of a given type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include recent incidents.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('reported_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to only include incidents in a specific zone.
     */
    public function scopeInZone($query, int $zoneId)
    {
        return $query->whereHas('shift', function ($query) use ($zoneId) {
            $query->where('zone_id', $zoneId);
        });
    }

    /**
     * Scope a query to only include incidents reported by a specific user.
     */
    public function scopeReportedBy($query, int $userId)
    {
        return $query->whereHas('shift', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }
} 