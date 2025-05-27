<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'required_points',
        'description'
    ];

    public static function getCurrentRank($points)
    {
        return static::where('required_points', '<=', $points)
            ->orderBy('required_points', 'desc')
            ->first();
    }

    public static function getNextRank($points)
    {
        return static::where('required_points', '>', $points)
            ->orderBy('required_points', 'asc')
            ->first();
    }

    public function getProgressToNextAttribute()
    {
        $nextRank = self::getNextRank($this->required_points);
        if (!$nextRank) {
            return 100;
        }

        $pointsNeeded = $nextRank->required_points - $this->required_points;
        $pointsGained = auth()->user()->safety_points - $this->required_points;
        
        return min(100, round(($pointsGained / $pointsNeeded) * 100));
    }
}