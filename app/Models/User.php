<?php

namespace App\Models;

use App\Events\UserStatusChanged;
use App\Events\UserRoleChanged;
use App\Services\AuditService;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\PushSubscription;

/**
 * Class User
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $avatar
 * @property string $role
 * @property string|null $phone
 * @property string|null $department
 * @property string|null $student_id
 * @property array|null $notification_prefs
 * @property float|null $last_location_lat
 * @property float|null $last_location_lng
 * @property int $safety_points
 * @property array|null $permissions
 * @property bool $is_active
 * @property string|null $status
 * @property string|null $badge_number
 * @property string|null $job_title
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $last_active_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'phone',
        'department',
        'student_id',
        'notification_prefs',
        'last_location_lat',
        'last_location_lng',
        'safety_points',
        'permissions',
        'is_active',
        'status',
        'badge_number',
        'job_title',
        'last_login_at',
        'last_login_ip',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        'notification_prefs' => 'array',
        'permissions' => 'array',
        'last_location_lat' => 'float',
        'last_location_lng' => 'float',
        'safety_points' => 'integer',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    /**
     * User roles available in the system
     * 
     * @var array<string>
     */
    public const ROLES = [
        'admin' => 'Administrator',
        'security' => 'Security Personnel',
        'staff' => 'Staff Member',
        'student' => 'Student',
        'guest' => 'Guest'
    ];

    /**
     * User status values
     * 
     * @var array<string>
     */
    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'on_leave' => 'On Leave',
        'on_duty' => 'On Duty',
        'off_duty' => 'Off Duty'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure new users have default values
        static::creating(function ($user) {
            // Set default role if not specified
            if (!$user->role) {
                $user->role = 'student';
            }
            
            // Set default active status
            if (!isset($user->is_active)) {
                $user->is_active = true;
            }
            
            // Set default status
            if (!$user->status) {
                $user->status = 'active';
            }
            
            // Set default safety points
            if (!$user->safety_points) {
                $user->safety_points = 0;
            }
        });

        // Log role changes
        static::updating(function ($user) {
            if ($user->isDirty('role')) {
                $oldRole = $user->getOriginal('role');
                $newRole = $user->role;
                
                // Fire event for role change
                event(new UserRoleChanged($user, $oldRole, $newRole));
                
                // Log action
                AuditService::logAction(
                    'user_role_changed',
                    'user',
                    $user->id,
                    [
                        'old_role' => $oldRole,
                        'new_role' => $newRole,
                        'changed_by' => Auth::id() ?? 'system'
                    ]
                );
            }

            // Log status changes
            if ($user->isDirty('status') || $user->isDirty('is_active')) {
                $oldStatus = $user->getOriginal('status');
                $newStatus = $user->status;
                $oldActive = $user->getOriginal('is_active');
                $newActive = $user->is_active;
                
                // Fire event for status change
                event(new UserStatusChanged($user, $oldStatus, $newStatus, $oldActive, $newActive));
                
                // Log action
                AuditService::logAction(
                    'user_status_changed',
                    'user',
                    $user->id,
                    [
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'old_active' => $oldActive,
                        'new_active' => $newActive,
                        'changed_by' => Auth::id() ?? 'system'
                    ]
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all subscriptions for this user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all badges earned by this user.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)->withTimestamps();
    }

    /**
     * Get all reports submitted by this user.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get all emergency responses by this user.
     */
    public function emergencyResponses(): HasMany
    {
        return $this->hasMany(EmergencyResponse::class, 'responder_id');
    }

    /**
     * Get the notification preferences for this user.
     */
    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get all comments made by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ReportComment::class);
    }

    /**
     * Get all security teams this user belongs to.
     */
    public function securityTeams(): BelongsToMany
    {
        return $this->belongsToMany(SecurityTeam::class, 'security_team_members')
                   ->withPivot('role', 'joined_at', 'badge_number', 'skills', 'active')
                   ->withTimestamps();
    }

    /**
     * Get active security teams this user belongs to.
     */
    public function activeSecurityTeams(): BelongsToMany
    {
        return $this->belongsToMany(SecurityTeam::class, 'security_team_members')
                   ->wherePivot('active', true)
                   ->withPivot('role', 'joined_at', 'badge_number', 'skills')
                   ->withTimestamps();
    }

    /**
     * Get all teams where this user is the leader.
     */
    public function leadingTeams(): HasMany
    {
        return $this->hasMany(SecurityTeam::class, 'leader_id');
    }

    /**
     * Get reports assigned to this user.
     */
    public function assignedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'assigned_user_id');
    }

    /**
     * Get all campus zones this user is subscribed to.
     */
    public function subscribedZones(): MorphToMany
    {
        return $this->morphedByMany(CampusZone::class, 'subscriptable', 'subscriptions');
    }

    /**
     * Get all audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get push notification subscriptions for this user.
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Get specific area subscriptions for this user.
     */
    public function areaSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('type', 'area');
    }

    /*
    |--------------------------------------------------------------------------
    | Role and Permission Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user has the specified role(s).
     *
     * @param string|array $role Role or roles to check
     * @return bool
     */
    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    /**
     * Check if user is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is security personnel.
     *
     * @return bool
     */
    public function isSecurity(): bool
    {
        return $this->role === 'security';
    }

    /**
     * Check if user is staff.
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is a student.
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permission Permission to check
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Admins have all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check user-specific permissions
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Assign a permission to the user.
     *
     * @param string $permission Permission to assign
     * @return bool
     */
    public function grantPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            
            $result = $this->save();
            
            // Log action
            AuditService::logAction(
                'permission_granted',
                'user',
                $this->id,
                [
                    'permission' => $permission,
                    'granted_by' => Auth::id() ?? 'system'
                ]
            );
            
            return $result;
        }
        
        return true;
    }

    /**
     * Remove a permission from the user.
     *
     * @param string $permission Permission to remove
     * @return bool
     */
    public function revokePermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        if (($key = array_search($permission, $permissions)) !== false) {
            unset($permissions[$key]);
            $this->permissions = array_values($permissions); // Re-index array
            
            $result = $this->save();
            
            // Log action
            AuditService::logAction(
                'permission_revoked',
                'user',
                $this->id,
                [
                    'permission' => $permission,
                    'revoked_by' => Auth::id() ?? 'system'
                ]
            );
            
            return $result;
        }
        
        return true;
    }

    /**
     * Change the user's role.
     *
     * @param string $role New role
     * @param string|null $reason Reason for role change
     * @return bool
     */
    public function changeRole(string $role, ?string $reason = null): bool
    {
        if (!array_key_exists($role, self::ROLES)) {
            return false;
        }
        
        $oldRole = $this->role;
        $this->role = $role;
        
        $result = $this->save();
        
        // Log action with reason
        if ($result && $oldRole !== $role) {
            AuditService::logAction(
                'role_changed',
                'user',
                $this->id,
                [
                    'old_role' => $oldRole,
                    'new_role' => $role,
                    'reason' => $reason,
                    'changed_by' => Auth::id() ?? 'system'
                ]
            );
        }
        
        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Status and Activity Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status !== 'suspended';
    }

    /**
     * Set the user's status.
     *
     * @param string $status New status
     * @param string|null $reason Reason for status change
     * @return bool
     */
    public function setStatus(string $status, ?string $reason = null): bool
    {
        if (!array_key_exists($status, self::STATUSES)) {
            return false;
        }
        
        $oldStatus = $this->status;
        $this->status = $status;
        
        // Automatically update is_active based on status
        if ($status === 'suspended' || $status === 'inactive') {
            $this->is_active = false;
        } elseif ($status === 'active' || $status === 'on_duty') {
            $this->is_active = true;
        }
        
        $result = $this->save();
        
        // Log action with reason
        if ($result && $oldStatus !== $status) {
            AuditService::logAction(
                'status_changed',
                'user',
                $this->id,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'reason' => $reason,
                    'changed_by' => Auth::id() ?? 'system'
                ]
            );
        }
        
        return $result;
    }

    /**
     * Activate a user account.
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->is_active = true;
        $this->status = 'active';
        
        $result = $this->save();
        
        // Log action
        if ($result) {
            AuditService::logAction(
                'user_activated',
                'user',
                $this->id,
                [
                    'activated_by' => Auth::id() ?? 'system'
                ]
            );
        }
        
        return $result;
    }

    /**
     * Deactivate a user account.
     *
     * @param string|null $reason Reason for deactivation
     * @return bool
     */
    public function deactivate(?string $reason = null): bool
    {
        $this->is_active = false;
        $this->status = 'inactive';
        
        $result = $this->save();
        
        // Log action
        if ($result) {
            AuditService::logAction(
                'user_deactivated',
                'user',
                $this->id,
                [
                    'reason' => $reason,
                    'deactivated_by' => Auth::id() ?? 'system'
                ]
            );
        }
        
        return $result;
    }

    /**
     * Suspend a user account.
     *
     * @param string|null $reason Reason for suspension
     * @return bool
     */
    public function suspend(?string $reason = null): bool
    {
        $this->is_active = false;
        $this->status = 'suspended';
        
        $result = $this->save();
        
        // Log action
        if ($result) {
            AuditService::logAction(
                'user_suspended',
                'user',
                $this->id,
                [
                    'reason' => $reason,
                    'suspended_by' => Auth::id() ?? 'system'
                ]
            );
        }
        
        return $result;
    }

    /**
     * Track user login.
     *
     * @param string|null $ip IP address of login
     * @return void
     */
    public function trackLogin(?string $ip = null): void
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ip;
        $this->save();
        
        // Log login activity
        AuditService::logAction(
            'user_login',
            'user',
            $this->id,
            [
                'ip_address' => $ip,
                'user_agent' => request()->userAgent()
            ]
        );
    }

    /**
     * Update user's last known location.
     *
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function updateLocation(float $latitude, float $longitude): bool
    {
        $this->last_location_lat = $latitude;
        $this->last_location_lng = $longitude;
        
        return $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Zone Subscription Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Subscribe to a campus zone.
     *
     * @param CampusZone $zone Zone to subscribe to
     * @return bool
     */
    public function subscribeToZone(CampusZone $zone): bool
    {
        if (!$this->isSubscribedToZone($zone)) {
            $this->subscribedZones()->attach($zone->id);
            
            // Log action
            AuditService::logAction(
                'zone_subscribed',
                'user',
                $this->id,
                [
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->name
                ]
            );
            
            return true;
        }
        
        return false;
    }

    /**
     * Unsubscribe from a campus zone.
     *
     * @param CampusZone $zone Zone to unsubscribe from
     * @return bool
     */
    public function unsubscribeFromZone(CampusZone $zone): bool
    {
        if ($this->isSubscribedToZone($zone)) {
            $this->subscribedZones()->detach($zone->id);
            
            // Log action
            AuditService::logAction(
                'zone_unsubscribed',
                'user',
                $this->id,
                [
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->name
                ]
            );
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if user is subscribed to a zone.
     *
     * @param CampusZone $zone Zone to check
     * @return bool
     */
    public function isSubscribedToZone(CampusZone $zone): bool
    {
        return $this->subscribedZones()->where('id', $zone->id)->exists();
    }

    /**
     * Get all zones that should notify this user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNotificationZones()
    {
        // Include directly subscribed zones
        $zones = $this->subscribedZones;
        
        // For security personnel, also include zones they're responsible for
        if ($this->isSecurity()) {
            // Add primary zones from teams
            foreach ($this->activeSecurityTeams as $team) {
                if ($team->primaryZone && !$zones->contains($team->primaryZone)) {
                    $zones->push($team->primaryZone);
                }
                
                // Add secondary zones from teams
                if ($team->secondary_zones) {
                    $secondaryZones = CampusZone::whereIn('id', $team->secondary_zones)->get();
                    $zones = $zones->merge($secondaryZones);
                }
            }
            
            // Remove duplicates
            $zones = $zones->unique('id');
        }
        
        return $zones;
    }

    /*
    |--------------------------------------------------------------------------
    | Security Team Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Join a security team.
     *
     * @param SecurityTeam $team Team to join
     * @param string $role Role in the team
     * @param array $attributes Additional attributes
     * @return bool
     */
    public function joinTeam(SecurityTeam $team, string $role = 'member', array $attributes = []): bool
    {
        return $team->addMember($this, $role, $attributes);
    }

    /**
     * Leave a security team.
     *
     * @param SecurityTeam $team Team to leave
     * @param bool $softRemove Whether to soft-remove (mark as inactive)
     * @return bool
     */
    public function leaveTeam(SecurityTeam $team, bool $softRemove = true): bool
    {
        return $team->removeMember($this, $softRemove);
    }

    /**
     * Check if user is a member of a security team.
     *
     * @param SecurityTeam $team Team to check
     * @param bool $activeOnly Check only active memberships
     * @return bool
     */
    public function isTeamMember(SecurityTeam $team, bool $activeOnly = true): bool
    {
        $query = $this->securityTeams()->where('security_team_id', $team->id);
        
        if ($activeOnly) {
            $query->wherePivot('active', true);
        }
        
        return $query->exists();
    }

    /**
     * Check if user is the leader of a security team.
     *
     * @param SecurityTeam $team Team to check
     * @return bool
     */
    public function isTeamLeader(SecurityTeam $team): bool
    {
        return $team->leader_id === $this->id;
    }

    /**
     * Get user's role in a specific team.
     *
     * @param SecurityTeam $team Team to check
     * @return string|null Role or null if not a member
     */
    public function getTeamRole(SecurityTeam $team): ?string
    {
        $membership = $this->securityTeams()
                          ->where('security_team_id', $team->id)
                          ->first();
        
        return $membership ? $membership->pivot->role : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the notification preferences attribute.
     *
     * @param mixed $value
     * @return array
     */
    public function getNotificationPrefsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [
            'email' => true,
            'push' => true,
            'area' => true,
            'sms' => false,
            'high_priority_only' => false,
            'quiet_hours' => false,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ];
    }

    /**
     * Set the notification preferences attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setNotificationPrefsAttribute($value): void
    {
        $this->attributes['notification_prefs'] = json_encode($value);
    }

    /**
     * Route notifications for the WebPush channel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function routeNotificationForWebPush()
    {
        return $this->pushSubscriptions();
    }

    /**
     * Check if user can receive notifications.
     *
     * @param string $channel Channel to check (email, push, sms, area)
     * @param string $severity Severity of the notification
     * @return bool
     */
    public function canReceiveNotification(string $channel, string $severity = 'medium'): bool
    {
        $prefs = $this->notification_prefs;
        
        // Check if notifications are enabled for this channel
        if (!isset($prefs[$channel]) || !$prefs[$channel]) {
            return false;
        }
        
        // Check if user only wants high priority notifications
        if (isset($prefs['high_priority_only']) && $prefs['high_priority_only']) {
            if ($severity !== 'high' && $severity !== 'critical') {
                return false;
            }
        }
        
        // Check quiet hours
        if (isset($prefs['quiet_hours']) && $prefs['quiet_hours']) {
            $now = now()->format('H:i');
            $start = $prefs['quiet_hours_start'] ?? '22:00';
            $end = $prefs['quiet_hours_end'] ?? '07:00';
            
            // Check if current time is within quiet hours
            if ($start < $end) {
                // Normal range (e.g., 22:00 to 07:00)
                if ($now >= $start && $now <= $end) {
                    // Only allow critical notifications during quiet hours
                    return $severity === 'critical';
                }
            } else {
                // Wrapped range (e.g., 22:00 to 07:00)
                if ($now >= $start || $now <= $end) {
                    // Only allow critical notifications during quiet hours
                    return $severity === 'critical';
                }
            }
        }
        
        return true;
    }

    /**
     * Update notification preferences.
     *
     * @param array $preferences New preferences
     * @return bool
     */
    public function updateNotificationPreferences(array $preferences): bool
    {
        $currentPrefs = $this->notification_prefs;
        $this->notification_prefs = array_merge($currentPrefs, $preferences);
        
        return $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Report Handling Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get reports handled by this user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHandledReports()
    {
        return $this->assignedReports;
    }

    /**
     * Get active reports assigned to this user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveReports()
    {
        return $this->assignedReports()
                   ->whereIn('status', Report::ACTIVE_STATUSES)
                   ->get();
    }

    /**
     * Assign a report to this user.
     *
     * @param Report $report Report to assign
     * @param string|null $notes Assignment notes
     * @return bool
     */
    public function assignReport(Report $report, ?string $notes = null): bool
    {
        return $report->assignTo(null, $this->id, $notes);
    }

    /**
     * Check if user can handle a specific report.
     *
     * @param Report $report Report to check
     * @return bool
     */
    public function canHandleReport(Report $report): bool
    {
        // Admins can handle any report
        if ($this->isAdmin()) {
            return true;
        }
        
        // Security personnel can handle reports
        if (!$this->isSecurity()) {
            return false;
        }
        
        // Check if user is assigned to the report
        if ($report->assigned_user_id === $this->id) {
            return true;
        }
        
        // Check if user is on a team assigned to the report
        if ($report->assigned_team_id) {
            return $this->isTeamMember(SecurityTeam::find($report->assigned_team_id));
        }
        
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Performance and Gamification Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Earn safety points.
     *
     * @param int $points Points to earn
     * @return self
     */
    public function earnPoints(int $points): self
    {
        $this->increment('safety_points', $points);
        return $this->fresh();
    }

    /**
     * Get the points history attribute.
     *
     * @return array
     */
    public function getPointsHistoryAttribute(): array
    {
        return [
            'reports' => $this->reports()->count() * 10,
            'responses' => $this->emergencyResponses()->count() * 15,
            'verified_reports' => $this->reports()->where('verified', true)->count() * 20,
            'badges' => $this->badges()->count() * 50,
            'patrols' => $this->getPatrolCount() * 5,
            'resolved_reports' => $this->assignedReports()->whereIn('status', Report::CLOSED_STATUSES)->count() * 15
        ];
    }

    /**
     * Get user's performance metrics.
     *
     * @return array
     */
    public function getPerformanceMetrics(): array
    {
        // Only relevant for security personnel
        if (!$this->isSecurity() && !$this->isAdmin()) {
            return [];
        }
        
        // Reports handled
        $assignedReports = $this->assignedReports;
        $totalAssigned = $assignedReports->count();
        $resolvedReports = $assignedReports->whereIn('status', Report::CLOSED_STATUSES)->count();
        
        // Resolution rate
        $resolutionRate = $totalAssigned > 0 ? ($resolvedReports / $totalAssigned) * 100 : 0;
        
        // Average response time
        $responseTimes = [];
        foreach ($assignedReports->whereNotNull('response_time') as $report) {
            $responseTimes[] = $report->getResponseTimeInMinutes();
        }
        $avgResponseTime = count($responseTimes) > 0 ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        // Average resolution time
        $resolutionTimes = [];
        foreach ($assignedReports->whereNotNull('resolution_time') as $report) {
            $resolutionTimes[] = $report->getResolutionTimeInMinutes();
        }
        $avgResolutionTime = count($resolutionTimes) > 0 ? array_sum($resolutionTimes) / count($resolutionTimes) : 0;
        
        return [
            'total_assigned' => $totalAssigned,
            'resolved_reports' => $resolvedReports,
            'resolution_rate' => round($resolutionRate, 1),
            'avg_response_time' => round($avgResponseTime),
            'avg_resolution_time' => round($avgResolutionTime),
            'patrol_count' => $this->getPatrolCount(),
            'emergency_responses' => $this->emergencyResponses()->count(),
            'teams_count' => $this->activeSecurityTeams()->count(),
            'performance_rating' => $this->calculatePerformanceRating()
        ];
    }

    /**
     * Get count of patrols completed by this user.
     *
     * @return int
     */
    protected function getPatrolCount(): int
    {
        // Count from audit logs where action is 'zone_patrolled'
        return AuditLog::where('user_id', $this->id)
                    ->where('action', 'zone_patrolled')
                    ->count();
    }

    /**
     * Calculate performance rating based on metrics.
     *
     * @return float Rating from 1-5
     */
    protected function calculatePerformanceRating(): float
    {
        // Only relevant for security personnel
        if (!$this->isSecurity() && !$this->isAdmin()) {
            return 0;
        }
        
        $metrics = [
            'resolution_rate' => 0,
            'response_time' => 0,
            'patrol_activity' => 0,
            'team_participation' => 0
        ];
        
        // Resolution rate score (0-5)
        $assignedReports = $this->assignedReports;
        $totalAssigned = $assignedReports->count();
        $resolvedReports = $assignedReports->whereIn('status', Report::CLOSED_STATUSES)->count();
        $resolutionRate = $totalAssigned > 0 ? ($resolvedReports / $totalAssigned) * 100 : 0;
        
        if ($totalAssigned >= 5) {
            if ($resolutionRate >= 90) $metrics['resolution_rate'] = 5;
            elseif ($resolutionRate >= 80) $metrics['resolution_rate'] = 4;
            elseif ($resolutionRate >= 70) $metrics['resolution_rate'] = 3;
            elseif ($resolutionRate >= 60) $metrics['resolution_rate'] = 2;
            elseif ($resolutionRate >= 50) $metrics['resolution_rate'] = 1;
        }
        
        // Response time score (0-5, lower is better)
        $responseTimes = [];
        foreach ($assignedReports->whereNotNull('response_time') as $report) {
            $responseTimes[] = $report->getResponseTimeInMinutes();
        }
        $avgResponseTime = count($responseTimes) > 0 ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        if (count($responseTimes) >= 5) {
            if ($avgResponseTime <= 5) $metrics['response_time'] = 5;
            elseif ($avgResponseTime <= 10) $metrics['response_time'] = 4;
            elseif ($avgResponseTime <= 15) $metrics['response_time'] = 3;
            elseif ($avgResponseTime <= 20) $metrics['response_time'] = 2;
            elseif ($avgResponseTime <= 30) $metrics['response_time'] = 1;
        }
        
        // Patrol activity score (0-5)
        $patrolCount = $this->getPatrolCount();
        
        if ($patrolCount >= 20) $metrics['patrol_activity'] = 5;
        elseif ($patrolCount >= 15) $metrics['patrol_activity'] = 4;
        elseif ($patrolCount >= 10) $metrics['patrol_activity'] = 3;
        elseif ($patrolCount >= 5) $metrics['patrol_activity'] = 2;
        elseif ($patrolCount >= 1) $metrics['patrol_activity'] = 1;
        
        // Team participation score (0-5)
        $teamsCount = $this->activeSecurityTeams()->count();
        $isLeader = $this->leadingTeams()->count() > 0;
        
        if ($isLeader && $teamsCount >= 1) $metrics['team_participation'] = 5;
        elseif ($teamsCount >= 2) $metrics['team_participation'] = 4;
        elseif ($teamsCount >= 1) $metrics['team_participation'] = 3;
        
        // Calculate average rating
        $sum = array_sum($metrics);
        $count = count(array_filter($metrics));
        
        return $count > 0 ? round($sum / $count, 1) : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication and Access Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Change user's password.
     *
     * @param string $newPassword New password
     * @return bool
     */
    public function changePassword(string $newPassword): bool
    {
        $this->password = Hash::make($newPassword);
        
        $result = $this->save();
        
        // Log action (but not the password itself)
        if ($result) {
            AuditService::logAction(
                'password_changed',
                'user',
                $this->id,
                [
                    'changed_by' => Auth::id() ?? $this->id
                ]
            );
        }
        
        return $result;
    }

    /**
     * Check if user's account needs a security review.
     *
     * @return bool
     */
    public function needsSecurityReview(): bool
    {
        // Check for suspicious activity
        $suspiciousLogins = $this->auditLogs()
                                ->where('action', 'user_login')
                                ->whereJsonContains('details->suspicious', true)
                                ->exists();
        
        if ($suspiciousLogins) {
            return true;
        }
        
        // Check if user has admin or security role but no recent activity
        if (($this->isAdmin() || $this->isSecurity()) && $this->last_login_at) {
            $daysInactive = now()->diffInDays($this->last_login_at);
            if ($daysInactive > 30) {
                return true;
            }
        }
        
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Attribute Accessors/Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get the avatar URL attribute.
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar) 
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D8ABC&color=fff';
    }

    /**
     * Get the full name with role attribute.
     *
     * @return string
     */
    public function getFullNameWithRoleAttribute(): string
    {
        return $this->name . ' (' . (self::ROLES[$this->role] ?? 'User') . ')';
    }

    /**
     * Get the badge display name attribute.
     *
     * @return string|null
     */
    public function getBadgeNameAttribute(): ?string
    {
        return $this->badge_number ? "#{$this->badge_number}" : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to get responders (security personnel and admins).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResponders($query)
    {
        return $query->whereIn('role', ['security', 'admin']);
    }

    /**
     * Scope to get active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $roles
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRole($query, $roles)
    {
        if (is_array($roles)) {
            return $query->whereIn('role', $roles);
        }
        
        return $query->where('role', $roles);
    }

    /**
     * Scope to get users on duty.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnDuty($query)
    {
        return $query->where('status', 'on_duty');
    }

    /**
     * Update the user's last active timestamp
     */
    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }
}
