<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SecurityTeam;
use App\Models\SecurityShift;
use App\Models\CampusZone;
use App\Models\ZoneCheckpoint;
use App\Models\ShiftIncident;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function dashboard()
    {
        // Get overall statistics
        $stats = $this->getOverallStats();
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity();
        
        // Get performance metrics
        $performance = $this->getPerformanceMetrics();
        
        // Get trend data
        $trends = $this->getTrendData();

        return view('admin.analytics.dashboard', compact(
            'stats',
            'recentActivity',
            'performance',
            'trends'
        ));
    }

    /**
     * Display report analytics.
     */
    public function reports(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // Get report statistics
        $reportStats = $this->getReportStats($dateRange);
        
        // Get report trends
        $reportTrends = $this->getReportTrends($dateRange);
        
        // Get category distribution
        $categoryDistribution = $this->getCategoryDistribution($dateRange);
        
        // Get response time analysis
        $responseAnalysis = $this->getResponseTimeAnalysis($dateRange);
        
        // Get zone-wise report distribution
        $zoneDistribution = $this->getZoneDistribution($dateRange);

        return view('admin.analytics.reports', compact(
            'reportStats',
            'reportTrends',
            'categoryDistribution',
            'responseAnalysis',
            'zoneDistribution',
            'dateRange'
        ));
    }

    /**
     * Display security analytics.
     */
    public function security(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // Get security team performance
        $teamPerformance = $this->getTeamPerformance($dateRange);
        
        // Get shift coverage analysis
        $shiftCoverage = $this->getShiftCoverage($dateRange);
        
        // Get incident response metrics
        $incidentMetrics = $this->getIncidentMetrics($dateRange);
        
        // Get checkpoint compliance
        $checkpointCompliance = $this->getCheckpointCompliance($dateRange);
        
        // Get patrol route efficiency
        $patrolEfficiency = $this->getPatrolEfficiency($dateRange);

        return view('admin.analytics.security', compact(
            'teamPerformance',
            'shiftCoverage',
            'incidentMetrics',
            'checkpointCompliance',
            'patrolEfficiency',
            'dateRange'
        ));
    }

    /**
     * Display crime heatmap.
     */
    public function heatmap(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // Get incident locations
        $incidents = Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('location', 'severity', 'category')
            ->get();
        
        // Get zone boundaries
        $zones = CampusZone::select('name', 'boundaries')->get();
        
        // Get checkpoint locations
        $checkpoints = ZoneCheckpoint::select('name', 'location')->get();
        
        // Get active patrol routes
        $patrolRoutes = SecurityShift::where('status', 'in_progress')
            ->select('team_id', 'patrol_route')
            ->with('team:id,name')
            ->get();

        return view('admin.analytics.heatmap', compact(
            'incidents',
            'zones',
            'checkpoints',
            'patrolRoutes',
            'dateRange'
        ));
    }

    /**
     * Get overall statistics.
     */
    private function getOverallStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_reports' => Report::count(),
            'reports_today' => Report::whereDate('created_at', $today)->count(),
            'reports_this_month' => Report::whereDate('created_at', '>=', $thisMonth)->count(),
            'active_teams' => SecurityTeam::where('active', true)->count(),
            'active_shifts' => SecurityShift::where('status', 'in_progress')->count(),
            'total_zones' => CampusZone::count(),
            'total_checkpoints' => ZoneCheckpoint::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get recent activity.
     */
    private function getRecentActivity()
    {
        return DB::table('audit_logs')
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Get performance metrics.
     */
    private function getPerformanceMetrics()
    {
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'average_response_time' => Report::where('created_at', '>=', $thisMonth)
                ->whereNotNull('resolved_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, resolved_at)')),
            
            'checkpoint_compliance' => $this->calculateCheckpointCompliance(),
            
            'team_performance' => SecurityTeam::where('active', true)
                ->with(['shifts' => function($query) use ($thisMonth) {
                    $query->where('created_at', '>=', $thisMonth);
                }])
                ->get()
                ->map(function($team) {
                    return [
                        'team' => $team->name,
                        'completed_shifts' => $team->shifts->where('status', 'completed')->count(),
                        'incidents_handled' => $team->shifts->flatMap->incidents->count(),
                    ];
                }),
            
            'zone_coverage' => CampusZone::with(['shifts' => function($query) use ($thisMonth) {
                $query->where('created_at', '>=', $thisMonth);
            }])
            ->get()
            ->map(function($zone) {
                return [
                    'zone' => $zone->name,
                    'patrol_count' => $zone->shifts->count(),
                    'incident_count' => $zone->reports->count(),
                ];
            }),
        ];
    }

    /**
     * Get trend data.
     */
    private function getTrendData()
    {
        $days = collect(range(6, 0))->map(function($day) {
            $date = Carbon::now()->subDays($day);
            
            return [
                'date' => $date->format('Y-m-d'),
                'reports' => Report::whereDate('created_at', $date)->count(),
                'incidents' => ShiftIncident::whereDate('created_at', $date)->count(),
                'patrols' => SecurityShift::whereDate('created_at', $date)->count(),
            ];
        });

        return [
            'daily' => $days,
            'categories' => Report::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get(),
            'severity' => Report::select('severity', DB::raw('count(*) as count'))
                ->groupBy('severity')
                ->get(),
        ];
    }

    /**
     * Get report statistics.
     */
    private function getReportStats($dateRange)
    {
        return [
            'total' => Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'resolved' => Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'resolved')
                ->count(),
            'pending' => Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'pending')
                ->count(),
            'average_resolution_time' => Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->whereNotNull('resolved_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, resolved_at)')),
        ];
    }

    /**
     * Get report trends.
     */
    private function getReportTrends($dateRange)
    {
        return Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();
    }

    /**
     * Get category distribution.
     */
    private function getCategoryDistribution($dateRange)
    {
        return Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();
    }

    /**
     * Get response time analysis.
     */
    private function getResponseTimeAnalysis($dateRange)
    {
        return Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('resolved_at')
            ->select(
                'severity',
                DB::raw('avg(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_response_time'),
                DB::raw('min(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as min_response_time'),
                DB::raw('max(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as max_response_time')
            )
            ->groupBy('severity')
            ->get();
    }

    /**
     * Get zone distribution.
     */
    private function getZoneDistribution($dateRange)
    {
        return Report::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('zone_id', DB::raw('count(*) as count'))
            ->groupBy('zone_id')
            ->with('zone:id,name')
            ->get();
    }

    /**
     * Get team performance.
     */
    private function getTeamPerformance($dateRange)
    {
        return SecurityTeam::where('active', true)
            ->with(['shifts' => function($query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }])
            ->get()
            ->map(function($team) {
                return [
                    'team' => $team->name,
                    'total_shifts' => $team->shifts->count(),
                    'completed_shifts' => $team->shifts->where('status', 'completed')->count(),
                    'incidents_handled' => $team->shifts->flatMap->incidents->count(),
                    'average_response_time' => $team->shifts->flatMap->incidents->avg('response_time'),
                    'checkpoint_compliance' => $this->calculateTeamCheckpointCompliance($team),
                ];
            });
    }

    /**
     * Get shift coverage.
     */
    private function getShiftCoverage($dateRange)
    {
        return SecurityShift::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                'zone_id',
                DB::raw('count(*) as total_shifts'),
                DB::raw('sum(case when status = "completed" then 1 else 0 end) as completed_shifts'),
                DB::raw('avg(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration')
            )
            ->groupBy('zone_id')
            ->with('zone:id,name')
            ->get();
    }

    /**
     * Get incident metrics.
     */
    private function getIncidentMetrics($dateRange)
    {
        return ShiftIncident::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                'severity',
                DB::raw('count(*) as count'),
                DB::raw('avg(response_time) as avg_response_time')
            )
            ->groupBy('severity')
            ->get();
    }

    /**
     * Get checkpoint compliance.
     */
    private function getCheckpointCompliance($dateRange)
    {
        return ZoneCheckpoint::with(['scans' => function($query) use ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }])
        ->get()
        ->map(function($checkpoint) {
            return [
                'checkpoint' => $checkpoint->name,
                'total_scans' => $checkpoint->scans->count(),
                'missed_scans' => $checkpoint->getMissedScans(),
                'compliance_rate' => $checkpoint->getComplianceRate(),
            ];
        });
    }

    /**
     * Get patrol efficiency.
     */
    private function getPatrolEfficiency($dateRange)
    {
        return SecurityShift::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'completed')
            ->with(['checkpoints', 'incidents'])
            ->get()
            ->map(function($shift) {
                return [
                    'shift_id' => $shift->id,
                    'team' => $shift->team->name,
                    'zone' => $shift->zone->name,
                    'checkpoints_covered' => $shift->checkpoints->count(),
                    'incidents_handled' => $shift->incidents->count(),
                    'duration' => $shift->getDuration(),
                    'efficiency_score' => $shift->calculateEfficiencyScore(),
                ];
            });
    }

    /**
     * Calculate checkpoint compliance.
     */
    private function calculateCheckpointCompliance()
    {
        $checkpoints = ZoneCheckpoint::with('scans')->get();
        $totalRequired = 0;
        $totalCompleted = 0;

        foreach ($checkpoints as $checkpoint) {
            $required = $checkpoint->getRequiredScans();
            $completed = $checkpoint->scans->count();
            
            $totalRequired += $required;
            $totalCompleted += $completed;
        }

        return $totalRequired > 0 ? ($totalCompleted / $totalRequired) * 100 : 0;
    }

    /**
     * Calculate team checkpoint compliance.
     */
    private function calculateTeamCheckpointCompliance($team)
    {
        $totalRequired = 0;
        $totalCompleted = 0;

        foreach ($team->shifts as $shift) {
            $required = $shift->getRequiredCheckpoints();
            $completed = $shift->checkpoints->count();
            
            $totalRequired += $required;
            $totalCompleted += $completed;
        }

        return $totalRequired > 0 ? ($totalCompleted / $totalRequired) * 100 : 0;
    }

    /**
     * Get date range from request.
     */
    private function getDateRange(Request $request)
    {
        $end = Carbon::now();
        $start = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : $end->copy()->subDays(30);

        if ($request->filled('end_date')) {
            $end = Carbon::parse($request->input('end_date'));
        }

        return [
            'start' => $start->startOfDay(),
            'end' => $end->endOfDay(),
        ];
    }
} 