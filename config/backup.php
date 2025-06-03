<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | This file contains settings for the backup system.
    |
    */

    // Backup Schedule
    'frequency' => env('BACKUP_FREQUENCY', 'daily'),
    'time' => env('BACKUP_TIME', '00:00'),
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    // Storage Settings
    'disk' => env('BACKUP_DISK', 'local'),
    'compress' => env('BACKUP_COMPRESS', true),
    'database_only' => env('BACKUP_DATABASE_ONLY', false),

    // Notification Settings
    'notify_on_success' => env('BACKUP_NOTIFY_SUCCESS', true),
    'notify_on_failure' => env('BACKUP_NOTIFY_FAILURE', true),
    'notification_email' => env('BACKUP_NOTIFICATION_EMAIL'),

    // Cloud Storage Settings
    's3' => [
        'bucket' => env('AWS_BUCKET'),
        'region' => env('AWS_DEFAULT_REGION'),
    ],
    'google' => [
        'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
        'path' => env('BACKUP_GOOGLE_CLOUD_PATH', 'backups'),
    ],

    // Backup Sources
    'include_files' => [
        base_path('.env'),
        base_path('composer.json'),
        app_path(),
        config_path(),
        database_path(),
        resource_path(),
        storage_path('app/public'),
    ],
    'exclude_files' => [
        base_path('node_modules'),
        base_path('vendor'),
        storage_path('framework'),
        storage_path('logs'),
    ],
]; 