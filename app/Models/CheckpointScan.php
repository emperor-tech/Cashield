<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckpointScan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'shift_id',
        'checkpoint_id',
        'scanned_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scanned_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the shift this scan belongs to.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(SecurityShift::class, 'shift_id');
    }

    /**
     * Get the checkpoint that was scanned.
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(ZoneCheckpoint::class, 'checkpoint_id');
    }

    /**
     * Get the security officer who performed the scan.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
     * Get the time elapsed since the scan.
     */
    public function getTimeElapsedAttribute(): string
    {
        return $this->scanned_at->diffForHumans();
    }

    /**
     * Scope a query to only include scans from a specific shift.
     */
    public function scopeFromShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Scope a query to only include scans at a specific checkpoint.
     */
    public function scopeAtCheckpoint($query, int $checkpointId)
    {
        return $query->where('checkpoint_id', $checkpointId);
    }

    /**
     * Scope a query to only include recent scans.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('scanned_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to only include scans by a specific officer.
     */
    public function scopeByOfficer($query, int $userId)
    {
        return $query->whereHas('shift', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to only include scans in a specific zone.
     */
    public function scopeInZone($query, int $zoneId)
    {
        return $query->whereHas('checkpoint', function ($query) use ($zoneId) {
            $query->where('zone_id', $zoneId);
        });
    }

    /**
     * Check if this scan was on time.
     */
    public function wasOnTime(int $intervalMinutes = 60): bool
    {
        $previousScan = static::where('checkpoint_id', $this->checkpoint_id)
            ->where('scanned_at', '<', $this->scanned_at)
            ->latest('scanned_at')
            ->first();

        if (!$previousScan) {
            return true;
        }

        return $previousScan->scanned_at->diffInMinutes($this->scanned_at) <= $intervalMinutes;
    }

    /**
     * Get the scan status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->wasOnTime() ? 'admin-badge-green' : 'admin-badge-red';
    }

    /**
     * Get the scan status text.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->wasOnTime() ? 'On Time' : 'Late';
    }
} 