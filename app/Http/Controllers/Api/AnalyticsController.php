<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IncidentAnalytics;
use App\Models\Report;
use App\Models\SecurityTeam;
use App\Models\CampusZone;
use App\Models\ResponseProtocol;
use App\Models\IncidentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected $analytics;

    public function __construct(IncidentAnalytics $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index(Request $request)
    {
        $filters = [
            'start_date' => Carbon::now()->subDays($request->input('days', 30)),
            'type' => $request->input('type'),
            'location' => $request->input('location')
        ];

        $stats = $this->analytics->getIncidentStats($filters);
        $prevPeriodStats = $this->analytics->getIncidentStats([
            'start_date' => Carbon::now()->subDays($request->input('days', 30) * 2),
            'end_date' => Carbon::now()->subDays($request->input('days', 30))
        ]);

        $overview = $this->calculateOverviewStats($stats, $prevPeriodStats);
        $highRiskAreas = $this->analytics->predictHighRiskAreas();

        return response()->json([
            'overview' => $overview,
            'by_type' => $stats['by_type'],
            'by_location' => $stats['by_location'],
            'response_times' => $stats['response_times'],
            'time_patterns' => $stats['time_patterns'],
            'high_risk_areas' => $highRiskAreas,
            'hotspots' => $stats['hotspots']
        ]);
    }

    private function calculateOverviewStats($current, $previous)
    {
        $calculateTrend = function($current, $previous) {
            if ($previous == 0) return 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        return [
            'total_incidents' => [
                'label' => 'Total Incidents',
                'value' => $current['total_incidents'],
                'color' => 'text-blue-600',
                'trend' => $calculateTrend(
                    $current['total_incidents'],
                    $previous['total_incidents']
                )
            ],
            'high_severity' => [
                'label' => 'High Severity',
                'value' => $current['by_severity']['high'] ?? 0,
                'color' => 'text-red-600',
                'trend' => $calculateTrend(
                    $current['by_severity']['high'] ?? 0,
                    $previous['by_severity']['high'] ?? 0
                )
            ],
            'avg_response' => [
                'label' => 'Avg Response (min)',
                'value' => round(collect($current['response_times'])->avg(), 1),
                'color' => 'text-green-600',
                'trend' => $calculateTrend(
                    collect($current['response_times'])->avg() ?? 0,
                    collect($previous['response_times'])->avg() ?? 0
                ) * -1 // Inverse trend for response time (lower is better)
            ],
            'hotspots' => [
                'label' => 'Active Hotspots',
                'value' => count($current['hotspots']),
                'color' => 'text-orange-600',
                'trend' => $calculateTrend(
                    count($current['hotspots']),
                    count($previous['hotspots'])
                )
            ]
        ];
    }

    /**
     * Get team performance metrics with resolution rates and response times.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function teamPerformance(Request $request)
    {
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now();

        $teams = SecurityTeam::withCount(['assignedReports as total_reports' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }, 'assignedReports as resolved_reports' => function ($query) use ($startDate, $endDate) {
            $query->whereIn('status', ['resolved', 'closed'])
                  ->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        $teamStats = $teams->map(function ($team) use ($startDate, $endDate) {
            // Calculate average response time for this team
            $avgResponseTime = $team->assignedReports()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('response_time')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, response_time)'));

            // Calculate resolution rate
            $resolutionRate = $team->total_reports > 0 
                ? ($team->resolved_reports / $team->total_reports) * 100 
                : 0;

            return [
                'id' => $team->id,
                'name' => $team->name,
                'total_incidents' => $team->total_reports,
                'resolved_incidents' => $team->resolved_reports,
                'resolution_rate' => round($resolutionRate, 1),
                'avg_response_time' => $avgResponseTime ? round($avgResponseTime, 1) : 0,
                'team_members' => $team->members()->count(),
                'incidents_per_member' => $team->members()->count() > 0 
                    ? round($team->total_reports / $team->members()->count(), 1) 
                    : 0
            ];
        });

        return response()->json([
            'teams' => $teamStats,
            'top_performer' => $teamStats->sortByDesc('resolution_rate')->first(),
            'fastest_response' => $teamStats->sortBy('avg_response_time')->first()
        ]);
    }

    /**
     * Get risk assessment for campus zones with incident frequency and severity.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function zoneRiskAssessment(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->subDays(30);

        $zones = CampusZone::withCount(['reports as total_incidents' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])->get();

        $zoneData = $zones->map(function ($zone) use ($startDate) {
            // Get severity distribution
            $severityCounts = $zone->reports()
                ->where('created_at', '>=', $startDate)
                ->select('severity', DB::raw('count(*) as count'))
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray();

            // Calculate risk score based on incident frequency and severity
            $riskScore = 0;
            $riskScore += ($severityCounts['low'] ?? 0) * 1;
            $riskScore += ($severityCounts['medium'] ?? 0) * 3;
            $riskScore += ($severityCounts['high'] ?? 0) * 5;

            // Determine risk level
            $riskLevel = 'low';
            if ($riskScore > 50) $riskLevel = 'high';
            elseif ($riskScore > 20) $riskLevel = 'medium';

            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'total_incidents' => $zone->total_incidents,
                'severity_distribution' => $severityCounts,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'coverage' => $zone->coverage_level ?? 'standard',
            ];
        });

        return response()->json([
            'zones' => $zoneData,
            'high_risk_zones' => $zoneData->where('risk_level', 'high')->values(),
            'risk_distribution' => [
                'low' => $zoneData->where('risk_level', 'low')->count(),
                'medium' => $zoneData->where('risk_level', 'medium')->count(),
                'high' => $zoneData->where('risk_level', 'high')->count(),
            ]
        ]);
    }

    /**
     * Get detailed response time analytics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseTimeAnalytics(Request $request)
    {
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);

        $query = Report::where('created_at', '>=', $startDate)
            ->whereNotNull('response_time');

        // Response time by severity
        $responseBySeverity = $query->clone()
            ->select('severity', 
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, response_time)) as avg_response_time'))
            ->groupBy('severity')
            ->pluck('avg_response_time', 'severity')
            ->map(function($time) {
                return round($time, 1);
            });

        // Response time by time of day (hour)
        $responseByHour = $query->clone()
            ->select(DB::raw('HOUR(created_at) as hour'), 
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, response_time)) as avg_response_time'))
            ->groupBy('hour')
            ->pluck('avg_response_time', 'hour')
            ->map(function($time) {
                return round($time, 1);
            });

        // Response time by day of week
        $responseByDay = $query->clone()
            ->select(DB::raw('WEEKDAY(created_at) as day'), 
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, response_time)) as avg_response_time'))
            ->groupBy('day')
            ->pluck('avg_response_time', 'day')
            ->map(function($time) {
                return round($time, 1);
            });

        // Get protocols and their performance
        $protocolPerformance = ResponseProtocol::with(['reports' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)
                  ->whereNotNull('response_time');
        }])->get()->map(function($protocol) {
            $avgTime = $protocol->reports->avg(function($report) {
                return Carbon::parse($report->created_at)
                    ->diffInMinutes(Carbon::parse($report->response_time));
            });
            
            return [
                'id' => $protocol->id,
                'name' => $protocol->name,
                'avg_response_time' => $avgTime ? round($avgTime, 1) : 0,
                'target_time' => $protocol->target_response_time ?? 30,
                'performance' => $protocol->target_response_time && $avgTime 
                    ? round(($protocol->target_response_time / $avgTime) * 100, 1) 
                    : 0
            ];
        });

        return response()->json([
            'overall_avg_response' => $query->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, response_time)')),
            'by_severity' => $responseBySeverity,
            'by_hour' => $responseByHour,
            'by_day' => $responseByDay,
            'protocol_performance' => $protocolPerformance
        ]);
    }

    /**
     * Detect patterns and trends in incident data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function patternDetection(Request $request)
    {
        $days = $request->input('days', 90);
        $startDate = Carbon::now()->subDays($days);

        // Time-based patterns
        $hourlyDistribution = Report::where('created_at', '>=', $startDate)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        $dailyDistribution = Report::where('created_at', '>=', $startDate)
            ->select(DB::raw('WEEKDAY(created_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        // Monthly trend
        $monthlyTrend = Report::where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(DB::raw('YEAR(created_at) as year, MONTH(created_at) as month'), 
                DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'date' => Carbon::createFromDate($item->year, $item->month, 1)->format('Y-m'),
                    'count' => $item->count
                ];
            });

        // Category correlation
        $categoryCorrelations = IncidentCategory::withCount(['reports' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->orderBy('reports_count', 'desc')
        ->limit(5)
        ->get(['id', 'name', 'reports_count']);

        // Detect recurring patterns
        $recurringLocations = Report::where('created_at', '>=', $startDate)
            ->select('location', DB::raw('COUNT(*) as count'))
            ->groupBy('location')
            ->having('count', '>=', 3)
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'hourly_distribution' => $hourlyDistribution,
            'daily_distribution' => $dailyDistribution,
            'monthly_trend' => $monthlyTrend,
            'category_correlations' => $categoryCorrelations,
            'recurring_locations' => $recurringLocations,
            'peak_hours' => $hourlyDistribution->sortDesc()->take(3)->keys()->toArray(),
            'peak_days' => $dailyDistribution->sortDesc()->take(2)->keys()->toArray()
        ]);
    }

    /**
     * Analyze resource utilization and efficiency.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resourceUtilization(Request $request)
    {
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        // Team workload distribution
        $teamWorkload = SecurityTeam::withCount(['assignedReports as total_reports' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->with(['members'])
        ->get()
        ->map(function($team) {
            $memberCount = $team->members->count();
            return [
                'id' => $team->id,
                'name' => $team->name,
                'member_count' => $memberCount,
                'total_reports' => $team->total_reports,
                'reports_per_member' => $memberCount > 0 
                    ? round($team->total_reports / $memberCount, 1) 
                    : 0
            ];
        });

        // Zone coverage analysis
        $zoneCoverage = CampusZone::withCount(['reports as incident_count' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->get()
        ->map(function($zone) {
            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'incident_count' => $zone->incident_count,
                'security_resources' => $zone->security_resources ?? 'standard',
                'area_size' => $zone->area_size ?? 'unknown',
                'coverage_ratio' => $zone->incident_count > 0 
                    ? ($zone->security_level ?? 1) / $zone->incident_count 
                    : 0
            ];
        });

        // Resource allocation recommendations
        $underStaffedZones = $zoneCoverage->where('incident_count', '>', 5)
            ->sortByDesc('incident_count')
            ->values();

        return response()->json([
            'team_workload' => $teamWorkload,
            'zone_coverage' => $zoneCoverage,
            'resource_recommendations' => [
                'understaffed_zones' => $underStaffedZones->take(3),
                'overallocated_teams' => $teamWorkload->sortByDesc('reports_per_member')->take(2),
                'underutilized_teams' => $teamWorkload->sortBy('reports_per_member')->take(2)
            ]
        ]);
    }

    /**
     * Generate predictive analytics for future incident likelihood.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function predictiveAnalytics(Request $request)
    {
        // This would ideally connect to a more sophisticated machine learning model
        // For demonstration, we'll use historical patterns to make simple predictions
        
        // Get historical data for predictions
        $historicalData = $this->analytics->getIncidentStats([
            'start_date' => Carbon::now()->subMonths(6)
        ]);
        
        // Predict high-risk time periods based on historical patterns
        $highRiskTimes = collect($historicalData['time_patterns'])
            ->sortByDesc('count')
            ->take(3)
            ->map(function($item) {
                return [
                    'day' => $item['day'] ?? 'unknown',
                    'hour' => $item['hour'] ?? 'unknown',
                    'likelihood' => $item['count'] / collect($historicalData['time_patterns'])->sum('count')
                ];
            });

        // Predict future incident volume
        $monthlyTrend = Report::where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(DB::raw('YEAR(created_at) as year, MONTH(created_at) as month'), 
                DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Simple linear regression to predict next month
        $nextMonthPrediction = $this->predictNextMonthIncidents($monthlyTrend);
        
        return response()->json([
            'predicted_high_risk_times' => $highRiskTimes,
            'predicted_high_risk_areas' => $this->analytics->predictHighRiskAreas(),
            'next_month_prediction' => [
                'month' => Carbon::now()->addMonth()->format('Y-m'),
                'predicted_incidents' => $nextMonthPrediction
            ],
            'confidence_level' => 'medium', // Would depend on model accuracy
            'prediction_factors' => [
                'historical_patterns',
                'seasonal_variations',
                'location_based_clustering'
            ]
        ]);
    }

    /**
     * Simple prediction method for next month's incidents
     * In a production app, this would use more sophisticated machine learning
     */
    private function predictNextMonthIncidents($monthlyData)
    {
        if ($monthlyData->count() < 2) {
            return $monthlyData->avg('count') ?? 0;
        }
        
        // Simple average of last 3 months with a slight trend adjustment
        $recentMonths = $monthlyData->sortByDesc(function($item) {
            return Carbon::createFromDate($item->year, $item->month, 1)->timestamp;
        })->take(3);
        
        if ($recentMonths->count() >= 2) {
            $latestMonth = $recentMonths->first();
            $previousMonth = $recentMonths->skip(1)->first();
            $trend = ($latestMonth->count - $previousMonth->count) / $previousMonth->count;
            
            // Cap the trend effect
            $trendFactor = max(min($trend, 0.2), -0.2);
            
            return round($recentMonths->avg('count') * (1 + $trendFactor));
        }
        
        return round($recentMonths->avg('count'));
    }

    /**
     * Generate a custom report based on specified parameters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'severity' => 'nullable|array',
            'severity.*' => 'in:low,medium,high',
            'status' => 'nullable|array',
            'status.*' => 'in:open,in_progress,resolved,closed',
            'category_id' => 'nullable|array',
            'category_id.*' => 'integer|exists:incident_categories,id',
            'zone_id' => 'nullable|array',
            'zone_id.*' => 'integer|exists:campus_zones,id',
            'team_id' => 'nullable|array',
            'team_id.*' => 'integer|exists:security_teams,id',
            'metrics' => 'required|array',
            'metrics.*' => 'in:incident_count,response_time,resolution_time,severity_distribution,category_distribution,zone_distribution,team_performance'
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::now()->toDateString();

        $query = Report::whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if (!empty($validated['severity'])) {
            $query->whereIn('severity', $validated['severity']);
        }

        if (!empty($validated['status'])) {
            $query->whereIn('status', $validated['status']);
        }

        if (!empty($validated['category_id'])) {
            $query->whereIn('category_id', $validated['category_id']);
        }

        if (!empty($validated['zone_id'])) {
            $query->whereIn('zone_id', $validated['zone_id']);
        }

        if (!empty($validated['team_id'])) {
            $query->whereIn('assigned_team_id', $validated['team_id']);
        }

        // Prepare report data based on requested metrics
        $reportData = [];
        $metrics = collect($validated['metrics']);

        if ($metrics->contains('incident_count')) {
            $reportData['incident_count'] = [
                'total' => $query->count(),
                'daily_average' => round($query->count() / Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)), 1)
            ];
        }

        if ($metrics->contains('response_time')) {
            $reportData['response_time'] = [
                'average' => $query->clone()->whereNotNull('response_time')
                    ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, response_time)')),
                'by_severity' => $query->clone()->whereNotNull('response_time')
                    ->select('severity', DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, response_time)) as avg_time'))
                    ->groupBy('severity')
                    ->pluck('avg_time', 'severity')
            ];
        }

        if ($metrics->contains('resolution_time')) {
            $reportData['resolution_time'] = [
                'average' => $query->clone()->whereNotNull('resolution_time')
                    ->avg('resolution_time'),
                'by_severity' => $query->clone()->whereNotNull('resolution_time')
                    ->select('severity', DB::raw('AVG(resolution_time) as avg_time'))
                    ->groupBy('severity')
                    ->pluck('avg_time', 'severity')
            ];
        }

        if ($metrics->contains('severity_distribution')) {
            $reportData['severity_distribution'] = $query->clone()
                ->select('severity', DB::raw('COUNT(*) as count'))
                ->groupBy('severity')
                ->pluck('count', 'severity');
        }

        if ($metrics->contains('category_distribution')) {
            $reportData['category_distribution'] = $query->clone()
                ->with('category')
                ->get()
                ->groupBy('category.name')
                ->map(function($group) {
                    return $group->count();
                });
        }

        if ($metrics->contains('zone_distribution')) {
            $reportData['zone_distribution'] = $query->clone()
                ->with('zone')
                ->get()
                ->groupBy('zone.name')
                ->map(function($group) {
                    return $group->count();
                });
        }

        if ($metrics->contains('team_performance')) {
            $reportData['team_performance'] = SecurityTeam::withCount(['assignedReports as total_reports' => function($q) use ($query) {
                $q->whereIn('id', $query->clone()->pluck('id'));
            }, 'assignedReports as resolved_reports' => function ($q) use ($query) {
                $q->whereIn('status', ['resolved', 'closed'])
                  ->whereIn('id', $query->clone()->pluck('id'));
            }])->get()->map(function($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'total_reports' => $team->total_reports,
                    'resolved_reports' => $team->resolved_reports,
                    'resolution_rate' => $team->total_reports > 0 
                        ? round(($team->resolved_reports / $team->total_reports) * 100, 1)
                        : 0
                ];
            });
        }

        return response()->json([
            'report_title' => 'Custom Security Report',
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'filters' => array_filter([
                'severity' => $validated['severity'] ?? null,
                'status' => $validated['status'] ?? null,
                'categories' => !empty($validated['category_id']) 
                    ? IncidentCategory::whereIn('id', $validated['category_id'])->pluck('name') 
                    : null,
                'zones' => !empty($validated['zone_id'])
                    ? CampusZone::whereIn('id', $validated['zone_id'])->pluck('name')
                    : null,
                'teams' => !empty($validated['team_id'])
                    ? SecurityTeam::whereIn('id', $validated['team_id'])->pluck('name')
                    : null
            ]),
            'data' => $reportData,
            'generated_at' => Carbon::now()->toDateTimeString()
        ]);
    }
}
