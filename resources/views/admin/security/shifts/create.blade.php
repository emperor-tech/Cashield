@extends('admin.security.layout')

@section('security_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create Security Shift</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Schedule a new security shift assignment</p>
        </div>
        <div>
            <a href="{{ route('admin.security.shifts.index') }}" class="admin-btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Shifts
            </a>
        </div>
    </div>

    <!-- Create Shift Form -->
    <form action="{{ route('admin.security.shifts.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="admin-card p-6">
            <!-- Basic Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Shift Assignment</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Security Team -->
                    <div>
                        <label for="team_id" class="admin-label">Security Team</label>
                        <select name="team_id" 
                                id="team_id" 
                                class="admin-select @error('team_id') admin-input-error @enderror"
                                required>
                            <option value="">Select team...</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }} ({{ $team->team_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assigned User -->
                    <div>
                        <label for="user_id" class="admin-label">Assigned Officer</label>
                        <select name="user_id" 
                                id="user_id" 
                                class="admin-select @error('user_id') admin-input-error @enderror"
                                required>
                            <option value="">Select officer...</option>
                            @foreach($securityPersonnel as $person)
                                <option value="{{ $person->id }}" {{ old('user_id') == $person->id ? 'selected' : '' }}>
                                    {{ $person->name }} - {{ $person->badge_number ?? 'No Badge' }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Zone Assignment -->
                    <div>
                        <label for="zone_id" class="admin-label">Assigned Zone</label>
                        <select name="zone_id" 
                                id="zone_id" 
                                class="admin-select @error('zone_id') admin-input-error @enderror"
                                required>
                            <option value="">Select zone...</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }} ({{ $zone->zone_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('zone_id')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Shift Type -->
                    <div>
                        <label for="shift_type" class="admin-label">Shift Type</label>
                        <select name="shift_type" 
                                id="shift_type" 
                                class="admin-select @error('shift_type') admin-input-error @enderror"
                                required>
                            <option value="">Select shift type...</option>
                            <option value="patrol" {{ old('shift_type') === 'patrol' ? 'selected' : '' }}>Patrol</option>
                            <option value="stationary" {{ old('shift_type') === 'stationary' ? 'selected' : '' }}>Stationary Guard</option>
                            <option value="response" {{ old('shift_type') === 'response' ? 'selected' : '' }}>Emergency Response</option>
                            <option value="surveillance" {{ old('shift_type') === 'surveillance' ? 'selected' : '' }}>Surveillance</option>
                        </select>
                        @error('shift_type')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Schedule</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Start Date & Time -->
                    <div>
                        <label for="started_at" class="admin-label">Start Date & Time</label>
                        <input type="datetime-local" 
                               name="started_at" 
                               id="started_at" 
                               class="admin-input @error('started_at') admin-input-error @enderror"
                               value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}" 
                               required>
                        @error('started_at')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date & Time -->
                    <div>
                        <label for="ended_at" class="admin-label">End Date & Time (Optional)</label>
                        <input type="datetime-local" 
                               name="ended_at" 
                               id="ended_at" 
                               class="admin-input @error('ended_at') admin-input-error @enderror"
                               value="{{ old('ended_at') }}">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Leave empty for ongoing shift
                        </p>
                        @error('ended_at')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Quick Schedule Buttons -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="admin-btn-secondary text-sm" onclick="setShiftDuration(8)">
                        8 Hour Shift
                    </button>
                    <button type="button" class="admin-btn-secondary text-sm" onclick="setShiftDuration(12)">
                        12 Hour Shift
                    </button>
                    <button type="button" class="admin-btn-secondary text-sm" onclick="setShiftDuration(24)">
                        24 Hour Shift
                    </button>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Additional Information</h3>
                
                <!-- Notes -->
                <div>
                    <label for="notes" class="admin-label">Shift Notes</label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3" 
                              class="admin-textarea @error('notes') admin-input-error @enderror"
                              placeholder="Special instructions, equipment needed, etc.">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="admin-input-error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Route Data (for patrol shifts) -->
                <div id="route-section" style="display: none;">
                    <label for="route_data" class="admin-label">Patrol Route (JSON)</label>
                    <textarea name="route_data" 
                              id="route_data" 
                              rows="4" 
                              class="admin-textarea @error('route_data') admin-input-error @enderror"
                              placeholder='{"checkpoints": [{"id": 1, "name": "Main Gate"}, {"id": 2, "name": "Library"}]}'>{{ old('route_data') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Define patrol route and checkpoints (JSON format)
                    </p>
                    @error('route_data')
                        <p class="admin-input-error-message">{{ $message }}</p>
                    @enderror
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
                Create Shift
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const shiftTypeSelect = document.getElementById('shift_type');
    const routeSection = document.getElementById('route-section');
    
    // Show/hide route section based on shift type
    shiftTypeSelect.addEventListener('change', function() {
        if (this.value === 'patrol') {
            routeSection.style.display = 'block';
        } else {
            routeSection.style.display = 'none';
        }
    });

    // Validate JSON for route data
    const routeInput = document.getElementById('route_data');
    routeInput.addEventListener('blur', function() {
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

function setShiftDuration(hours) {
    const startInput = document.getElementById('started_at');
    const endInput = document.getElementById('ended_at');
    
    if (startInput.value) {
        const startDate = new Date(startInput.value);
        const endDate = new Date(startDate.getTime() + (hours * 60 * 60 * 1000));
        
        // Format for datetime-local input
        const year = endDate.getFullYear();
        const month = String(endDate.getMonth() + 1).padStart(2, '0');
        const day = String(endDate.getDate()).padStart(2, '0');
        const hour = String(endDate.getHours()).padStart(2, '0');
        const minute = String(endDate.getMinutes()).padStart(2, '0');
        
        endInput.value = `${year}-${month}-${day}T${hour}:${minute}`;
    }
}
</script>
@endpush