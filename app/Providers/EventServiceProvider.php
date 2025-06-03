<?php

namespace App\Providers;

use App\Events\ReportCreated;
use App\Events\EmergencyResponseUpdated;
use App\Events\ChatMessageSent;
use App\Events\IncidentReported;
use App\Events\TeamDispatched;
use App\Events\SecurityAlertTriggered;
use App\Events\GeofenceViolationDetected;
use App\Events\SecurityStatusChanged;
use App\Events\EmergencyModeActivated;
use App\Events\TeamLocationUpdated;
use App\Events\ProtocolActivated;
use App\Models\Badge;
use App\Services\GamificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ReportCreated::class => [
            \App\Listeners\NotifySecurityTeam::class,
            \App\Listeners\NotifyNearbyUsers::class,
        ],
        EmergencyResponseUpdated::class => [
            \App\Listeners\NotifyReportOwner::class,
        ],
        ChatMessageSent::class => [
            \App\Listeners\NotifyChatParticipants::class,
        ],
        IncidentReported::class => [
            \App\Listeners\NotifySecurityManagement::class,
            \App\Listeners\LogIncidentDetails::class,
            \App\Listeners\TriggerResponseProtocol::class,
        ],
        TeamDispatched::class => [
            \App\Listeners\NotifyTeamMembers::class,
            \App\Listeners\UpdateIncidentStatus::class,
            \App\Listeners\TrackResponseTime::class,
        ],
        SecurityAlertTriggered::class => [
            \App\Listeners\BroadcastAlert::class,
            \App\Listeners\NotifyZoneSubscribers::class,
            \App\Listeners\EscalateToAuthorities::class,
        ],
        GeofenceViolationDetected::class => [
            \App\Listeners\TriggerZoneProtocols::class,
            \App\Listeners\NotifyZoneSecurity::class,
            \App\Listeners\LogZoneViolation::class,
        ],
        SecurityStatusChanged::class => [
            \App\Listeners\UpdateSecurityDashboard::class,
            \App\Listeners\NotifyStakeholders::class,
            \App\Listeners\LogStatusChange::class,
        ],
        EmergencyModeActivated::class => [
            \App\Listeners\BroadcastEmergencyNotification::class,
            \App\Listeners\InitiateEmergencyProtocols::class,
            \App\Listeners\AlertExternalAuthorities::class,
        ],
        TeamLocationUpdated::class => [
            \App\Listeners\UpdateSecurityMap::class,
            \App\Listeners\OptimizeTeamDeployment::class,
            \App\Listeners\LogTeamMovements::class,
        ],
        ProtocolActivated::class => [
            \App\Listeners\AssignTeamTasks::class,
            \App\Listeners\NotifyProtocolParticipants::class,
            \App\Listeners\MonitorProtocolCompliance::class,
        ],
    ];

    public function boot(): void
    {
        $gamification = app(GamificationService::class);

        // Handle Report Creation
        Event::listen(ReportCreated::class, function ($event) use ($gamification) {
            $points = $gamification->awardPoints($event->report->user, 'report_submission', $event->report);
            $badges = $gamification->checkBadges($event->report->user, 'report_submitted', $event->report);

            if ($event->report->severity === 'high') {
                $gamification->awardPoints($event->report->user, 'high_priority_report');
            }
        });

        // Handle Emergency Response Updates
        Event::listen(EmergencyResponseUpdated::class, function ($event) use ($gamification) {
            $response = $event->response;
            $responder = $response->responder;
            
            $gamification->awardPoints($responder, 'emergency_response', $response);
            $gamification->checkBadges($responder, 'emergency_response', $response);

            if ($response->status === 'resolved') {
                $gamification->awardPoints($responder, 'report_resolved');
                
                // Also award points to the original reporter for accuracy
                if ($response->report->verified) {
                    $gamification->awardPoints($response->report->user, 'accurate_report');
                    $gamification->checkBadges($response->report->user, 'report_verified', $response->report);
                }
            }
        });

        // Handle Chat Messages
        Event::listen(ChatMessageSent::class, function ($event) use ($gamification) {
            if ($event->message->type === 'update') {
                $gamification->awardPoints($event->message->user, 'helpful_update');
            }
        });

        // Handle Security Events
        Event::listen(IncidentReported::class, function ($event) use ($gamification) {
            $reporter = $event->incident->reporter;
            $gamification->awardPoints($reporter, 'incident_reported', $event->incident);
            $gamification->checkBadges($reporter, 'security_contributor');
        });

        Event::listen(TeamDispatched::class, function ($event) use ($gamification) {
            // Award points to team leader for coordinating response
            $teamLeader = $event->team->leader;
            $gamification->awardPoints($teamLeader, 'team_dispatch_coordination', $event->team);
            
            // Award points to team members for response
            foreach ($event->team->members as $member) {
                $gamification->awardPoints($member, 'security_response_participation');
                $gamification->checkBadges($member, 'rapid_responder');
            }
        });

        Event::listen(SecurityAlertTriggered::class, function ($event) use ($gamification) {
            if ($event->alert->verified) {
                $gamification->awardPoints($event->alert->creator, 'verified_security_alert');
            }
        });

        Event::listen(ProtocolActivated::class, function ($event) use ($gamification) {
            // Award points to protocol coordinator
            $coordinator = $event->protocol->coordinator;
            $gamification->awardPoints($coordinator, 'protocol_management');
            
            // Check for perfect protocol execution badge
            if ($event->protocol->compliance_rate >= 95) {
                $gamification->checkBadges($coordinator, 'protocol_master');
            }
        });
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}