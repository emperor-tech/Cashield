@extends('layouts.admin')

@section('page_title', 'Create Incident Report')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h2 class="text-2xl font-bold mb-6">Create New Incident Report</h2>
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.reports.store') }}" enctype="multipart/form-data" class="space-y-8 admin-card p-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" class="admin-input w-full min-h-[100px]" required>{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="form-label">Location</label>
                <div class="flex gap-2 items-center">
                    <input type="text" name="location" id="locationInput" class="admin-input w-full" value="{{ old('location') }}" required>
                    <button type="button" class="admin-btn-secondary" onclick="getLocation()">Use My Location</button>
                </div>
                <div id="map" class="w-full h-64 mt-2 rounded-lg"></div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>
            <div>
                <label class="form-label">Severity</label>
                <select name="severity" class="admin-select w-full" required>
                    <option value="">Select Severity</option>
                    <option value="low" {{ old('severity')=='low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('severity')=='medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('severity')=='high' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category_id" class="admin-select w-full" required>
                    <option value="">Select Category</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ old('category_id')==$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="form-label">Assign To</label>
            <select name="assigned_to" class="admin-select w-full">
                <option value="">Unassigned</option>
                @foreach($teams ?? [] as $team)
                    <option value="team:{{ $team->id }}" {{ old('assigned_to')=='team:'.$team->id ? 'selected' : '' }}>Team: {{ $team->name }}</option>
                @endforeach
                @foreach($users ?? [] as $user)
                    <option value="user:{{ $user->id }}" {{ old('assigned_to')=='user:'.$user->id ? 'selected' : '' }}>User: {{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Evidence / Media</label>
            <input type="file" name="evidence[]" class="admin-input w-full" id="evidenceInput" multiple accept="*/*" onchange="previewFiles()" max="5">
            <p class="text-xs text-gray-400 mt-1">You can upload up to 5 files of any format as evidence.</p>
            <div id="filePreview" class="mt-2 flex flex-wrap gap-2"></div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="admin-btn-primary">Submit Report</button>
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
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            document.getElementById('locationInput').value = position.coords.latitude + ', ' + position.coords.longitude;
            if (window.reportMap) {
                window.reportMap.setView([position.coords.latitude, position.coords.longitude], 16);
                if (window.reportMarker) {
                    window.reportMarker.setLatLng([position.coords.latitude, position.coords.longitude]);
                } else {
                    window.reportMarker = L.marker([position.coords.latitude, position.coords.longitude], {draggable:true}).addTo(window.reportMap);
                }
            }
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([6.5244, 3.3792], 12); // Default to Lagos
    window.reportMap = map;
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);
    var marker;
    map.on('click', function(e) {
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
        document.getElementById('locationInput').value = e.latlng.lat + ', ' + e.latlng.lng;
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, {draggable:true}).addTo(map);
        }
        marker.on('dragend', function(ev) {
            var pos = ev.target.getLatLng();
            document.getElementById('latitude').value = pos.lat;
            document.getElementById('longitude').value = pos.lng;
            document.getElementById('locationInput').value = pos.lat + ', ' + pos.lng;
        });
    });
});
function previewFiles() {
    const input = document.getElementById('evidenceInput');
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    if (input.files.length > 5) {
        alert('You can upload a maximum of 5 files.');
        input.value = '';
        return;
    }
    Array.from(input.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'p-2 border rounded';
        div.textContent = file.name;
        preview.appendChild(div);
    });
}
</script>
@endpush 