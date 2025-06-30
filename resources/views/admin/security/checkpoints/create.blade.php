@extends('admin.security.layout')

@section('security_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create Checkpoint</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Define a new security checkpoint</p>
        </div>
        <div>
            <a href="{{ route('admin.security.checkpoints.index') }}" class="admin-btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Checkpoints
            </a>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.security.checkpoints.store') }}" class="space-y-6">
        @csrf
        <div class="admin-card p-6">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Checkpoint Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="admin-label">Checkpoint Name</label>
                        <input type="text" name="name" id="name" class="admin-input @error('name') admin-input-error @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="zone_id" class="admin-label">Zone</label>
                        <select name="zone_id" id="zone_id" class="admin-select @error('zone_id') admin-input-error @enderror" required>
                            <option value="">Select zone...</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                        @error('zone_id')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="latitude" class="admin-label">Location (Latitude)</label>
                        <input type="text" name="latitude" id="latitude" class="admin-input @error('latitude') admin-input-error @enderror" value="{{ old('latitude') }}" required>
                        @error('latitude')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="admin-label">Location (Longitude)</label>
                        <input type="text" name="longitude" id="longitude" class="admin-input @error('longitude') admin-input-error @enderror" value="{{ old('longitude') }}" required>
                        @error('longitude')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="admin-label">Description</label>
                        <textarea name="description" id="description" class="admin-textarea @error('description') admin-input-error @enderror" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="admin-input-error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="reset" class="admin-btn-secondary">
                <i class="fas fa-undo mr-2"></i>
                Reset Form
            </button>
            <button type="submit" class="admin-btn-primary">
                <i class="fas fa-save mr-2"></i>
                Create Checkpoint
            </button>
        </div>
    </form>
</div>
@endsection 