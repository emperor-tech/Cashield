@extends('layouts.admin')

@section('page_title', 'User Details')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">User Details</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View user information and activity</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-edit mr-2"></i>
                Edit User
            </a>
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                        class="{{ $user->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} inline-flex items-center px-4 py-2 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Information -->
            <div class="admin-card p-6">
                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0">
                        <img class="h-24 w-24 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=96" alt="{{ $user->name }}">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->name }}</h3>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($user->role === 'admin')
                                    bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                @elseif($user->role === 'security')
                                    bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($user->role === 'staff')
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else
                                    bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                        <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-clock mr-1.5"></i>
                            Joined {{ $user->created_at->format('M d, Y') }}
                        </div>
                        <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-circle {{ $user->is_active ? 'text-green-500' : 'text-red-500' }} mr-1.5"></i>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                            @if($user->last_active_at)
                                • Last active {{ $user->last_active_at->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="admin-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Reports</h3>
                        <span class="text-2xl font-semibold text-blue-600 dark:text-blue-400">{{ $activityStats['total_reports'] }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ $activityStats['resolved_reports'] }} resolved
                    </p>
                </div>

                <div class="admin-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Comments</h3>
                        <span class="text-2xl font-semibold text-blue-600 dark:text-blue-400">{{ $activityStats['total_comments'] }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Total comments made
                    </p>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Reports</h3>
                @if($user->reports->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($user->reports->take(5) as $report)
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full
                                        @if($report->severity === 'high')
                                            bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300
                                        @elseif($report->severity === 'medium')
                                            bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300
                                        @else
                                            bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300
                                        @endif">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ Str::limit($report->description, 100) }}
                                        </p>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $report->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-map-marker-alt mr-1.5"></i>
                                            {{ $report->location }}
                                        </span>
                                        <span class="mx-2 text-gray-500 dark:text-gray-400">•</span>
                                        <span class="capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($report->status === 'open')
                                                bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($report->status === 'in_progress')
                                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else
                                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @endif">
                                            {{ $report->status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.report.show', $report) }}" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No reports submitted yet.</p>
                @endif
            </div>

            <!-- Recent Comments -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Comments</h3>
                @if($user->comments->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($user->comments->take(5) as $comment)
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            {{ $comment->comment }}
                                        </p>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $comment->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        On Report #{{ $comment->report_id }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.report.show', $comment->report_id) }}" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                        View Report
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No comments made yet.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Activity Timeline -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Activity Timeline</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($user->auditLogs->take(10) as $log)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                                @if(Str::contains($log->action, 'create'))
                                                    bg-green-100 dark:bg-green-900
                                                @elseif(Str::contains($log->action, 'update'))
                                                    bg-blue-100 dark:bg-blue-900
                                                @elseif(Str::contains($log->action, 'delete'))
                                                    bg-red-100 dark:bg-red-900
                                                @else
                                                    bg-gray-100 dark:bg-gray-900
                                                @endif">
                                                <i class="fas 
                                                    @if(Str::contains($log->action, 'create'))
                                                        fa-plus text-green-600 dark:text-green-300
                                                    @elseif(Str::contains($log->action, 'update'))
                                                        fa-edit text-blue-600 dark:text-blue-300
                                                    @elseif(Str::contains($log->action, 'delete'))
                                                        fa-trash text-red-600 dark:text-red-300
                                                    @else
                                                        fa-circle text-gray-600 dark:text-gray-300
                                                    @endif">
                                                </i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                    @if($log->details)
                                                        - {{ json_encode($log->details) }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                {{ $log->created_at->format('M d, Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No activity recorded yet.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Account Actions -->
            @if($user->id !== auth()->id())
            <div class="admin-card p-6 border-red-200 dark:border-red-900">
                <h3 class="text-sm font-medium text-red-700 dark:text-red-400 mb-3">Account Actions</h3>
                <div class="space-y-4">
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 