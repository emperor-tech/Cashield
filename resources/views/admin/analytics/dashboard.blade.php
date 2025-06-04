@extends('layouts.admin')

@section('page_title', 'Admin Dashboard Analytics')

@section('content')
<div class="admin-main">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Admin Dashboard Analytics</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Overview of system analytics and performance.</p>
            </div>
        </div>

        <!-- Overall Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Reports Card -->
            <div class="admin-card p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Reports</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_reports'] }}</p>
                </div>
                <i class="fas fa-file-alt text-3xl text-blue-500"></i>
            </div>

            <!-- Reports Today Card -->
            <div class="admin-card p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Reports Today</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['reports_today'] }}</p>
                </div>
                <i class="fas fa-calendar-day text-3xl text-green-500"></i>
            </div>

            <!-- Active Teams Card -->
            <div class="admin-card p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Teams</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_teams'] }}</p>
                </div>
                <i class="fas fa-shield-alt text-3xl text-purple-500"></i>
            </div>

            <!-- Pending Reports Card -->
            <div class="admin-card p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Reports</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['pending_reports'] }}</p>
                </div>
                <i class="fas fa-clock text-3xl text-red-500"></i>
            </div>
        </div>

        <!-- Performance Metrics and Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Performance Metrics -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Metrics</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Response Time (minutes)</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ round($performance['average_response_time'] ?? 0, 2) }} min</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Checkpoint Compliance</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ round($performance['checkpoint_compliance'] ?? 0, 2) }}%</dd>
                    </div>
                    {{-- Calculate total completed shifts and incidents handled --}}
                    @php
                        $totalCompletedShifts = 0;
                        $totalIncidentsHandled = 0;
                        foreach ($performance['team_performance'] as $teamPerf) {
                            $totalCompletedShifts += $teamPerf['completed_shifts'] ?? 0;
                            $totalIncidentsHandled += $teamPerf['incidents_handled'] ?? 0;
                        }
                    @endphp

                    {{-- Display Total Completed Shifts --}}
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Completed Shifts (This Month)</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $totalCompletedShifts }}</dd>
                    </div>

                    {{-- Display Total Incidents Handled --}}
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Incidents Handled (This Month)</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $totalIncidentsHandled }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Daily Trends Chart -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Reports & Incidents (Last 7 Days)</h3>
                <canvas id="dailyTrendsChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Category Distribution Chart -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Category Distribution</h3>
                @if($trends['categories']->isNotEmpty())
                <canvas id="categoryDistributionChart"></canvas>
                @else
                <p class="text-center text-gray-500 dark:text-gray-400">No category data available.</p>
                @endif
            </div>

            <!-- Severity Distribution Chart -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Severity Distribution</h3>
                @if($trends['severity']->isNotEmpty())
                <canvas id="severityDistributionChart"></canvas>
                @else
                <p class="text-center text-gray-500 dark:text-gray-400">No severity data available.</p>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="admin-card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Timestamp</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentActivity as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $activity->user_id ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $activity->action }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 truncate" style="max-width: 300px;">{{ $activity->details }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No recent activity found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Trends Chart
        var dailyTrendsCtx = document.getElementById('dailyTrendsChart').getContext('2d');
        var dailyTrendsData = @json($trends['daily']);
        var dates = dailyTrendsData.map(item => item.date);
        var reportCounts = dailyTrendsData.map(item => item.reports);
        var incidentCounts = dailyTrendsData.map(item => item.incidents);

        new Chart(dailyTrendsCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Reports',
                        data: reportCounts,
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.1
                    },
                    {
                        label: 'Incidents',
                        data: incidentCounts,
                        borderColor: 'rgb(239, 68, 68)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Distribution Chart
        var categoryDistributionCtx = document.getElementById('categoryDistributionChart').getContext('2d');
        var categoryDistributionData = @json($trends['categories']);
        var categories = categoryDistributionData.map(item => item.category);
        var categoryCounts = categoryDistributionData.map(item => item.count);

        new Chart(categoryDistributionCtx, {
            type: 'pie',
            data: {
                labels: categories,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966CC',
                        '#FF9900'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Report Categories'
                    }
                }
            }
        });

        // Severity Distribution Chart
        var severityDistributionCtx = document.getElementById('severityDistributionChart').getContext('2d');
        var severityDistributionData = @json($trends['severity']);
        var severities = severityDistributionData.map(item => item.severity);
        var severityCounts = severityDistributionData.map(item => item.count);

        new Chart(severityDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: severities,
                datasets: [{
                    data: severityCounts,
                    backgroundColor: [
                        '#FF6384', // High
                        '#FFCE56', // Medium
                        '#36A2EB'  // Low
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Report Severity'
                    }
                }
            }
        });
    });
</script>
@endpush 