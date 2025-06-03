@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Security Settings</h1>
    </div>

    <!-- Settings Form -->
    <div class="admin-card">
        <form action="{{ route('admin.settings.security') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Authentication Settings -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Authentication</h2>
                
                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="two_factor_enabled" 
                               class="admin-checkbox mr-2" 
                               {{ config('security.two_factor_enabled') ? 'checked' : '' }}>
                        Enable Two-Factor Authentication
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Require two-factor authentication for all admin users
                    </p>
                </div>

                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="force_password_change" 
                               class="admin-checkbox mr-2" 
                               {{ config('security.force_password_change') ? 'checked' : '' }}>
                        Force Password Change
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Force users to change their password every 90 days
                    </p>
                </div>
            </div>

            <!-- Session Settings -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Session</h2>
                
                <div>
                    <label for="session_timeout" class="admin-label">Session Timeout (minutes)</label>
                    <input type="number" 
                           name="session_timeout" 
                           id="session_timeout" 
                           class="admin-input" 
                           value="{{ config('security.session_timeout', 120) }}"
                           min="1">
                </div>

                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="single_session" 
                               class="admin-checkbox mr-2" 
                               {{ config('security.single_session') ? 'checked' : '' }}>
                        Single Session
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Allow only one active session per user
                    </p>
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