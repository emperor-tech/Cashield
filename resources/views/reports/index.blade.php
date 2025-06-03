@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column: Stats and Map -->
        <div class="lg:w-2/3">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-red-100 dark:bg-red-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-500 rounded-full">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-red-600 dark:text-red-400">High Priority</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $stats['high_priority'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-100 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-500 rounded-full">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-yellow-600 dark:text-yellow-400">Medium Priority</p>
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $stats['medium_priority'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-100 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-500 rounded-full">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-green-600 dark:text-green-400">Resolved</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $stats['resolved'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-100 dark:bg-blue-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500 rounded-full">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-blue-600 dark:text-blue-400">Total Reports</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incident Map -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
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
        </div>

        <!-- Right Column: Recent Reports -->
        <div class="lg:w-1/3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Reports</h2>
                <div class="space-y-4">
                    @forelse($reports as $report)
                    <div class="border-b dark:border-gray-700 last:border-0 pb-4 last:pb-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $report->location }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($report->severity === 'high') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300
                                @elseif($report->severity === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                                @else bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 @endif">
                                {{ ucfirst($report->severity) }}
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2 mb-2">{{ $report->description }}</p>
                        <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                            View Details →
                        </a>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400">No reports found.</p>
                    @endforelse
                </div>
                @if($reports->hasPages())
                <div class="mt-4 border-t dark:border-gray-700 pt-4">
                    {{ $reports->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
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

@push('scripts')
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
                    html: `<div class="marker-cluster marker-cluster-large"><div>${cluster.getChildCount()}</div></div>`,
                    className: '',
                    iconSize: L.point(40, 40)
                });
            }
        }),
        medium: L.markerClusterGroup({
            maxClusterRadius: 50,
            iconCreateFunction: (cluster) => {
                return L.divIcon({
                    html: `<div class="marker-cluster marker-cluster-medium"><div>${cluster.getChildCount()}</div></div>`,
                    className: '',
                    iconSize: L.point(40, 40)
                });
            }
        }),
        low: L.markerClusterGroup({
            maxClusterRadius: 50,
            iconCreateFunction: (cluster) => {
                return L.divIcon({
                    html: `<div class="marker-cluster marker-cluster-small"><div>${cluster.getChildCount()}</div></div>`,
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
        
        const markerHtml = `<div class="w-6 h-6 rounded-full bg-${markerColor}-500 border-2 border-white shadow-lg flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-white text-xs"></i>
        </div>`;
        
        const marker = L.marker([incident.lat, incident.lng], {
            icon: L.divIcon({
                className: `marker-${incident.severity}`,
                html: markerHtml,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            })
        });

        const popupContent = `
            <div class="p-3 min-w-[200px]">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-900">${incident.location}</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-${markerColor}-100 text-${markerColor}-800">
                        ${incident.severity.charAt(0).toUpperCase() + incident.severity.slice(1)}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-2">${incident.description}</p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>${incident.created_at}</span>
                    <a href="${incident.url}" class="text-blue-600 hover:text-blue-800 font-medium">View Details →</a>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'custom-popup'
        });

        return marker;
    }

    // Initial markers
    const incidents = @json($mapIncidents);
    incidents.forEach(incident => {
        const marker = createMarker(incident);
        markers.all.addLayer(marker);
        markers[incident.severity].addLayer(marker);
    });

    // Show all markers by default
    map.addLayer(markers.all);

    // Fit map to show all markers
    if (markers.all.getLayers().length > 0) {
        map.fitBounds(markers.all.getBounds().pad(0.1));
    }

    // Handle filter buttons
    document.querySelectorAll('.map-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.map-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Hide all marker groups
            Object.values(markers).forEach(markerGroup => map.removeLayer(markerGroup));

            // Show filtered markers
            const severity = this.dataset.severity;
            if (severity === 'all') {
                map.addLayer(markers.all);
            } else {
                map.addLayer(markers[severity]);
            }
        });
    });

    // Real-time updates
    function updateMap() {
        fetch('/reports/map-updates')
            .then(response => response.json())
            .then(newIncidents => {
                newIncidents.forEach(incident => {
                    // Check if marker already exists
                    const existingMarker = Array.from(markers.all.getLayers()).find(
                        layer => layer.getLatLng().lat === incident.lat && layer.getLatLng().lng === incident.lng
                    );

                    if (!existingMarker) {
                        const marker = createMarker(incident);
                        markers.all.addLayer(marker);
                        markers[incident.severity].addLayer(marker);

                        // Show notification for new incident
                        const notification = document.createElement('div');
                        notification.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-up';
                        notification.innerHTML = `
                            <div class="flex items-center">
                                <i class="fas fa-bell mr-2"></i>
                                <span>New incident reported at ${incident.location}</span>
                            </div>
                        `;
                        document.body.appendChild(notification);
                        setTimeout(() => {
                            notification.classList.add('animate-fade-out-down');
                            setTimeout(() => notification.remove(), 500);
                        }, 5000);
                    }
                });
            });
    }

    // Update map every 30 seconds
    setInterval(updateMap, 30000);
});
</script>
@endpush
@endsection
