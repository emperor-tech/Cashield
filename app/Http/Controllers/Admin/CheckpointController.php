<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZoneCheckpoint;
use App\Models\CampusZone;
use App\Models\CheckpointScan;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class CheckpointController extends Controller
{
    /**
     * Display a listing of the checkpoints.
     */
    public function index(Request $request)
    {
        $query = ZoneCheckpoint::with(['zone'])
            ->when($request->filled('zone'), function($q) use ($request) {
                $q->where('zone_id', $request->zone);
            })
            ->when($request->filled('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            });

        $checkpoints = $query->latest()->paginate(15);
        $zones = CampusZone::where('active', true)->get();

        return view('admin.security.checkpoints.index', compact('checkpoints', 'zones'));
    }

    /**
     * Show the form for creating a new checkpoint.
     */
    public function create()
    {
        $zones = CampusZone::where('active', true)->get();
        return view('admin.security.checkpoints.create', compact('zones'));
    }

    /**
     * Store a newly created checkpoint.
     */
    public function store(Request $request)
    {
        $request->validate([
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:zone_checkpoints'],
            'location' => ['required', 'array'],
            'location.lat' => ['required', 'numeric'],
            'location.lng' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'scan_interval' => ['required', 'integer', 'min:0'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'instructions' => ['nullable', 'string'],
        ]);

        $checkpoint = ZoneCheckpoint::create([
            'zone_id' => $request->zone_id,
            'name' => $request->name,
            'code' => $request->code,
            'location' => $request->location,
            'description' => $request->description,
            'scan_interval' => $request->scan_interval,
            'priority' => $request->priority,
            'instructions' => $request->instructions,
            'status' => 'active',
            'last_scanned_at' => null,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_checkpoint',
            'subject_type' => 'zone_checkpoint',
            'subject_id' => $checkpoint->id,
            'details' => [
                'name' => $checkpoint->name,
                'code' => $checkpoint->code,
                'zone_id' => $checkpoint->zone_id,
            ],
        ]);

        return redirect()->route('admin.security.checkpoints.index')
            ->with('success', 'Checkpoint created successfully.');
    }

    /**
     * Display the specified checkpoint.
     */
    public function show(ZoneCheckpoint $checkpoint)
    {
        $checkpoint->load(['zone', 'scans' => function($query) {
            $query->latest()->take(10);
        }]);

        $scanStats = $checkpoint->getScanStats();
        $lastScan = $checkpoint->getLastScan();
        $nextScanDue = $checkpoint->getNextScanDue();
        $complianceRate = $checkpoint->getComplianceRate();

        return view('admin.security.checkpoints.show', compact(
            'checkpoint',
            'scanStats',
            'lastScan',
            'nextScanDue',
            'complianceRate'
        ));
    }

    /**
     * Show the form for editing the specified checkpoint.
     */
    public function edit(ZoneCheckpoint $checkpoint)
    {
        $zones = CampusZone::where('active', true)->get();
        return view('admin.security.checkpoints.edit', compact('checkpoint', 'zones'));
    }

    /**
     * Update the specified checkpoint.
     */
    public function update(Request $request, ZoneCheckpoint $checkpoint)
    {
        $request->validate([
            'zone_id' => ['required', 'exists:campus_zones,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:zone_checkpoints,code,' . $checkpoint->id],
            'location' => ['required', 'array'],
            'location.lat' => ['required', 'numeric'],
            'location.lng' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'scan_interval' => ['required', 'integer', 'min:0'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'instructions' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
        ]);

        $checkpoint->update([
            'zone_id' => $request->zone_id,
            'name' => $request->name,
            'code' => $request->code,
            'location' => $request->location,
            'description' => $request->description,
            'scan_interval' => $request->scan_interval,
            'priority' => $request->priority,
            'instructions' => $request->instructions,
            'status' => $request->status,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_checkpoint',
            'subject_type' => 'zone_checkpoint',
            'subject_id' => $checkpoint->id,
            'details' => [
                'name' => $checkpoint->name,
                'code' => $checkpoint->code,
                'status' => $checkpoint->status,
            ],
        ]);

        return redirect()->route('admin.security.checkpoints.show', $checkpoint)
            ->with('success', 'Checkpoint updated successfully.');
    }

    /**
     * Remove the specified checkpoint.
     */
    public function destroy(ZoneCheckpoint $checkpoint)
    {
        // Check if checkpoint has any scans
        if ($checkpoint->scans()->exists()) {
            return back()->with('error', 'Cannot delete checkpoint with existing scan records.');
        }

        // Log action before deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_checkpoint',
            'subject_type' => 'zone_checkpoint',
            'subject_id' => $checkpoint->id,
            'details' => [
                'name' => $checkpoint->name,
                'code' => $checkpoint->code,
            ],
        ]);

        $checkpoint->delete();

        return redirect()->route('admin.security.checkpoints.index')
            ->with('success', 'Checkpoint deleted successfully.');
    }

    /**
     * Record a checkpoint scan.
     */
    public function recordScan(Request $request, ZoneCheckpoint $checkpoint)
    {
        $request->validate([
            'shift_id' => ['required', 'exists:security_shifts,id'],
            'location' => ['required', 'array'],
            'location.lat' => ['required', 'numeric'],
            'location.lng' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'condition' => ['required', 'string', 'in:good,damaged,missing,other'],
        ]);

        $scan = CheckpointScan::create([
            'checkpoint_id' => $checkpoint->id,
            'shift_id' => $request->shift_id,
            'scanned_by' => auth()->id(),
            'location' => $request->location,
            'notes' => $request->notes,
            'condition' => $request->condition,
            'scanned_at' => now(),
        ]);

        $checkpoint->update([
            'last_scanned_at' => now(),
            'last_condition' => $request->condition,
        ]);

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'record_checkpoint_scan',
            'subject_type' => 'checkpoint_scan',
            'subject_id' => $scan->id,
            'details' => [
                'checkpoint_id' => $checkpoint->id,
                'shift_id' => $request->shift_id,
                'condition' => $request->condition,
            ],
        ]);

        return back()->with('success', 'Checkpoint scan recorded successfully.');
    }

    /**
     * Get checkpoint statistics.
     */
    public function getStats(ZoneCheckpoint $checkpoint)
    {
        $stats = [
            'total_scans' => $checkpoint->scans()->count(),
            'compliance_rate' => $checkpoint->getComplianceRate(),
            'condition_history' => $checkpoint->getConditionHistory(),
            'scan_intervals' => $checkpoint->getScanIntervals(),
            'missed_scans' => $checkpoint->getMissedScans(),
        ];

        return response()->json($stats);
    }

    /**
     * Export checkpoint report.
     */
    public function exportReport(ZoneCheckpoint $checkpoint)
    {
        $report = $checkpoint->generateReport();
        
        return response()->json([
            'checkpoint' => $checkpoint,
            'report' => $report,
        ]);
    }
} 