@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Dashboard Overview</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Welcome back, {{ auth()->user()->name }}! Here's what's happening today.</p>
        </div>
        <div class="flex items-center gap-3">
            <div x-data="{ open: false }" class="relative">
                <button type="button" class="admin-btn-secondary" @click="open = !open">
                    <i class="fas fa-download mr-2"></i>
                    Export
                    <i class="fas fa-chevron-down ml-1"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded shadow-lg z-50">
                    <a href="{{ route('admin.reports.export.csv') }}" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </a>
                    <a href="{{ route('admin.reports.export.pdf') }}" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                </div>
            </div>
            <a href="{{ route('admin.reports.create') }}" class="admin-btn-primary">
                <i class="fas fa-plus mr-2"></i>
                New Report
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="admin-grid">
        <!-- Total Reports -->
        <div class="stats-card">
            <div class="stats-content">
                <h3 class="stats-title">Total Reports</h3>
                <p class="stats-value">{{ $totalReports ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @if(isset($reportIncrease) && $reportIncrease > 0)
                        <span class="text-green-600 dark:text-green-400">
                            <i class="fas fa-arrow-up"></i> {{ $reportIncrease }}%
                        </span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">No change</span>
                    @endif
                    <span class="ml-1">from last month</span>
                </p>
            </div>
            <div class="stats-icon blue">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>

        <!-- Total Users -->
        <div class="stats-card">
            <div class="stats-content">
                <h3 class="stats-title">Total Users</h3>
                <p class="stats-value">{{ $totalUsers ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @if(isset($userIncrease) && $userIncrease > 0)
                        <span class="text-green-600 dark:text-green-400">
                            <i class="fas fa-arrow-up"></i> {{ $userIncrease }}%
                        </span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">No change</span>
                    @endif
                    <span class="ml-1">from last month</span>
                </p>
            </div>
            <div class="stats-icon green">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="stats-card">
            <div class="stats-content">
                <h3 class="stats-title">Active Alerts</h3>
                <p class="stats-value">{{ $activeAlerts ?? 0 }}</p>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Requires immediate attention
                </p>
            </div>
            <div class="stats-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

        <!-- Today's Reports -->
        <div class="stats-card">
            <div class="stats-content">
                <h3 class="stats-title">Today's Reports</h3>
                <p class="stats-value">{{ $todayReports ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-clock mr-1"></i>
                    Updated just now
                </p>
            </div>
            <div class="stats-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="admin-grid-2">
        <!-- Reports Trend -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Reports Trend</h3>
                <div class="flex items-center gap-4">
                    <select class="admin-select" id="reportTrendRange">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
            </div>
            <div class="h-[300px]">
                <canvas id="reportsTrendChart"></canvas>
            </div>
        </div>

        <!-- Reports by Category -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Reports by Category</h3>
                <button class="admin-btn-icon" id="refreshCategories">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="h-[300px]">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="admin-card">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Reports</h3>
                <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary text-sm">
                    View All
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Reported By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReports ?? [] as $report)
                    <tr>
                        <td>#{{ $report->id }}</td>
                        <td class="max-w-xs truncate">{{ Str::limit($report->description ?? 'No description', 50) }}</td>
                        <td>
                            <span class="admin-badge admin-badge-{{ $report->category?->color ?? 'blue' }}">
                                {{ $report->category?->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <span class="admin-badge admin-badge-{{ $report->status_color }}">
                                {{ $report->status ?? 'unknown' }}
                            </span>
                        </td>
                        <td>{{ $report->user?->name ?? 'Anonymous' }}</td>
                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.reports.show', $report) }}" 
                                   class="admin-btn-icon">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" 
                                    class="admin-btn-icon" 
                                    @click.prevent="openAssignModal({{ $report->id }})">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-4xl text-gray-400 dark:text-gray-600 mb-3"></i>
                                <p class="text-gray-500 dark:text-gray-400">No recent reports found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div x-data="{ show: false, reportId: null }" x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Assign Report</h3>
        <form method="POST" action="{{ route('admin.reports.assign', $report ) }}">
            @csrf
            <input type="hidden" name="report_id" :value="reportId"> 
            <div class="mb-4">
                <label class="form-label">Assign To</label>
                <select name="assigned_to" class="admin-select w-full" required>
                    <option value="">Select...</option>
                    @foreach($teams ?? [] as $team)
                        <option value="team:{{ $team->id }}">Team: {{ $team->name }}</option>
                    @endforeach
                    @foreach($users ?? [] as $user)
                        <option value="user:{{ $user->id }}">User: {{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="admin-btn-secondary" @click="show = false">Cancel</button>
                <button type="submit" class="admin-btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reports Trend Chart
    const trendCtx = document.getElementById('reportsTrendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: @json($trendLabels ?? []),
            datasets: [{
                label: 'Reports',
                data: @json($trendData ?? []),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)'
                    },
                    ticks: {
                        color: '#9CA3AF'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9CA3AF'
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($categoryLabels ?? []),
            datasets: [{
                data: @json($categoryData ?? []),
                backgroundColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#9CA3AF',
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });

    // Handle trend range change
    document.getElementById('reportTrendRange').addEventListener('change', function(e) {
        fetch(`/admin/reports/trend/${e.target.value}`)
            .then(response => response.json())
            .then(data => {
                trendChart.data.labels = data.labels;
                trendChart.data.datasets[0].data = data.data;
                trendChart.update();
            });
    });

    // Handle category refresh
    document.getElementById('refreshCategories').addEventListener('click', function() {
        this.classList.add('animate-spin');
        setTimeout(() => {
            this.classList.remove('animate-spin');
        }, 1000);
    });
});

function openAssignModal(reportId) {
    const modal = document.querySelector('[x-data*="show: false, reportId: null"]');
    if (modal) {
        modal.__x.$data.show = true;
        modal.__x.$data.reportId = reportId;
    }
}
</script>
@endpush
