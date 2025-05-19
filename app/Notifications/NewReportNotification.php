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
        return (new WebPushMessage)
            ->title('New Crime Report')
            ->icon('/icon.png')
            ->body('A new report has been submitted on campus!')
            ->action('View', url('/reports/' . $this->report->id));
    }
}