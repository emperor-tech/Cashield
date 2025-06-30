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

                    <!-- Security Level -->
                    <div>
                        <label for="security_level" class="admin-label">Security Level</label>
                        <select name="security_level" 
                                id="security_level" 
                                class="admin-select @error('security_level') admin-input-error @enderror"
                                required>
                            <option value="">Select security level...</option>
                            <option value="minimum" {{ old('security_level') === 'minimum' ? 'selected' : '' }}>Minimum</option>
                            <option value="standard" {{ old('security_level') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="enhanced" {{ old('security_level') === 'enhanced' ? 'selected' : '' }}>Enhanced</option>
                            <option value="maximum" {{ old('security_level') === 'maximum' ? 'selected' : '' }}>Maximum</option>
                        </select>
                        @error('security_level')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Restricted Access -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="restricted_access" 
                                   id="restricted_access" 
                                   value="1"
                                   class="admin-checkbox"
                                   {{ old('restricted_access') ? 'checked' : '' }}>
                            <label for="restricted_access" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Restricted Access Zone
                            </label>
                        </div>
                        @error('restricted_access')
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
                    <label for="boundaries_json" class="admin-label">Boundary Points (JSON)</label>
                    <textarea name="boundaries_json" 
                              id="boundaries_json" 
                              rows="4" 
                              class="admin-textarea @error('boundaries') admin-input-error @enderror"
                              placeholder='[{"lat": 14.123, "lng": 121.456}, {"lat": 14.124, "lng": 121.457}]'>{{ old('boundaries_json') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Define zone boundaries as an array of coordinate objects
                    </p>
                    @error('boundaries')
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

                    <!-- Operating Hours -->
                    <div>
                        <label for="operating_hours_json" class="admin-label">Operating Hours (JSON)</label>
                        <textarea name="operating_hours_json" 
                                  id="operating_hours_json" 
                                  rows="3" 
                                  class="admin-textarea @error('operating_hours') admin-input-error @enderror"
                                  placeholder='{"monday": {"open": "08:00", "close": "18:00"}, "tuesday": {"open": "08:00", "close": "18:00"}}'>{{ old('operating_hours_json') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Define operating hours for each day of the week (optional)
                        </p>
                        @error('operating_hours')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
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
    const boundaryInput = document.getElementById('boundaries_json');
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

    // Validate JSON for operating hours
    const operatingHoursInput = document.getElementById('operating_hours_json');
    operatingHoursInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            try {
                JSON.parse(this.value);
                this.classList.remove('admin-input-error');
            } catch (e) {
                this.classList.add('admin-input-error');
            }
        }
    });

    // Simple form validation - no complex processing needed since server handles JSON conversion
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        console.log('Form submission started');
        
        // Validate JSON fields before submission
        const boundariesJson = document.getElementById('boundaries_json').value;
        const operatingHoursJson = document.getElementById('operating_hours_json').value;
        
        // Validate boundaries JSON if provided
        if (boundariesJson.trim()) {
            try {
                const boundaries = JSON.parse(boundariesJson);
                if (!Array.isArray(boundaries) || boundaries.length === 0) {
                    e.preventDefault();
                    alert('Boundary points must be a non-empty array');
                    return false;
                }
                console.log('Boundaries JSON is valid');
            } catch (error) {
                e.preventDefault();
                console.error('Boundary JSON parse error:', error);
                alert('Invalid JSON format for boundary points');
                return false;
            }
        }
        
        // Validate operating hours JSON if provided
        if (operatingHoursJson.trim()) {
            try {
                const operatingHours = JSON.parse(operatingHoursJson);
                console.log('Operating hours JSON is valid');
            } catch (error) {
                e.preventDefault();
                console.error('Operating hours JSON parse error:', error);
                alert('Invalid JSON format for operating hours');
                return false;
            }
        }
        
        console.log('Form validation passed, submitting form');
    });
});
</script>
@endpush