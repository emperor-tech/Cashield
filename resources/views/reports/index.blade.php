@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex justify-between- items-center mb-6">
        <h1 class="text-3xl font-extrabold text-blue-900 dark:text-blue-200 tracking-tight flex items-center">
            <svg class="w-8 h-8 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Security Reports Dashboard
        </h1>
        <div class="flex space-x-4 ml-8">
            <button @click="$dispatch('show-stats')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          Statistics
            </button>
            <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          New Report
            </a>
        </div>
    </div>

    <div x-data="reportFeed()" x-init="init()" class="space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">High Priority</h2>
                        <p class="mt-1 text-3xl font-bold text-red-600 dark:text-red-400" x-text="highPriorityCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Medium Priority</h2>
                        <p class="mt-1 text-3xl font-bold text-yellow-600 dark:text-yellow-400" x-text="mediumPriorityCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Resolved</h2>
                        <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400" x-text="resolvedCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Total Reports</h2>
                        <p class="mt-1 text-3xl font-bold text-blue-600 dark:text-blue-400" x-text="reports.length">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Incident Map
            </h2>
            <div id="map" class="w-full h-96 rounded-lg"></div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Recent Reports
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="r in reports" :key="r.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap" x-text="r.campus"></td>
                                <td class="px-6 py-4 whitespace-nowrap" x-text="r.location"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': r.severity === 'high',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': r.severity === 'medium',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': r.severity === 'low'
                                        }"
                                        x-text="r.severity">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': r.status === 'resolved',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !r.status || r.status === 'open'
                                        }"
                                        x-text="r.status ?? 'open'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDate(r.created_at)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a :href="'/reports/' + r.id" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View Details</a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
function reportFeed() {
    return {
        reports: @json($reports->items()),
        map: null,
        markers: [],
        markerLayer: null,
        get highPriorityCount() {
            return this.reports.filter(r => r.severity === 'high').length;
        },
        get mediumPriorityCount() {
            return this.reports.filter(r => r.severity === 'medium').length;
        },
        get resolvedCount() {
            return this.reports.filter(r => r.status === 'resolved').length;
        },
        formatDate(date) {
            return new Date(date).toLocaleString();
        },
        getMarkerColor(severity) {
            switch(severity) {
                case 'high': return 'red';
                case 'medium': return 'orange';
                case 'low': return 'green';
                default: return 'blue';
            }
        },
        init() {
            this.initMap();
            this.reports.forEach(r => this.addMarker(r));
            
            // Listen for new reports
            window.Echo.channel('reports')
                .listen('ReportCreated', (e) => {
                    this.reports.unshift(e);
                    this.addMarker(e);
                    this.showNotification('New Report', `A new ${e.severity} priority report has been created.`);
                });

            // Listen for report updates
            window.Echo.channel('reports')
                .listen('ReportUpdated', (e) => {
                    const index = this.reports.findIndex(r => r.id === e.id);
                    if (index !== -1) {
                        this.reports[index] = e;
                        this.updateMarker(e);
                    }
                });
        },
        initMap() {
            this.map = L.map('map').setView([6.5244, 3.3792], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            // Create a layer group for markers
            this.markerLayer = L.layerGroup().addTo(this.map);
        },
        addMarker(report) {
            if (!report.location) return;

            const coords = report.location.split(',').map(Number);
            if (coords.length !== 2) return;

            const color = this.getMarkerColor(report.severity);
            const marker = L.circleMarker([coords[0], coords[1]], {
                radius: 8,
                fillColor: color,
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            });

            marker.bindPopup(`
                <div class="p-2">
                    <h3 class="font-bold">${report.campus}</h3>
                    <p class="text-sm">${report.description}</p>
                    <p class="text-xs mt-1">Priority: <span class="capitalize font-bold" style="color: ${color}">${report.severity}</span></p>
                    <a href="/reports/${report.id}" class="text-blue-600 hover:text-blue-800 text-sm">View Details</a>
                </div>
            `);

            marker.addTo(this.markerLayer);
            this.markers.push({ id: report.id, marker });

            // Auto-adjust map bounds to show all markers
            const bounds = L.latLngBounds(this.markers.map(m => m.marker.getLatLng()));
            this.map.fitBounds(bounds, { padding: [50, 50] });
        },
        updateMarker(report) {
            const markerObj = this.markers.find(m => m.id === report.id);
            if (markerObj) {
                const color = this.getMarkerColor(report.severity);
                markerObj.marker.setStyle({
                    fillColor: color
                });
                markerObj.marker.setPopupContent(`
                    <div class="p-2">
                        <h3 class="font-bold">${report.campus}</h3>
                        <p class="text-sm">${report.description}</p>
                        <p class="text-xs mt-1">Priority: <span class="capitalize font-bold" style="color: ${color}">${report.severity}</span></p>
                        <a href="/reports/${report.id}" class="text-blue-600 hover:text-blue-800 text-sm">View Details</a>
                    </div>
                `);
            }
        },
        showNotification(title, message) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, {
                    body: message,
                    icon: '/images/logo.png'
                });
            }
        }
    }
}
</script>
@endsection
