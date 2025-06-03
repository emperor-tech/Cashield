<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get current month and previous month dates
        $now = Carbon::now();
        $currentMonth = $now->startOfMonth();
        $lastMonth = $now->copy()->subMonth();

        // Get reports count for current and previous month
        $currentMonthReports = Report::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();
        $lastMonthReports = Report::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        // Calculate percentage increase
        $reportIncrease = $lastMonthReports > 0 
            ? round((($currentMonthReports - $lastMonthReports) / $lastMonthReports) * 100, 1)
            : 0;

        // Get users count for current and previous month
        $currentMonthUsers = User::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();
        $lastMonthUsers = User::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        // Calculate percentage increase for users
        $userIncrease = $lastMonthUsers > 0 
            ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1)
            : 0;

        // Get active alerts (high severity, unresolved reports)
        $activeAlerts = Report::where('severity', 'high')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // Get today's reports
        $todayReports = Report::whereDate('created_at', Carbon::today())->count();

        // Get severity statistics
        $severityStats = Report::selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Get monthly trend data for the last 6 months
        $trendData = collect();
        $trendLabels = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Report::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $trendData->push($count);
            $trendLabels->push($date->format('M Y'));
        }

        // Get category statistics
        $categoryStats = Report::with('category')
            ->whereHas('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($group) {
                return $group->count();
            });

        $categoryLabels = $categoryStats->keys()->toArray();
        $categoryData = $categoryStats->values()->toArray();

        // Get recent reports with pagination and relationships
        $recentReports = Report::with(['user', 'category'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($report) {
                $report->status_color = match($report->status) {
                    'open' => 'blue',
                    'in_progress' => 'yellow',
                    'resolved' => 'green',
                    'closed' => 'gray',
                    default => 'gray'
                };
                return $report;
            });

        return view('admin.index', [
            'totalReports' => Report::count(),
            'totalUsers' => User::count(),
            'activeAlerts' => $activeAlerts,
            'todayReports' => $todayReports,
            'reportIncrease' => $reportIncrease,
            'userIncrease' => $userIncrease,
            'severityStats' => $severityStats,
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'categoryLabels' => $categoryLabels,
            'categoryData' => $categoryData,
            'recentReports' => $recentReports
        ]);
    }
} 