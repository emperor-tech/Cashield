<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | This file contains settings for various third-party service integrations.
    |
    */

    // SMS Gateway
    'sms_provider' => env('SMS_PROVIDER', ''),
    'sms_api_key' => env('SMS_API_KEY', ''),
    'sms_api_secret' => env('SMS_API_SECRET', ''),
    'sms_from_number' => env('SMS_FROM_NUMBER', ''),

    // Email Service
    'mail_provider' => env('MAIL_PROVIDER', 'smtp'),
    'mail_api_key' => env('MAIL_API_KEY', ''),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@cashield.com'),
    'mail_from_name' => env('MAIL_FROM_NAME', 'Cashield'),

    // Push Notification Service
    'push_provider' => env('PUSH_PROVIDER', 'firebase'),
    'firebase_server_key' => env('FIREBASE_SERVER_KEY', ''),
    'firebase_sender_id' => env('FIREBASE_SENDER_ID', ''),

    // Maps Integration
    'maps_provider' => env('MAPS_PROVIDER', 'google'),
    'google_maps_key' => env('GOOGLE_MAPS_KEY', ''),
    'maps_default_center' => [
        'lat' => env('MAPS_DEFAULT_LAT', 0),
        'lng' => env('MAPS_DEFAULT_LNG', 0),
    ],
]; 