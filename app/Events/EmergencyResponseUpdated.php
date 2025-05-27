<?php

namespace App\Events;

use App\Models\EmergencyResponse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyResponseUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $response;

    public function __construct(EmergencyResponse $response)
    {
        $this->response = $response;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('emergency.response.' . $this->response->report_id),
            new Channel('campus.security')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'response' => $this->response->toArray(),
            'responder' => $this->response->responder,
            'updates' => $this->response->updates,
            'report' => $this->response->report
        ];
    }
}