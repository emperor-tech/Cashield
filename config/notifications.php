<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | This file contains notification-related configuration settings.
    |
    */

    // Email Notifications
    'email_new_report' => env('EMAIL_NEW_REPORT', true),
    'email_report_updates' => env('EMAIL_REPORT_UPDATES', true),
    'email_daily_digest' => env('EMAIL_DAILY_DIGEST', false),
    'email_weekly_summary' => env('EMAIL_WEEKLY_SUMMARY', true),

    // Push Notifications
    'push_enabled' => env('PUSH_NOTIFICATIONS_ENABLED', true),
    'push_severity' => env('PUSH_MIN_SEVERITY', 'medium'),
    'push_provider' => env('PUSH_PROVIDER', 'firebase'),

    // SMS Notifications
    'sms_enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),
    'sms_provider' => env('SMS_PROVIDER', 'twilio'),
    'sms_critical_only' => env('SMS_CRITICAL_ONLY', true),

    // Notification Preferences
    'allow_user_preferences' => env('ALLOW_USER_NOTIFICATION_PREFERENCES', true),
    'default_quiet_hours' => [
        'start' => env('DEFAULT_QUIET_HOURS_START', '22:00'),
        'end' => env('DEFAULT_QUIET_HOURS_END', '07:00'),
    ],
]; 