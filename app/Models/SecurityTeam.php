<?php

namespace App\Models;

use App\Events\TeamStatusChanged;
use App\Events\TeamMemberAdded;
use App\Events\TeamAssignedToReport;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SecurityTeam
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $team_type
 * @property int $team_size
 * @property int|null $leader_id
 * @property int|null $primary_zone_id
 * @property array|null $secondary_zones
 * @property string $shift
 * @property array|null $shift_schedule
 * @property string|null $shift_start
 * @property string|null $shift_end
 * @property array|null $off_days
 * @property array|null $equipment
 * @property bool $has_vehicle
 * @property string|null $vehicle_id
 * @property bool $has_radio
 * @property bool $has_body_cam
 * @property string $status
 * @property bool $available_for_dispatch
 * @property Carbon|null $last_dispatch_time
 * @property Carbon|null $next_available_time
 * @property int $incidents_resolved
 * @property int $average_response_time
 * @property float $performance_rating
 * @property int $patrols_completed
 * @property string|null $contact_number
 * @property string|null $radio_channel
 * @property string|null $emergency_contact
 * @property string|null $notes
 * @property string $color
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * 
 * @property User|null $leader
 * @property CampusZone|null $primaryZone
 * @property Collection $members
 * @property Collection $activeReports
 * @property Collection $resolvedReports
 */
