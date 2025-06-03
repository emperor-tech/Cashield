<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityTeam;
use App\Models\User;
use App\Models\CampusZone;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class SecurityTeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index()
    {
        $teams = SecurityTeam::with(['leader', 'members', 'zone'])
            ->latest()
            ->paginate(10);

        $securityPersonnel = User::where('role', 'security')
            ->where('is_active', true)
            ->get();

        $zones = CampusZone::all();

        return view('admin.security.teams.index', compact('teams', 'securityPersonnel', 'zones'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        $securityPersonnel = User::where('role', 'security')
            ->where('is_active', true)
            ->get();

        $zones = CampusZone::all();

        return view('admin.security.teams.create', compact('securityPersonnel', 'zones'));
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'leader_id' => ['required', 'exists:users,id'],
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'team_type' => ['required', 'string'],
            'shift' => ['required', 'string'],
            'equipment' => ['array'],
            'contact_number' => ['nullable', 'string'],
            'radio_channel' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $team = SecurityTeam::create([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
            'zone_id' => $request->zone_id,
            'team_type' => $request->team_type,
            'shift' => $request->shift,
            'equipment' => $request->equipment,
            'contact_number' => $request->contact_number,
            'radio_channel' => $request->radio_channel,
            'notes' => $request->notes,
            'active' => true,
        ]);

        // Add leader as a member
        $team->members()->attach($request->leader_id, [
            'role' => 'leader',
            'joined_at' => now(),
            'active' => true,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_security_team',
            'subject_type' => 'security_team',
            'subject_id' => $team->id,
            'details' => [
                'name' => $team->name,
                'leader_id' => $team->leader_id,
            ],
        ]);

        return redirect()->route('admin.security.teams.index')
            ->with('success', 'Security team created successfully.');
    }

    /**
     * Display the specified team.
     */
    public function show(SecurityTeam $team)
    {
        $team->load(['leader', 'members', 'zone', 'shifts' => function($query) {
            $query->latest()->take(10);
        }]);

        $activeShifts = $team->activeShifts;
        $metrics = $team->getPerformanceMetrics();

        return view('admin.security.teams.show', compact('team', 'activeShifts', 'metrics'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(SecurityTeam $team)
    {
        $team->load(['leader', 'members', 'zone']);
        
        $securityPersonnel = User::where('role', 'security')
            ->where('is_active', true)
            ->get();

        $zones = CampusZone::all();

        return view('admin.security.teams.edit', compact('team', 'securityPersonnel', 'zones'));
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, SecurityTeam $team)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'leader_id' => ['required', 'exists:users,id'],
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'team_type' => ['required', 'string'],
            'shift' => ['required', 'string'],
            'equipment' => ['array'],
            'contact_number' => ['nullable', 'string'],
            'radio_channel' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldLeaderId = $team->leader_id;
        
        $team->update([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
            'zone_id' => $request->zone_id,
            'team_type' => $request->team_type,
            'shift' => $request->shift,
            'equipment' => $request->equipment,
            'contact_number' => $request->contact_number,
            'radio_channel' => $request->radio_channel,
            'notes' => $request->notes,
        ]);

        // Ensure leader is a member
        if (!$team->members->contains($request->leader_id)) {
            $team->members()->attach($request->leader_id, [
                'role' => 'leader',
                'joined_at' => now(),
                'active' => true,
            ]);
        }

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_security_team',
            'subject_type' => 'security_team',
            'subject_id' => $team->id,
            'details' => [
                'old_leader_id' => $oldLeaderId,
                'new_leader_id' => $team->leader_id,
            ],
        ]);

        return redirect()->route('admin.security.teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Toggle team active status.
     */
    public function toggleTeam(SecurityTeam $team)
    {
        $team->active = !$team->active;
        $team->save();

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggle_security_team',
            'subject_type' => 'security_team',
            'subject_id' => $team->id,
            'details' => [
                'new_status' => $team->active ? 'active' : 'inactive',
            ],
        ]);

        $status = $team->active ? 'activated' : 'deactivated';
        return back()->with('success', "Team {$status} successfully.");
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, SecurityTeam $team)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string', 'in:member,supervisor'],
        ]);

        if ($team->members->contains($request->user_id)) {
            return back()->with('error', 'User is already a member of this team.');
        }

        $team->members()->attach($request->user_id, [
            'role' => $request->role,
            'joined_at' => now(),
            'active' => true,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'add_team_member',
            'subject_type' => 'security_team',
            'subject_id' => $team->id,
            'details' => [
                'member_id' => $request->user_id,
                'role' => $request->role,
            ],
        ]);

        return back()->with('success', 'Member added to team.');
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(SecurityTeam $team, User $user)
    {
        if ($team->leader_id === $user->id) {
            return back()->with('error', 'Cannot remove team leader. Assign a new leader first.');
        }

        $team->members()->detach($user->id);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'remove_team_member',
            'subject_type' => 'security_team',
            'subject_id' => $team->id,
            'details' => [
                'member_id' => $user->id,
            ],
        ]);

        return back()->with('success', 'Member removed from team.');
    }
} 