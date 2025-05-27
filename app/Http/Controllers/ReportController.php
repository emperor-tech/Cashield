<?php
namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\ReportCreated;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewReportNotification;
use App\Helpers\AuthHelper;

class ReportController extends Controller {
    public function index() {
        $reports = Report::latest()->paginate(10);
        return view('reports.index', compact('reports'));
    }

    public function create() {
        return view('reports.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'campus'      => 'required|string',
            'description' => 'required|string',
            'location'    => 'required|string',
            'severity'    => 'required|in:low,medium,high',
            'anonymous'   => 'sometimes|boolean',
            'media'       => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
            'guest_name'  => 'nullable|string',
            'guest_email' => 'nullable|email',
        ]);

        // Handle media upload
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('reports', 'public');
            $data['media_path'] = $mediaPath;
        }

        // Set user_id or null for guests
        $data['user_id'] = auth()->id();
        if (!$data['user_id']) {
            $data['anonymous'] = true;
        }

        // Optionally store guest info in description
        if (!$data['user_id'] && ($request->guest_name || $request->guest_email)) {
            $data['description'] = "[Guest: {$request->guest_name}, {$request->guest_email}]\n" . $data['description'];
        }

        $report = Report::create($data);
        // broadcast to admins
        \Notification::send(
        \App\Models\User::where('role','admin')->get(),
        new \App\Notifications\NewReportNotification($report)
        );
        // broadcast to public feed
        ReportCreated::dispatch($report);

        return redirect()->route('reports.index')
                         ->with('success', 'Report submitted.');
    }

    public function show(Report $report) {
        return view('reports.show', compact('report'));
    }

    public function panic(Request $request) {
        $data = $request->validate([
            'location' => 'required|string',
            'note'     => 'nullable|string',
        ]);

        // Get the user ID using our custom AuthHelper
        $userId = AuthHelper::id();
        
        if (!$userId) {
            // If no authenticated user, create an anonymous report
            $userId = 1; // Use default "anonymous" user ID
            \Illuminate\Support\Facades\Log::warning('Panic alert created without authentication');
        }

        $data['campus'] = 'Unknown';
        $data['description'] = 'Panic Alert' . ($data['note'] ? (": " . $data['note']) : '');
        $data['severity'] = 'high';
        $data['anonymous'] = true;
        $data['user_id'] = $userId;

        try {
            $report = Report::create($data);
            
            // Notify all admin users
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewReportNotification($report));
            }
            
            // Broadcast to public feed
            ReportCreated::dispatch($report);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Panic alert sent! Help is on the way.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create panic report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send panic alert. Please try again or contact emergency services directly.'
            ], 500);
        }
    }
}
