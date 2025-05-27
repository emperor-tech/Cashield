<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function index(Request $request)
    {
        $preferences = NotificationPreference::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'enabled' => true,
                'notification_methods' => ['web', 'email'],
                'incident_type' => 'all',
                'severity_level' => 'all',
                'area_radius' => 1,
            ]
        );

        return response()->json([
            'preferences' => $preferences
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'notification_methods' => 'required|array',
            'notification_methods.*' => 'in:web,email,sms',
            'incident_type' => 'required|string',
            'severity_level' => 'required|string',
            'area_radius' => 'nullable|numeric|min:0.5|max:5',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        $preference = NotificationPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        // If location is provided, update user's last known location
        if ($request->has(['location_lat', 'location_lng'])) {
            auth()->user()->update([
                'last_location_lat' => $request->location_lat,
                'last_location_lng' => $request->location_lng,
            ]);
        }

        return response()->json([
            'message' => 'Preferences updated successfully',
            'preferences' => $preference
        ]);
    }

    public function test(Request $request)
    {
        // Send a test notification to verify settings
        $user = auth()->user();
        $preference = $user->notificationPreferences;

        foreach ($preference->notification_methods as $method) {
            switch ($method) {
                case 'web':
                    $user->notify(new \App\Notifications\TestNotification('web'));
                    break;
                case 'email':
                    $user->notify(new \App\Notifications\TestNotification('email'));
                    break;
                case 'sms':
                    $user->notify(new \App\Notifications\TestNotification('sms'));
                    break;
            }
        }

        return response()->json([
            'message' => 'Test notifications sent successfully'
        ]);
    }
}