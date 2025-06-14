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
            <input type="text" name="location" id="location" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" required>
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
            <input type="file" name="media" id="media" class="w-full border border-blue-200 dark:border-blue-700 rounded-lg p-2 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');

        // Attempt to get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                latitudeInput.value = position.coords.latitude;
                longitudeInput.value = position.coords.longitude;
            }, function (error) {
                console.error("Error getting geolocation:", error);
                // Optionally, inform the user that location could not be obtained
                // and the report might not be creatable without it.
            });
        } else {
            console.error("Geolocation is not supported by this browser.");
            // Optionally, inform the user that geolocation is not supported
            // and the report might not be creatable without it.
        }
    });
</script>
@endpush
