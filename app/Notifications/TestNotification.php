<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $channel;

    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    public function via($notifiable)
    {
        return [$this->channel];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Test Notification - Cashield')
            ->greeting('Hello!')
            ->line('This is a test notification to verify your email notification settings.')
            ->line('If you received this, your email notifications are working correctly.')
            ->action('View Notification Settings', url('/profile#notifications'));
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Test Notification')
            ->icon('/images/notification-icon.png')
            ->body('This is a test notification to verify your web push notification settings.')
            ->action('View Settings', 'view_settings')
            ->data(['url' => url('/profile#notifications')]);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Test Notification',
            'message' => 'This is a test notification to verify your notification settings.',
            'type' => 'test'
        ];
    }
}