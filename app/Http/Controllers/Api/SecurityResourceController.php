<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SecurityResource;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityResourceController extends Controller
{
    public function activeIncidents()
    {
        $incidents = Report::with(['responses.responder', 'responses.updates'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'incidents' => $incidents
        ]);
    }

    public function resources()
    {
        $resources = SecurityResource::with(['currentLocation'])
            ->orderBy('name')
            ->get()
            ->map(function ($resource) {
                return [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'type' => $resource->type,
                    'location' => $resource->currentLocation?->name ?? 'Unknown',
                    'available' => $resource->available,
                    'last_used' => $resource->last_used,
                    'capabilities' => $resource->capabilities
                ];
            });

        return response()->json([
            'resources' => $resources
        ]);
    }

    public function updateResourceStatus(Request $request, SecurityResource $resource)
    {
        $validated = $request->validate([
            'available' => 'required|boolean',
            'location_id' => 'required|exists:locations,id',
            'notes' => 'nullable|string'
        ]);

        $resource->update([
            'available' => $validated['available'],
            'current_location_id' => $validated['location_id'],
            'last_used' => $validated['available'] ? null : now(),
            'notes' => $validated['notes']
        ]);

        return response()->json([
            'message' => 'Resource status updated',
            'resource' => $resource->fresh(['currentLocation'])
        ]);
    }

    public function availableTeams()
    {
        $teams = User::role('security')
            ->whereDoesntHave('activeResponses')
            ->with('currentLocation')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'location' => $user->currentLocation?->name ?? 'Unknown',
                    'specialization' => $user->security_specialization,
                    'status' => 'available'
                ];
            });

        return response()->json([
            'teams' => $teams
        ]);
    }
}