<?php

namespace App\Models;

use App\Events\ProtocolActivated;
use App\Events\ProtocolEscalated;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class ResponseProtocol
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string $priority
 * @property int|null $category_id
 * @property bool $applies_to_all_categories
 * @property array|null $required_teams
 * @property array|null $required_resources
 * @property array|null $external_agencies
 * @property int $target_response_time
 * @property int $resolution_time_target
 * @property array|null $steps_low
 * @property array|null $steps_medium
 * @property array|null $steps_high
 * @property array|null $steps_critical
 * @property array|null $escalation_triggers
 * @property array|null $escalation_steps
 * @property int|null $auto_escalation_time
 * @property array|null $notification_list
 * @property bool $requires_police_report
 * @property bool $requires_medical_response
 * @property bool $requires_evacuation_plan
 * @property bool $requires_evidence_collection
 * @property array|null $required_documentation
 * @property array|null $follow_up_actions
 * @property string|null $notes
 * @property string|null $author
 * @property Carbon|null $last_reviewed
 * @property bool $active
 * @property int $version
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * 
 * @property IncidentCategory|null $category
 * @property \Illuminate\Database\Eloquent\Collection $reports
 */
class ResponseProtocol extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 'code', 'description', 'priority', 'category_id', 'applies_to_all_categories',
        'required_teams', 'required_resources', 'external_agencies', 'target_response_time',
        'resolution_time_target', 'steps_low', 'steps_medium', 'steps_high', 'steps_critical',
        'escalation_triggers', 'escalation_steps', 'auto_escalation_time', 'notification_list',
        'requires_police_report', 'requires_medical_response', 'requires_evacuation_plan',
        'requires_evidence_collection', 'required_documentation', 'follow_up_actions',
        'notes', 'author', 'last_reviewed', 'active', 'version'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'required_teams' => 'array',
        'required_resources' => 'array',
        'external_agencies' => 'array',
        'steps_low' => 'array',
        'steps_medium' => 'array',
        'steps_high' => 'array',
        'steps_critical' => 'array',
        'escalation_triggers' => 'array',
        'escalation_steps' => 'array',
        'notification_list' => 'array',
        'required_documentation' => 'array',
        'follow_up_actions' => 'array',
        'applies_to_all_categories' => 'boolean',
        'requires_police_report' => 'boolean',
        'requires_medical_response' => 'boolean',
        'requires_evacuation_plan' => 'boolean',
        'requires_evidence_collection' => 'boolean',
        'active' => 'boolean',
        'last_reviewed' => 'datetime',
        'target_response_time' => 'integer',
        'resolution_time_target' => 'integer',
        'auto_escalation_time' => 'integer',
        'version' => 'integer',
    ];

    /**
     * Priority levels for protocols from lowest to highest
     * 
     * @var array<string>
     */
    public const PRIORITY_LEVELS = ['low', 'medium', 'high', 'critical'];

    /**
     * Team types that can be required for protocols
     * 
     * @var array<string>
     */
    public const TEAM_TYPES = [
        'patrol', 'response', 'investigation', 'surveillance', 
        'escort', 'access_control', 'emergency', 'k9', 'specialized'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($protocol) {
            // Generate a unique code if not provided
            if (!$protocol->code) {
                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $protocol->name), 0, 3));
                $count = self::where('code', 'like', $prefix . '%')->count() + 1;
                $protocol->code = $prefix . '-' . $count;
            }

            // Set version to 1 for new protocols
            if (!$protocol->version) {
                $protocol->version = 1;
            }

            // Set author if available
            if (!$protocol->author && Auth::check()) {
                $protocol->author = Auth::user()->name;
            }

            // Set last_reviewed to now
            $protocol->last_reviewed = now();
        });

        static::updating(function ($protocol) {
            // Log the update
            try {
                AuditService::logModelChange('response_protocol', $protocol->id, $protocol->getDirty());
            } catch (\Exception $e) {
                Log::error("Failed to log audit for protocol update: " . $e->getMessage());
            }

            // If significant changes were made, increment version and update last_reviewed
            $significantFields = [
                'steps_low', 'steps_medium', 'steps_high', 'steps_critical',
                'escalation_triggers', 'escalation_steps', 'required_teams',
                'required_resources', 'external_agencies', 'target_response_time',
                'resolution_time_target'
            ];

            $hasSignificantChanges = false;
            foreach ($significantFields as $field) {
                if ($protocol->isDirty($field)) {
                    $hasSignificantChanges = true;
                    break;
                }
            }

            if ($hasSignificantChanges) {
                $protocol->version += 1;
                $protocol->last_reviewed = now();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the incident category this protocol is primarily associated with.
     */
    public function category()
    {
        return $this->belongsTo(IncidentCategory::class, 'category_id');
    }

    /**
     * Get reports that used this protocol.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'protocol_id');
    }

    /**
     * Get protocol versions history.
     * (Assuming you're using soft deletes to keep older versions)
     */
    public function previousVersions()
    {
        $currentVersion = $this->version;
        $protocolName = $this->name;
        
        return self::withTrashed()
            ->where('name', $protocolName)
            ->where('version', '<', $currentVersion)
            ->orderBy('version', 'desc')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Protocol Step Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get steps for a specific severity level.
     *
     * @param string $severity The severity level (low, medium, high, critical)
     * @return array The steps for the given severity
     */
    public function getStepsForSeverity(string $severity): array
    {
        $severity = strtolower($severity);
        
        // Default to medium if the requested severity doesn't exist
        if (!in_array($severity, ['low', 'medium', 'high', 'critical'])) {
            $severity = 'medium';
        }
        
        $stepsField = "steps_{$severity}";
        
        // If steps for requested severity don't exist, fall back to the next lower severity
        if (empty($this->$stepsField)) {
            if ($severity === 'critical' && !empty($this->steps_high)) {
                return $this->steps_high;
            } elseif ($severity === 'high' && !empty($this->steps_medium)) {
                return $this->steps_medium;
            } elseif ($severity === 'medium' && !empty($this->steps_low)) {
                return $this->steps_low;
            }
        }
        
        return $this->$stepsField ?? [];
    }

    /**
     * Update steps for a specific severity level.
     *
     * @param string $severity The severity level (low, medium, high, critical)
     * @param array $steps The new steps to set
     * @return bool
     */
    public function updateSteps(string $severity, array $steps): bool
    {
        $severity = strtolower($severity);
        
        if (!in_array($severity, ['low', 'medium', 'high', 'critical'])) {
            return false;
        }
        
        $stepsField = "steps_{$severity}";
        $this->$stepsField = $steps;
        
        return $this->save();
    }

    /**
     * Add a step to a specific severity level.
     *
     * @param string $severity The severity level
     * @param string $step The step to add
     * @param int|null $position Position to insert (null for append)
     * @return bool
     */
    public function addStep(string $severity, string $step, ?int $position = null): bool
    {
        $severity = strtolower($severity);
        
        if (!in_array($severity, ['low', 'medium', 'high', 'critical'])) {
            return false;
        }
        
        $stepsField = "steps_{$severity}";
        $steps = $this->$stepsField ?? [];
        
        if ($position !== null && $position >= 0 && $position <= count($steps)) {
            // Insert at specific position
            array_splice($steps, $position, 0, [$step]);
        } else {
            // Append to end
            $steps[] = $step;
        }
        
        $this->$stepsField = $steps;
        
        return $this->save();
    }

    /**
     * Remove a step from a specific severity level.
     *
     * @param string $severity The severity level
     * @param int $position Position of the step to remove
     * @return bool
     */
    public function removeStep(string $severity, int $position): bool
    {
        $severity = strtolower($severity);
        
        if (!in_array($severity, ['low', 'medium', 'high', 'critical'])) {
            return false;
        }
        
        $stepsField = "steps_{$severity}";
        $steps = $this->$stepsField ?? [];
        
        if ($position >= 0 && $position < count($steps)) {
            array_splice($steps, $position, 1);
            $this->$stepsField = $steps;
            return $this->save();
        }
        
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Escalation Handling Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add an escalation trigger.
     *
     * @param string $trigger The trigger condition
     * @return bool
     */
    public function addEscalationTrigger(string $trigger): bool
    {
        $triggers = $this->escalation_triggers ?? [];
        
        if (!in_array($trigger, $triggers)) {
            $triggers[] = $trigger;
            $this->escalation_triggers = $triggers;
            return $this->save();
        }
        
        return true;
    }

    /**
     * Remove an escalation trigger.
     *
     * @param string $trigger The trigger to remove
     * @return bool
     */
    public function removeEscalationTrigger(string $trigger): bool
    {
        $triggers = $this->escalation_triggers ?? [];
        
        if (($key = array_search($trigger, $triggers)) !== false) {
            unset($triggers[$key]);
            $this->escalation_triggers = array_values($triggers); // Re-index
            return $this->save();
        }
        
        return false;
    }

    /**
     * Update escalation steps.
     *
     * @param array $steps The new escalation steps
     * @return bool
     */
    public function updateEscalationSteps(array $steps): bool
    {
        $this->escalation_steps = $steps;
        return $this->save();
    }

    /**
     * Set auto-escalation time.
     *
     * @param int $minutes Minutes after which to auto-escalate
     * @return bool
     */
    public function setAutoEscalationTime(int $minutes): bool
    {
        $this->auto_escalation_time = max(0, $minutes);
        return $this->save();
    }

    /**
     * Check if a report meets escalation criteria.
     *
     * @param Report $report The report to check
     * @return bool Whether the report should be escalated
     */
    public function shouldEscalate(Report $report): bool
    {
        // Check time-based auto-escalation
        if ($this->auto_escalation_time) {
            $created = $report->incident_date ?? $report->created_at;
            $minutesSinceCreation = $created->diffInMinutes(now());
            
            if ($minutesSinceCreation >= $this->auto_escalation_time && $report->isActive()) {
                return true;
            }
        }
        
        // Check escalation triggers
        if (!empty($this->escalation_triggers) && $report->isActive()) {
            foreach ($this->escalation_triggers as $trigger) {
                // Check for severity-based triggers
                if ($trigger === 'high_severity' && $report->severity === 'high') {
                    return true;
                }
                
                // Check for specific keyword triggers in description or comments
                if (strpos($trigger, 'keyword:') === 0) {
                    $keyword = substr($trigger, 9);
                    
                    if (stripos($report->description, $keyword) !== false) {
                        return true;
                    }
                    
                    // Check in comments
                    foreach ($report->comments as $comment) {
                        if (stripos($comment->comment, $keyword) !== false) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Perform escalation for a report.
     *
     * @param Report $report The report to escalate
     * @param string|null $reason Optional reason for escalation
     * @return bool
     */
    public function escalateReport(Report $report, ?string $reason = null): bool
    {
        // Upgrade priority if needed
        if ($report->priority_level !== 'critical') {
            // Find current index and move up one level
            $currentIndex = array_search($report->priority_level, self::PRIORITY_LEVELS);
            if ($currentIndex !== false && $currentIndex < count(self::PRIORITY_LEVELS) - 1) {
                $report->priority_level = self::PRIORITY_LEVELS[$currentIndex + 1];
            }
        }
        
        // Add escalation to status history
        $history = $report->status_history ?? [];
        $history[] = [
            'status' => $report->status,
            'user_id' => Auth::id() ?? 1,
            'timestamp' => now()->toIso8601String(),
            'notes' => $reason ?? 'Escalated due to protocol criteria',
            'action' => 'escalation'
        ];
        $report->status_history = $history;
        
        // Apply any specialized escalation steps from protocol
        if (!empty($this->escalation_steps)) {
            // Notify additional stakeholders if defined
            // This is a placeholder - actual implementation would depend on notification system
        }
        
        // Save the report changes
        $success = $report->save();
        
        // Fire escalation event
        if ($success) {
            event(new ProtocolEscalated($report, $this, $reason));
            
            // Log the escalation
            AuditService::logAction(
                'report_escalated',
                'report',
                $report->id,
                [
                    'protocol_id' => $this->id,
                    'protocol_name' => $this->name,
                    'reason' => $reason
                ]
            );
        }
        
        return $success;
    }

    /*
    |--------------------------------------------------------------------------
    | Team and Resource Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add a required team type.
     *
     * @param string $teamType The type of team required
     * @return bool
     */
    public function addRequiredTeam(string $teamType): bool
    {
        if (!in_array($teamType, self::TEAM_TYPES)) {
            return false;
        }
        
        $teams = $this->required_teams ?? [];
        
        if (!in_array($teamType, $teams)) {
            $teams[] = $teamType;
            $this->required_teams = $teams;
            return $this->save();
        }
        
        return true;
    }

    /**
     * Remove a required team type.
     *
     * @param string $teamType The type of team to remove
     * @return bool
     */
    public function removeRequiredTeam(string $teamType): bool
    {
        $teams = $this->required_teams ?? [];
        
        if (($key = array_search($teamType, $teams)) !== false) {
            unset($teams[$key]);
            $this->required_teams = array_values($teams); // Re-index
            return $this->save();
        }
        
        return false;
    }

    /**
     * Add a required resource.
     *
     * @param string $resource The resource required
     * @return bool
     */
    public function addRequiredResource(string $resource): bool
    {
        $resources = $this->required_resources ?? [];
        
        if (!in_array($resource, $resources)) {
            $resources[] = $resource;
            $this->required_resources = $resources;
            return $this->save();
        }
        
        return true;
    }

    /**
     * Remove a required resource.
     *
     * @param string $resource The resource to remove
     * @return bool
     */
    public function removeRequiredResource(string $resource): bool
    {
        $resources = $this->required_resources ?? [];
        
        if (($key = array_search($resource, $resources)) !== false) {
            unset($resources[$key]);
            $this->required_resources = array_values($resources); // Re-index
            return $this->save();
        }
        
        return false;
    }

    /**
     * Add an external agency.
     *
     * @param array $agency Agency details (name, contact, etc.)
     * @return bool
     */
    public function addExternalAgency(array $agency): bool
    {
        if (!isset($agency['name'])) {
            return false;
        }
        
        $agencies = $this->external_agencies ?? [];
        
        // Check if agency already exists by name
        foreach ($agencies as $existing) {
            if (isset($existing['name']) && $existing['name'] === $agency['name']) {
                return true;
            }
        }
        
        $agencies[] = $agency;
        $this->external_agencies = $agencies;
        
        return $this->save();
    }

    /**
     * Remove an external agency.
     *
     * @param string $agencyName The name of the agency to remove
     * @return bool
     */
    public function removeExternalAgency(string $agencyName): bool
    {
        $agencies = $this->external_agencies ?? [];
        
        foreach ($agencies as $key => $agency) {
            if (isset($agency['name']) && $agency['name'] === $agencyName) {
                unset($agencies[$key]);
                $this->external_agencies = array_values($agencies); // Re-index
                return $this->save();
            }
        }
        
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add a notification recipient.
     *
     * @param string $role The role/position to notify
     * @param string $severityLevels Comma-separated levels at which to notify (e.g., 'high,critical')
     * @return bool
     */
    public function addNotificationRecipient(string $role, string $severityLevels = 'all'): bool
    {
        $notifications = $this->notification_list ?? [];
        $notifications[$role] = $severityLevels;
        $this->notification_list = $notifications;
        
        return $this->save();
    }

    /**
     * Remove a notification recipient.
     *
     * @param string $role The role to remove from notifications
     * @return bool
     */
    public function removeNotificationRecipient(string $role): bool
    {
        $notifications = $this->notification_list ?? [];
        
        if (isset($notifications[$role])) {
            unset($notifications[$role]);
            $this->notification_list = $notifications;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Get recipients who should be notified for a specific severity.
     *
     * @param string $severity The incident severity
     * @return array List of roles that should be notified
     */
    public function getNotificationRecipientsForSeverity(string $severity): array
    {
        $notifications = $this->notification_list ?? [];
        $recipients = [];
        
        foreach ($notifications as $role => $levels) {
            if ($levels === 'all' || strpos($levels, $severity) !== false) {
                $recipients[] = $role;
            }
        }
        
        return $recipients;
    }

    /*
    |--------------------------------------------------------------------------
    | Documentation Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add a required document.
     *
     * @param array $document Document details
     * @return bool
     */
    public function addRequiredDocument(array $document): bool
    {
        if (!isset($document['name'])) {
            return false;
        }
        
        $documents = $this->required_documentation ?? [];
        
        // Check if document already exists by name
        foreach ($documents as $existing) {
            if (isset($existing['name']) && $existing['name'] === $document['name']) {
                return true;
            }
        }
        
        $documents[] = $document;
        $this->required_documentation = $documents;
        
        return $this->save();
    }

    /**
     * Remove a required document.
     *
     * @param string $documentName The name of the document to remove
     * @return bool
     */
    public function removeRequiredDocument(string $documentName): bool
    {
        $documents = $this->required_documentation ?? [];
        
        foreach ($documents as $key => $document) {
            if (isset($document['name']) && $document['name'] === $documentName) {
                unset($documents[$key]);
                $this->required_documentation = array_values($documents); // Re-index
                return $this->save();
            }
        }
        
        return false;
    }

    /**
     * Add a follow-up action.
     *
     * @param string $action The follow-up action to add
     * @return bool
     */
    public function addFollowUpAction(string $action): bool
    {
        $actions = $this->follow_up_actions ?? [];
        
        if (!in_array($action, $actions)) {
            $actions[] = $action;
            $this->follow_up_actions = $actions;
            return $this->save();
        }
        
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Protocol Application Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Apply this protocol to a report.
     *
     * @param Report $report The report to apply the protocol to
     * @return bool
     */
    public function applyToReport(Report $report): bool
    {
        // Link protocol to report
        $report->protocol_id = $this->id;
        
        // Update report priority based on protocol if not already set
        if (!$report->priority_level) {
            $report->priority_level = $this->priority;
        }
        
        // Set documentation requirements
        if ($this->requires_evidence_collection) {
            $report->requires_evidence = true;
        }
        
        // Add to status history
        $history = $report->status_history ?? [];
        $history[] = [
            'status' => $report->status,
            'user_id' => Auth::id() ?? 1,
            'timestamp' => now()->toIso8601String(),
            'notes' => "Protocol {$this->code} applied",
            'action' => 'protocol_applied'
        ];
        $report->status_history = $history;
        
        // Save the report
        $success = $report->save();
        
        // Fire event for protocol activation
        if ($success) {
            event(new ProtocolActivated($report, $this));
            
            // Log the application
            AuditService::logAction(
                'protocol_applied',
                'report',
                $report->id,
                [
                    'protocol_id' => $this->id,
                    'protocol_name' => $this->name,
                    'protocol_version' => $this->version
                ]
            );
        }
        
        return $success;
    }

    /**
     * Find the most appropriate teams for a report based on this protocol.
     *
     * @param Report $report The report to find teams for
     * @param int $limit Maximum number of teams to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findTeamsForReport(Report $report, int $limit = 2)
    {
        $teams = collect();
        
        // If no required teams specified, return empty collection
        if (empty($this->required_teams)) {
            return $teams;
        }
        
        // Get zone from report if available
        $zone = null;
        if ($report->zone_id) {
            $zone = CampusZone::find($report->zone_id);
        }
        
        // For each required team type, find an available team
        foreach ($this->required_teams as $teamType) {
            $availableTeams = SecurityTeam::findAvailableTeams($zone, $teamType);
            
            if (!$availableTeams->isEmpty()) {
                $teams = $teams->merge($availableTeams);
                
                if ($teams->count() >= $limit) {
                    return $teams->take($limit);
                }
            }
        }
        
        // If we still need more teams, add any available teams regardless of type
        if ($teams->count() < $limit) {
            $moreTeams = SecurityTeam::findAvailableTeams($zone);
            $moreTeams = $moreTeams->diff($teams); // Remove any already included
            $teams = $teams->merge($moreTeams->take($limit - $teams->count()));
        }
        
        return $teams;
    }

    /*
    |--------------------------------------------------------------------------
    | Compliance Checking Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a report is following the protocol correctly.
     *
     * @param Report $report The report to check
     * @return array Compliance status with details
     */
    public function checkCompliance(Report $report): array
    {
        $issues = [];
        $compliant = true;
        
        // Check response time against target
        if ($report->response_time && $this->target_response_time > 0) {
            $responseTime = $report->getResponseTimeInMinutes();
            
            if ($responseTime > $this->target_response_time) {
                $issues[] = "Response time ({$responseTime} minutes) exceeded target ({$this->target_response_time} minutes)";
                $compliant = false;
            }
        } elseif ($report->isActive() && $report->isResponseOverdue()) {
            $issues[] = "No response recorded and target response time exceeded";
            $compliant = false;
        }
        
        // Check resolution time against target for resolved reports
        if ($report->isClosed() && $report->resolution_time && $this->resolution_time_target > 0) {
            $resolutionTime = $report->getResolutionTimeInMinutes();
            
            if ($resolutionTime > $this->resolution_time_target) {
                $issues[] = "Resolution time ({$resolutionTime} minutes) exceeded target ({$this->resolution_time_target} minutes)";
                $compliant = false;
            }
        }
        
        // Check if required evidence was collected
        if ($this->requires_evidence_collection && empty($report->evidence_files)) {
            $issues[] = "Evidence collection required but no evidence files recorded";
            $compliant = false;
        }
        
        // Check if required documentation is present
        if (!empty($this->required_documentation)) {
            foreach ($this->required_documentation as $document) {
                $documentName = $document['name'] ?? 'Unknown document';
                // This is a placeholder - actual implementation would check report's documents
                $issues[] = "Required document '{$documentName}' not verified";
                $compliant = false;
            }
        }
        
        // Check if external agencies were notified when required
        if ($this->requires_police_report && !$report->police_report_filed) {
            $issues[] = "Police report required but not filed";
            $compliant = false;
        }
        
        if ($this->requires_medical_response && !$report->medical_response_received) {
            $issues[] = "Medical response required but not recorded";
            $compliant = false;
        }
        
        return [
            'compliant' => $compliant,
            'issues' => $issues,
            'report_id' => $report->id,
            'protocol_id' => $this->id,
            'protocol_version' => $this->version,
            'checked_at' => now()->toIso8601String()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Validate the protocol for completeness and consistency.
     *
     * @return array Validation results
     */
    public function validate(): array
    {
        $issues = [];
        
        // Check if protocol has steps for at least one severity level
        if (empty($this->steps_low) && empty($this->steps_medium) && 
            empty($this->steps_high) && empty($this->steps_critical)) {
            $issues[] = "Protocol has no response steps defined for any severity level";
        }
        
        // Check for team requirements
        if (empty($this->required_teams)) {
            $issues[] = "No required teams specified";
        }
        
        // Check for missing notification recipients
        if (empty($this->notification_list)) {
            $issues[] = "No notification recipients specified";
        }
        
        // Verify target times are reasonable
        if ($this->target_response_time <= 0) {
            $issues[] = "Invalid target response time";
        }
        
        if ($this->resolution_time_target <= 0) {
            $issues[] = "Invalid resolution time target";
        }
        
        // Validate external agencies if specified
        if (!empty($this->external_agencies)) {
            foreach ($this->external_agencies as $agency) {
                if (!isset($agency['name']) || !isset($agency['contact'])) {
                    $issues[] = "External agency missing required details (name and contact)";
                    break;
                }
            }
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'protocol_id' => $this->id,
            'protocol_name' => $this->name,
            'validated_at' => now()->toIso8601String()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Find the most appropriate protocol for a given report.
     *
     * @param Report $report The report to find a protocol for
     * @return ResponseProtocol|null
     */
    public static function findProtocolForReport(Report $report): ?ResponseProtocol
    {
        // First try to find a protocol matching the report's category
        if ($report->category_id) {
            $protocol = self::where('category_id', $report->category_id)
                           ->where('active', true)
                           ->orderBy('version', 'desc')
                           ->first();
            
            if ($protocol) {
                return $protocol;
            }
        }
        
        // If no category-specific protocol, look for protocols that apply to all categories
        $generalProtocol = self::where('applies_to_all_categories', true)
                               ->where('active', true)
                               ->orderBy('version', 'desc')
                               ->first();
        
        if ($generalProtocol) {
            return $generalProtocol;
        }
        
        // If still no protocol, find one based on severity
        return self::where('active', true)
                   ->whereNull('category_id')
                   ->where('priority', $report->severity)
                   ->orderBy('version', 'desc')
                   ->first();
    }

    /**
     * Get the color for priority display.
     *
     * @return string
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'critical' => 'purple',
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'blue'
        };
    }
}

