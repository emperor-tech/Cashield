<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\Rank;
use App\Models\Report;
use App\Notifications\AchievementUnlocked;
use App\Notifications\RankUpNotification;
use Carbon\Carbon;

class GamificationService
{
    protected $pointValues = [
        'report_submission' => 10,
        'high_priority_report' => 20,
        'emergency_response' => 15,
        'comment_added' => 5,
        'report_resolved' => 25,
        'quick_response' => 30, // Response within 5 minutes
        'accurate_report' => 15, // Report verified by security
        'helpful_update' => 10
    ];

    protected $badges = [
        'first_report' => [
            'name' => 'First Report',
            'description' => 'Submitted your first report',
            'icon' => '🎯'
        ],
        'vigilant_reporter' => [
            'name' => 'Vigilant Reporter',
            'description' => 'Submitted 5 reports',
            'icon' => '🥈'
        ],
        'safety_expert' => [
            'name' => 'Safety Expert',
            'description' => 'Submitted 25 reports',
            'icon' => '🥇'
        ],
        'quick_responder' => [
            'name' => 'Quick Responder',
            'description' => 'Responded to an emergency within 5 minutes',
            'icon' => '⚡'
        ],
        'life_saver' => [
            'name' => 'Life Saver',
            'description' => 'Successfully handled a high-priority emergency',
            'icon' => '💖'
        ],
        'night_watch' => [
            'name' => 'Night Watch',
            'description' => 'Reported incidents during night hours (10 PM - 5 AM)',
            'icon' => '🌙'
        ],
        'team_player' => [
            'name' => 'Team Player',
            'description' => 'Collaborated on 10 incident responses',
            'icon' => '🤝'
        ],
        'accuracy_star' => [
            'name' => 'Accuracy Star',
            'description' => 'Had 10 reports verified as accurate',
            'icon' => '⭐'
        ],
        'campus_guardian' => [
            'name' => 'Campus Guardian',
            'description' => 'Earned 1000 points in safety contributions',
            'icon' => '🛡️'
        ],
        'first_aid_hero' => [
            'name' => 'First Aid Hero',
            'description' => 'Responded to 5 medical emergencies',
            'icon' => '🏥'
        ]
    ];

    public function awardPoints(User $user, string $action, $data = null)
    {
        if (!isset($this->pointValues[$action])) {
            return 0;
        }

        $points = $this->pointValues[$action];

        // Bonus points for special conditions
        if ($action === 'report_submission' && $this->isNightTimeReport($data)) {
            $points += 10;
        }

        $previousPoints = $user->safety_points;
        $user->increment('safety_points', $points);
        
        // Check for rank up
        $this->checkRankProgress($user);
        
        // Check for point-based achievements
        $this->checkPointBasedAchievements($user);
        
        return $points;
    }

    public function checkRankProgress(User $user)
    {
        $currentRank = Rank::getCurrentRank($user->safety_points);
        $nextRank = Rank::getNextRank($user->safety_points);

        if ($nextRank && $user->safety_points >= $nextRank->required_points) {
            $user->notify(new RankUpNotification($nextRank));
        }

        return [
            'current' => $currentRank,
            'next' => $nextRank,
            'progress' => $currentRank ? $currentRank->progress_to_next : 0
        ];
    }

    public function checkBadges(User $user, string $trigger, $data = null)
    {
        $earnedBadges = [];

        switch ($trigger) {
            case 'report_submitted':
                $reportCount = $user->reports()->count();
                
                if ($reportCount === 1) {
                    $earnedBadges[] = $this->awardBadge($user, 'first_report');
                }
                if ($reportCount === 5) {
                    $earnedBadges[] = $this->awardBadge($user, 'vigilant_reporter');
                }
                if ($reportCount === 25) {
                    $earnedBadges[] = $this->awardBadge($user, 'safety_expert');
                }
                if ($this->isNightTimeReport($data)) {
                    $earnedBadges[] = $this->awardBadge($user, 'night_watch');
                }
                break;

            case 'emergency_response':
                if ($this->isQuickResponse($data)) {
                    $earnedBadges[] = $this->awardBadge($user, 'quick_responder');
                }
                if ($data->report->severity === 'high') {
                    $earnedBadges[] = $this->awardBadge($user, 'life_saver');
                }
                break;

            case 'report_verified':
                $verifiedCount = $user->reports()->where('verified', true)->count();
                if ($verifiedCount >= 10) {
                    $earnedBadges[] = $this->awardBadge($user, 'accuracy_star');
                }
                break;
        }

        return array_filter($earnedBadges);
    }

