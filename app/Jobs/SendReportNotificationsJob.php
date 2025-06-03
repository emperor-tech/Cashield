<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendReportNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The report instance.
     *
     * @var \App\Models\Report
     */
    protected $report;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Report  $report
     * @return void
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get all admin users
            $admins = User::where('role', 'admin')->get();
            
            if ($admins->isNotEmpty()) {
                // Send notifications to all admins
                Notification::send($admins, new NewReportNotification($this->report));
                Log::info('Notifications sent for report #' . $this->report->id);
            } else {
                Log::warning('No admin users found to notify for report #' . $this->report->id);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications for report #' . $this->report->id . ': ' . $e->getMessage());
            
            // Retry the job with an exponential backoff
            if ($this->attempts() < 3) {
                $this->release(30 * $this->attempts());
            }
        }
    }
}

