<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IncidentAnalytics;
use Illuminate\Http\Request;
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
}