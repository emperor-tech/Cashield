@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-gray-600 dark:text-gray-300">Stay safe and help keep our campus secure. Report any incidents or concerns you observe.</p>
        <div class="mt-4">
            <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm">
                View all campus reports <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Reports</h3>
                <i class="fas fa-chart-bar text-blue-500 text-xl"></i>
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $stats['total_reports'] }}</div>
            <div class="flex space-x-4 text-sm">
                <span class="text-green-500"><i class="fas fa-check-circle"></i> {{ $stats['resolved_reports'] }} Resolved</span>
                <span class="text-yellow-500"><i class="fas fa-clock"></i> {{ $stats['in_progress_reports'] }} In Progress</span>
                <span class="text-red-500"><i class="fas fa-exclamation-circle"></i> {{ $stats['open_reports'] }} Open</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Priority Distribution</h3>
                <i class="fas fa-fire text-orange-500 text-xl"></i>
            </div>
            <div class="space-y-2">
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mr-2">
                        <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $stats['total_reports'] > 0 ? ($stats['high_priority'] / $stats['total_reports'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">High: {{ $stats['high_priority'] }}</span>
                </div>
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mr-2">
                        <div class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ $stats['total_reports'] > 0 ? ($stats['medium_priority'] / $stats['total_reports'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Med: {{ $stats['medium_priority'] }}</span>
                </div>
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mr-2">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $stats['total_reports'] > 0 ? ($stats['low_priority'] / $stats['total_reports'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Low: {{ $stats['low_priority'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Response Times</h3>
                <i class="fas fa-stopwatch text-purple-500 text-xl"></i>
            </div>
            @if($responseAnalytics && $responseAnalytics->avg_response_time)
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Average</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ round($responseAnalytics->avg_response_time) }} min</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Fastest</span>
                        <span class="text-sm font-semibold text-green-500">{{ $responseAnalytics->min_response_time }} min</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Slowest</span>
                        <span class="text-sm font-semibold text-red-500">{{ $responseAnalytics->max_response_time }} min</span>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No response time data available</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Weekly Trend</h3>
                <i class="fas fa-chart-line text-green-500 text-xl"></i>
            </div>
            @if($weeklyDistribution->isNotEmpty())
                <div class="h-32">
                    <canvas id="weeklyTrendChart"></canvas>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No weekly data available</p>
            @endif
        </div>
    </div>

    <!-- Incident Map -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Incident Map</h2>
            <div class="flex gap-2">
                <button class="map-filter-btn active" data-severity="all">
                    <i class="fas fa-globe-americas mr-1"></i> All
                </button>
                <button class="map-filter-btn" data-severity="high">
                    <i class="fas fa-exclamation-circle text-red-500 mr-1"></i> High
                </button>
                <button class="map-filter-btn" data-severity="medium">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i> Medium
                </button>
                <button class="map-filter-btn" data-severity="low">
                    <i class="fas fa-info-circle text-green-500 mr-1"></i> Low
                </button>
            </div>
        </div>
        <div id="incident-map" class="h-96 rounded-lg border-2 border-gray-200 dark:border-gray-700"></div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('reports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-lg p-6 transition duration-200">
            <div class="flex items-center">
                <i class="fas fa-plus-circle text-3xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold mb-1">Submit New Report</h3>
                    <p class="text-sm opacity-90">Report an incident or security concern</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.index') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-lg p-6 transition duration-200">
            <div class="flex items-center">
                <i class="fas fa-clipboard-list text-3xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold mb-1">View Reports</h3>
                    <p class="text-sm opacity-90">Track your submitted reports</p>
                </div>
            </div>
        </a>

        <button id="panicButton" class="bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-lg p-6 transition duration-200">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-3xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold mb-1">Emergency Alert</h3>
                    <p class="text-sm opacity-90">Send immediate distress signal</p>
                </div>
            </div>
        </button>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
            <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @if($recentReports->count() > 0)
            <div class="space-y-4">
                @foreach($recentReports as $report)
                    <div class="flex items-start space-x-4 p-4 @if(!$loop->last) border-b dark:border-gray-700 @endif">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full 
                                @if($report->severity === 'high') bg-red-100 text-red-500
                                @elseif($report->severity === 'medium') bg-yellow-100 text-yellow-500
                                @else bg-green-100 text-green-500 @endif">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                        </div>
                        <div class="flex-grow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->location }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($report->status === 'open') bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300
                                    @elseif($report->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                                    @else bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ $report->description }}</p>
                            <div class="mt-2">
                                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    View Details <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                </a>
                            </div>
                            @if($report->comments_count > 0)
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-comments mr-1"></i> {{ $report->comments_count }} {{ Str::plural('comment', $report->comments_count) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                    <i class="fas fa-clipboard-list text-2xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-400">No reports submitted yet.</p>
                <a href="{{ route('reports.create') }}" class="mt-4 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    Submit your first report <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Emergency Alert Modal -->
<div id="emergencyAlertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-red-600">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-4">Emergency Alert Confirmation</h3>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                    Are you sure you want to send an emergency alert?<br>
                    This will immediately notify security personnel.
                </p>
                <div class="flex justify-center space-x-4">
                    <button id="cancelEmergencyAlert" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        Cancel
                    </button>
                    <button id="confirmEmergencyAlert" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        Send Alert
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div id="statusModalContent">
                    <!-- Content will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Trend Chart
    @if($weeklyDistribution->isNotEmpty())
    const ctx = document.getElementById('weeklyTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($weeklyDistribution->pluck('date')->map(function($date) { 
                return \Carbon\Carbon::parse($date)->format('M d');
            })) !!},
            datasets: [{
                label: 'Total Reports',
                data: {!! json_encode($weeklyDistribution->pluck('total')) !!},
                borderColor: '#3B82F6',
                tension: 0.4,
                fill: false
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
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif

    // Emergency Alert Modal Handling
    const panicButton = document.getElementById('panicButton');
    const emergencyAlertModal = document.getElementById('emergencyAlertModal');
    const cancelEmergencyAlert = document.getElementById('cancelEmergencyAlert');
    const confirmEmergencyAlert = document.getElementById('confirmEmergencyAlert');
    const statusModal = document.getElementById('statusModal');
    const statusModalContent = document.getElementById('statusModalContent');

    panicButton.addEventListener('click', function() {
        emergencyAlertModal.classList.remove('hidden');
    });

    cancelEmergencyAlert.addEventListener('click', function() {
        emergencyAlertModal.classList.add('hidden');
    });

    confirmEmergencyAlert.addEventListener('click', function() {
        emergencyAlertModal.classList.add('hidden');
        showStatusModal('sending');

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                sendPanicAlert(position.coords.latitude, position.coords.longitude);
            }, function(error) {
                sendPanicAlert(null, null);
            });
        } else {
            sendPanicAlert(null, null);
        }
    });

    function showStatusModal(status, message = '') {
        const modalContent = {
            sending: `
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mb-4"></div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Sending Emergency Alert</h3>
                    <p class="text-gray-600 dark:text-gray-400">Please wait while we notify security personnel...</p>
                </div>
            `,
            success: `
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Alert Sent Successfully</h3>
                    <p class="text-gray-600 dark:text-gray-400">Security personnel have been notified and are responding.</p>
                </div>
            `,
            error: `
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 text-red-500 mb-4">
                        <i class="fas fa-exclamation-circle text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Error Sending Alert</h3>
                    <p class="text-gray-600 dark:text-gray-400">${message || 'Please try again or contact security directly.'}</p>
                    <button id="statusModalCloseButton" class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        Close
                    </button>
                </div>
            `
        };

        statusModalContent.innerHTML = modalContent[status];
        statusModal.classList.remove('hidden');

        // Add event listener to the close button if it exists in the current modal content
        const closeButton = statusModalContent.querySelector('#statusModalCloseButton');
        if (closeButton) {
            closeButton.addEventListener('click', closeStatusModal);
        }
    }

    function closeStatusModal() {
        statusModal.classList.add('hidden');
        // Clear previous content
        statusModalContent.innerHTML = '';
    }

    function sendPanicAlert(latitude, longitude) {
        fetch('/api/panic', { // Corrected URL from /panic to /api/panic
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                latitude: latitude,
                longitude: longitude
            })
        })
        .then(response => {
            // Check for non-OK response status first
            if (!response.ok) {
                let errorDetail = 'Unknown error occurred';
                // Attempt to parse JSON error response if available
                return response.json().then(errorBody => {
                    errorDetail = errorBody.message || errorBody.error || JSON.stringify(errorBody);
                    throw new Error(errorDetail);
                }).catch(() => {
                    // If JSON parsing fails, read response as text (e.g., HTML error page)
                     return response.text().then(textError => {
                         errorDetail = `Request failed with status ${response.status}: ${textError.substring(0, 200)}...`; // Limit text for brevity
                         throw new Error(errorDetail);
                     }).catch(textError => {
                         console.error('Failed to read response body as text:', textError);
                         errorDetail = `Request failed with status ${response.status}`;
                         throw new Error(errorDetail);
                     });
                });
            }
            // If response is OK, proceed to parse JSON
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showStatusModal('success');
                setTimeout(() => {
                    window.location.href = `/reports/${data.report_id}`;
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to send alert');
            }
        })
        .catch(error => {
            showStatusModal('error', error.message);
        });
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === emergencyAlertModal) {
            emergencyAlertModal.classList.add('hidden');
        }
    });
});
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(1rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOutDown {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(1rem);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}

.animate-fade-out-down {
    animation: fadeOutDown 0.5s ease-out;
}
</style>

<!-- MarkerCluster CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('incident-map').setView([
        {{ config('integrations.maps_default_center.lat') }},
        {{ config('integrations.maps_default_center.lng') }}
    ], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Initialize marker clusters
    const markers = {
        all: L.markerClusterGroup({
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        }),
        high: L.markerClusterGroup({
            maxClusterRadius: 50,
            iconCreateFunction: (cluster) => {
                return L.divIcon({
                    html: `<div class=\"marker-cluster marker-cluster-large\"><div>${cluster.getChildCount()}</div></div>`,
                    className: '',
                    iconSize: L.point(40, 40)
                });
            }
        }),
        medium: L.markerClusterGroup({
            maxClusterRadius: 50,
            iconCreateFunction: (cluster) => {
                return L.divIcon({
                    html: `<div class=\"marker-cluster marker-cluster-medium\"><div>${cluster.getChildCount()}</div></div>`,
                    className: '',
                    iconSize: L.point(40, 40)
                });
            }
        }),
        low: L.markerClusterGroup({
            maxClusterRadius: 50,
            iconCreateFunction: (cluster) => {
                return L.divIcon({
                    html: `<div class=\"marker-cluster marker-cluster-small\"><div>${cluster.getChildCount()}</div></div>`,
                    className: '',
                    iconSize: L.point(40, 40)
                });
            }
        })
    };

    // Add all marker clusters to map
    Object.values(markers).forEach(markerGroup => map.addLayer(markerGroup));

    // Function to create a marker
    function createMarker(incident) {
        const markerColor = incident.severity === 'high' ? 'red' : 
                          incident.severity === 'medium' ? 'yellow' : 'green';
        const markerHtml = `<div class=\"w-6 h-6 rounded-full bg-${markerColor}-500 border-2 border-white shadow-lg flex items-center justify-center\">`
            + `<i class=\"fas fa-exclamation-triangle text-white text-xs\"></i></div>`;
        const marker = L.marker([incident.lat, incident.lng], {
            icon: L.divIcon({
                className: `marker-${incident.severity}`,
                html: markerHtml,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            })
        });
        const popupContent = `
            <div class=\"p-3 min-w-[200px]\">
                <div class=\"flex items-center justify-between mb-2\">
                    <h3 class=\"font-bold text-gray-900\">${incident.location}</h3>
                    <span class=\"px-2 py-1 text-xs rounded-full bg-${markerColor}-100 text-${markerColor}-800\">${incident.severity.charAt(0).toUpperCase() + incident.severity.slice(1)}</span>
                </div>
                <div class=\"text-sm text-gray-700 mb-2\">${incident.description}</div>
                <div class=\"text-xs text-gray-500\">${incident.created_at}</div>
                <a href=\"${incident.url}\" class=\"block mt-2 text-blue-600 hover:text-blue-800\">View Details</a>
            </div>
        `;
        marker.bindPopup(popupContent);
        return marker;
    }

    // Fetch incidents and add to map
    function loadIncidents() {
        fetch('/incidents/json')
            .then(res => res.json())
            .then(incidents => {
                // Clear all clusters
                Object.values(markers).forEach(mg => mg.clearLayers());
                incidents.forEach(incident => {
                    const marker = createMarker(incident);
                    markers.all.addLayer(marker);
                    if (incident.severity === 'high') markers.high.addLayer(marker);
                    if (incident.severity === 'medium') markers.medium.addLayer(marker);
                    if (incident.severity === 'low') markers.low.addLayer(marker);
                });
            });
    }
    loadIncidents();
    setInterval(loadIncidents, 60000); // Refresh every 60s

    // Filtering
    document.querySelectorAll('.map-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const severity = this.getAttribute('data-severity');
            Object.values(markers).forEach(mg => map.removeLayer(mg));
            if (severity === 'all') map.addLayer(markers.all);
            if (severity === 'high') map.addLayer(markers.high);
            if (severity === 'medium') map.addLayer(markers.medium);
            if (severity === 'low') map.addLayer(markers.low);
        });
    });
});
</script>