    protected function awardBadge(User $user, string $badgeKey)
    {
        if (!isset($this->badges[$badgeKey])) {
            return null;
        }

        $badge = Badge::firstOrCreate(
            ['name' => $this->badges[$badgeKey]['name']],
            [
                'description' => $this->badges[$badgeKey]['description'],
                'icon' => $this->badges[$badgeKey]['icon']
            ]
        );

        if (!$user->badges->contains($badge->id)) {
            $user->badges()->attach($badge->id);
            
            // Calculate points for this badge
            $points = $this->getPointsForBadge($badgeKey);
            
            // Award points
            if ($points > 0) {
                $user->increment('safety_points', $points);
            }

            // Send notification
            $user->notify(new AchievementUnlocked([
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon
            ], $points));

            return $badge;
        }

        return null;
    }

    protected function getPointsForBadge($badgeKey)
    {
        $pointsMap = [
            'first_report' => 50,
            'vigilant_reporter' => 100,
            'safety_expert' => 250,
            'quick_responder' => 150,
            'life_saver' => 300,
            'night_watch' => 200,
            'team_player' => 200,
            'accuracy_star' => 250,
            'campus_guardian' => 500,
            'first_aid_hero' => 300
        ];

        return $pointsMap[$badgeKey] ?? 100;
    }

    protected function isNightTimeReport($report)
    {
        if (!$report || !$report->created_at) {
            return false;
        }

        $hour = $report->created_at->hour;
        return $hour >= 22 || $hour <= 5;
    }

    protected function isQuickResponse($response)
    {
        if (!$response || !$response->created_at || !$response->report) {
            return false;
        }

        $responseTime = $response->created_at->diffInMinutes($response->report->created_at);
        return $responseTime <= 5;
    }

    protected function checkPointBasedAchievements(User $user)
    {
        if ($user->safety_points >= 1000) {
            $this->awardBadge($user, 'campus_guardian');
        }
    }

    public function getUserStats(User $user)
    {
        $rankInfo = $this->checkRankProgress($user);
        
        $stats = [
            'total_points' => $user->safety_points,
            'badges_earned' => $user->badges()->count(),
            'reports_submitted' => $user->reports()->count(),
            'reports_verified' => $user->reports()->where('verified', true)->count(),
            'emergency_responses' => $user->emergencyResponses()->count(),
            'average_response_time' => $this->calculateAverageResponseTime($user),
            'most_active_areas' => $this->getMostActiveAreas($user),
            'contribution_streak' => $this->calculateContributionStreak($user),
            'current_rank' => $rankInfo['current'],
            'next_rank' => $rankInfo['next'],
            'rank_progress' => $rankInfo['progress']
        ];

        return $stats;
    }

    protected function calculateAverageResponseTime(User $user)
    {
        return $user->emergencyResponses()
            ->whereHas('report')
            ->get()
            ->avg(function ($response) {
                return $response->created_at->diffInMinutes($response->report->created_at);
            });
    }

    protected function getMostActiveAreas(User $user)
    {
        return $user->reports()
            ->select('location', \DB::raw('count(*) as count'))
            ->groupBy('location')
            ->orderByDesc('count')
            ->limit(3)
            ->get();
    }

    protected function calculateContributionStreak(User $user)
    {
        $activities = $user->reports()
            ->select('created_at')
            ->union($user->emergencyResponses()->select('created_at'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('created_at');

        if ($activities->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = Carbon::now()->startOfDay();
        $lastActivity = $activities->first()->startOfDay();

        if ($currentDate->diffInDays($lastActivity) > 1) {
            return 0;
        }

        foreach ($activities as $activity) {
            $activityDate = $activity->startOfDay();
            if ($lastActivity->diffInDays($activityDate) <= 1) {
                $streak++;
                $lastActivity = $activityDate;
            } else {
                break;
            }
        }

        return $streak;
    }
}