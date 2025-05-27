<?php

namespace App\Services;

use App\Models\Report;
use App\Models\EmergencyResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncidentAnalytics
{
    public function getIncidentStats(array $filters = [])
    {
        $query = Report::query();

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return [
            'total_incidents' => $query->count(),
            'by_severity' => $this->getIncidentsBySeverity($query),
            'by_type' => $this->getIncidentsByType($query),
            'by_location' => $this->getIncidentsByLocation($query),
            'response_times' => $this->getAverageResponseTimes($query),
            'time_patterns' => $this->getTimePatterns($query),
            'hotspots' => $this->getHotspots($query),
        ];
    }

    private function getIncidentsBySeverity($query)
    {
        return $query->clone()
            ->select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->get()
            ->pluck('count', 'severity');
    }

    private function getIncidentsByType($query)
    {
        return $query->clone()
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();
    }

    private function getIncidentsByLocation($query)
    {
        return $query->clone()
            ->select('location', 
                    'location_lat', 
                    'location_lng',
                    DB::raw('count(*) as incident_count'))
            ->groupBy('location', 'location_lat', 'location_lng')
            ->having('incident_count', '>', 1)
            ->orderByDesc('incident_count')
            ->get();
    }

    private function getAverageResponseTimes($query)
    {
        return $query->clone()
            ->join('emergency_responses', 'reports.id', '=', 'emergency_responses.report_id')
            ->select(
                'reports.severity',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, reports.created_at, emergency_responses.created_at)) as avg_response_time')
            )
            ->groupBy('severity')
            ->get()
            ->pluck('avg_response_time', 'severity');
    }

    private function getTimePatterns($query)
    {
        return $query->clone()
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                DB::raw('count(*) as count')
            )
            ->groupBy('hour', 'day_of_week')
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($dayData) {
                return $dayData->pluck('count', 'hour');
            });
    }

    private function getHotspots($query)
    {
        return $query->clone()
            ->select(
                'location_lat',
                'location_lng',
                DB::raw('count(*) as incident_count'),
                DB::raw('GROUP_CONCAT(DISTINCT type) as incident_types')
            )
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->groupBy('location_lat', 'location_lng')
            ->having('incident_count', '>', 2)
            ->orderByDesc('incident_count')
            ->get()
            ->map(function ($hotspot) {
                return [
                    'position' => [
                        'lat' => $hotspot->location_lat,
                        'lng' => $hotspot->location_lng
                    ],
                    'count' => $hotspot->incident_count,
                    'types' => explode(',', $hotspot->incident_types)
                ];
            });
    }

    public function predictHighRiskAreas()
    {
        $historicalData = $this->getIncidentStats([
            'start_date' => Carbon::now()->subMonths(3)
        ]);

        $riskScores = collect($historicalData['by_location'])->map(function ($location) {
            return [
                'location' => $location->location,
                'coordinates' => [
                    'lat' => $location->location_lat,
                    'lng' => $location->location_lng
                ],
                'risk_score' => $this->calculateRiskScore($location),
                'incident_types' => $this->getCommonIncidentTypes($location->location)
            ];
        })->sortByDesc('risk_score');

        return $riskScores;
    }

    private function calculateRiskScore($location)
    {
        $recentIncidents = Report::where('location', $location->location)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        $score = 0;
        foreach ($recentIncidents as $incident) {
            $score += match($incident->severity) {
                'high' => 5,
                'medium' => 3,
                'low' => 1,
                default => 0
            };

            // Add temporal weight (more recent = higher weight)
            $daysAgo = Carbon::now()->diffInDays($incident->created_at);
            $score += (30 - $daysAgo) / 10;
        }

        return $score;
    }

    private function getCommonIncidentTypes($location)
    {
        return Report::where('location', $location)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(3)
            ->get()
            ->pluck('type');
    }
}