class SecurityTeam extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 'code', 'team_type', 'team_size', 'leader_id', 'primary_zone_id', 
        'secondary_zones', 'shift', 'shift_schedule', 'shift_start', 'shift_end', 
        'off_days', 'equipment', 'has_vehicle', 'vehicle_id', 'has_radio', 
        'has_body_cam', 'status', 'available_for_dispatch', 'last_dispatch_time', 
        'next_available_time', 'incidents_resolved', 'average_response_time', 
        'performance_rating', 'patrols_completed', 'contact_number', 'radio_channel', 
        'emergency_contact', 'notes', 'color', 'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'secondary_zones' => 'array',
        'shift_schedule' => 'array',
        'off_days' => 'array',
        'equipment' => 'array',
        'has_vehicle' => 'boolean',
        'has_radio' => 'boolean',
        'has_body_cam' => 'boolean',
        'available_for_dispatch' => 'boolean',
        'last_dispatch_time' => 'datetime',
        'next_available_time' => 'datetime',
        'performance_rating' => 'float',
        'active' => 'boolean',
        'team_size' => 'integer',
        'incidents_resolved' => 'integer',
        'average_response_time' => 'integer',
        'patrols_completed' => 'integer',
    ];

    /**
     * Team statuses that are considered "available" for dispatch
     *
     * @var array<string>
     */
    public const AVAILABLE_STATUSES = ['active'];

    /**
     * Team statuses that are considered "unavailable" for dispatch
     *
     * @var array<string>
     */
    public const UNAVAILABLE_STATUSES = ['off_duty', 'training', 'on_leave', 'responding', 'unavailable'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            // Generate a unique code if not provided
            if (!$team->code) {
                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $team->name), 0, 3));
                $count = self::where('code', 'like', $prefix . '%')->count() + 1;
                $team->code = $prefix . '-' . $count;
            }
        });

        static::updating(function ($team) {
            // Track status changes
            if ($team->isDirty('status')) {
                // Update availability based on status
                if (in_array($team->status, self::UNAVAILABLE_STATUSES)) {
                    $team->available_for_dispatch = false;
                } elseif (in_array($team->status, self::AVAILABLE_STATUSES)) {
                    $team->available_for_dispatch = true;
                }
                
                // Fire event for status change
                event(new TeamStatusChanged($team));
            }

            // Log the audit record
            try {
                AuditService::logModelChange('security_team', $team->id, $team->getDirty());
            } catch (\Exception $e) {
                Log::error("Failed to log audit for security team update: " . $e->getMessage());
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the team leader.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the primary zone this team is assigned to.
     */
    public function primaryZone(): BelongsTo
    {
        return $this->belongsTo(CampusZone::class, 'primary_zone_id');
    }

    /**
     * Get all team members.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'security_team_members')
                    ->withPivot('role', 'joined_at', 'badge_number', 'skills', 'active')
                    ->withTimestamps();
    }

    /**
     * Get active members only.
     */
    public function activeMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'security_team_members')
                    ->wherePivot('active', true)
                    ->withPivot('role', 'joined_at', 'badge_number', 'skills')
                    ->withTimestamps();
    }

    /**
     * Get reports assigned to this team.
     */
    public function assignedReports()
    {
        return $this->hasMany(Report::class, 'assigned_team_id');
    }

    /**
     * Get active reports assigned to this team.
     */
    public function activeReports()
    {
        return $this->hasMany(Report::class, 'assigned_team_id')
                    ->whereIn('status', Report::ACTIVE_STATUSES);
    }

    /**
     * Get resolved reports by this team.
     */
    public function resolvedReports()
    {
        return $this->hasMany(Report::class, 'assigned_team_id')
                    ->whereIn('status', Report::CLOSED_STATUSES);
    }

    /**
     * Get secondary zones this team covers.
     */
    public function secondaryZones()
    {
        if (!$this->secondary_zones) {
            return collect();
        }

        return CampusZone::whereIn('id', $this->secondary_zones)->get();
    }

    /**
     * Get the shifts for this team.
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(SecurityShift::class, 'team_id');
    }

    /**
     * Get the active shifts for this team.
     */
    public function activeShifts(): HasMany
    {
        return $this->shifts()->whereNull('ended_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Team Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if team is currently available for dispatch.
     *
     * @return bool
     */
    public function isAvailableForDispatch(): bool
    {
        // Check status
        if (!in_array($this->status, self::AVAILABLE_STATUSES) || !$this->available_for_dispatch) {
            return false;
        }

        // Check time constraints
        if ($this->next_available_time && $this->next_available_time->isFuture()) {
            return false;
        }

        // Check if currently within shift hours
        if (!$this->isCurrentlyOnDuty()) {
            return false;
        }

        return true;
    }

    /**
     * Check if team is currently on duty based on shift schedule.
     *
     * @return bool
     */
    public function isCurrentlyOnDuty(): bool
    {
        // 24-hour teams are always on duty
        if ($this->shift === '24h') {
            return true;
        }

        // Get current day and time
        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        // Check if today is an off day
        if ($this->off_days && in_array($currentDay, $this->off_days)) {
            return false;
        }

        // If using shift_schedule (detailed schedule by day)
        if ($this->shift_schedule && isset($this->shift_schedule[$currentDay])) {
            $daySchedule = $this->shift_schedule[$currentDay];
            
            // Format could be ['08:00', '16:00'] or 'closed'
            if (is_array($daySchedule) && count($daySchedule) === 2) {
                return $currentTime >= $daySchedule[0] && $currentTime <= $daySchedule[1];
            }
            
            return $daySchedule !== 'closed';
        }

        // If using simple shift_start and shift_end
        if ($this->shift_start && $this->shift_end) {
            // For shifts that span midnight
            if ($this->shift_start > $this->shift_end) {
                return $currentTime >= $this->shift_start || $currentTime <= $this->shift_end;
            }
            
            return $currentTime >= $this->shift_start && $currentTime <= $this->shift_end;
        }

        // If no specific schedule is set, assume always on duty
        return true;
    }

    /**
     * Update team status with appropriate side effects.
     *
     * @param string $status New status
     * @param Carbon|null $nextAvailableTime When team will be available again
     * @param string|null $notes Additional notes about status change
     * @return bool
     */
    public function updateStatus(string $status, ?Carbon $nextAvailableTime = null, ?string $notes = null): bool
    {
        $oldStatus = $this->status;
        $this->status = $status;

        // Update availability
        if (in_array($status, self::UNAVAILABLE_STATUSES)) {
            $this->available_for_dispatch = false;
        } elseif (in_array($status, self::AVAILABLE_STATUSES)) {
            $this->available_for_dispatch = true;
        }

        // Set next available time if provided
        if ($nextAvailableTime) {
            $this->next_available_time = $nextAvailableTime;
        } elseif ($status === 'active') {
            $this->next_available_time = null; // Clear next available time if active
        }

        // Save changes
        $success = $this->save();

        // Log audit
        AuditService::logAction(
            'update_team_status',
            'security_team',
            $this->id,
            [
                'old_status' => $oldStatus,
                'new_status' => $status,
                'next_available_time' => $nextAvailableTime ? $nextAvailableTime->toIso8601String() : null,
                'notes' => $notes
            ]
        );

        return $success;
    }

    /**
     * Mark team as dispatched to an incident.
     *
     * @param Report $report The report being responded to
     * @param int $estimatedDurationMinutes Estimated response duration in minutes
     * @return bool
     */
    public function dispatchToIncident(Report $report, int $estimatedDurationMinutes = 60): bool
    {
        // Set dispatch time
        $this->last_dispatch_time = now();
        
        // Calculate when team will be available again
        $this->next_available_time = now()->addMinutes($estimatedDurationMinutes);
        
        // Update status
        $this->status = 'responding';
        $this->available_for_dispatch = false;
        
        // Save changes
        $success = $this->save();
        
        // Assign team to the report
        if ($success && !$report->assigned_team_id) {
            $report->assignTo($this->id);
            
            // Fire event
            event(new TeamAssignedToReport($this, $report));
            
            // Log audit
            AuditService::logAction(
                'team_dispatched',
                'security_team',
                $this->id,
                [
                    'report_id' => $report->id,
                    'estimated_duration' => $estimatedDurationMinutes,
                    'dispatch_time' => $this->last_dispatch_time->toIso8601String()
                ]
            );
        }
        
        return $success;
    }

    /**
     * Mark team as returned from an incident.
     *
     * @param Report $report The report that was responded to
     * @param bool $markAvailable Whether to mark team as available immediately
     * @return bool
     */
    public function returnFromIncident(Report $report, bool $markAvailable = true): bool
    {
        // Calculate response time
        $responseTime = 0;
        if ($this->last_dispatch_time && $report->response_time) {
            $responseTime = $this->last_dispatch_time->diffInMinutes($report->response_time);
        }
        
        // Update team statistics
        if ($report->isClosed()) {
            $this->incidents_resolved += 1;
            
            // Update average response time
            if ($responseTime > 0) {
                // Weighted average to account for previous responses
                $totalResponses = $this->incidents_resolved;
                $this->average_response_time = (
                    ($this->average_response_time * ($totalResponses - 1)) + $responseTime
                ) / $totalResponses;
            }
        }
        
        // Reset team status
        if ($markAvailable) {
            $this->status = 'active';
            $this->available_for_dispatch = true;
            $this->next_available_time = null;
        } else {
            // Set to off duty but keep next_available_time
            $this->status = 'off_duty';
            $this->available_for_dispatch = false;
        }
        
        // Save changes
        $success = $this->save();
        
        // Log audit
        AuditService::logAction(
            'team_returned',
            'security_team',
            $this->id,
            [
                'report_id' => $report->id,
                'response_time' => $responseTime,
                'marked_available' => $markAvailable
            ]
        );
        
        return $success;
    }

    /**
     * Record a completed patrol.
     *
     * @param CampusZone|null $zone The zone that was patrolled
     * @param array $notes Optional notes about the patrol
     * @return bool
     */
    public function recordPatrol(?CampusZone $zone = null, array $notes = []): bool
    {
        // Increment patrol count
        $this->patrols_completed += 1;
        
        // Update zone's last_patrolled_at timestamp if provided
        if ($zone) {
            $zone->last_patrolled_at = now();
            $zone->save();
        }
        
        // Save changes
        $success = $this->save();
        
        // Log audit
        AuditService::logAction(
            'patrol_completed',
            'security_team',
            $this->id,
            [
                'zone_id' => $zone ? $zone->id : null,
                'zone_name' => $zone ? $zone->name : 'Unknown',
                'notes' => $notes
            ]
        );
        
        return $success;
    }

    /*
    |--------------------------------------------------------------------------
    | Member Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add a member to the team.
     *
     * @param User $user The user to add
     * @param string $role Role in team (leader, deputy, member, trainee)
     * @param array $attributes Additional pivot attributes
     * @return bool
     */
    public function addMember(User $user, string $role = 'member', array $attributes = []): bool
    {
        // Check if user is already a member
        if ($this->members()->where('user_id', $user->id)->exists()) {
            // Update pivot if already a member
            $this->members()->updateExistingPivot($user->id, array_merge([
                'role' => $role,
                'active' => true
            ], $attributes));
            
            return true;
        }
        
        // Add new member
        $pivotData = array_merge([
            'role' => $role,
            'joined_at' => now(),
            'active' => true
        ], $attributes);
        
        $this->members()->attach($user->id, $pivotData);
        
        // Update leader if role is leader
        if ($role === 'leader' && $this->leader_id !== $user->id) {
            $this->leader_id = $user->id;
            $this->save();
        }
        
        // Fire event
        event(new TeamMemberAdded($this, $user, $role));
        
        // Log audit
        AuditService::logAction(
            'add_team_member',
            'security_team',
            $this->id,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => $role
            ]
        );
        
        return true;
    }

    /**
     * Remove a member from the team.
     *
     * @param User $user The user to remove
     * @param bool $softRemove Whether to soft-remove (mark as inactive) instead of detaching
     * @return bool
     */
    public function removeMember(User $user, bool $softRemove = true): bool
    {
        // Check if user is a member
        if (!$this->members()->where('user_id', $user->id)->exists()) {
            return false;
        }
        
        // Soft remove (mark as inactive) or hard remove (detach)
        if ($softRemove) {
            $this->members()->updateExistingPivot($user->id, [
                'active' => false
            ]);
        } else {
            $this->members()->detach($user->id);
        }
        
        // If user was the leader, clear leader_id
        if ($this->leader_id === $user->id) {
            $this->leader_id = null;
            $this->save();
        }
        
        // Log audit
        AuditService::logAction(
            'remove_team_member',
            'security_team',
            $this->id,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'soft_remove' => $softRemove
            ]
        );
        
        return true;
    }

    /**
     * Change member's role within the team.
     *
     * @param User $user The user to update
     * @param string $newRole The new role
     * @return bool
     */
    public function changeMemberRole(User $user, string $newRole): bool
    {
        // Check if user is a member
        if (!$this->members()->where('user_id', $user->id)->exists()) {
            return false;
        }
        
        // Update role
        $this->members()->updateExistingPivot($user->id, [
            'role' => $newRole
        ]);
        
        // If new role is leader, update leader_id
        if ($newRole === 'leader') {
            $this->leader_id = $user->id;
            $this->save();
        }
        // If user was the leader but no longer is, clear leader_id
        else if ($this->leader_id === $user->id) {
            $this->leader_id = null;
            $this->save();
        }
        
        // Log audit
        AuditService::logAction(
            'change_member_role',
            'security_team',
            $this->id,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'new_role' => $newRole
            ]
        );
        
        return true;
    }

    /**
     * Get team members by role.
     *
     * @param string $role Role to filter by
     * @return Collection
     */
    public function getMembersByRole(string $role): Collection
    {
        return $this->members()
                    ->wherePivot('role', $role)
                    ->wherePivot('active', true)
                    ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Equipment Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add equipment to the team.
     *
     * @param array $equipmentItems List of equipment to add
     * @return bool
     */
    public function addEquipment(array $equipmentItems): bool
    {
        $currentEquipment = $this->equipment ?? [];
        $this->equipment = array_merge($currentEquipment, $equipmentItems);
        
        return $this->save();
    }

    /**
     * Remove equipment from the team.
     *
     * @param string $equipmentItem The equipment item to remove
     * @return bool
     */
    public function removeEquipment(string $equipmentItem): bool
    {
        $currentEquipment = $this->equipment ?? [];
        
        if (($key = array_search($equipmentItem, $currentEquipment)) !== false) {
            unset($currentEquipment[$key]);
            $this->equipment = array_values($currentEquipment); // Re-index array
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Assign a vehicle to the team.
     *
     * @param string $vehicleId Vehicle identifier
     * @return bool
     */
    public function assignVehicle(string $vehicleId): bool
    {
        $this->has_vehicle = true;
        $this->vehicle_id = $vehicleId;
        
        return $this->save();
    }

    /**
     * Remove vehicle assignment from the team.
     *
     * @return bool
     */
    public function removeVehicle(): bool
    {
        $this->has_vehicle = false;
        $this->vehicle_id = null;
        
        return $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Zone Coverage Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Set the primary zone for this team.
     *
     * @param CampusZone $zone The zone to set as primary
     * @return bool
     */
    public function setPrimaryZone(CampusZone $zone): bool
    {
        $this->primary_zone_id = $zone->id;
        
        return $this->save();
    }

    /**
     * Add a secondary zone to this team's coverage.
     *
     * @param CampusZone $zone The zone to add
     * @return bool
     */
    public function addSecondaryZone(CampusZone $zone): bool
    {
        $secondaryZones = $this->secondary_zones ?? [];
        
        if (!in_array($zone->id, $secondaryZones)) {
            $secondaryZones[] = $zone->id;
            $this->secondary_zones = $secondaryZones;
            
            return $this->save();
        }
        
        return true;
    }

    /**
     * Remove a secondary zone from this team's coverage.
     *
     * @param CampusZone $zone The zone to remove
     * @return bool
     */
    public function removeSecondaryZone(CampusZone $zone): bool
    {
        $secondaryZones = $this->secondary_zones ?? [];
        
        if (($key = array_search($zone->id, $secondaryZones)) !== false) {
            unset($secondaryZones[$key]);
            $this->secondary_zones = array_values($secondaryZones); // Re-index array
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Check if team covers a specific zone (primary or secondary).
     *
     * @param CampusZone $zone The zone to check
     * @return bool
     */
    public function coversZone(CampusZone $zone): bool
    {
        // Check primary zone
        if ($this->primary_zone_id === $zone->id) {
            return true;
        }
        
        // Check secondary zones
        $secondaryZones = $this->secondary_zones ?? [];
        return in_array($zone->id, $secondaryZones);
    }

    /*
    |--------------------------------------------------------------------------
    | Performance & Analytics Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Update the team's performance rating.
     *
     * @param float $rating New rating (1-5)
     * @return bool
     */
    public function updatePerformanceRating(float $rating): bool
    {
        // Ensure rating is between 1 and 5
        $rating = max(1, min(5, $rating));
        
        $this->performance_rating = $rating;
        
        return $this->save();
    }

    /**
     * Calculate and return key performance metrics.
     *
     * @return array
     */
    public function getPerformanceMetrics(): array
    {
        // Calculate response efficiency
        $responseEfficiency = 0;
        if ($this->average_response_time > 0) {
            // Lower is better for response time
            $responseEfficiency = 100 - min(100, ($this->average_response_time / 15) * 100);
        }
        
        // Calculate incident resolution rate
        $totalIncidents = $this->assignedReports()->count();
        $resolutionRate = 0;
        if ($totalIncidents > 0) {
            $resolutionRate = ($this->incidents_resolved / $totalIncidents) * 100;
        }
        
        // Calculate patrol coverage
        $patrolCoverage = 0;
        $requiredPatrols = 0;
        
        if ($this->primary_zone) {
            $frequency = $this->primary_zone->patrol_frequency;
            if ($frequency > 0) {
                // Calculate required patrols based on team existence duration
                $daysSinceCreation = $this->created_at->diffInDays(now()) + 1;
                $requiredPatrols = ceil($daysSinceCreation * 24 / $frequency);
                
                // Calculate coverage percentage
                $patrolCoverage = min(100, ($this->patrols_completed / max(1, $requiredPatrols)) * 100);
            }
        }
        
        return [
            'response_time_avg' => $this->average_response_time,
            'response_efficiency' => round($responseEfficiency, 1),
            'incidents_resolved' => $this->incidents_resolved,
            'resolution_rate' => round($resolutionRate, 1),
            'patrols_completed' => $this->patrols_completed,
            'patrol_coverage' => round($patrolCoverage, 1),
            'performance_rating' => $this->performance_rating,
            'total_incidents_assigned' => $totalIncidents,
            'active_incidents' => $this->activeReports()->count(),
            'required_patrols' => $requiredPatrols
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get users who should be notified about team events.
     *
     * @return Collection
     */
    public function getNotificationRecipients(): Collection
    {
        // Always include active team members
        $recipients = $this->activeMembers;
        
        // Include primary zone manager if set
        if ($this->primaryZone && $this->primaryZone->zone_manager) {
            $zoneManager = User::where('name', $this->primaryZone->zone_manager)->first();
            if ($zoneManager) {
                $recipients->push($zoneManager);
            }
        }
        
        // Always include security director (assuming admin role)
        $securityDirector = User::where('role', 'admin')->first();
        if ($securityDirector) {
            $recipients->push($securityDirector);
        }
        
        // Remove duplicates
        return $recipients->unique('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Find available teams for dispatch based on zone and team type.
     *
     * @param CampusZone|null $zone Optional zone to prioritize teams for
     * @param string|null $teamType Optional team type to filter by
     * @return Collection
     */
    public static function findAvailableTeams(?CampusZone $zone = null, ?string $teamType = null): Collection
    {
        $query = self::where('active', true)
                     ->where('available_for_dispatch', true)
                     ->whereIn('status', self::AVAILABLE_STATUSES)
                     ->where(function($q) {
                         $q->whereNull('next_available_time')
                           ->orWhere('next_available_time', '<=', now());
                     });
        
        // Filter by team type if specified
        if ($teamType) {
            $query->where('team_type', $teamType);
        }
        
        // First get all available teams
        $teams = $query->get();
        
        // Then filter by current duty status (which may depend on shift schedules)
        $teams = $teams->filter(function($team) {
            return $team->isCurrentlyOnDuty();
        });
        
        // If zone specified, prioritize teams that cover this zone
        if ($zone) {
            // Split into teams that cover this zone and those that don't
            $zoneCoveringTeams = $teams->filter(function($team) use ($zone) {
                return $team->coversZone($zone);
            });
            
            $otherTeams = $teams->diff($zoneCoveringTeams);
            
            // Return zone-covering teams first, then others
            return $zoneCoveringTeams->merge($otherTeams);
        }
        
        return $teams;
    }

    /**
     * Find the most appropriate team for a given report.
     *
     * @param Report $report The report to find a team for
     * @return SecurityTeam|null
     */
    public static function findTeamForReport(Report $report): ?SecurityTeam
    {
        // Determine zone from report
        $zone = null;
        if ($report->zone_id) {
            $zone = CampusZone::find($report->zone_id);
        }
        
        // Determine required team type based on severity/category
        $teamType = 'patrol'; // Default
        
        if ($report->severity === 'high' || $report->priority_level === 'critical') {
            $teamType = 'response'; // Emergency response teams for high severity
        } elseif ($report->category_id) {
            // Check if category has specific team type requirement
            $category = $report->category;
            if ($category && $category->protocol) {
                $protocol = $category->protocol;
                $requiredTeams = $protocol->required_teams ?? [];
                
                if (!empty($requiredTeams) && is_array($requiredTeams)) {
                    $teamType = $requiredTeams[0]; // Use first required team type
                }
            }
        }
        
        // Find available teams with appropriate prioritization
        $teams = self::findAvailableTeams($zone, $teamType);
        
        // If no teams of required type, fall back to any available team
        if ($teams->isEmpty() && $teamType !== 'patrol') {
            $teams = self::findAvailableTeams($zone);
        }
        
        // Return the most appropriate team (first in prioritized list)
        return $teams->first();
    }

    /**
     * Get the color for team type display.
     *
     * @return string
     */
    public function getTeamTypeColor(): string
    {
        return match($this->team_type) {
            'patrol' => 'blue',
            'response' => 'red',
            'investigation' => 'purple',
            'surveillance' => 'gray',
            'escort' => 'green',
            'access_control' => 'yellow',
            'emergency' => 'red',
            'k9' => 'orange',
            'specialized' => 'indigo',
            default => 'blue'
        };
    }

    /**
     * Get the color for status display.
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'green',
            'off_duty' => 'gray',
            'training' => 'blue',
            'on_leave' => 'yellow',
            'responding' => 'red',
            'unavailable' => 'gray',
            default => 'gray'
        };
    }
}

