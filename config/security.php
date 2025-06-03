<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration settings for the application.
    |
    */

    // Two-Factor Authentication
    'two_factor_enabled' => env('TWO_FACTOR_ENABLED', false),
    'force_password_change' => env('FORCE_PASSWORD_CHANGE', false),

    // Session Settings
    'session_timeout' => env('SESSION_TIMEOUT', 120), // minutes
    'single_session' => env('SINGLE_SESSION', false),

    // Password Policy
    'password_expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),
    'password_history_count' => env('PASSWORD_HISTORY_COUNT', 5),
    'min_password_length' => env('MIN_PASSWORD_LENGTH', 8),
    'require_special_chars' => env('REQUIRE_SPECIAL_CHARS', true),

    // Login Security
    'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
    'lockout_duration' => env('LOCKOUT_DURATION', 15), // minutes
    'remember_me_lifetime' => env('REMEMBER_ME_LIFETIME', 7), // days
]; 