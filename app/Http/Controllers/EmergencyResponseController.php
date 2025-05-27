<?php

namespace App\Http\Controllers;

use App\Models\EmergencyResponse;
use App\Models\Report;
use App\Models\ResponseUpdate;
use Illuminate\Http\Request;
use App\Events\EmergencyResponseUpdated;

class EmergencyResponseController extends Controller
{
    public function respond(Request $request, Report $report)
    {
        $validated = $request->validate([
            'eta_minutes' => 'required|integer|min:1',
            'resources_needed' => 'nullable|array',
            'action_taken' => 'required|string|max:500',
        ]);

        $response = EmergencyResponse::create([
            'report_id' => $report->id,
            'responder_id' => auth()->id(),
            'status' => 'en_route',
            'eta_minutes' => $validated['eta_minutes'],
            'action_taken' => $validated['action_taken'],
            'resources_needed' => $validated['resources_needed'] ?? [],
            'location_lat' => $request->input('location_lat'),
            'location_lng' => $request->input('location_lng'),
        ]);

        broadcast(new EmergencyResponseUpdated($response))->toOthers();

        return response()->json([
            'message' => 'Response team dispatched',
            'response' => $response->load('responder')
        ]);
    }

    public function update(Request $request, EmergencyResponse $response)
    {
        $validated = $request->validate([
            'status' => 'required|in:en_route,on_scene,resolved,withdrawn',
            'message' => 'required|string|max:500',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        $update = new ResponseUpdate([
            'emergency_response_id' => $response->id,
            'status' => $validated['status'],
            'message' => $validated['message'],
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
        ]);

        if ($request->hasFile('image')) {
            $update->image_path = $request->file('image')->store('response-updates', 'public');
        }

        $update->save();
        $response->update(['status' => $validated['status']]);

        broadcast(new EmergencyResponseUpdated($response->fresh()->load('updates')))->toOthers();

        return response()->json([
            'message' => 'Response updated',
            'response' => $response->load(['updates', 'responder'])
        ]);
    }

    public function show(EmergencyResponse $response)
    {
        return response()->json([
            'response' => $response->load(['updates', 'responder', 'report'])
        ]);
    }

    public function index(Report $report)
    {
        return response()->json([
            'responses' => $report->emergencyResponses()
                ->with(['updates', 'responder'])
                ->get()
        ]);
    }
}