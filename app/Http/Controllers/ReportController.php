<?php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Events\ReportCreated;

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
        $data['campus'] = 'Unknown';
        $data['description'] = 'Panic Alert' . ($data['note'] ? (": " . $data['note']) : '');
        $data['severity'] = 'high';
        $data['anonymous'] = true;
        $data['user_id'] = auth()->id();
        $report = Report::create($data);
        // broadcast to admins
        \Notification::send(
            \App\Models\User::where('role','admin')->get(),
            new \App\Notifications\NewReportNotification($report)
        );
        // broadcast to public feed
        ReportCreated::dispatch($report);
        return redirect()->back()->with('success', 'Panic alert sent! Help is on the way.');
    }
}
