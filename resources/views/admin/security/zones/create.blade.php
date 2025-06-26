@extends('admin.security.layout')

@section('security_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create Campus Zone</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Define a new security zone with boundaries and checkpoints</p>
        </div>
        <div>
            <a href="{{ route('admin.security.zones.index') }}" class="admin-btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Zones
            </a>
        </div>
    </div>

    <!-- Create Zone Form -->
    <form action="{{ route('admin.security.zones.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="admin-card p-6">
            <!-- Basic Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Zone Name -->
                    <div>
                        <label for="name" class="admin-label">Zone Name</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="admin-input @error('name') admin-input-error @enderror"
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Zone Code -->
                    <div>
                        <label for="code" class="admin-label">Zone Code</label>
                        <input type="text" 
                               name="code" 
                               id="code" 
                               class="admin-input @error('code') admin-input-error @enderror"
                               value="{{ old('code') }}" 
                               required>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Unique identifier (e.g., ZONE-A1)</p>
                        @error('code')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Zone Type -->
                    <div>
                        <label for="zone_type" class="admin-label">Zone Type</label>
                        <select name="zone_type" 
                                id="zone_type" 
                                class="admin-select @error('zone_type') admin-input-error @enderror"
                                required>
                            <option value="">Select zone type...</option>
                            <option value="academic" {{ old('zone_type') === 'academic' ? 'selected' : '' }}>Academic Building</option>
                            <option value="residential" {{ old('zone_type') === 'residential' ? 'selected' : '' }}>Residential Area</option>
                            <option value="recreational" {{ old('zone_type') === 'recreational' ? 'selected' : '' }}>Recreational Facility</option>
                            <option value="parking" {{ old('zone_type') === 'parking' ? 'selected' : '' }}>Parking Area</option>
                            <option value="administrative" {{ old('zone_type') === 'administrative' ? 'selected' : '' }}>Administrative Building</option>
                            <option value="outdoor" {{ old('zone_type') === 'outdoor' ? 'selected' : '' }}>Outdoor Area</option>
                        </select>
                        @error('zone_type')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Risk Level -->
                    <div>
                        <label for="risk_level" class="admin-label">Risk Level</label>
                        <select name="risk_level" 
                                id="risk_level" 
                                class="admin-select @error('risk_level') admin-input-error @enderror"
                                required>
                            <option value="">Select risk level...</option>
                            <option value="low" {{ old('risk_level') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('risk_level') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('risk_level') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('risk_level') === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                        @error('risk_level')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="admin-label">Zone Description</label>
                    <textarea name="description" 
                              id="description" 
                              rows="3" 
                              class="admin-textarea @error('description') admin-input-error @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="admin-input-error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Location & Boundaries -->
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Location & Boundaries</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Center Coordinates -->
                    <div>
                        <label for="center_latitude" class="admin-label">Center Latitude</label>
                        <input type="number" 
                               name="center_latitude" 
                               id="center_latitude" 
                               step="any"
                               class="admin-input @error('center_latitude') admin-input-error @enderror"
                               value="{{ old('center_latitude') }}">
                        @error('center_latitude')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="center_longitude" class="admin-label">Center Longitude</label>
                        <input type="number" 
                               name="center_longitude" 
                               id="center_longitude" 
                               step="any"
                               class="admin-input @error('center_longitude') admin-input-error @enderror"
                               value="{{ old('center_longitude') }}">
                        @error('center_longitude')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Boundary Points -->
                <div>
                    <label for="boundary_points" class="admin-label">Boundary Points (JSON)</label>
                    <textarea name="boundary_points" 
                              id="boundary_points" 
                              rows="4" 
                              class="admin-textarea @error('boundary_points') admin-input-error @enderror"
                              placeholder='[{"lat": 14.123, "lng": 121.456}, {"lat": 14.124, "lng": 121.457}]'>{{ old('boundary_points') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Define zone boundaries as an array of coordinate objects
                    </p>
                    @error('boundary_points')
                        <p class="admin-input-error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Security Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Patrol Frequency -->
                    <div>
                        <label for="patrol_frequency" class="admin-label">Patrol Frequency (minutes)</label>
                        <input type="number" 
                               name="patrol_frequency" 
                               id="patrol_frequency" 
                               min="5"
                               max="1440"
                               class="admin-input @error('patrol_frequency') admin-input-error @enderror"
                               value="{{ old('patrol_frequency', 60) }}">
                        @error('patrol_frequency')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Max Response Time -->
                    <div>
                        <label for="max_response_time" class="admin-label">Max Response Time (minutes)</label>
                        <input type="number" 
                               name="max_response_time" 
                               id="max_response_time" 
                               min="1"
                               max="60"
                               class="admin-input @error('max_response_time') admin-input-error @enderror"
                               value="{{ old('max_response_time', 10) }}">
                        @error('max_response_time')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Zone Status -->
                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               value="1"
                               class="admin-checkbox"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Active Zone
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="requires_escort" 
                               id="requires_escort" 
                               value="1"
                               class="admin-checkbox"
                               {{ old('requires_escort') ? 'checked' : '' }}>
                        <label for="requires_escort" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Requires Security Escort
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3">
            <button type="reset" class="admin-btn-secondary">
                <i class="fas fa-undo mr-2"></i>
                Reset Form
            </button>
            <button type="submit" class="admin-btn-primary">
                <i class="fas fa-save mr-2"></i>
                Create Zone
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate zone code from name
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    
    nameInput.addEventListener('input', function() {
        if (!codeInput.value) {
            const code = 'ZONE-' + this.value.toUpperCase()
                .replace(/[^A-Z0-9\s]/g, '')
                .replace(/\s+/g, '-')
                .substring(0, 8);
            codeInput.value = code;
        }
    });

    // Validate JSON for boundary points
    const boundaryInput = document.getElementById('boundary_points');
    boundaryInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            try {
                JSON.parse(this.value);
                this.classList.remove('admin-input-error');
            } catch (e) {
                this.classList.add('admin-input-error');
            }
        }
    });
});
</script>
@endpush