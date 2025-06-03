@extends('admin.security.layout')

@section('security_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create Security Team</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create a new security team and assign members</p>
        </div>
        <div>
            <a href="{{ route('admin.security.teams.index') }}" class="admin-btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Teams
            </a>
        </div>
    </div>

    <!-- Create Team Form -->
    <form action="{{ route('admin.security.teams.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="admin-card p-6">
            <!-- Basic Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Team Name -->
                    <div>
                        <label for="name" class="admin-label">Team Name</label>
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

                    <!-- Team Type -->
                    <div>
                        <label for="team_type" class="admin-label">Team Type</label>
                        <select name="team_type" 
                                id="team_type" 
                                class="admin-select @error('team_type') admin-input-error @enderror"
                                required>
                            <option value="">Select team type...</option>
                            <option value="patrol" {{ old('team_type') === 'patrol' ? 'selected' : '' }}>Patrol</option>
                            <option value="response" {{ old('team_type') === 'response' ? 'selected' : '' }}>Response</option>
                            <option value="surveillance" {{ old('team_type') === 'surveillance' ? 'selected' : '' }}>Surveillance</option>
                            <option value="investigation" {{ old('team_type') === 'investigation' ? 'selected' : '' }}>Investigation</option>
                        </select>
                        @error('team_type')
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
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('zone_id')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Shift Schedule -->
                    <div>
                        <label for="shift" class="admin-label">Shift Schedule</label>
                        <select name="shift" 
                                id="shift" 
                                class="admin-select @error('shift') admin-input-error @enderror"
                                required>
                            <option value="">Select shift...</option>
                            <option value="morning" {{ old('shift') === 'morning' ? 'selected' : '' }}>Morning (6:00 AM - 2:00 PM)</option>
                            <option value="afternoon" {{ old('shift') === 'afternoon' ? 'selected' : '' }}>Afternoon (2:00 PM - 10:00 PM)</option>
                            <option value="night" {{ old('shift') === 'night' ? 'selected' : '' }}>Night (10:00 PM - 6:00 AM)</option>
                        </select>
                        @error('shift')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_number" class="admin-label">Contact Number</label>
                        <input type="tel" 
                               name="contact_number" 
                               id="contact_number" 
                               class="admin-input @error('contact_number') admin-input-error @enderror"
                               value="{{ old('contact_number') }}" 
                               required>
                        @error('contact_number')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="radio_channel" class="admin-label">Radio Channel</label>
                        <input type="text" 
                               name="radio_channel" 
                               id="radio_channel" 
                               class="admin-input @error('radio_channel') admin-input-error @enderror"
                               value="{{ old('radio_channel') }}" 
                               required>
                        @error('radio_channel')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="admin-label">Team Description</label>
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

        <!-- Team Leader -->
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Team Leader</h3>
                
                <div>
                    <label for="leader_id" class="admin-label">Select Team Leader</label>
                    <select name="leader_id" 
                            id="leader_id" 
                            class="admin-select @error('leader_id') admin-input-error @enderror"
                            required>
                        <option value="">Select team leader...</option>
                        @foreach($securityPersonnel as $person)
                            <option value="{{ $person->id }}" {{ old('leader_id') == $person->id ? 'selected' : '' }}>
                                {{ $person->name }} - {{ $person->badge_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('leader_id')
                        <p class="admin-input-error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Initial Team Members -->
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Initial Team Members</h3>
                
                <div>
                    <label for="members" class="admin-label">Select Team Members</label>
                    <select name="members[]" 
                            id="members" 
                            class="admin-select @error('members') admin-input-error @enderror"
                            multiple>
                        @foreach($securityPersonnel as $person)
                            <option value="{{ $person->id }}" {{ in_array($person->id, old('members', [])) ? 'selected' : '' }}>
                                {{ $person->name }} - {{ $person->badge_number }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Hold Ctrl/Cmd to select multiple members
                    </p>
                    @error('members')
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
                Create Team
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-label {
        font-weight: 500;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .card-header {
        border-bottom: none;
        background-color: transparent;
        padding: 1.5rem;
    }
    .card-body {
        padding: 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });

    // Handle zone selection
    document.getElementById('zone_id').addEventListener('change', function() {
        // You can add additional logic here if needed
        // For example, loading checkpoints for the selected zone
    });

    // Handle leader selection
    document.getElementById('leader_id').addEventListener('change', function() {
        // You can add additional logic here if needed
        // For example, updating team contact information based on leader
    });
</script>
@endpush 