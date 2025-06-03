<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusZone;
use App\Models\SecurityTeam;
use App\Models\ZoneCheckpoint;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class ZoneController extends Controller
{
    /**
     * Display a listing of the zones.
     */
    public function index()
    {
        $zones = CampusZone::withCount(['reports', 'checkpoints', 'teams'])
            ->latest()
            ->paginate(10);

        return view('admin.security.zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create()
    {
        return view('admin.security.zones.create');
    }

    /**
     * Store a newly created zone.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:campus_zones'],
            'description' => ['nullable', 'string'],
            'boundaries' => ['required', 'array'],
            'boundaries.*.lat' => ['required', 'numeric'],
            'boundaries.*.lng' => ['required', 'numeric'],
            'operating_hours' => ['array'],
            'operating_hours.*' => ['array'],
            'restricted_access' => ['boolean'],
            'security_level' => ['required', 'string'],
            'patrol_frequency' => ['required', 'integer', 'min:0'],
        ]);

        $zone = CampusZone::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'boundaries' => $request->boundaries,
            'operating_hours' => $request->operating_hours,
            'restricted_access' => $request->restricted_access,
            'security_level' => $request->security_level,
            'patrol_frequency' => $request->patrol_frequency,
            'active' => true,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_zone',
            'subject_type' => 'campus_zone',
            'subject_id' => $zone->id,
            'details' => [
                'name' => $zone->name,
                'code' => $zone->code,
            ],
        ]);

        return redirect()->route('admin.security.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    /**
     * Display the specified zone.
     */
    public function show(CampusZone $zone)
    {
        $zone->load(['teams', 'checkpoints', 'reports' => function($query) {
            $query->latest()->take(5);
        }]);

        $activeTeams = $zone->teams()->where('active', true)->get();
        $recentIncidents = $zone->reports()
            ->where('severity', 'high')
            ->latest()
            ->take(5)
            ->get();

        $patrolStats = [
            'last_patrol' => $zone->last_patrolled_at,
            'next_patrol_due' => $zone->isPatrolDue(),
            'minutes_until_next' => $zone->minutesUntilNextPatrol(),
            'patrol_frequency' => $zone->patrol_frequency,
        ];

        return view('admin.security.zones.show', compact('zone', 'activeTeams', 'recentIncidents', 'patrolStats'));
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit(CampusZone $zone)
    {
        return view('admin.security.zones.edit', compact('zone'));
    }

    /**
     * Update the specified zone.
     */
    public function update(Request $request, CampusZone $zone)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:campus_zones,code,' . $zone->id],
            'description' => ['nullable', 'string'],
            'boundaries' => ['required', 'array'],
            'boundaries.*.lat' => ['required', 'numeric'],
            'boundaries.*.lng' => ['required', 'numeric'],
            'operating_hours' => ['array'],
            'operating_hours.*' => ['array'],
            'restricted_access' => ['boolean'],
            'security_level' => ['required', 'string'],
            'patrol_frequency' => ['required', 'integer', 'min:0'],
        ]);

        $zone->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'boundaries' => $request->boundaries,
            'operating_hours' => $request->operating_hours,
            'restricted_access' => $request->restricted_access,
            'security_level' => $request->security_level,
            'patrol_frequency' => $request->patrol_frequency,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_zone',
            'subject_type' => 'campus_zone',
            'subject_id' => $zone->id,
            'details' => [
                'name' => $zone->name,
                'code' => $zone->code,
            ],
        ]);

        return redirect()->route('admin.security.zones.show', $zone)
            ->with('success', 'Zone updated successfully.');
    }

    /**
     * Remove the specified zone.
     */
    public function destroy(CampusZone $zone)
    {
        // Check if zone has active teams
        if ($zone->teams()->where('active', true)->exists()) {
            return back()->with('error', 'Cannot delete zone with active teams.');
        }

        // Log action before deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_zone',
            'subject_type' => 'campus_zone',
            'subject_id' => $zone->id,
            'details' => [
                'name' => $zone->name,
                'code' => $zone->code,
            ],
        ]);

        $zone->delete();

        return redirect()->route('admin.security.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }

    /**
     * Toggle zone active status.
     */
    public function toggleStatus(CampusZone $zone)
    {
        $zone->active = !$zone->active;
        $zone->save();

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggle_zone_status',
            'subject_type' => 'campus_zone',
            'subject_id' => $zone->id,
            'details' => [
                'new_status' => $zone->active ? 'active' : 'inactive',
            ],
        ]);

        $status = $zone->active ? 'activated' : 'deactivated';
        return back()->with('success', "Zone {$status} successfully.");
    }

    /**
     * Update zone boundaries.
     */
    public function updateBoundaries(Request $request, CampusZone $zone)
    {
        $request->validate([
            'boundaries' => ['required', 'array'],
            'boundaries.*.lat' => ['required', 'numeric'],
            'boundaries.*.lng' => ['required', 'numeric'],
        ]);

        $zone->setBoundaries($request->boundaries);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_zone_boundaries',
            'subject_type' => 'campus_zone',
            'subject_id' => $zone->id,
            'details' => [
                'boundaries' => $request->boundaries,
            ],
        ]);

        return back()->with('success', 'Zone boundaries updated successfully.');
    }

    /**
     * Set zone operating hours.
     */
    public function setOperatingHours(Request $request, CampusZone $zone)
    {
        $request->validate([
            'operating_hours' => ['required', 'array'],
            'operating_hours.*' => ['array'],
            'operating_hours.*.open' => ['required', 'string'],
            'operating_hours.*.close' => ['required', 'string'],
        ]);

        foreach ($request->operating_hours as $day => $hours) {
            $zone->setDayHours($day, $hours);
        }

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_zone_hours',
            'subject_type' => 'campus_zone',
            'subject_id' => $zone->id,
            'details' => [
                'operating_hours' => $request->operating_hours,
            ],
        ]);

        return back()->with('success', 'Operating hours updated successfully.');
    }

    /**
     * Get zone statistics.
     */
    public function getStats(CampusZone $zone)
    {
        $incidentStats = $zone->getIncidentStats();
        $responseStats = $zone->getResponseTimeStats();

        return response()->json([
            'incidents' => $incidentStats,
            'response_times' => $responseStats,
        ]);
    }
}