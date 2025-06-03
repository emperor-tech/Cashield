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
        // Get statistics
        $stats = [
            'total_reports' => Report::count(),
            'total_users' => User::count(),
            'panic_alerts' => Report::where('severity', 'high')->count(),
            'today_reports' => Report::whereDate('created_at', Carbon::today())->count(),
        ];

        // Get severity statistics
        $severityStats = Report::selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Get monthly trend
        $monthlyTrend = Report::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Get recent reports with pagination
        $reports = Report::with(['user'])
            ->latest()
            ->paginate(10);

        return view('admin.index', compact('stats', 'severityStats', 'monthlyTrend', 'reports'));
    }
} 