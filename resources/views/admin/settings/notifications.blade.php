@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notification Settings</h1>
    </div>

    <!-- Settings Form -->
    <div class="admin-card">
        <form action="{{ route('admin.settings.notifications') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Email Notifications -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email Notifications</h2>
                
                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="email_new_report" 
                               class="admin-checkbox mr-2" 
                               {{ config('notifications.email_new_report') ? 'checked' : '' }}>
                        New Report Notifications
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Send email notifications when new reports are submitted
                    </p>
                </div>

                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="email_report_updates" 
                               class="admin-checkbox mr-2" 
                               {{ config('notifications.email_report_updates') ? 'checked' : '' }}>
                        Report Update Notifications
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Send email notifications when reports are updated
                    </p>
                </div>
            </div>

            <!-- Push Notifications -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Push Notifications</h2>
                
                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="push_enabled" 
                               class="admin-checkbox mr-2" 
                               {{ config('notifications.push_enabled') ? 'checked' : '' }}>
                        Enable Push Notifications
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Enable browser push notifications for important updates
                    </p>
                </div>

                <div>
                    <label for="push_severity" class="admin-label">Minimum Severity for Push Notifications</label>
                    <select name="push_severity" id="push_severity" class="admin-select">
                        <option value="low" {{ config('notifications.push_severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ config('notifications.push_severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ config('notifications.push_severity') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ config('notifications.push_severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
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