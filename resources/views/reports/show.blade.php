@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Reports
        </a>
    </div>

    <!-- Report Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Report #{{ $report->id }}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Submitted {{ $report->created_at->diffForHumans() }}
                    @if($report->created_at != $report->updated_at)
                        · Updated {{ $report->updated_at->diffForHumans() }}
                    @endif
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($report->status === 'open') bg-blue-100 text-blue-800
                @elseif($report->status === 'in_progress') bg-yellow-100 text-yellow-800
                @else bg-green-100 text-green-800 @endif">
                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Details -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Incident Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</h3>
                        <p class="text-gray-900 dark:text-white">{{ $report->location }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
                        <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $report->description }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Severity</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                            @if($report->severity === 'high') bg-red-100 text-red-800
                            @elseif($report->severity === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($report->severity) }}
                        </span>
                    </div>

                    @if($report->evidence)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Evidence</h3>
                        <div class="mt-2 grid grid-cols-2 gap-4">
                            @foreach($report->evidence as $evidence)
                            <div class="relative">
                                <img src="{{ Storage::url($evidence) }}" alt="Evidence" class="rounded-lg shadow-sm">
                                <a href="{{ Storage::url($evidence) }}" target="_blank" class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75">
                                    <i class="fas fa-expand"></i>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Updates</h2>
                
                @if($report->comments->count() > 0)
                <div class="space-y-4">
                    @foreach($report->comments as $comment)
                    <div class="flex space-x-4 p-4 @if(!$loop->last) border-b dark:border-gray-700 @endif">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <i class="fas fa-user text-gray-500 dark:text-gray-400"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $comment->user->name }}
                                </h3>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $comment->content }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-600 dark:text-gray-400">No updates yet.</p>
                @endif

                @if($report->status !== 'resolved')
                <form action="{{ route('reports.comments.store', $report) }}" method="POST" class="mt-6">
                    @csrf
                    <div>
                        <label for="comment" class="sr-only">Add a comment</label>
                        <textarea id="comment" name="content" rows="3" class="admin-textarea" placeholder="Add a comment or question..."></textarea>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="admin-btn-primary">
                            Post Comment
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Map -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Location</h2>
                <div id="map" class="h-64 rounded-lg"></div>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Timeline</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-4 w-4 rounded-full bg-green-500"></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Report Submitted</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @if($report->status === 'in_progress')
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-4 w-4 rounded-full bg-yellow-500"></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Under Investigation</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($report->status === 'resolved')
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-4 w-4 rounded-full bg-blue-500"></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Resolved</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->resolved_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const map = L.map('map').setView([{{ $report->latitude ?? config('integrations.maps_default_center.lat') }}, 
                                     {{ $report->longitude ?? config('integrations.maps_default_center.lng') }}], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add marker for incident location
    const marker = L.marker([{{ $report->latitude ?? config('integrations.maps_default_center.lat') }}, 
                           {{ $report->longitude ?? config('integrations.maps_default_center.lng') }}]).addTo(map);
    
    marker.bindPopup("<b>Incident Location</b><br>{{ $report->location }}").openPopup();
});
</script>
@endpush
@endsection 