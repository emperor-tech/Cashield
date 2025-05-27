<?php

namespace App\Providers;

use App\Events\ReportCreated;
use App\Events\EmergencyResponseUpdated;
use App\Events\ChatMessageSent;
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
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}