<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZoneCheckpoint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'zone_id',
        'name',
        'code',
        'location',
        'description',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'location' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Get the zone this checkpoint belongs to.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(CampusZone::class, 'zone_id');
    }

    /**
     * Get the scans for this checkpoint.
     */
    public function scans(): HasMany
    {
        return $this->hasMany(CheckpointScan::class, 'checkpoint_id');
    }

    /**
     * Get the recent scans for this checkpoint.
     */
    public function recentScans(int $hours = 24): HasMany
    {
        return $this->scans()->where('scanned_at', '>=', now()->subHours($hours));
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
     * Get the total number of scans.
     */
    public function getScansCountAttribute(): int
    {
        return $this->scans()->count();
    }

    /**
     * Get the last scan time.
     */
    public function getLastScanAttribute(): ?string
    {
        $lastScan = $this->scans()->latest('scanned_at')->first();
        return $lastScan ? $lastScan->scanned_at->format('M d, Y H:i') : null;
    }

    /**
     * Get the last security officer who scanned this checkpoint.
     */
    public function getLastOfficerAttribute(): ?User
    {
        $lastScan = $this->scans()->with('shift.user')->latest('scanned_at')->first();
        return $lastScan ? $lastScan->shift->user : null;
    }

    /**
     * Check if the checkpoint is due for a scan.
     */
    public function isDueForScan(int $intervalMinutes = 60): bool
    {
        $lastScan = $this->scans()->latest('scanned_at')->first();
        
        if (!$lastScan) {
            return true;
        }

        return $lastScan->scanned_at->diffInMinutes(now()) >= $intervalMinutes;
    }

    /**
     * Get the time until next scan is due.
     */
    public function getTimeUntilNextScanAttribute(): string
    {
        $lastScan = $this->scans()->latest('scanned_at')->first();
        
        if (!$lastScan) {
            return 'Due now';
        }

        $nextScanDue = $lastScan->scanned_at->addHour();
        
        if ($nextScanDue->isPast()) {
            return 'Overdue';
        }

        return $nextScanDue->diffForHumans(['parts' => 2]);
    }

    /**
     * Scope a query to only include active checkpoints.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include checkpoints in a specific zone.
     */
    public function scopeInZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Scope a query to only include checkpoints that are due for a scan.
     */
    public function scopeDueForScan($query, int $intervalMinutes = 60)
    {
        return $query->whereDoesntHave('scans', function ($query) use ($intervalMinutes) {
            $query->where('scanned_at', '>=', now()->subMinutes($intervalMinutes));
        });
    }

    /**
     * Generate a new unique checkpoint code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
} 