<?php
namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\ReportCreated;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewReportNotification;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportController extends Controller {
    /**
     * Display a listing of the reports.
     */
    public function index() {
        $reports = Report::with('user')
            ->latest()
            ->paginate(10);

        $stats = [
            'high_priority' => Report::where('severity', 'high')->where('status', '!=', 'resolved')->count(),
            'medium_priority' => Report::where('severity', 'medium')->where('status', '!=', 'resolved')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'total' => Report::count(),
        ];

        // Get all active incidents for the map
        $mapIncidents = Report::select('id', 'location', 'latitude', 'longitude', 'severity', 'description', 'created_at', 'status')
            ->where('status', '!=', 'resolved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'location' => $report->location,
                    'lat' => (float) $report->latitude,
                    'lng' => (float) $report->longitude,
                    'severity' => $report->severity,
                    'description' => Str::limit($report->description, 100),
                    'created_at' => $report->created_at->diffForHumans(),
                    'status' => $report->status,
                    'url' => route('reports.show', $report)
                ];
            });

        return view('reports.index', compact('reports', 'stats', 'mapIncidents'));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create() {
        return view('reports.create');
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'evidence.*' => 'nullable|image|max:5120', // 5MB max per image
            'anonymous' => 'boolean',
        ]);

        $report = new Report();
        $report->description = $validated['description'];
        $report->location = $validated['location'];
        $report->severity = $validated['severity'];
        $report->latitude = $validated['latitude'];
        $report->longitude = $validated['longitude'];
        $report->anonymous = $request->boolean('anonymous', false);
        $report->user_id = Auth::id();
        $report->status = 'open';

        // Handle evidence uploads
        if ($request->hasFile('evidence')) {
            $paths = [];
            foreach ($request->file('evidence') as $file) {
                $paths[] = $file->store('evidence', 'public');
            }
            $report->evidence = $paths;
        }

        $report->save();

        // Trigger notifications based on severity
        if ($report->severity === 'high') {
            // TODO: Implement emergency notifications
        }

        return redirect()->route('reports.show', $report)
                         ->with('success', 'Report submitted successfully.');
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Allow viewing if:
        // 1. User is admin or security
        // 2. User is the report creator
        // 3. Report is not marked as anonymous
        // 4. Report is marked as public
        if ($user->hasRole(['admin', 'security']) || 
            $report->user_id === $user->id || 
            !$report->anonymous || 
            $report->is_public) {
            
            $report->load(['user', 'comments.user']);
            return view('reports.show', compact('report'));
        }

        // If not authorized, show a more user-friendly error page
        return response()->view('errors.unauthorized', [
            'message' => 'This report is private or anonymous. You do not have permission to view it.'
        ], 403);
    }

    /**
     * Submit a panic report.
     */
    public function panic()
    {
        try {
            $user = Auth::user();
            
            // Allow unauthenticated users to create panic reports
            // If user is not authenticated, user_id will be null

            $report = Report::create([
                'user_id' => $user ? $user->id : null, // Set user_id to null if not authenticated
                'description' => request('note') ?: 'PANIC BUTTON ACTIVATED - Immediate assistance required',
                'location' => 'User\'s last known location',
                'severity' => 'high',
                'priority_level' => 'high',
                'status' => 'open',
                'campus' => 'Main Campus',
                'latitude' => request('latitude', null),
                'longitude' => request('longitude', null),
                'is_panic' => true,
                'is_public' => true, // Make panic reports public by default
                'incident_date' => now(),
                'status_history' => [[
                    'status' => 'open',
                    // Use user ID if authenticated, otherwise indicate anonymous
                    'user_id' => $user ? $user->id : null,
                    'timestamp' => now()->toIso8601String(),
                    'notes' => 'Initial panic alert created' . ($user ? '': ' (Anonymous)')
                ]]
            ]);

            // If there's a note, add it as a comment only if authenticated
            if (request('note') && $user) {
                $report->comments()->create([
                    'user_id' => $user->id,
                    'content' => request('note'),
                ]);
            }

            // TODO: Implement emergency notifications
            // TODO: Notify security team
            // TODO: Send SMS alerts
            // TODO: Trigger emergency protocols

            return response()->json([
                'success' => true,
                'message' => 'Emergency alert sent successfully',
                'report_id' => $report->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Panic alert creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send emergency alert. Please try again or contact security directly.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Add a comment to a report.
     */
    public function addComment(Request $request, Report $report) {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $report->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Get real-time map updates
     */
    public function mapUpdates()
    {
        $incidents = Report::select('id', 'location', 'latitude', 'longitude', 'severity', 'description', 'created_at', 'status')
            ->where('status', '!=', 'resolved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'location' => $report->location,
                    'lat' => (float) $report->latitude,
                    'lng' => (float) $report->longitude,
                    'severity' => $report->severity,
                    'description' => Str::limit($report->description, 100),
                    'created_at' => $report->created_at->diffForHumans(),
                    'status' => $report->status,
                    'url' => route('reports.show', $report)
                ];
            });

        return response()->json($incidents);
    }
    
    /**
     * Show the form for creating a new anonymous report.
     */
    public function createAnonymous() {
        return view('reports.anonymous.create');
    }
    
    /**
     * Store a newly created anonymous report in storage.
     */
    public function storeAnonymous(Request $request) {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'evidence.*' => 'nullable|image|max:5120', // 5MB max per image
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $report = new Report();
        $report->description = $validated['description'];
        $report->location = $validated['location'];
        $report->severity = $validated['severity'];
        $report->latitude = $validated['latitude'];
        $report->longitude = $validated['longitude'];
        $report->anonymous = true;
        $report->status = 'open';
        
        // Store contact information if provided
        if (isset($validated['contact_email']) || isset($validated['contact_phone'])) {
            $report->contact_info = [
                'email' => $validated['contact_email'] ?? null,
                'phone' => $validated['contact_phone'] ?? null,
            ];
        }

        // Handle evidence uploads
        if ($request->hasFile('evidence')) {
            $paths = [];
            foreach ($request->file('evidence') as $file) {
                $paths[] = $file->store('evidence', 'public');
            }
            $report->evidence = $paths;
        }

        $report->save();

        // Generate a tracking code for anonymous users to check their report status
        $trackingCode = strtoupper(Str::random(8));
        $report->tracking_code = $trackingCode;
        $report->save();

        // Trigger notifications based on severity
        if ($report->severity === 'high') {
            // TODO: Implement emergency notifications
        }

        return view('reports.anonymous.confirmation', [
            'report' => $report,
            'trackingCode' => $trackingCode
        ]);
    }
}
