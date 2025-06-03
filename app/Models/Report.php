<?php
namespace App\Models;

use App\Events\ReportStatusChanged;
use App\Events\ReportAssigned;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class Report
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property int $user_id
 * @property string $campus
 * @property string $description
 * @property string $location
 * @property string $severity
 * @property bool $anonymous
 * @property bool $is_public
 * @property string $status
 * @property string|null $media_path
 * @property Carbon|null $response_time
 * @property Carbon|null $resolution_time
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property string $priority_level
 * @property int|null $assigned_team_id
 * @property int|null $assigned_user_id
 * @property string|null $resolution_notes
 * @property int $witness_count
 * @property array|null $evidence_files
 * @property array|null $location_meta
 * @property array|null $status_history
 * @property Carbon|null $incident_date
 * @property bool $is_panic
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 * @property User|null $assignedUser
 * @property IncidentCategory|null $category
 * @property IncidentCategory|null $subCategory
 * @property SecurityTeam|null $assignedTeam
 * @property CampusZone|null $zone
 * @property ReportComment[] $comments
 * @property ChatMessage[] $chatMessages
 */
class Report extends Model {
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id', 'campus', 'description', 'location', 'severity', 'anonymous', 'is_public',
        'status', 'media_path', 'response_time', 'resolution_time', 'category_id', 
        'sub_category_id', 'priority_level', 'assigned_team_id', 'assigned_user_id', 
        'resolution_notes', 'witness_count', 'evidence_files', 'location_meta', 
        'status_history', 'incident_date', 'is_panic', 'latitude', 'longitude',
        'tracking_code', 'contact_info', 'evidence'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'anonymous' => 'boolean',
        'is_public' => 'boolean',
        'evidence_files' => 'array',
        'location_meta' => 'array',
        'status_history' => 'array',
        'contact_info' => 'array',
        'evidence' => 'array',
        'response_time' => 'datetime',
        'resolution_time' => 'datetime',
        'incident_date' => 'datetime',
        'witness_count' => 'integer',
        'is_panic' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    /**
     * The status values that determine if a report is considered active.
     *
     * @var array<string>
     */
    public const ACTIVE_STATUSES = ['open', 'in_progress'];

