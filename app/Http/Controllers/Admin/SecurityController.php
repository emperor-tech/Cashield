<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityTeam;
use App\Models\User;
use App\Models\CampusZone;
use App\Models\SecurityShift;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class SecurityController extends Controller
{
    /**
     * Display the security management dashboard.
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'active_teams' => SecurityTeam::where('active', true)->count(),
            'on_duty' => User::where('status', 'on_duty')->count(),
            'active_shifts' => SecurityShift::whereNull('ended_at')->count(),
            'active_alerts' => 0, // Implement based on your alert system
        ];

        // Get teams with pagination
        $teams = SecurityTeam::with(['leader', 'members', 'zone'])
            ->latest()
            ->paginate(10);

        // Get active shifts
        $activeShifts = SecurityShift::with(['user', 'team', 'zone'])
            ->whereNull('ended_at')
            ->get();

        // Get security personnel for team creation
        $securityPersonnel = User::where('role', 'security')
            ->where('is_active', true)
            ->get();

        // Get zones for team assignment
        $zones = CampusZone::all();

        return view('admin.security.index', compact('stats', 'teams', 'activeShifts', 'securityPersonnel', 'zones'));
    }

    /**
     * Store a new security team.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'leader_id' => ['required', 'exists:users,id'],
            'zone_id' => ['required', 'exists:campus_zones,id'],
        ]);

        $team = SecurityTeam::create([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
            'zone_id' => $request->zone_id,
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

        return redirect()->route('admin.security.index')
            ->with('success', 'Security team created successfully.');
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
     * Show team details.
     */
    public function show(SecurityTeam $team)
    {
        $team->load(['leader', 'members', 'zone', 'shifts']);
        
        return view('admin.security.show', compact('team'));
    }

    /**
     * Update team details.
     */
    public function update(Request $request, SecurityTeam $team)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'leader_id' => ['required', 'exists:users,id'],
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'members' => ['array'],
            'members.*' => ['exists:users,id'],
        ]);

        $oldLeaderId = $team->leader_id;
        
        $team->update([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
            'zone_id' => $request->zone_id,
        ]);

        // Update members
        if ($request->has('members')) {
            $team->members()->sync($request->members);
        }

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

        return redirect()->route('admin.security.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove a member from a team.
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

    /**
     * Add a member to a team.
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
} 