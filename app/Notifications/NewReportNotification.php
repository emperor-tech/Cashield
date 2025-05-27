<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Report;
use NotificationChannels\WebPush\WebPushMessage;

class NewReportNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Report $report
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'mail', 'database'];
    }

    public function toBroadcast($notifiable) {
        return new BroadcastMessage([
            'report_id'   => $this->report->id,
            'campus'      => $this->report->campus,
            'severity'    => $this->report->severity,
            'anonymous'   => $this->report->anonymous,
            'reported_at' => $this->report->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Report Submitted')
            ->line('A new report has been submitted.')
            ->line('Report Title: ' . $this->report->title)
            ->action('View Report', url('/reports/' . $this->report->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'title' => $this->report->title,
            'message' => 'A new report has been submitted'
        ];
    }

    public function toWebPush($notifiable, $notification = null)
    {
        $severity = strtolower($this->report->severity);
        $soundFile = "notification-{$severity}.mp3";
        $urgencyLevel = $severity === 'high' ? 'high' : ($severity === 'medium' ? 'normal' : 'low');
        
        return (new WebPushMessage)
            ->title($this->getSeverityTitle())
            ->icon($this->getSeverityIcon())
            ->body($this->getNotificationBody())
            ->action('View Report', url('/reports/' . $this->report->id))
            ->action('Send Help', url('/reports/' . $this->report->id . '/respond'))
            ->badge('/badges/alert-badge.png')
            ->dir('auto')
            ->image($this->report->media_path ? url('storage/' . $this->report->media_path) : null)
            ->lang('en')
            ->renotify(true)
            ->requireInteraction($severity === 'high')
            ->tag('report-' . $this->report->id)
            ->timestamp($this->report->created_at->timestamp)
            ->urgency($urgencyLevel)
            ->vibrate([100, 50, 100])
            ->data([
                'report_id' => $this->report->id,
                'sound' => asset('sounds/' . $soundFile),
                'url' => url('/reports/' . $this->report->id)
            ]);
    }

    private function getSeverityTitle(): string
    {
        return match(strtolower($this->report->severity)) {
            'high' => '🚨 URGENT: Campus Security Alert!',
            'medium' => '⚠️ Campus Security Notice',
            default => 'Campus Alert'
        };
    }

    private function getSeverityIcon(): string
    {
        return match(strtolower($this->report->severity)) {
            'high' => '/icons/urgent-alert.png',
            'medium' => '/icons/warning-alert.png',
            default => '/icons/info-alert.png'
        };
    }

    private function getNotificationBody(): string
    {
        $location = $this->report->location ? " at {$this->report->location}" : '';
        $type = $this->report->type ? "{$this->report->type}: " : '';
        return "{$type}{$this->report->description}{$location}";
    }
}