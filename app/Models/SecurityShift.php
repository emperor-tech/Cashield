<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityShift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'zone_id',
        'started_at',
        'ended_at',
        'notes',
        'route_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'route_data' => 'array',
    ];

    /**
     * Get the user assigned to this shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team this shift belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SecurityTeam::class, 'team_id');
    }

    /**
     * Get the zone this shift is assigned to.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(CampusZone::class, 'zone_id');
    }

    /**
     * Get the incidents reported during this shift.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(ShiftIncident::class, 'shift_id');
    }

    /**
     * Get the checkpoint scans made during this shift.
     */
    public function checkpointScans(): HasMany
    {
        return $this->hasMany(CheckpointScan::class, 'shift_id');
    }

    /**
     * Check if the shift is currently active.
     */
    public function isActive(): bool
    {
        return !$this->ended_at;
    }

    /**
     * Get the duration of the shift in minutes.
     */
    public function getDurationInMinutes(): int
    {
        if (!$this->ended_at) {
            return $this->started_at->diffInMinutes(now());
        }

        return $this->started_at->diffInMinutes($this->ended_at);
    }

    /**
     * Get the formatted duration of the shift.
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->getDurationInMinutes();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }

    /**
     * Get the total distance covered during the shift (in meters).
     */
    public function getTotalDistanceAttribute(): float
    {
        if (!$this->route_data || empty($this->route_data['coordinates'])) {
            return 0.0;
        }

        $distance = 0.0;
        $coordinates = $this->route_data['coordinates'];
        $count = count($coordinates);

        for ($i = 1; $i < $count; $i++) {
            $distance += $this->calculateDistance(
                $coordinates[$i - 1]['lat'],
                $coordinates[$i - 1]['lng'],
                $coordinates[$i]['lat'],
                $coordinates[$i]['lng']
            );
        }

        return round($distance, 2);
    }

    /**
     * Calculate the distance between two points using the Haversine formula.
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * End the current shift.
     */
    public function end(?string $notes = null): bool
    {
        if ($this->ended_at) {
            return false;
        }

        $this->ended_at = now();
        
        if ($notes) {
            $this->notes = $notes;
        }

        return $this->save();
    }

    /**
     * Add a checkpoint scan to the shift.
     */
    public function addCheckpointScan(ZoneCheckpoint $checkpoint, array $metadata = []): CheckpointScan
    {
        return $this->checkpointScans()->create([
            'checkpoint_id' => $checkpoint->id,
            'scanned_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Report an incident during the shift.
     */
    public function reportIncident(
        string $type,
        string $description,
        string $severity = 'low',
        ?array $location = null,
        ?array $metadata = null
    ): ShiftIncident {
        return $this->incidents()->create([
            'type' => $type,
            'description' => $description,
            'severity' => $severity,
            'location' => $location,
            'metadata' => $metadata,
            'reported_at' => now(),
        ]);
    }

    /**
     * Update the route data with a new coordinate.
     */
    public function addRouteCoordinate(float $latitude, float $longitude, ?array $metadata = null): bool
    {
        $routeData = $this->route_data ?? ['coordinates' => []];
        
        $routeData['coordinates'][] = array_merge([
            'lat' => $latitude,
            'lng' => $longitude,
            'timestamp' => now()->toIso8601String(),
        ], $metadata ?? []);

        $this->route_data = $routeData;
        return $this->save();
    }
} 