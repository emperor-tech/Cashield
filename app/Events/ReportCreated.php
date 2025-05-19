<?php

namespace App\Events;

use App\Models\Report;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function broadcastOn()
    {
        return new Channel('reports');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->report->id,
            'campus' => $this->report->campus,
            'description' => $this->report->description,
            'location' => $this->report->location,
            'severity' => $this->report->severity,
            'anonymous' => $this->report->anonymous,
            'media_path' => $this->report->media_path,
            'created_at' => $this->report->created_at->toDateTimeString(),
        ];
    }
} 