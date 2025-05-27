<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'incident_type',
        'severity_level',
        'area_radius',
        'notification_methods',
        'quiet_hours_start',
        'quiet_hours_end',
        'enabled'
    ];

    protected $casts = [
        'notification_methods' => 'array',
        'enabled' => 'boolean',
        'quiet_hours_start' => 'datetime',
        'quiet_hours_end' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shouldNotify(Report $report): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Check severity level
        if ($this->severity_level !== 'all' && $report->severity !== $this->severity_level) {
            return false;
        }

        // Check incident type
        if ($this->incident_type !== 'all' && $report->type !== $this->incident_type) {
            return false;
        }

        // Check quiet hours
        if ($this->quiet_hours_start && $this->quiet_hours_end) {
            $now = now();
            $start = $this->quiet_hours_start->copy()->setDate(
                $now->year, $now->month, $now->day
            );
            $end = $this->quiet_hours_end->copy()->setDate(
                $now->year, $now->month, $now->day
            );
            
            if ($now->between($start, $end)) {
                return false;
            }
        }

        // Check location radius if both coordinates are set
        if ($this->area_radius && $report->location_lat && $report->location_lng) {
            $distance = $this->calculateDistance(
                $report->location_lat,
                $report->location_lng,
                $this->user->last_location_lat,
                $this->user->last_location_lng
            );
            
            if ($distance > $this->area_radius) {
                return false;
            }
        }

        return true;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // Radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}