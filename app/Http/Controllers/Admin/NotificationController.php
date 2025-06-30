<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\SecurityTeam;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::with(['sender', 'recipient'])
            ->when($request->filled('type'), function($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('recipient'), function($q) use ($request) {
                $q->where('recipient_id', $request->recipient);
            });

        $notifications = $query->latest()->paginate(20);
        $users = User::where('is_active', true)->get();

        return view('admin.communication.notifications.index', compact('notifications', 'users'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        $users = User::where('is_active', true)->get();
        $teams = SecurityTeam::where('active', true)->get();
        return view('admin.communication.notifications.create', compact('users', 'teams'));
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'in:info,warning,alert,task'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'recipient_type' => ['required', 'string', 'in:user,team,role'],
            'recipient_id' => ['required_if:recipient_type,user,team', 'exists:users,id'],
            'recipient_role' => ['required_if:recipient_type,role', 'string'],
            'schedule_at' => ['nullable', 'date', 'after:now'],
            'expires_at' => ['nullable', 'date', 'after:schedule_at'],
            'requires_action' => ['boolean'],
            'action_url' => ['nullable', 'url'],
            'action_text' => ['nullable', 'string'],
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'priority' => $request->priority,
            'recipient_type' => $request->recipient_type,
            'recipient_id' => $request->recipient_id,
            'recipient_role' => $request->recipient_role,
            'schedule_at' => $request->schedule_at,
            'expires_at' => $request->expires_at,
            'requires_action' => $request->requires_action,
            'action_url' => $request->action_url,
            'action_text' => $request->action_text,
            'sender_id' => auth()->id(),
            'status' => $request->schedule_at ? 'scheduled' : 'sent',
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_notification',
            'subject_type' => 'notification',
            'subject_id' => $notification->id,
            'details' => [
                'title' => $notification->title,
                'type' => $notification->type,
                'recipient_type' => $notification->recipient_type,
            ],
        ]);

        // Send notification if not scheduled
        if (!$request->schedule_at) {
            $this->sendNotification($notification);
        }

        return redirect()->route('admin.communication.notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification)
    {
        $notification->load(['sender', 'recipient', 'actions']);
        
        $stats = [
            'sent_at' => $notification->sent_at,
            'read_at' => $notification->read_at,
            'action_taken_at' => $notification->action_taken_at,
            'expires_at' => $notification->expires_at,
        ];

        return view('admin.communication.notifications.show', compact('notification', 'stats'));
    }

    /**
     * Show the form for editing the specified notification.
     */
    public function edit(Notification $notification)
    {
        if ($notification->status === 'sent') {
            return back()->with('error', 'Cannot edit a notification that has already been sent.');
        }

        $users = User::where('is_active', true)->get();
        $teams = SecurityTeam::where('active', true)->get();
        return view('admin.communication.notifications.edit', compact('notification', 'users', 'teams'));
    }

    /**
     * Update the specified notification.
     */
    public function update(Request $request, Notification $notification)
    {
        if ($notification->status === 'sent') {
            return back()->with('error', 'Cannot update a notification that has already been sent.');
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'in:info,warning,alert,task'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'recipient_type' => ['required', 'string', 'in:user,team,role'],
            'recipient_id' => ['required_if:recipient_type,user,team', 'exists:users,id'],
            'recipient_role' => ['required_if:recipient_type,role', 'string'],
            'schedule_at' => ['nullable', 'date', 'after:now'],
            'expires_at' => ['nullable', 'date', 'after:schedule_at'],
            'requires_action' => ['boolean'],
            'action_url' => ['nullable', 'url'],
            'action_text' => ['nullable', 'string'],
        ]);

        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'priority' => $request->priority,
            'recipient_type' => $request->recipient_type,
            'recipient_id' => $request->recipient_id,
            'recipient_role' => $request->recipient_role,
            'schedule_at' => $request->schedule_at,
            'expires_at' => $request->expires_at,
            'requires_action' => $request->requires_action,
            'action_url' => $request->action_url,
            'action_text' => $request->action_text,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_notification',
            'subject_type' => 'notification',
            'subject_id' => $notification->id,
            'details' => [
                'title' => $notification->title,
                'type' => $notification->type,
            ],
        ]);

        return redirect()->route('admin.communication.notifications.show', $notification)
            ->with('success', 'Notification updated successfully.');
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->status === 'sent') {
            return back()->with('error', 'Cannot delete a notification that has already been sent.');
        }

        // Log action before deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_notification',
            'subject_type' => 'notification',
            'subject_id' => $notification->id,
            'details' => [
                'title' => $notification->title,
                'type' => $notification->type,
            ],
        ]);

        $notification->delete();

        return redirect()->route('admin.communication.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    /**
     * Send the notification now.
     */
    public function send(Notification $notification)
    {
        if ($notification->status === 'sent') {
            return back()->with('error', 'Notification has already been sent.');
        }

        $this->sendNotification($notification);

        return back()->with('success', 'Notification sent successfully.');
    }

    /**
     * Cancel the scheduled notification.
     */
    public function cancel(Notification $notification)
    {
        if ($notification->status === 'sent') {
            return back()->with('error', 'Cannot cancel a notification that has already been sent.');
        }

        $notification->update(['status' => 'cancelled']);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'cancel_notification',
            'subject_type' => 'notification',
            'subject_id' => $notification->id,
            'details' => [
                'title' => $notification->title,
                'type' => $notification->type,
            ],
        ]);

        return back()->with('success', 'Notification cancelled successfully.');
    }

    /**
     * Get notification statistics.
     */
    public function getStats(Notification $notification)
    {
        $stats = [
            'sent_at' => $notification->sent_at,
            'read_at' => $notification->read_at,
            'action_taken_at' => $notification->action_taken_at,
            'time_to_read' => $notification->read_at 
                ? Carbon::parse($notification->sent_at)->diffInMinutes($notification->read_at) 
                : null,
            'time_to_action' => $notification->action_taken_at 
                ? Carbon::parse($notification->sent_at)->diffInMinutes($notification->action_taken_at) 
                : null,
        ];

        return response()->json($stats);
    }

    /**
     * Send the notification to recipients.
     */
    private function sendNotification(Notification $notification)
    {
        // Get recipients based on type
        $recipients = $this->getNotificationRecipients($notification);

        foreach ($recipients as $recipient) {
            // Create notification record for each recipient
            $recipientNotification = $notification->replicate();
            $recipientNotification->recipient_id = $recipient->id;
            $recipientNotification->status = 'sent';
            $recipientNotification->sent_at = now();
            $recipientNotification->save();

            // Send actual notification
            $recipient->notify(new \App\Notifications\SystemNotification($recipientNotification));
        }

        // Update original notification status
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'send_notification',
            'subject_type' => 'notification',
            'subject_id' => $notification->id,
            'details' => [
                'recipients_count' => count($recipients),
            ],
        ]);
    }

    /**
     * Get notification recipients based on type.
     */
    private function getNotificationRecipients(Notification $notification)
    {
        switch ($notification->recipient_type) {
            case 'user':
                return User::where('id', $notification->recipient_id)
                    ->where('is_active', true)
                    ->get();
            
            case 'team':
                return SecurityTeam::find($notification->recipient_id)
                    ->members()
                    ->where('is_active', true)
                    ->get();
            
            case 'role':
                return User::where('role', $notification->recipient_role)
                    ->where('is_active', true)
                    ->get();
            
            default:
                return collect();
        }
    }

    public function adminNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->take(10)->get();
        $unreadCount = $user->unreadNotifications()->count();
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
} 