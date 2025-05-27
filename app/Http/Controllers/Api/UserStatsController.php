<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserStatsController extends Controller
{
    protected $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    public function stats(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $stats = $this->gamification->getUserStats($user);

        return response()->json([
            'stats' => $stats
        ]);
    }

    public function achievements(Request $request, User $user)
    {
        $this->authorize('view', $user);

        // Get recent achievements (last 30 days)
        $achievements = $user->badges()
            ->wherePivot('created_at', '>=', Carbon::now()->subDays(30))
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'title' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'points' => $this->getPointsForBadge($badge->name),
                    'earned_at' => $badge->pivot->created_at->diffForHumans()
                ];
            });

        return response()->json([
            'achievements' => $achievements
        ]);
    }

    protected function getPointsForBadge($badgeName)
    {
        $pointsMap = [
            'First Report' => 50,
            'Vigilant Reporter' => 100,
            'Safety Expert' => 250,
            'Quick Responder' => 150,
            'Life Saver' => 300,
            'Night Watch' => 200,
            'Team Player' => 200,
            'Accuracy Star' => 250,
            'Campus Guardian' => 500,
            'First Aid Hero' => 300
        ];

        return $pointsMap[$badgeName] ?? 100;
    }
}