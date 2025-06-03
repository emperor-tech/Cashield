@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Backup Settings</h1>
    </div>

    <!-- Settings Form -->
    <div class="admin-card">
        <form action="{{ route('admin.settings.backup') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Backup Schedule -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Backup Schedule</h2>
                
                <div>
                    <label for="backup_frequency" class="admin-label">Backup Frequency</label>
                    <select name="backup_frequency" id="backup_frequency" class="admin-select">
                        <option value="daily" {{ config('backup.frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ config('backup.frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ config('backup.frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>

                <div>
                    <label for="backup_time" class="admin-label">Backup Time</label>
                    <input type="time" 
                           name="backup_time" 
                           id="backup_time" 
                           class="admin-input" 
                           value="{{ config('backup.time', '00:00') }}">
                </div>

                <div>
                    <label for="retention_days" class="admin-label">Retention Period (days)</label>
                    <input type="number" 
                           name="retention_days" 
                           id="retention_days" 
                           class="admin-input" 
                           value="{{ config('backup.retention_days', 30) }}"
                           min="1">
                </div>
            </div>

            <!-- Storage Settings -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Storage Settings</h2>
                
                <div>
                    <label for="storage_disk" class="admin-label">Storage Disk</label>
                    <select name="storage_disk" id="storage_disk" class="admin-select">
                        <option value="local" {{ config('backup.disk') === 'local' ? 'selected' : '' }}>Local</option>
                        <option value="s3" {{ config('backup.disk') === 's3' ? 'selected' : '' }}>Amazon S3</option>
                        <option value="google" {{ config('backup.disk') === 'google' ? 'selected' : '' }}>Google Drive</option>
                    </select>
                </div>

                <div>
                    <label class="admin-label flex items-center">
                        <input type="checkbox" 
                               name="compress_backup" 
                               class="admin-checkbox mr-2" 
                               {{ config('backup.compress') ? 'checked' : '' }}>
                        Compress Backups
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Compress backup files to save storage space
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