@extends('layouts.admin')

@section('page_title', 'Report Details')

@section('content')
<div class="space-y-6">
    <!-- Report Header -->
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
            <div class="flex items-center space-x-3">
                <form method="POST" action="{{ route('admin.report.status', $report) }}" class="inline-flex">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()" 
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="open" @if($report->status=='open')selected @endif>Open</option>
                        <option value="in_progress" @if($report->status=='in_progress')selected @endif>In Progress</option>
                        <option value="resolved" @if($report->status=='resolved')selected @endif>Resolved</option>
                    </select>
                </form>
                <a href="{{ route('admin.reports.export.pdf', ['id' => $report->id]) }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-download mr-2"></i>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Report Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Description</h3>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $report->description }}</p>
            </div>

            <!-- Media Attachments -->
    @if($report->media_path)
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Media Attachments</h3>
                <div class="rounded-lg overflow-hidden">
                    @if(Str::endsWith($report->media_path, ['.jpg', '.jpeg', '.png', '.gif']))
                        <img src="{{ Storage::url($report->media_path) }}" alt="Report Media" class="w-full">
                    @elseif(Str::endsWith($report->media_path, ['.mp4', '.mov', '.avi']))
                        <video controls class="w-full">
                            <source src="{{ Storage::url($report->media_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <a href="{{ Storage::url($report->media_path) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800">
                            <i class="fas fa-download mr-2"></i>
                            Download Attachment
                        </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Comments -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Comments</h3>
                
                <!-- Comment Form -->
                <form action="{{ route('admin.report.comment', $report) }}" method="POST" class="mb-6">
                    @csrf
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" alt="{{ Auth::user()->name }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm">
                                <textarea rows="3" name="comment" 
                                    class="block w-full rounded-lg border-0 bg-white dark:bg-gray-700 py-3 px-4 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    placeholder="Add a comment..."></textarea>
                            </div>
                            <div class="mt-2 flex justify-end">
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Post Comment
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-6">
                    @forelse($report->comments->sortByDesc('created_at') as $comment)
                        <div class="flex space-x-4">
                            <div class="flex-shrink-0">
                                <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}" alt="{{ $comment->user->name }}">
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $comment->comment }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Location Info -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Location</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Campus</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $report->campus }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Specific Location</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $report->location }}</p>
                    </div>
                </div>
                @if($report->latitude && $report->longitude)
                    <div class="mt-4 h-48 rounded-lg overflow-hidden" id="location-map"></div>
    @endif
</div>

            <!-- Reporter Info -->
            @if(!$report->anonymous && $report->user)
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Reporter Information</h3>
                <div class="flex items-center space-x-4">
                    <img class="h-12 w-12 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($report->user->name) }}" alt="{{ $report->user->name }}">
                    <div>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $report->user->name }}</p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $report->user->email }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Timeline</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                            <i class="fas fa-plus text-blue-600 dark:text-blue-300"></i>
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Report Created</p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                            {{ $report->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @foreach($report->statusHistory ?? [] as $history)
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                            <i class="fas fa-check text-green-600 dark:text-green-300"></i>
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Status changed to <span class="font-medium">{{ ucfirst($history->new_status) }}</span>
                                            </p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                            {{ $history->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
@if($report->latitude && $report->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
    // Initialize location map
    const map = L.map('location-map').setView([{{ $report->latitude }}, {{ $report->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    L.marker([{{ $report->latitude }}, {{ $report->longitude }}])
        .bindPopup('<strong>{{ $report->location }}</strong>')
        .addTo(map);
</script>
@endif
@endpush 