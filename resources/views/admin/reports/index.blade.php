@extends('layouts.admin')

@section('page_title', 'Report Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Reports</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage and monitor all incident reports</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.export.csv') }}" 
               class="admin-btn-secondary">
                <i class="fas fa-file-csv mr-2"></i>
                Export CSV
            </a>
            <a href="{{ route('admin.reports.export.pdf') }}" 
               class="admin-btn-secondary">
                <i class="fas fa-file-pdf mr-2"></i>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filter">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="admin-filter-group">
            <div class="flex-1 min-w-[200px]">
                <div class="admin-search">
                    <div class="admin-search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="admin-search-input"
                        placeholder="Search reports...">
                </div>
            </div>

            <div class="w-full sm:w-auto">
                <select name="severity" class="admin-select">
                    <option value="">All Severities</option>
                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <select name="status" class="admin-select">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>

            <button type="submit" class="admin-btn-secondary">
                <i class="fas fa-filter mr-2"></i>
                Filter
            </button>

            @if(request()->hasAny(['search', 'severity', 'status']))
                <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Reports Table -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reporter</th>
                        <th>Location</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                #{{ $report->id }}
                            </td>
                            <td>
                                @if($report->anonymous)
                                    <span class="text-gray-500 dark:text-gray-400 italic">Anonymous</span>
                                @else
                                    <div class="flex items-center">
                                        <img class="h-8 w-8 rounded-full mr-2" 
                                             src="https://ui-avatars.com/api/?name={{ urlencode($report->user->name) }}" 
                                             alt="{{ $report->user->name }}">
                                        <span>{{ $report->user->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                    {{ $report->location }}
                                </div>
                            </td>
                            <td>
                                <span class="admin-badge
                                    @if($report->severity === 'high')
                                        admin-badge-red
                                    @elseif($report->severity === 'medium')
                                        admin-badge-yellow
                                    @else
                                        admin-badge-green
                                    @endif">
                                    {{ ucfirst($report->severity) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.report.status', $report) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" 
                                        class="admin-select text-sm">
                                        <option value="open" @if($report->status=='open')selected @endif>Open</option>
                                        <option value="in_progress" @if($report->status=='in_progress')selected @endif>In Progress</option>
                                        <option value="resolved" @if($report->status=='resolved')selected @endif>Resolved</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                {{ $report->created_at->format('M d, Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.report.show', $report) }}" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.reports.export.pdf', ['id' => $report->id]) }}" 
                                       class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300"
                                       title="Export PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No reports found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($reports->hasPages())
            <div class="admin-pagination">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 