    /**
     * The status values that determine if a report is considered closed.
     *
     * @var array<string>
     */
    public const CLOSED_STATUSES = ['resolved', 'closed', 'duplicate', 'invalid'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            // If no user_id is set, use the anonymous user
            if (!$report->user_id) {
                $report->user_id = 1;
            }

            // Set incident date to now if not provided
            if (!$report->incident_date) {
                $report->incident_date = now();
            }

            // Initialize status history
            $report->status_history = [
                [
                    'status' => $report->status ?? 'open',
                    'user_id' => Auth::id() ?? 1,
                    'timestamp' => now()->toIso8601String(),
                    'notes' => 'Initial report created'
                ]
            ];

            // Auto-determine priority level based on severity if not set
            if (!$report->priority_level) {
                $report->priority_level = match($report->severity) {
                    'high' => 'high',
                    'medium' => 'medium',
                    'low' => 'low',
                    default => 'medium'
                };
            }
        });

        static::updating(function ($report) {
            // Track status changes in status_history
            if ($report->isDirty('status')) {
                $history = $report->status_history ?? [];
                $history[] = [
                    'status' => $report->status,
                    'user_id' => Auth::id() ?? 1,
                    'timestamp' => now()->toIso8601String(),
                    'notes' => 'Status updated'
                ];
                $report->status_history = $history;
                
                // Set resolution_time if status changed to resolved
                if ($report->status === 'resolved' && !$report->resolution_time) {
                    $report->resolution_time = now();
                }
                
                // Fire status changed event
                event(new ReportStatusChanged($report));
            }

            // Track assignment changes
            if ($report->isDirty('assigned_team_id') || $report->isDirty('assigned_user_id')) {
                event(new ReportAssigned($report));
                
                // Set response_time on first assignment if not already set
                if (!$report->response_time) {
                    $report->response_time = now();
                }
            }

            // Log the audit record
            try {
                AuditService::logModelChange('report', $report->id, $report->getDirty());
            } catch (\Exception $e) {
                Log::error("Failed to log audit for report update: " . $e->getMessage());
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who reported the incident.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to this report.
     */
    public function assignedUser() {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get the incident category.
     */
    public function category() {
        return $this->belongsTo(IncidentCategory::class);
    }

    /**
     * Get the incident sub-category.
     */
    public function subCategory() {
        return $this->belongsTo(IncidentCategory::class, 'sub_category_id');
    }

    /**
     * Get the security team assigned to this report.
     */
    public function assignedTeam() {
        return $this->belongsTo(SecurityTeam::class, 'assigned_team_id');
    }

    /**
     * Get the campus zone where the incident occurred.
     */
    public function zone() {
        return $this->belongsTo(CampusZone::class, 'zone_id');
    }

    /**
     * Get the comments for this report.
     */
    public function comments() {
        return $this->hasMany(ReportComment::class);
    }

    /**
     * Get the chat messages for this report.
     */
    public function chatMessages() {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get the emergency responses for this report.
     */
    public function emergencyResponses() {
        return $this->hasMany(EmergencyResponse::class);
    }

    /**
     * Get the audit logs for this report.
     */
    public function auditLogs() {
        return AuditLog::where('subject_type', 'report')
            ->where('subject_id', $this->id)
            ->orderBy('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Timeline & Activity Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get all timeline events for this report in chronological order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function timelineEvents() {
        $events = collect();
        
        // Add comments
        foreach ($this->comments as $comment) {
            $events->push([
                'type' => 'comment',
                'user' => $comment->user->name ?? 'Unknown',
                'user_id' => $comment->user_id,
                'message' => $comment->body,
                'created_at' => $comment->created_at,
                'id' => $comment->id
            ]);
        }
        
        // Add chat messages
        foreach ($this->chatMessages as $msg) {
            $events->push([
                'type' => 'chat',
                'user' => $msg->user->name ?? 'Unknown',
                'user_id' => $msg->user_id,
                'message' => $msg->message,
                'created_at' => $msg->created_at,
                'id' => $msg->id
            ]);
        }
        
        // Add status changes from history
        if ($this->status_history && is_array($this->status_history)) {
            foreach ($this->status_history as $index => $status) {
                $user = User::find($status['user_id'] ?? 1);
                $events->push([
                    'type' => 'status',
                    'user' => $user ? $user->name : 'System',
                    'user_id' => $status['user_id'] ?? 1,
                    'message' => 'Status changed to ' . ($status['status'] ?? 'unknown'),
                    'notes' => $status['notes'] ?? null,
                    'created_at' => Carbon::parse($status['timestamp'] ?? now()),
                    'id' => 'status-' . $index
                ]);
            }
        }
        
        // Add assignment events
        if ($this->response_time) {
            $events->push([
                'type' => 'assignment',
                'user' => 'System',
                'message' => $this->assignedTeam 
                    ? 'Assigned to team: ' . $this->assignedTeam->name
                    : 'Assigned for response',
                'created_at' => $this->response_time,
                'id' => 'assignment-1'
            ]);
        }
        
        // Add resolution event
        if ($this->resolution_time) {
            $events->push([
                'type' => 'resolution',
                'user' => $this->assignedUser ? $this->assignedUser->name : 'System',
                'user_id' => $this->assigned_user_id,
                'message' => 'Report resolved',
                'notes' => $this->resolution_notes,
                'created_at' => $this->resolution_time,
                'id' => 'resolution-1'
            ]);
        }
        
        // Sort by created_at
        return $events->sortBy('created_at')->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Status Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the report is active (open or in progress).
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }

    /**
     * Check if the report is closed (resolved or other closed states).
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES);
    }

    /**
     * Check if the report requires immediate attention based on severity and status.
     *
     * @return bool
     */
    public function needsImmediateAttention(): bool
    {
        return $this->isActive() && 
               ($this->severity === 'high' || $this->priority_level === 'critical') &&
               !$this->assigned_team_id;
    }

    /**
     * Update the report status with auditing.
     *
     * @param string $status The new status
     * @param string|null $notes Optional notes about the status change
     * @return bool
     */
    public function updateStatus(string $status, ?string $notes = null): bool
    {
        $oldStatus = $this->status;
        $this->status = $status;
        
        // Add to status history
        $history = $this->status_history ?? [];
        $history[] = [
            'status' => $status,
            'user_id' => Auth::id() ?? 1,
            'timestamp' => now()->toIso8601String(),
            'notes' => $notes ?? 'Status updated'
        ];
        $this->status_history = $history;
        
        // Set resolution time if resolving
        if ($status === 'resolved' && !$this->resolution_time) {
            $this->resolution_time = now();
        }
        
        $success = $this->save();
        
        // Log the audit record
        AuditService::logAction(
            'update_status',
            'report',
            $this->id,
            ['old_status' => $oldStatus, 'new_status' => $status, 'notes' => $notes]
        );
        
        return $success;
    }

    /**
     * Assign the report to a security team and/or user.
     *
     * @param int|null $teamId The security team ID
     * @param int|null $userId The user ID
     * @param string|null $notes Assignment notes
     * @return bool
     */
    public function assignTo(?int $teamId = null, ?int $userId = null, ?string $notes = null): bool
    {
        $changed = false;
        
        if ($teamId !== null && $this->assigned_team_id !== $teamId) {
            $this->assigned_team_id = $teamId;
            $changed = true;
        }
        
        if ($userId !== null && $this->assigned_user_id !== $userId) {
            $this->assigned_user_id = $userId;
            $changed = true;
        }
        
        if ($changed) {
            // Set response time if first assignment
            if (!$this->response_time) {
                $this->response_time = now();
            }
            
            // Update status if currently open
            if ($this->status === 'open') {
                $this->status = 'in_progress';
                
                // Add to status history
                $history = $this->status_history ?? [];
                $history[] = [
                    'status' => 'in_progress',
                    'user_id' => Auth::id() ?? 1,
                    'timestamp' => now()->toIso8601String(),
                    'notes' => $notes ?? 'Automatically updated to in progress upon assignment'
                ];
                $this->status_history = $history;
            }
            
            $success = $this->save();
            
            // Log the audit record
            AuditService::logAction(
                'assign_report',
                'report',
                $this->id,
                [
                    'team_id' => $teamId,
                    'user_id' => $userId,
                    'notes' => $notes
                ]
            );
            
            return $success;
        }
        
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Location Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get latitude and longitude from the location string.
     *
     * @return array|null Array with 'lat' and 'lng' keys or null if invalid
     */
    public function getLatLng(): ?array
    {
        // First check if we have structured location data
        if (!empty($this->location_meta) && isset($this->location_meta['latitude']) && isset($this->location_meta['longitude'])) {
            return [
                'lat' => (float) $this->location_meta['latitude'],
                'lng' => (float) $this->location_meta['longitude']
            ];
        }
        
        // Fall back to parsing location string
        if (!$this->location || !str_contains($this->location, ',')) {
            return null;
        }
        
        $parts = explode(',', $this->location);
        if (count($parts) !== 2) {
            return null;
        }
        
        return [
            'lat' => (float) trim($parts[0]),
            'lng' => (float) trim($parts[1])
        ];
    }

    /**
     * Get the full location information including coordinates and metadata.
     *
     * @return array
     */
    public function getLocationData(): array
    {
        $result = [
            'raw' => $this->location,
            'coordinates' => $this->getLatLng(),
            'campus' => $this->campus,
        ];
        
        // Add structured location metadata if available
        if (!empty($this->location_meta)) {
            $result = array_merge($result, $this->location_meta);
        }
        
        // Add zone information if available
        if ($this->zone) {
            $result['zone'] = [
                'id' => $this->zone->id,
                'name' => $this->zone->name,
                'code' => $this->zone->code,
                'type' => $this->zone->zone_type,
            ];
        }
        
        return $result;
    }

    /**
     * Set structured location data.
     *
     * @param float $latitude
     * @param float $longitude
     * @param array $metadata Additional location metadata
     * @return void
     */
    public function setLocationData(float $latitude, float $longitude, array $metadata = []): void
    {
        // Update the location string
        $this->location = "$latitude,$longitude";
        
        // Create/update the location_meta
        $this->location_meta = array_merge($metadata, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $metadata['accuracy'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
        
        // Try to determine zone based on coordinates
        $this->determineZone();
        
        $this->save();
    }

    /**
     * Determine which campus zone the report belongs to based on coordinates.
     *
     * @return void
     */
    protected function determineZone(): void
    {
        $coords = $this->getLatLng();
        if (!$coords) {
            return;
        }
        
        // Find the nearest zone
        $zone = CampusZone::findNearestZone($coords['lat'], $coords['lng']);
        if ($zone) {
            $this->zone_id = $zone->id;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Response Time Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the response time in minutes.
     *
     * @return int|null
     */
    public function getResponseTimeInMinutes(): ?int
    {
        if (!$this->response_time) {
            return null;
        }
        
        $created = $this->incident_date ?? $this->created_at;
        return $created->diffInMinutes($this->response_time);
    }

    /**
     * Calculate the resolution time in minutes.
     *
     * @return int|null
     */
    public function getResolutionTimeInMinutes(): ?int
    {
        if (!$this->resolution_time) {
            return null;
        }
        
        $created = $this->incident_date ?? $this->created_at;
        return $created->diffInMinutes($this->resolution_time);
    }

    /**
     * Get the target response time based on category and severity.
     *
     * @return int Minutes
     */
    public function getTargetResponseTime(): int
    {
        // If there's a category with a protocol, use that
        if ($this->category && $this->category->protocol) {
            return $this->category->protocol->target_response_time;
        }
        
        // Otherwise use default times based on severity
        return match($this->severity) {
            'high' => 5,
            'medium' => 15,
            'low' => 30,
            default => 15
        };
    }

    /**
     * Check if the report is overdue for response.
     *
     * @return bool
     */
    public function isResponseOverdue(): bool
    {
        if ($this->response_time || !$this->isActive()) {
            return false;
        }
        
        $targetMinutes = $this->getTargetResponseTime();
        $created = $this->incident_date ?? $this->created_at;
        
        return $created->diffInMinutes(now()) > $targetMinutes;
    }

    /*
    |--------------------------------------------------------------------------
    | Evidence Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add evidence files to the report.
     *
     * @param array $files Array of file paths or file data
     * @return void
     */
    public function addEvidence(array $files): void
    {
        $evidence = $this->evidence_files ?? [];
        
        foreach ($files as $file) {
            $evidence[] = [
                'path' => is_array($file) ? $file['path'] : $file,
                'type' => is_array($file) ? ($file['type'] ?? 'unknown') : 'file',
                'added_by' => Auth::id() ?? 1,
                'added_at' => now()->toIso8601String(),
                'description' => is_array($file) ? ($file['description'] ?? null) : null,
            ];
        }
        
        $this->evidence_files = $evidence;
        $this->save();
        
        // Log the audit record
        AuditService::logAction(
            'add_evidence',
            'report',
            $this->id,
            ['files_count' => count($files)]
        );
    }

    /**
     * Remove an evidence file by index.
     *
     * @param int $index
     * @return bool
     */
    public function removeEvidence(int $index): bool
    {
        $evidence = $this->evidence_files ?? [];
        
        if (isset($evidence[$index])) {
            $removed = $evidence[$index];
            unset($evidence[$index]);
            $this->evidence_files = array_values($evidence); // Re-index array
            $this->save();
            
            // Log the audit record
            AuditService::logAction(
                'remove_evidence',
                'report',
                $this->id,
                ['removed_file' => $removed['path'] ?? 'unknown']
            );
            
            return true;
        }
        
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get users who should be notified about this report.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNotificationRecipients()
    {
        $recipients = collect();
        
        // Always include the report creator
        if (!$this->anonymous && $this->user_id !== 1) {
            $recipients->push($this->user);
        }
        
        // Include assigned user
        if ($this->assigned_user_id) {
            $recipients->push($this->assignedUser);
        }
        
        // Include team members if a team is assigned
        if ($this->assigned_team_id) {
            $teamMembers = $this->assignedTeam->members;
            $recipients = $recipients->merge($teamMembers);
        }
        
        // Include subscribers of the zone
        if ($this->zone_id) {
            $zoneSubscribers = User::whereHas('subscriptions', function($query) {
                $query->where('subscriptable_type', 'App\Models\CampusZone')
                    ->where('subscriptable_id', $this->zone_id);
            })->get();
            
            $recipients = $recipients->merge($zoneSubscribers);
        }
        
        // Add administrators based on severity
        if ($this->severity === 'high' || $this->priority_level === 'critical') {
            $admins = User::where('role', 'admin')->get();
            $recipients = $recipients->merge($admins);
        }
        
        // Remove duplicates
        return $recipients->unique('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods & Analytics
    |--------------------------------------------------------------------------
    */

    /**
     * Get status counts for reports.
     *
     * @return array
     */
    public static function getStatusCounts(): array
    {
        return [
            'open' => self::where('status', 'open')->count(),
            'in_progress' => self::where('status', 'in_progress')->count(),
            'resolved' => self::where('status', 'resolved')->count()
        ];
    }

    /**
     * Get reports by campus.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getCampusStats()
    {
        return self::selectRaw('campus, COUNT(*) as count')
            ->groupBy('campus')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get severity distribution.
     *
     * @return array
     */
    public static function getSeverityStats()
    {
        return [
            'high' => self::where('severity', 'high')->count(),
            'medium' => self::where('severity', 'medium')->count(),
            'low' => self::where('severity', 'low')->count()
        ];
    }

    /**
     * Get monthly trend data.
     *
     * @param int $months Number of months to include
     * @return \Illuminate\Support\Collection
     */
    public static function getMonthlyTrend($months = 6)
    {
        return self::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get category distribution.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getCategoryStats()
    {
        return self::selectRaw('incident_categories.name, COUNT(reports.id) as count')
            ->join('incident_categories', 'reports.category_id', '=', 'incident_categories.id')
            ->groupBy('incident_categories.name')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get zone distribution.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getZoneStats()
    {
        return self::selectRaw('campus_zones.name, COUNT(reports.id) as count')
            ->join('campus_zones', 'reports.zone_id', '=', 'campus_zones.id')
            ->groupBy('campus_zones.name')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get average response time in minutes.
     *
     * @return float
     */
    public static function getAverageResponseTime(): float
    {
        $reports = self::whereNotNull('response_time')->get();
        
        if ($reports->isEmpty()) {
            return 0;
        }
        
        $totalMinutes = 0;
        $count = 0;
        
        foreach ($reports as $report) {
            $responseTime = $report->getResponseTimeInMinutes();
            if ($responseTime !== null) {
                $totalMinutes += $responseTime;
                $count++;
            }
        }
        
        return $count > 0 ? $totalMinutes / $count : 0;
    }

    /**
     * Get average resolution time in minutes.
     *
     * @return float
     */
    public static function getAverageResolutionTime(): float
    {
        $reports = self::whereNotNull('resolution_time')->get();
        
        if ($reports->isEmpty()) {
            return 0;
        }
        
        $totalMinutes = 0;
        $count = 0;
        
        foreach ($reports as $report) {
            $resolutionTime = $report->getResolutionTimeInMinutes();
            if ($resolutionTime !== null) {
                $totalMinutes += $resolutionTime;
                $count++;
            }
        }
        
        return $count > 0 ? $totalMinutes / $count : 0;
    }

    /**
     * Get active reports that require immediate attention.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUrgentReports()
    {
        return self::whereIn('status', self::ACTIVE_STATUSES)
            ->where(function($query) {
                $query->where('severity', 'high')
                      ->orWhere('priority_level', 'critical');
            })
            ->whereNull('assigned_team_id')
            ->latest()
            ->get();
    }

    /**
     * Get color for severity display.
     *
     * @return string
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get color for priority display.
     *
     * @return string
     */
    public function getPriorityColor(): string
    {
        return match($this->priority_level) {
            'critical' => 'purple',
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'blue'
        };
    }

    /**
     * Get color for status display.
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'resolved' => 'green',
            'closed' => 'gray',
            'duplicate' => 'purple',
            'invalid' => 'red',
            default => 'gray'
        };
    }

    public function show(Report $report)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Allow viewing if:
        // 1. User is admin or security
        // 2. User is the report creator
        // 3. Report is not marked as anonymous
        // 4. Report is marked as public
        if ($user->hasRole(['admin', 'security']) || 
            $report->user_id === $user->id || 
            !$report->anonymous || 
            $report->is_public) {
            
            $report->load(['user', 'comments.user']);
            return view('reports.show', compact('report'));
        }

        // If not authorized, show a more user-friendly error page
        return response()->view('errors.unauthorized', [
            'message' => 'This report is private or anonymous. You do not have permission to view it.'
        ], 403);
    }
}
