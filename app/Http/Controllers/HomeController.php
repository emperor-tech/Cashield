<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function home()
    {
        $user = Auth::user();
        
        // Redirect to home page if user is not authenticated
        if (!$user) {
            return view('home');
        }
        
        // Initialize query builder for user reports
        $userReports = Report::where('user_id', $user->id);

        // Basic statistics
        $stats = [
            'total_reports' => $userReports->count(),
            'open_reports' => $userReports->where('status', 'open')->count(),
            'in_progress_reports' => $userReports->where('status', 'in_progress')->count(),
            'resolved_reports' => $userReports->where('status', 'resolved')->count(),
            'high_priority' => $userReports->where('severity', 'high')->count(),
            'medium_priority' => $userReports->where('severity', 'medium')->count(),
            'low_priority' => $userReports->where('severity', 'low')->count(),
        ];

        // Recent reports with more details
        $recentReports = Report::where('user_id', $user->id)
            ->with(['comments', 'assignedUser', 'assignedTeam'])
            ->withCount('comments')
            ->latest()
            ->take(5)
            ->get();

        // Monthly trend for the current year
        $monthlyReports = Report::where('user_id', $user->id)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Response time analytics
        $responseAnalytics = Report::where('user_id', $user->id)
            ->whereNotNull('response_time')
            ->selectRaw('
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time
            ')
            ->first();

        // Status transition timeline
        $statusTimeline = Report::where('user_id', $user->id)
            ->whereNotNull('status_history')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'history' => $report->status_history,
                    'current_status' => $report->status
                ];
            });

        // Weekly report distribution
        $weeklyDistribution = DB::table('reports')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subWeeks(2))
            ->whereNull('deleted_at')
            ->selectRaw("
                DATE(created_at) as report_date,
                COUNT(*) as total_count,
                SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_count,
                SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_count,
                SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_count
            ")
            ->groupBy('report_date')
            ->orderBy('report_date', 'desc')
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentReports',
            'monthlyReports',
            'responseAnalytics',
            'statusTimeline',
            'weeklyDistribution'
        ));
    }
} 