<?php

namespace App\Models;

use App\Events\ZoneRiskLevelChanged;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CampusZone
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property array|null $boundaries
 * @property float|null $center_latitude
 * @property float|null $center_longitude
 * @property float|null $radius
 * @property string $zone_type
 * @property string $risk_level
 * @property string $security_level
 * @property array|null $operating_hours
 * @property bool $24h_access
 * @property bool $restricted_access
 * @property string|null $emergency_contact
 * @property string|null $emergency_phone
 * @property string|null $zone_manager
 * @property string|null $security_post
 * @property array|null $buildings
 * @property array|null $access_points
 * @property array|null $emergency_exits
 * @property array|null $security_devices
 * @property bool $active
 * @property string $color
 * @property int $patrol_frequency
 * @property int $incident_count
 * @property Carbon|null $last_patrolled_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * 
 * @property Collection $reports
 * @property Collection $primaryTeams
 * @property Collection $subscribedUsers
 */
class CampusZone extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 'code', 'description', 'boundaries', 'center_latitude', 'center_longitude', 
        'radius', 'zone_type', 'risk_level', 'security_level', 'operating_hours', 
        '24h_access', 'restricted_access', 'emergency_contact', 'emergency_phone', 
        'zone_manager', 'security_post', 'buildings', 'access_points', 'emergency_exits', 
        'security_devices', 'active', 'color', 'patrol_frequency', 'incident_count', 
        'last_patrolled_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'boundaries' => 'array',
        'operating_hours' => 'array',
        'buildings' => 'array',
        'access_points' => 'array',
        'emergency_exits' => 'array',
        'security_devices' => 'array',
        '24h_access' => 'boolean',
        'restricted_access' => 'boolean',
        'active' => 'boolean',
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'radius' => 'float',
        'patrol_frequency' => 'integer',
        'incident_count' => 'integer',
        'last_patrolled_at' => 'datetime',
    ];

    /**
     * Risk levels from lowest to highest
     * 
     * @var array<string>
     */
    public const RISK_LEVELS = ['low', 'medium', 'high', 'critical'];

    /**
     * Security levels from lowest to highest
     * 
     * @var array<string>
     */
    public const SECURITY_LEVELS = ['minimum', 'standard', 'enhanced', 'maximum'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($zone) {
            // Generate a unique code if not provided
            if (!$zone->code) {
                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $zone->name), 0, 4));
                $count = self::where('code', 'like', $prefix . '%')->count() + 1;
                $zone->code = $prefix . '-' . $count;
            }
        });

        static::updating(function ($zone) {
            // Track risk level changes
            if ($zone->isDirty('risk_level')) {
                $oldLevel = $zone->getOriginal('risk_level');
                $newLevel = $zone->risk_level;
                
                // Fire event for risk level change
                event(new ZoneRiskLevelChanged($zone, $oldLevel, $newLevel));
                
                // Log the audit record
                AuditService::logAction(
                    'update_risk_level',
                    'campus_zone',
                    $zone->id,
                    [
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel,
                        'zone_name' => $zone->name
                    ]
                );
            }

            // Track security level changes
            if ($zone->isDirty('security_level')) {
                AuditService::logAction(
                    'update_security_level',
                    'campus_zone',
                    $zone->id,
                    [
                        'old_level' => $zone->getOriginal('security_level'),
                        'new_level' => $zone->security_level,
                        'zone_name' => $zone->name
                    ]
                );
            }

            // Log other critical changes
            try {
                if ($zone->isDirty(['boundaries', 'center_latitude', 'center_longitude', 'restricted_access'])) {
                    AuditService::logModelChange('campus_zone', $zone->id, $zone->getDirty());
                }
            } catch (\Exception $e) {
                Log::error("Failed to log audit for campus zone update: " . $e->getMessage());
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get reports that occurred in this zone.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'zone_id');
    }

    /**
     * Get security teams primarily assigned to this zone.
     */
    public function primaryTeams()
    {
        return $this->hasMany(SecurityTeam::class, 'primary_zone_id');
    }

    /**
     * Get security teams that cover this zone as a secondary zone.
     */
    public function secondaryTeams()
    {
        // This is more complex since it's stored in a JSON array
        // We'll implement this as a method instead
        return SecurityTeam::whereJsonContains('secondary_zones', $this->id)->get();
    }

    /**
     * Get users subscribed to this zone for alerts.
     */
    public function subscribedUsers()
    {
        return $this->morphToMany(User::class, 'subscriptable', 'subscriptions')
                    ->withTimestamps();
    }

    /**
     * Get the teams assigned to this zone.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(SecurityTeam::class, 'zone_id');
    }

    /**
     * Get the checkpoints in this zone.
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(ZoneCheckpoint::class, 'zone_id');
    }

    /**
     * Get the active checkpoints in this zone.
     */
    public function activeCheckpoints(): HasMany
    {
        return $this->checkpoints()->where('active', true);
    }

    /**
     * Get the shifts that occurred in this zone.
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(SecurityShift::class, 'zone_id');
    }

    /**
     * Get the active shifts in this zone.
     */
    public function activeShifts(): HasMany
    {
        return $this->shifts()->whereNull('ended_at');
    }

    /**
     * Get the total number of incidents reported in this zone.
     */
    public function getIncidentsCountAttribute(): int
    {
        return ShiftIncident::whereHas('shift', function ($query) {
            $query->where('zone_id', $this->id);
        })->count();
    }

    /**
     * Get the total number of checkpoint scans in this zone.
     */
    public function getCheckpointScansCountAttribute(): int
    {
        return CheckpointScan::whereHas('checkpoint', function ($query) {
            $query->where('zone_id', $this->id);
        })->count();
    }

    /**
     * Get the total patrol duration in this zone (in minutes).
     */
    public function getTotalPatrolDurationAttribute(): int
    {
        return $this->shifts()
            ->whereNotNull('ended_at')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as total_duration')
            ->value('total_duration') ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Zone Boundary Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Set zone boundaries using GeoJSON polygon format.
     *
     * @param array $coordinates Array of [longitude, latitude] coordinates forming a polygon
     * @return bool
     */
    public function setBoundaries(array $coordinates): bool
    {
        // Validate the coordinates format
        if (empty($coordinates) || !is_array($coordinates[0]) || !is_array($coordinates[0][0])) {
            return false;
        }

        // Store as GeoJSON compatible format
        $this->boundaries = [
            'type' => 'Polygon',
            'coordinates' => [$coordinates] // GeoJSON requires an extra nesting level
        ];

        // Calculate and update the center point
        $this->calculateCenter();
        
        return $this->save();
    }

    /**
     * Set a circular boundary using center point and radius.
     *
     * @param float $latitude Center latitude
     * @param float $longitude Center longitude
     * @param float $radiusMeters Radius in meters
     * @return bool
     */
    public function setCircularBoundary(float $latitude, float $longitude, float $radiusMeters): bool
    {
        $this->center_latitude = $latitude;
        $this->center_longitude = $longitude;
        $this->radius = $radiusMeters;
        
        // Generate a circular polygon approximation for boundaries
        $this->boundaries = $this->generateCirclePolygon($latitude, $longitude, $radiusMeters);
        
        return $this->save();
    }

    /**
     * Calculate the center point of the zone boundaries.
     *
     * @return void
     */
    protected function calculateCenter(): void
    {
        if (empty($this->boundaries) || !isset($this->boundaries['coordinates'])) {
            return;
        }

        $coordinates = $this->boundaries['coordinates'][0] ?? [];
        if (empty($coordinates)) {
            return;
        }

        // Calculate centroid of polygon
        $latSum = 0;
        $lngSum = 0;
        $count = count($coordinates);

        foreach ($coordinates as $coord) {
            if (count($coord) >= 2) {
                $lngSum += $coord[0];
                $latSum += $coord[1];
            }
        }

        if ($count > 0) {
            $this->center_latitude = $latSum / $count;
            $this->center_longitude = $lngSum / $count;
        }
    }

    /**
     * Generate a polygon approximation of a circle.
     *
     * @param float $latitude Center latitude
     * @param float $longitude Center longitude
     * @param float $radiusMeters Radius in meters
     * @param int $numPoints Number of points to use (more = smoother circle)
     * @return array GeoJSON polygon
     */
    protected function generateCirclePolygon(float $latitude, float $longitude, float $radiusMeters, int $numPoints = 32): array
    {
        $coordinates = [];
        $earthRadius = 6371000; // Earth radius in meters
        
        // Convert radius from meters to degrees (approximate)
        $radiusLat = $radiusMeters / $earthRadius * (180 / M_PI);
        $radiusLng = $radiusLat / cos($latitude * M_PI / 180);
        
        // Generate points around the circle
        for ($i = 0; $i <= $numPoints; $i++) {
            $angle = ($i / $numPoints) * 2 * M_PI;
            $lat = $latitude + $radiusLat * sin($angle);
            $lng = $longitude + $radiusLng * cos($angle);
            $coordinates[] = [$lng, $lat]; // GeoJSON uses [lng, lat] order
        }
        
        // Close the polygon by repeating the first point
        if (!empty($coordinates) && $coordinates[0] !== $coordinates[count($coordinates) - 1]) {
            $coordinates[] = $coordinates[0];
        }
        
        return [
            'type' => 'Polygon',
            'coordinates' => [$coordinates]
        ];
    }

    /**
     * Check if a point is within the zone boundaries.
     *
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function containsPoint(float $latitude, float $longitude): bool
    {
        if (!$this->boundaries) {
            return false;
        }

        // Implement point-in-polygon algorithm
        $vertices = collect($this->boundaries)->map(function ($point) {
            return [
                'lat' => $point['lat'] ?? $point[0],
                'lng' => $point['lng'] ?? $point[1]
            ];
        })->toArray();

        $vertexCount = count($vertices);
        $isInside = false;

        for ($i = 0, $j = $vertexCount - 1; $i < $vertexCount; $j = $i++) {
            if (
                ($vertices[$i]['lat'] > $latitude) != ($vertices[$j]['lat'] > $latitude) &&
                $longitude < ($vertices[$j]['lng'] - $vertices[$i]['lng']) * ($latitude - $vertices[$i]['lat']) / ($vertices[$j]['lat'] - $vertices[$i]['lat']) + $vertices[$i]['lng']
            ) {
                $isInside = !$isInside;
            }
        }
        
        return $isInside;
    }

    /**
     * Calculate distance from zone center to a point in meters.
     *
     * @param float $latitude
     * @param float $longitude
     * @return float Distance in meters
     */
    public function distanceToPoint(float $latitude, float $longitude): float
    {
        if (!$this->center_latitude || !$this->center_longitude) {
            return PHP_FLOAT_MAX;
        }
        
        // Haversine formula for distance calculation
        $earthRadius = 6371000; // meters
        
        $latFrom = deg2rad($this->center_latitude);
        $lonFrom = deg2rad($this->center_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + 
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            
        return $angle * $earthRadius;
    }

    /*
    |--------------------------------------------------------------------------
    | Risk Level Assessment Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Update the risk level based on incident data.
     *
     * @param bool $notify Whether to trigger notifications
     * @return bool
     */
    public function updateRiskLevel(bool $notify = true): bool
    {
        $oldLevel = $this->risk_level;
        
        // Count incidents in the last 30 days
        $recentIncidents = $this->reports()
            ->where('created_at', '>=', now()->subDays(30))
            ->get();
            
        $recentCount = $recentIncidents->count();
        
        // Count high severity incidents
        $highSeverityCount = $recentIncidents->where('severity', 'high')->count();
        
        // Determine new risk level based on metrics
        $newLevel = 'low';
        
        if ($highSeverityCount >= 5 || $recentCount >= 20) {
            $newLevel = 'critical';
        } elseif ($highSeverityCount >= 3 || $recentCount >= 10) {
            $newLevel = 'high';
        } elseif ($highSeverityCount >= 1 || $recentCount >= 5) {
            $newLevel = 'medium';
        }
        
        // Update the risk level
        $this->risk_level = $newLevel;
        
        // Automatically adjust security level if necessary
        $this->adjustSecurityLevel();
        
        $success = $this->save();
        
        // Fire event if risk level changed and notifications are enabled
        if ($success && $oldLevel !== $newLevel && $notify) {
            event(new ZoneRiskLevelChanged($this, $oldLevel, $newLevel));
        }
        
        return $success;
    }

    /**
     * Adjust the security level based on risk level.
     *
     * @return void
     */
    public function adjustSecurityLevel(): void
    {
        // Map risk levels to minimum required security levels
        $securityMap = [
            'low' => 'minimum',
            'medium' => 'standard',
            'high' => 'enhanced',
            'critical' => 'maximum'
        ];
        
        $minimumSecurity = $securityMap[$this->risk_level] ?? 'standard';
        
        // Get the index of current and minimum security levels
        $currentIndex = array_search($this->security_level, self::SECURITY_LEVELS);
        $minimumIndex = array_search($minimumSecurity, self::SECURITY_LEVELS);
        
        // If current security level is lower than minimum required, upgrade it
        if ($currentIndex < $minimumIndex) {
            $this->security_level = $minimumSecurity;
        }
    }

    /**
     * Update the zone's incident count and check if risk level needs updating.
     *
     * @param bool $increment Whether to increment (true) or decrement (false)
     * @return bool
     */
    public function updateIncidentCount(bool $increment = true): bool
    {
        if ($increment) {
            $this->incident_count++;
        } elseif ($this->incident_count > 0) {
            $this->incident_count--;
        }
        
        $this->save();
        
        // Check if risk level needs updating
        if ($increment && $this->incident_count % 5 === 0) {
            $this->updateRiskLevel();
        }
        
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Operating Hours Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the zone is currently open based on operating hours.
     *
     * @return bool
     */
    public function isCurrentlyOpen(): bool
    {
        // 24-hour access zones are always open
        if ($this->getAttribute('24h_access')) {
            return true;
        }
        
        // Get current day and time
        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');
        
        // Check operating hours
        if ($this->operating_hours && isset($this->operating_hours[$currentDay])) {
            $daySchedule = $this->operating_hours[$currentDay];
            
            // If schedule is "closed", zone is closed
            if ($daySchedule === 'closed') {
                return false;
            }
            
            // If schedule is array with opening/closing times, check if current time is within range
            if (is_array($daySchedule) && count($daySchedule) === 2) {
                $openTime = $daySchedule[0];
                $closeTime = $daySchedule[1];
                
                // Handle schedules that span midnight
                if ($openTime > $closeTime) {
                    return $currentTime >= $openTime || $currentTime <= $closeTime;
                }
                
                return $currentTime >= $openTime && $currentTime <= $closeTime;
            }
        }
        
        // Default to closed if no operating hours defined
        return false;
    }

    /**
     * Set operating hours for a specific day.
     *
     * @param string $day Day of week (monday, tuesday, etc.)
     * @param string|array $hours 'closed' or ['09:00', '17:00']
     * @return bool
     */
    public function setDayHours(string $day, $hours): bool
    {
        $day = strtolower($day);
        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        if (!in_array($day, $validDays)) {
            return false;
        }
        
        $operatingHours = $this->operating_hours ?? [];
        $operatingHours[$day] = $hours;
        $this->operating_hours = $operatingHours;
        
        return $this->save();
    }

    /**
     * Set 24/7 access for the zone.
     *
     * @param bool $enabled
     * @return bool
     */
    public function set24HourAccess(bool $enabled): bool
    {
        $this->setAttribute('24h_access', $enabled);
        return $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Security Device Tracking Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add a security device to the zone.
     *
     * @param array $device Device details
     * @return bool
     */
    public function addSecurityDevice(array $device): bool
    {
        if (!isset($device['type']) || !isset($device['location'])) {
            return false;
        }
        
        $devices = $this->security_devices ?? [];
        $device['id'] = count($devices) + 1;
        $device['installed_at'] = now()->toIso8601String();
        
        $devices[] = $device;
        $this->security_devices = $devices;
        
        return $this->save();
    }

    /**
     * Remove a security device from the zone.
     *
     * @param int $deviceId
     * @return bool
     */
    public function removeSecurityDevice(int $deviceId): bool
    {
        $devices = $this->security_devices ?? [];
        
        foreach ($devices as $key => $device) {
            if (isset($device['id']) && $device['id'] === $deviceId) {
                unset($devices[$key]);
                $this->security_devices = array_values($devices); // Re-index array
                return $this->save();
            }
        }
        
        return false;
    }

    /**
     * Get all security devices of a specific type.
     *
     * @param string $type Device type (camera, sensor, alarm, etc.)
     * @return array
     */
    public function getSecurityDevicesByType(string $type): array
    {
        $devices = $this->security_devices ?? [];
        
        return array_filter($devices, function($device) use ($type) {
            return isset($device['type']) && $device['type'] === $type;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Access Control Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Set restricted access status for the zone.
     *
     * @param bool $restricted
     * @return bool
     */
    public function setRestrictedAccess(bool $restricted): bool
    {
        $this->restricted_access = $restricted;
        return $this->save();
    }

    /**
     * Add an access point to the zone.
     *
     * @param array $accessPoint Access point details
     * @return bool
     */
    public function addAccessPoint(array $accessPoint): bool
    {
        if (!isset($accessPoint['name']) || !isset($accessPoint['location'])) {
            return false;
        }
        
        $accessPoints = $this->access_points ?? [];
        $accessPoint['id'] = count($accessPoints) + 1;
        
        $accessPoints[] = $accessPoint;
        $this->access_points = $accessPoints;
        
        return $this->save();
    }

    /**
     * Remove an access point from the zone.
     *
     * @param int $accessPointId
     * @return bool
     */
    public function removeAccessPoint(int $accessPointId): bool
    {
        $accessPoints = $this->access_points ?? [];
        
        foreach ($accessPoints as $key => $point) {
            if (isset($point['id']) && $point['id'] === $accessPointId) {
                unset($accessPoints[$key]);
                $this->access_points = array_values($accessPoints); // Re-index array
                return $this->save();
            }
        }
        
        return false;
    }

    /**
     * Add an emergency exit to the zone.
     *
     * @param array $exit Emergency exit details
     * @return bool
     */
    public function addEmergencyExit(array $exit): bool
    {
        if (!isset($exit['name']) || !isset($exit['location'])) {
            return false;
        }
        
        $exits = $this->emergency_exits ?? [];
        $exit['id'] = count($exits) + 1;
        
        $exits[] = $exit;
        $this->emergency_exits = $exits;
        
        return $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Patrol Scheduling Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Set patrol frequency for the zone.
     *
     * @param int $frequencyMinutes Minutes between patrols
     * @return bool
     */
    public function setPatrolFrequency(int $frequencyMinutes): bool
    {
        if ($frequencyMinutes < 10) {
            $frequencyMinutes = 10; // Minimum 10 minutes
        }
        
        $this->patrol_frequency = $frequencyMinutes;
        return $this->save();
    }

    /**
     * Record a patrol in this zone.
     *
     * @param SecurityTeam|null $team The team that performed the patrol
     * @param User|null $user The user who performed the patrol
     * @return bool
     */
    public function recordPatrol(?SecurityTeam $team = null, ?User $user = null): bool
    {
        $this->last_patrolled_at = now();
        
        $result = $this->save();
        
        // Log the patrol
        AuditService::logAction(
            'zone_patrolled',
            'campus_zone',
            $this->id,
            [
                'team_id' => $team ? $team->id : null,
                'team_name' => $team ? $team->name : null,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : null,
                'zone_name' => $this->name
            ]
        );
        
        return $result;
    }

    /**
     * Check if a patrol is due for this zone.
     *
     * @return bool
     */
    public function isPatrolDue(): bool
    {
        if (!$this->last_patrolled_at) {
            return true;
        }
        
        $minutesSinceLastPatrol = $this->last_patrolled_at->diffInMinutes(now());
        return $minutesSinceLastPatrol >= $this->patrol_frequency;
    }

    /**
     * Get the time until next patrol is due.
     *
     * @return int Minutes until next patrol
     */
    public function minutesUntilNextPatrol(): int
    {
        if (!$this->last_patrolled_at) {
            return 0;
        }
        
        $minutesSinceLastPatrol = $this->last_patrolled_at->diffInMinutes(now());
        $minutesRemaining = $this->patrol_frequency - $minutesSinceLastPatrol;
        
        return max(0, $minutesRemaining);
    }

    /*
    |--------------------------------------------------------------------------
    | Zone Statistics Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get incident statistics for this zone.
     *
     * @param int $days Number of days to include
     * @return array
     */
    public function getIncidentStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $incidents = $this->reports()
            ->where('created_at', '>=', $startDate)
            ->get();
        
        // Total incidents
        $total = $incidents->count();
        
        // Severity breakdown
        $severityStats = [
            'high' => $incidents->where('severity', 'high')->count(),
            'medium' => $incidents->where('severity', 'medium')->count(),
            'low' => $incidents->where('severity', 'low')->count()
        ];
        
        // Status breakdown
        $statusStats = [
            'open' => $incidents->where('status', 'open')->count(),
            'in_progress' => $incidents->where('status', 'in_progress')->count(),
            'resolved' => $incidents->where('status', 'resolved')->count()
        ];
        
        // Category breakdown (if categories exist)
        $categoryStats = [];
        foreach ($incidents as $incident) {
            if ($incident->category_id) {
                $categoryName = $incident->category ? $incident->category->name : 'Unknown';
                if (!isset($categoryStats[$categoryName])) {
                    $categoryStats[$categoryName] = 0;
                }
                $categoryStats[$categoryName]++;
            }
        }
        
        // Time of day breakdown
        $timeStats = [
            'morning' => $incidents->filter(function($i) {
                return $i->created_at->hour >= 6 && $i->created_at->hour < 12;
            })->count(),
            'afternoon' => $incidents->filter(function($i) {
                return $i->created_at->hour >= 12 && $i->created_at->hour < 18;
            })->count(),
            'evening' => $incidents->filter(function($i) {
                return $i->created_at->hour >= 18 && $i->created_at->hour < 22;
            })->count(),
            'night' => $incidents->filter(function($i) {
                return $i->created_at->hour >= 22 || $i->created_at->hour < 6;
            })->count()
        ];
        
        // Day of week breakdown
        $dayStats = [];
        foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
            $dayName = strtolower(now()->startOfWeek()->addDays($day)->format('l'));
            $dayStats[$dayName] = $incidents->filter(function($i) use ($day) {
                return $i->created_at->dayOfWeek === $day;
            })->count();
        }
        
        return [
            'total' => $total,
            'severity' => $severityStats,
            'status' => $statusStats,
            'category' => $categoryStats,
            'time_of_day' => $timeStats,
            'day_of_week' => $dayStats
        ];
    }

    /**
     * Get response time statistics for this zone.
     *
     * @param int $days Number of days to include
     * @return array
     */
    public function getResponseTimeStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $incidents = $this->reports()
            ->whereNotNull('response_time')
            ->where('created_at', '>=', $startDate)
            ->get();
        
        if ($incidents->isEmpty()) {
            return [
                'average' => 0,
                'median' => 0,
                'min' => 0,
                'max' => 0,
                'by_severity' => [
                    'high' => 0,
                    'medium' => 0,
                    'low' => 0
                ]
            ];
        }
        
        // Calculate response times in minutes
        $responseTimes = [];
        foreach ($incidents as $incident) {
            $created = $incident->incident_date ?? $incident->created_at;
            $responseTime = $created->diffInMinutes($incident->response_time);
            $responseTimes[] = $responseTime;
        }
        
        // Sort for median calculation
        sort($responseTimes);
        
        // Calculate median
        $count = count($responseTimes);
        $middle = floor(($count - 1) / 2);
        $median = $responseTimes[$middle];
        if ($count % 2 === 0) {
            $median = ($median + $responseTimes[$middle + 1]) / 2;
        }
        
        // Calculate by severity
        $highSeverity = $incidents->where('severity', 'high');
        $mediumSeverity = $incidents->where('severity', 'medium');
        $lowSeverity = $incidents->where('severity', 'low');
        
        $calculateAvg = function($collection) {
            if ($collection->isEmpty()) {
                return 0;
            }
            $total = 0;
            $count = 0;
            foreach ($collection as $incident) {
                $created = $incident->incident_date ?? $incident->created_at;
                $total += $created->diffInMinutes($incident->response_time);
                $count++;
            }
            return $count > 0 ? round($total / $count) : 0;
        };
        
        return [
            'average' => round(array_sum($responseTimes) / $count),
            'median' => round($median),
            'min' => min($responseTimes),
            'max' => max($responseTimes),
            'by_severity' => [
                'high' => $calculateAvg($highSeverity),
                'medium' => $calculateAvg($mediumSeverity),
                'low' => $calculateAvg($lowSeverity)
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Find the zone containing a specific point.
     *
     * @param float $latitude
     * @param float $longitude
     * @return CampusZone|null
     */
    public static function findZoneContainingPoint(float $latitude, float $longitude): ?CampusZone
    {
        $zones = self::where('active', true)->get();
        
        foreach ($zones as $zone) {
            if ($zone->containsPoint($latitude, $longitude)) {
                return $zone;
            }
        }
        
        return null;
    }

    /**
     * Find the nearest zone to a specific point.
     *
     * @param float $latitude
     * @param float $longitude
     * @return CampusZone|null
     */
    public static function findNearestZone(float $latitude, float $longitude): ?CampusZone
    {
        // First check if any zone contains the point
        $containingZone = self::findZoneContainingPoint($latitude, $longitude);
        if ($containingZone) {
            return $containingZone;
        }
        
        // If not, find the nearest zone by center point
        $zones = self::where('active', true)
            ->whereNotNull('center_latitude')
            ->whereNotNull('center_longitude')
            ->get();
            
        if ($zones->isEmpty()) {
            return null;
        }
        
        $nearestZone = null;
        $nearestDistance = PHP_FLOAT_MAX;
        
        foreach ($zones as $zone) {
            $distance = $zone->distanceToPoint($latitude, $longitude);
            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestZone = $zone;
            }
        }
        
        return $nearestZone;
    }

    /**
     * Get zones requiring patrol based on patrol frequency.
     *
     * @return Collection
     */
    public static function getZonesRequiringPatrol(): Collection
    {
        return self::where('active', true)
            ->where(function($query) {
                $query->whereNull('last_patrolled_at')
                      ->orWhereRaw('TIME_TO_SEC(TIMEDIFF(NOW(), last_patrolled_at)) / 60 >= patrol_frequency');
            })
            ->orderBy('risk_level', 'desc') // Prioritize high-risk zones
            ->orderByRaw('COALESCE(TIME_TO_SEC(TIMEDIFF(NOW(), last_patrolled_at)) / 60, 9999) DESC') // Order by time since last patrol
            ->get();
    }

    /**
     * Get high-risk zones.
     *
     * @return Collection
     */
    public static function getHighRiskZones(): Collection
    {
        return self::where('active', true)
            ->whereIn('risk_level', ['high', 'critical'])
            ->get();
    }

    /**
     * Get the color for risk level display.
     *
     * @return string
     */
    public function getRiskLevelColor(): string
    {
        return match($this->risk_level) {
            'critical' => 'purple',
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'blue'
        };
    }

    /**
     * Get the color for security level display.
     *
     * @return string
     */
    public function getSecurityLevelColor(): string
    {
        return match($this->security_level) {
            'maximum' => 'red',
            'enhanced' => 'orange',
            'standard' => 'blue',
            'minimum' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get the color for zone type display.
     *
     * @return string
     */
    public function getZoneTypeColor(): string
    {
        return match($this->zone_type) {
            'academic' => 'blue',
            'residential' => 'green',
            'administrative' => 'purple',
            'recreational' => 'yellow',
            'parking' => 'gray',
            'medical' => 'red',
            'dining' => 'orange',
            'library' => 'indigo',
            default => 'gray'
        };
    }
}

