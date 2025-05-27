<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class AchievementUnlocked extends Notification implements ShouldQueue
{
    use Queueable;

    protected $achievement;
    protected $points;

    public function __construct($achievement, $points = 0)
    {
        $this->achievement = $achievement;
        $this->points = $points;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Achievement Unlocked! 🎉')
            ->greeting('Congratulations!')
            ->line("You've earned a new achievement: {$this->achievement['name']}")
            ->line($this->achievement['description'])
            ->line($this->points > 0 ? "You've earned {$this->points} safety points!" : '')
            ->action('View Your Achievements', url('/profile#achievements'));
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Achievement Unlocked! 🎉')
            ->icon('/images/achievement-icon.png')
            ->body("You've earned: {$this->achievement['name']}")
            ->action('View Achievements', 'view_achievements')
            ->data(['url' => url('/profile#achievements')]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'achievement',
            'title' => 'Achievement Unlocked!',
            'achievement' => $this->achievement,
            'points' => $this->points,
            'icon' => $this->achievement['icon'] ?? '🏆'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'type' => 'achievement',
            'title' => 'Achievement Unlocked!',
            'message' => "You've earned: {$this->achievement['name']}",
            'achievement' => $this->achievement,
            'points' => $this->points,
            'icon' => $this->achievement['icon'] ?? '🏆'
        ];
    }
}