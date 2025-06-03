<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\User;
use App\Models\SecurityTeam;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Events\BroadcastSent;
use App\Notifications\BroadcastNotification;
use Illuminate\Support\Facades\Notification;

class BroadcastController extends Controller
{
    /**
     * Display a listing of broadcasts.
     */
    public function index(Request $request)
    {
        $query = Broadcast::with(['sender'])
            ->when($request->filled('type'), function($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            });

        $broadcasts = $query->latest()->paginate(15);

        return view('admin.communication.broadcasts.index', compact('broadcasts'));
    }

    /**
     * Show the form for creating a new broadcast.
     */
    public function create()
    {
        $teams = SecurityTeam::where('active', true)->get();
        return view('admin.communication.broadcasts.create', compact('teams'));
    }

    /**
     * Store a newly created broadcast.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'in:announcement,alert,emergency'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'target_type' => ['required', 'string', 'in:all,security,team'],
            'target_team_id' => ['required_if:target_type,team', 'exists:security_teams,id'],
            'schedule_at' => ['nullable', 'date', 'after:now'],
            'expires_at' => ['nullable', 'date', 'after:schedule_at'],
            'requires_acknowledgment' => ['boolean'],
        ]);

        $broadcast = Broadcast::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'priority' => $request->priority,
            'target_type' => $request->target_type,
            'target_team_id' => $request->target_team_id,
            'schedule_at' => $request->schedule_at,
            'expires_at' => $request->expires_at,
            'requires_acknowledgment' => $request->requires_acknowledgment,
            'sender_id' => auth()->id(),
            'status' => $request->schedule_at ? 'scheduled' : 'sent',
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_broadcast',
            'subject_type' => 'broadcast',
            'subject_id' => $broadcast->id,
            'details' => [
                'title' => $broadcast->title,
                'type' => $broadcast->type,
                'target_type' => $broadcast->target_type,
            ],
        ]);

        // Send broadcast if not scheduled
        if (!$request->schedule_at) {
            $this->sendBroadcast($broadcast);
        }

        return redirect()->route('admin.communication.broadcasts.index')
            ->with('success', 'Broadcast created successfully.');
    }

    /**
     * Display the specified broadcast.
     */
    public function show(Broadcast $broadcast)
    {
        $broadcast->load(['sender', 'acknowledgments.user']);
        
        $stats = [
            'total_recipients' => $broadcast->getRecipientsCount(),
            'acknowledged' => $broadcast->acknowledgments->count(),
            'pending' => $broadcast->getRecipientsCount() - $broadcast->acknowledgments->count(),
        ];

        return view('admin.communication.broadcasts.show', compact('broadcast', 'stats'));
    }

    /**
     * Show the form for editing the specified broadcast.
     */
    public function edit(Broadcast $broadcast)
    {
        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Cannot edit a broadcast that has already been sent.');
        }

        $teams = SecurityTeam::where('active', true)->get();
        return view('admin.communication.broadcasts.edit', compact('broadcast', 'teams'));
    }

    /**
     * Update the specified broadcast.
     */
    public function update(Request $request, Broadcast $broadcast)
    {
        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Cannot update a broadcast that has already been sent.');
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'in:announcement,alert,emergency'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'target_type' => ['required', 'string', 'in:all,security,team'],
            'target_team_id' => ['required_if:target_type,team', 'exists:security_teams,id'],
            'schedule_at' => ['nullable', 'date', 'after:now'],
            'expires_at' => ['nullable', 'date', 'after:schedule_at'],
            'requires_acknowledgment' => ['boolean'],
        ]);

        $broadcast->update([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'priority' => $request->priority,
            'target_type' => $request->target_type,
            'target_team_id' => $request->target_team_id,
            'schedule_at' => $request->schedule_at,
            'expires_at' => $request->expires_at,
            'requires_acknowledgment' => $request->requires_acknowledgment,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_broadcast',
            'subject_type' => 'broadcast',
            'subject_id' => $broadcast->id,
            'details' => [
                'title' => $broadcast->title,
                'type' => $broadcast->type,
            ],
        ]);

        return redirect()->route('admin.communication.broadcasts.show', $broadcast)
            ->with('success', 'Broadcast updated successfully.');
    }

    /**
     * Remove the specified broadcast.
     */
    public function destroy(Broadcast $broadcast)
    {
        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Cannot delete a broadcast that has already been sent.');
        }

        // Log action before deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_broadcast',
            'subject_type' => 'broadcast',
            'subject_id' => $broadcast->id,
            'details' => [
                'title' => $broadcast->title,
                'type' => $broadcast->type,
            ],
        ]);

        $broadcast->delete();

        return redirect()->route('admin.communication.broadcasts.index')
            ->with('success', 'Broadcast deleted successfully.');
    }

    /**
     * Send the broadcast now.
     */
    public function send(Broadcast $broadcast)
    {
        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Broadcast has already been sent.');
        }

        $this->sendBroadcast($broadcast);

        return back()->with('success', 'Broadcast sent successfully.');
    }

    /**
     * Cancel the scheduled broadcast.
     */
    public function cancel(Broadcast $broadcast)
    {
        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Cannot cancel a broadcast that has already been sent.');
        }

        $broadcast->update(['status' => 'cancelled']);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'cancel_broadcast',
            'subject_type' => 'broadcast',
            'subject_id' => $broadcast->id,
            'details' => [
                'title' => $broadcast->title,
                'type' => $broadcast->type,
            ],
        ]);

        return back()->with('success', 'Broadcast cancelled successfully.');
    }

    /**
     * Get broadcast statistics.
     */
    public function getStats(Broadcast $broadcast)
    {
        $stats = [
            'total_recipients' => $broadcast->getRecipientsCount(),
            'acknowledged' => $broadcast->acknowledgments->count(),
            'pending' => $broadcast->getRecipientsCount() - $broadcast->acknowledgments->count(),
            'acknowledgment_rate' => $broadcast->getAcknowledgmentRate(),
            'response_times' => $broadcast->getResponseTimes(),
        ];

        return response()->json($stats);
    }

    /**
     * Send the broadcast to recipients.
     */
    private function sendBroadcast(Broadcast $broadcast)
    {
        // Get recipients based on target type
        $recipients = $this->getBroadcastRecipients($broadcast);

        // Send notifications
        Notification::send($recipients, new BroadcastNotification($broadcast));

        // Update broadcast status
        $broadcast->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Fire broadcast event
        event(new BroadcastSent($broadcast));

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'send_broadcast',
            'subject_type' => 'broadcast',
            'subject_id' => $broadcast->id,
            'details' => [
                'recipients_count' => count($recipients),
            ],
        ]);
    }

    /**
     * Get broadcast recipients based on target type.
     */
    private function getBroadcastRecipients(Broadcast $broadcast)
    {
        switch ($broadcast->target_type) {
            case 'all':
                return User::where('is_active', true)->get();
            
            case 'security':
                return User::where('role', 'security')
                    ->where('is_active', true)
                    ->get();
            
            case 'team':
                return SecurityTeam::find($broadcast->target_team_id)
                    ->members()
                    ->where('is_active', true)
                    ->get();
            
            default:
                return collect();
        }
    }
} 