<style>
.map-filter-btn {
    @apply px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 
           bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300
           hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200
           flex items-center justify-center min-w-[80px];
}
.map-filter-btn.active {
    @apply bg-blue-600 text-white border-blue-600 hover:bg-blue-700;
}
.leaflet-container {
    @apply font-sans;
}
.leaflet-popup-content-wrapper {
    @apply rounded-lg shadow-lg;
}
.leaflet-popup-content {
    @apply m-0 p-0;
}
.marker-cluster {
    background-clip: padding-box;
    border-radius: 20px;
    @apply bg-white bg-opacity-80 dark:bg-gray-800 dark:bg-opacity-80;
}
.marker-cluster div {
    width: 30px;
    height: 30px;
    margin-left: 5px;
    margin-top: 5px;
    text-align: center;
    border-radius: 15px;
    @apply font-bold flex items-center justify-center;
}
.marker-cluster-small {
    @apply bg-green-100 dark:bg-green-900/30;
}
.marker-cluster-small div {
    @apply bg-green-500 text-white;
}
.marker-cluster-medium {
    @apply bg-yellow-100 dark:bg-yellow-900/30;
}
.marker-cluster-medium div {
    @apply bg-yellow-500 text-white;
}
.marker-cluster-large {
    @apply bg-red-100 dark:bg-red-900/30;
}
.marker-cluster-large div {
    @apply bg-red-500 text-white;
}
</style>
@endpush
@endsection
