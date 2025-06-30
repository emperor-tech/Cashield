@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Submit Anonymous Report</h1>
        
        <form action="{{ route('reports.anonymous.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Description of Incident <span class="text-red-600">*</span>
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="5" 
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                    required
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Location <span class="text-red-600">*</span>
                    </label>
                    <div class="flex gap-2 items-center">
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            value="{{ old('location') }}" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                            required
                        >
                        <button type="button" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="getLocation()">
                            Use My Location
                        </button>
                    </div>
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="severity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Severity <span class="text-red-600">*</span>
                    </label>
                    <select 
                        id="severity" 
                        name="severity" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                        required
                    >
                        <option value="">Select severity</option>
                        <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('severity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Hidden location fields that will be populated by the map -->
            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
            
            <!-- Map for selecting location -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Pin Location on Map <span class="text-red-600">*</span>
                </label>
                <div id="map" class="w-full h-64 rounded-md border border-gray-300 dark:border-gray-700"></div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click on the map to set the incident location</p>
                @error('latitude')
                    <p class="mt-1 text-sm text-red-600">Please select a location on the map</p>
                @enderror
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Contact Email (Optional)
                    </label>
                    <input 
                        type="email" 
                        id="contact_email" 
                        name="contact_email" 
                        value="{{ old('contact_email') }}" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                    >
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">We'll only use this to follow up on your report</p>
                    @error('contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Contact Phone (Optional)
                    </label>
                    <input 
                        type="text" 
                        id="contact_phone" 
                        name="contact_phone" 
                        value="{{ old('contact_phone') }}" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                    >
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">For urgent situations only</p>
                    @error('contact_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div>
                <label for="evidence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Evidence (Optional)
                </label>
                <input 
                    type="file" 
                    id="evidence" 
                    name="evidence[]" 
                    multiple 
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                    accept="*/*"
                    onchange="previewFiles()"
                >
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload up to 5 files of any format as evidence</p>
                <div id="filePreview" class="mt-2 flex flex-wrap gap-2"></div>
                @error('evidence.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="pt-4">
                <button 
                    type="submit" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition"
                >
                    Submit Anonymous Report
                </button>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        Log in to submit as a registered user
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            if (window.anonymousMap) {
                window.anonymousMap.setView([lat, lng], 16);
                if (window.anonymousMarker) {
                    window.anonymousMarker.setLatLng([lat, lng]);
                } else {
                    window.anonymousMarker = L.marker([lat, lng], {draggable: true}).addTo(window.anonymousMap);
                }
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                // Reverse geocode
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
    const input = document.getElementById('evidence');
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

document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const map = L.map('map').setView([6.5244, 3.3792], 12); // Default to Lagos
    window.anonymousMap = map;
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    let marker;
    
    // Try to get user's location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            map.setView([lat, lng], 15);
            
            // Set initial marker at user's location
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            window.anonymousMarker = marker;
            
            // Update hidden fields with initial position
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            // Reverse geocode
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('location').value = data.display_name;
                    }
                });
            
            // Update marker and fields when map is clicked
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
                
                // Reverse geocode
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('location').value = data.display_name;
                        }
                    });
            });
            
            // Update marker and fields when marker is dragged
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat;
                document.getElementById('longitude').value = position.lng;
                
                // Reverse geocode
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.lat}&lon=${position.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('location').value = data.display_name;
                        }
                    });
            });
        }, function(error) {
            console.error('Error getting location:', error);
            // Set up map click handler even if geolocation fails
            map.on('click', function(e) {
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
                    window.anonymousMarker = marker;
                }
                
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
                
                // Reverse geocode
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
@endsection