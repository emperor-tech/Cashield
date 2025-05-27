<?php

namespace App\Notifications;

use App\Models\Rank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class RankUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newRank;

    public function __construct(Rank $newRank)
    {
        $this->newRank = $newRank;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Rank Achieved! 🎉')
            ->greeting('Congratulations!')
            ->line("You've reached a new rank: {$this->newRank->name}")
            ->line($this->newRank->description)
            ->line("Keep up the great work in making our campus safer!")
            ->action('View Your Profile', url('/profile#achievements'));
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('New Rank Achieved! 🎉')
            ->icon('/images/rank-icon.png')
            ->body("You've reached {$this->newRank->name} rank!")
            ->action('View Profile', 'view_profile')
            ->data(['url' => url('/profile#achievements')]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'rank_up',
            'title' => 'New Rank Achieved!',
            'rank' => [
                'name' => $this->newRank->name,
                'icon' => $this->newRank->icon,
                'description' => $this->newRank->description
            ]
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'type' => 'rank_up',
            'title' => 'New Rank Achieved!',
            'message' => "You've reached {$this->newRank->name} rank!",
            'rank' => [
                'name' => $this->newRank->name,
                'icon' => $this->newRank->icon,
                'description' => $this->newRank->description
            ]
        ];
    }
}