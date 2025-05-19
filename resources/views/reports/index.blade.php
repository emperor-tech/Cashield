@extends('layouts.app')

@section('content')
<h1 class="text-2xl mb-4">All Reports</h1>
<div x-data="reportFeed()" x-init="init()">
  <div id="map" class="w-full h-64 mb-6 rounded shadow"></div>
  <table class="w-full bg-white dark:bg-gray-800 shadow rounded" aria-label="Reports Table">
    <thead>
        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        <th class="p-2">Campus</th>
        <th class="p-2">Location</th>
        <th class="p-2">Severity</th>
          <th class="p-2">Status</th>
        <th class="p-2">Date</th>
      </tr>
    </thead>
    <tbody>
      <template x-for="r in reports" :key="r.id">
        <tr class="border-t border-gray-200 dark:border-gray-700">
          <td class="p-2" x-text="r.campus"></td>
          <td class="p-2" x-text="r.location"></td>
          <td class="p-2 capitalize" :class="{'text-red-600': r.severity === 'high', 'text-yellow-600': r.severity === 'medium', 'text-green-600': r.severity === 'low'}" x-text="r.severity"></td>
          <td class="p-2 capitalize" x-text="r.status ?? 'open'"></td>
          <td class="p-2" x-text="r.created_at"></td>
      </tr>
      </template>
    </tbody>
</table>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
function reportFeed() {
    return {
        reports: @json($reports->items()),
        map: null,
        markers: [],
        init() {
            this.initMap();
            this.reports.forEach(r => this.addMarker(r));
            window.Echo.channel('reports')
                .listen('ReportCreated', (e) => {
                    this.reports.unshift(e);
                    this.addMarker(e);
                });
        },
        initMap() {
            this.map = L.map('map').setView([6.5244, 3.3792], 13); // Default to Lagos, Nigeria
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(this.map);
        },
        addMarker(r) {
            // Try to parse lat/lng from location string if possible
            let latlng = [6.5244, 3.3792];
            if (r.location && r.location.includes(',')) {
                let parts = r.location.split(',');
                if (!isNaN(parseFloat(parts[0])) && !isNaN(parseFloat(parts[1]))) {
                    latlng = [parseFloat(parts[0]), parseFloat(parts[1])];
                }
            }
            let marker = L.marker(latlng).addTo(this.map);
            marker.bindPopup(`<b>${r.campus}</b><br>${r.description}<br><span class='capitalize'>${r.severity}</span>`);
            this.markers.push(marker);
        }
    }
}
</script>
@endsection
