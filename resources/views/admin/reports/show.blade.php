@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Report Details</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Reports
            </a>
            @if($report->status !== 'resolved')
                <form action="{{ route('admin.reports.resolve', $report) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="admin-btn-primary">
                        <i class="fas fa-check mr-2"></i>Mark as Resolved
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Report Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Details Card -->
            <div class="admin-card p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Incident Report #{{ $report->id }}
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <span class="admin-badge {{ $report->getSeverityColor() }}">
                                {{ ucfirst($report->severity) }} Severity
                            </span>
                            <span class="admin-badge {{ $report->getStatusColor() }}">
                                {{ ucfirst($report->status) }}
                            </span>
                            <span class="admin-badge {{ $report->getPriorityColor() }}">
                                {{ ucfirst($report->priority_level) }} Priority
                            </span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Reported {{ $report->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Description</h3>
                        <p class="text-gray-900 dark:text-white">{{ $report->description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Location</h3>
                            <p class="text-gray-900 dark:text-white">{{ $report->location }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Campus</h3>
                            <p class="text-gray-900 dark:text-white">{{ $report->campus }}</p>
                        </div>
                    </div>

                    @if($report->category)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Category</h3>
                            <p class="text-gray-900 dark:text-white">{{ $report->category->name }}</p>
                        </div>
                        @if($report->subCategory)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Sub-category</h3>
                            <p class="text-gray-900 dark:text-white">{{ $report->subCategory->name }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Evidence and Media -->
            @if($report->evidence_files || $report->media_path)
            <div class="admin-card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Evidence & Media</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @if($report->media_path)
                        <div class="relative aspect-video bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $report->media_path) }}" 
                                 alt="Report Media"
                                 class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    @endif
                    @if($report->evidence_files)
                        @foreach($report->evidence_files as $file)
                        <div class="relative aspect-video bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $file) }}" 
                                 alt="Evidence {{ $loop->iteration }}"
                                 class="absolute inset-0 w-full h-full object-cover">
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endif

            <!-- Resolution Notes -->
            @if($report->resolution_notes)
            <div class="admin-card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resolution Notes</h2>
                <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $report->resolution_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar Information -->
        <div class="space-y-6">
            <!-- Assignment Information -->
            <div class="admin-card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assignment</h2>
                
                @if($report->assignedTeam)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Assigned Team</h3>
                    <p class="text-gray-900 dark:text-white">{{ $report->assignedTeam->name }}</p>
                </div>
                @endif

                @if($report->assignedUser)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Assigned To</h3>
                    <div class="flex items-center">
                        @if($report->assignedUser->avatar)
                            <img src="{{ asset('storage/' . $report->assignedUser->avatar) }}" 
                                 alt="{{ $report->assignedUser->name }}"
                                 class="w-8 h-8 rounded-full mr-2">
                        @endif
                        <span class="text-gray-900 dark:text-white">{{ $report->assignedUser->name }}</span>
                    </div>
                </div>
                @endif

                @if($report->response_time)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Response Time</h3>
                    <p class="text-gray-900 dark:text-white">{{ $report->response_time->diffForHumans($report->created_at) }}</p>
                </div>
                @endif

                @if($report->resolved_at)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Resolved At</h3>
                    <p class="text-gray-900 dark:text-white">{{ $report->resolved_at->diffForHumans() }}</p>
                </div>
                @endif
                
                @if($report->resolution_time)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Resolution Duration</h3>
                    <p class="text-gray-900 dark:text-white">
                        @if($report->resolution_time < 60)
                            {{ $report->resolution_time }} minutes
                        @elseif($report->resolution_time < 1440)
                            {{ floor($report->resolution_time / 60) }} hours {{ $report->resolution_time % 60 }} minutes
                        @else
                            {{ floor($report->resolution_time / 1440) }} days {{ floor(($report->resolution_time % 1440) / 60) }} hours
                        @endif
                    </p>
                </div>
                @endif
            </div>

            <!-- Reporter Information -->
            <div class="admin-card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reporter Information</h2>
                
                @if(!$report->anonymous)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Reported By</h3>
                    <div class="flex items-center">
                        @if($report->user->avatar)
                            <img src="{{ asset('storage/' . $report->user->avatar) }}" 
                                 alt="{{ $report->user->name }}"
                                 class="w-8 h-8 rounded-full mr-2">
                        @endif
                        <span class="text-gray-900 dark:text-white">{{ $report->user->name }}</span>
                    </div>
                </div>
                @else
                <p class="text-gray-500 dark:text-gray-400">Anonymous Report</p>
                @endif

                @if($report->witness_count > 0)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Witnesses</h3>
                    <p class="text-gray-900 dark:text-white">{{ $report->witness_count }} witness(es)</p>
                </div>
                @endif
            </div>

            <!-- Status History -->
            <div class="admin-card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status History</h2>
                <div class="space-y-4">
                    @foreach($report->status_history ?? [] as $history)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ ucfirst($history['status']) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($history['timestamp'])->diffForHumans() }}
                            </p>
                            @if(!empty($history['notes']))
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                {{ $history['notes'] }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 