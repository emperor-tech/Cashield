@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mt-8">
    <h1 class="text-2xl font-bold mb-6 text-blue-900 dark:text-blue-200">Report an Incident</h1>
    <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" class="space-y-6">
  @csrf
        <div>
            <label for="campus" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Campus</label>
            <input type="text" name="campus" id="campus" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" required>
  </div>
        <div>
            <label for="location" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Location</label>
            <div class="flex gap-2 items-center">
                <input type="text" name="location" id="location" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" required>
                <button type="button" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="getLocation()">Use My Location</button>
            </div>
            <div id="map" class="w-full h-64 mt-2 rounded-lg"></div>
            @error('location')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
  </div>
        <div>
            <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" required></textarea>
            @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
  </div>
        <div>
            <label for="severity" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Severity</label>
            <select name="severity" id="severity" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400 focus:outline-none">
      <option value="low">Low</option>
      <option value="medium">Medium</option>
      <option value="high">High</option>
    </select>
            @error('severity')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
  </div>
        <div>
            <label for="media" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">Attach Media (optional)</label>
            <input type="file" name="media[]" id="media" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" multiple accept="*/*" onchange="previewFiles()">
            <p class="text-xs text-gray-400 mt-1">You can upload up to 5 files of any format.</p>
            <div id="filePreview" class="mt-2 flex flex-wrap gap-2"></div>
            @error('evidence')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
  </div>

        {{-- Hidden fields for latitude and longitude --}}
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        @error('latitude')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
        @error('longitude')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg shadow transition">Submit Report</button>
  </div>
</form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            if (window.userMap) {
                window.userMap.setView([lat, lng], 16);
                if (window.userMarker) {
                    window.userMarker.setLatLng([lat, lng]);
                } else {
                    window.userMarker = L.marker([lat, lng], {draggable: true}).addTo(window.userMap);
                }
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('location').value = data.display_name;
                        }
                    });
            }
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}
function previewFiles() {
    const input = document.getElementById('media');
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    if (input.files.length > 5) {
        alert('You can upload a maximum of 5 files.');
        input.value = '';
        return;
    }
    Array.from(input.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'p-2 border rounded bg-gray-50 dark:bg-gray-700';
        div.textContent = file.name;
        preview.appendChild(div);
    });
}
document.addEventListener('DOMContentLoaded', function () {
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const map = L.map('map').setView([6.5244, 3.3792], 12); // Default to Lagos
    window.userMap = map;
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    let marker;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 15);
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            window.userMarker = marker;
            latitudeInput.value = lat;
            longitudeInput.value = lng;
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('location').value = data.display_name;
                    }
                });
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                latitudeInput.value = e.latlng.lat;
                longitudeInput.value = e.latlng.lng;
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('location').value = data.display_name;
                        }
                    });
            });
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                latitudeInput.value = position.lat;
                longitudeInput.value = position.lng;
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.lat}&lon=${position.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('location').value = data.display_name;
                        }
                    });
            });
        }, function (error) {
            console.error("Error getting geolocation:", error);
            map.on('click', function(e) {
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
                    window.userMarker = marker;
                }
                latitudeInput.value = e.latlng.lat;
                longitudeInput.value = e.latlng.lng;
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('location').value = data.display_name;
                        }
                    });
            });
        });
    }
});
</script>
@endpush
