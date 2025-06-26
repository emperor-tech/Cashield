<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityShift;
use App\Models\SecurityTeam;
use App\Models\ShiftIncident;
use App\Models\CampusZone;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Display a listing of the shifts.
     */
    public function index(Request $request)
    {
        $query = SecurityShift::with(['team', 'zone'])
            ->when($request->filled('team'), function($q) use ($request) {
                $q->where('team_id', $request->team);
            })
            ->when($request->filled('zone'), function($q) use ($request) {
                $q->where('zone_id', $request->zone);
            })
            ->when($request->filled('status'), function($q) use ($request) {
                if ($request->status === 'active') {
                    $q->whereNull('ended_at');
                } elseif ($request->status === 'completed') {
                    $q->whereNotNull('ended_at');
                }
            })
            ->when($request->filled('date'), function($q) use ($request) {
                $date = Carbon::parse($request->date);
                $q->whereDate('started_at', $date);
            });

        $shifts = $query->latest()->paginate(15);
        $teams = SecurityTeam::where('active', true)->get();
        $zones = CampusZone::where('active', true)->get();

        return view('admin.security.shifts.index', compact('shifts', 'teams', 'zones'));
    }

    /**
     * Show the form for creating a new shift.
     */
    public function create()
    {
        $teams = SecurityTeam::where('active', true)->get();
        $zones = CampusZone::where('active', true)->get();
        $securityPersonnel = \App\Models\User::where('role', 'security')
            ->where('is_active', true)
            ->get();

        return view('admin.security.shifts.create', compact('teams', 'zones', 'securityPersonnel'));
    }

    /**
     * Store a newly created shift.
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => ['required', 'exists:security_teams,id'],
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'shift_type' => ['required', 'string'],
            'patrol_route' => ['nullable', 'array'],
            'patrol_route.*.lat' => ['required', 'numeric'],
            'patrol_route.*.lng' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $shift = SecurityShift::create([
            'team_id' => $request->team_id,
            'zone_id' => $request->zone_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'shift_type' => $request->shift_type,
            'patrol_route' => $request->patrol_route,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_shift',
            'subject_type' => 'security_shift',
            'subject_id' => $shift->id,
            'details' => [
                'team_id' => $shift->team_id,
                'zone_id' => $shift->zone_id,
                'start_time' => $shift->start_time,
            ],
        ]);

        return redirect()->route('admin.security.shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    /**
     * Display the specified shift.
     */
    public function show(SecurityShift $shift)
    {
        $shift->load(['team.members', 'zone', 'incidents', 'checkpoints']);

        $patrolProgress = $shift->getPatrolProgress();
        $checkpointScans = $shift->getCheckpointScans();
        $incidentStats = $shift->getIncidentStats();

        return view('admin.security.shifts.show', compact(
            'shift',
            'patrolProgress',
            'checkpointScans',
            'incidentStats'
        ));
    }

    /**
     * Show the form for editing the specified shift.
     */
    public function edit(SecurityShift $shift)
    {
        $teams = SecurityTeam::where('active', true)->get();
        $zones = CampusZone::where('active', true)->get();

        return view('admin.security.shifts.edit', compact('shift', 'teams', 'zones'));
    }

    /**
     * Update the specified shift.
     */
    public function update(Request $request, SecurityShift $shift)
    {
        $request->validate([
            'team_id' => ['required', 'exists:security_teams,id'],
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'shift_type' => ['required', 'string'],
            'patrol_route' => ['nullable', 'array'],
            'patrol_route.*.lat' => ['required', 'numeric'],
            'patrol_route.*.lng' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string'],
        ]);

        $shift->update([
            'team_id' => $request->team_id,
            'zone_id' => $request->zone_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'shift_type' => $request->shift_type,
            'patrol_route' => $request->patrol_route,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_shift',
            'subject_type' => 'security_shift',
            'subject_id' => $shift->id,
            'details' => [
                'team_id' => $shift->team_id,
                'zone_id' => $shift->zone_id,
                'status' => $shift->status,
            ],
        ]);

        return redirect()->route('admin.security.shifts.show', $shift)
            ->with('success', 'Shift updated successfully.');
    }

    /**
     * Update shift status.
     */
    public function updateStatus(Request $request, SecurityShift $shift)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldStatus = $shift->status;
        
        $shift->update([
            'status' => $request->status,
            'notes' => $request->notes ? $shift->notes . "\n" . $request->notes : $shift->notes,
        ]);

        if ($request->status === 'in_progress') {
            $shift->actual_start_time = now();
        } elseif ($request->status === 'completed') {
            $shift->actual_end_time = now();
        }

        $shift->save();

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_shift_status',
            'subject_type' => 'security_shift',
            'subject_id' => $shift->id,
            'details' => [
                'old_status' => $oldStatus,
                'new_status' => $shift->status,
            ],
        ]);

        return back()->with('success', 'Shift status updated successfully.');
    }

    /**
     * Record an incident during the shift.
     */
    public function recordIncident(Request $request, SecurityShift $shift)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'severity' => ['required', 'string', 'in:low,medium,high'],
            'location' => ['required', 'array'],
            'location.lat' => ['required', 'numeric'],
            'location.lng' => ['required', 'numeric'],
            'action_taken' => ['nullable', 'string'],
        ]);

        $incident = ShiftIncident::create([
            'shift_id' => $shift->id,
            'title' => $request->title,
            'description' => $request->description,
            'severity' => $request->severity,
            'location' => $request->location,
            'action_taken' => $request->action_taken,
            'reported_by' => auth()->id(),
            'status' => 'reported',
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'record_shift_incident',
            'subject_type' => 'shift_incident',
            'subject_id' => $incident->id,
            'details' => [
                'shift_id' => $shift->id,
                'severity' => $incident->severity,
            ],
        ]);

        return back()->with('success', 'Incident recorded successfully.');
    }

    /**
     * Get shift statistics.
     */
    public function getStats(SecurityShift $shift)
    {
        $stats = [
            'duration' => $shift->getDuration(),
            'coverage' => $shift->getZoneCoverage(),
            'checkpoints' => $shift->getCheckpointStats(),
            'incidents' => $shift->getIncidentStats(),
            'team_performance' => $shift->getTeamPerformance(),
        ];

        return response()->json($stats);
    }

    /**
     * Export shift report.
     */
    public function exportReport(SecurityShift $shift)
    {
        $report = $shift->generateReport();
        
        return response()->json([
            'shift' => $shift,
            'report' => $report,
        ]);
    }
} 