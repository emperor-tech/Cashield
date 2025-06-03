@extends('layouts.admin')

@section('page_title', 'Report History')

@section('content')
<div class="space-y-6">
    <!-- Report Info -->
    <div class="admin-card p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="p-3 rounded-full 
                    @if($report->severity === 'high')
                        bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300
                    @elseif($report->severity === 'medium')
                        bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300
                    @else
                        bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300
                    @endif">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Report #{{ $report->id }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Submitted {{ $report->created_at->diffForHumans() }} by 
                        @if($report->anonymous)
                            <span class="italic">Anonymous</span>
                        @else
                            {{ $report->user->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.report.show', $report) }}" class="admin-btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Report
                </a>
            </div>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="admin-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">History Timeline</h3>
        
        <div class="space-y-6">
            @forelse($history as $log)
                <div class="flex">
                    <!-- Timeline Line -->
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        @if(!$loop->last)
                            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700"></div>
                        @endif
                    </div>

                    <!-- Event Details -->
                    <div class="flex-1 ml-4">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $log->user->name }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full
                                    @switch($log->action)
                                        @case('update_status')
                                            bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                                            @break
                                        @case('resolve_report')
                                            bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                                            @break
                                        @case('assign_report')
                                            bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                                            @break
                                        @case('add_comment')
                                            bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300
                                            @break
                                        @default
                                            bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300
                                    @endswitch">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </div>

                            <div class="text-gray-600 dark:text-gray-300">
                                @switch($log->action)
                                    @case('update_status')
                                        Changed status from 
                                        <span class="font-medium">{{ ucfirst($log->details['old_status']) }}</span> 
                                        to 
                                        <span class="font-medium">{{ ucfirst($log->details['new_status']) }}</span>
                                        @break
                                    @case('resolve_report')
                                        Marked report as resolved
                                        @break
                                    @case('assign_report')
                                        Assigned report to 
                                        @if($log->details['assignee_type'] === 'user')
                                            user #{{ $log->details['assignee_id'] }}
                                        @else
                                            team #{{ $log->details['assignee_id'] }}
                                        @endif
                                        @break
                                    @case('add_comment')
                                        Added comment: "{{ $log->details['comment'] }}"
                                        @break
                                    @default
                                        {{ json_encode($log->details) }}
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400">
                    No history available for this report.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection 