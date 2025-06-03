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
                    <input 
                        type="text" 
                        id="location" 
                        name="location" 
                        value="{{ old('location') }}" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50"
                        required
                    >
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
                    accept="image/*"
                >
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload photos related to the incident (max 5MB each)</p>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map (using Leaflet or Google Maps)
        // This is a placeholder - you'll need to implement the actual map functionality
        const map = L.map('map').setView([0, 0], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        // Try to get user's location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                map.setView([lat, lng], 15);
                
                // Set initial marker at user's location
                let marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                
                // Update hidden fields with initial position
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                // Update location when marker is dragged
                marker.on('dragend', function(e) {
                    const position = marker.getLatLng();
                    document.getElementById('latitude').value = position.lat;
                    document.getElementById('longitude').value = position.lng;
                });
                
                // Update marker and fields when map is clicked
                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    document.getElementById('latitude').value = e.latlng.lat;
                    document.getElementById('longitude').value = e.latlng.lng;
                });
            });
        }
    });
</script>
@endpush
@endsection