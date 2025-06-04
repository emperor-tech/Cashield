@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">General Settings</h1>
    </div>

    <!-- Settings Form -->
    <div class="admin-card p-6">
        <form action="{{ route('admin.settings.general') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Site Information -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Site Information</h2>
                
                <div>
                    <label for="site_name" class="admin-label">Site Name</label>
                    <input type="text" 
                           name="site_name" 
                           id="site_name" 
                           class="admin-input" 
                           value="{{ config('app.name') }}">
                </div>

                <div>
                    <label for="site_description" class="admin-label">Site Description</label>
                    <textarea name="site_description" 
                              id="site_description" 
                              class="admin-textarea" 
                              rows="3">{{ config('app.description') }}</textarea>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Contact Information</h2>
                
                <div>
                    <label for="contact_email" class="admin-label">Contact Email</label>
                    <input type="email" 
                           name="contact_email" 
                           id="contact_email" 
                           class="admin-input" 
                           value="{{ config('app.contact_email') }}">
                </div>

                <div>
                    <label for="emergency_phone" class="admin-label">Emergency Contact Number</label>
                    <input type="tel" 
                           name="emergency_phone" 
                           id="emergency_phone" 
                           class="admin-input" 
                           value="{{ config('app.emergency_phone') }}">
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="admin-btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 