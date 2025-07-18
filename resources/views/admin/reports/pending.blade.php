@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pending Reports</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">
                <i class="fas fa-list mr-2"></i>All Reports
            </a>
            <a href="{{ route('admin.reports.export') }}" class="admin-btn-primary">
                <i class="fas fa-download mr-2"></i>Export
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filter mb-6">
        <div class="admin-filter-group">
            <div class="admin-search flex-1">
                <div class="relative">
                    <div class="admin-search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" 
                           placeholder="Search reports..." 
                           class="admin-search-input"
                           id="searchInput">
                </div>
            </div>
            <div class="flex gap-4">
                <select class="admin-select" id="severityFilter">
                    <option value="">All Severities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Severity</th>
                        <th>Reported</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->id }}</td>
                        <td class="max-w-xs truncate">{{ $report->description }}</td>
                        <td>{{ $report->location }}</td>
                        <td>
                            <span class="admin-badge {{ $report->getSeverityColor() }}">
                                {{ ucfirst($report->severity) }}
                            </span>
                        </td>
                        <td>{{ $report->created_at->diffForHumans() }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.reports.show', $report) }}" 
                                   class="admin-btn-icon" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.reports.resolve', $report) }}" 
                                      method="POST" 
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="admin-btn-icon text-green-600 hover:text-green-700 dark:text-green-500 dark:hover:text-green-400" 
                                            title="Mark as Resolved">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                            No pending reports found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($reports->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $reports->links() }}
        </div>
        @endif
    </div>
</div>
@endsection 