<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Models\SecurityTeam;
use PDF;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::latest()->paginate(10);
        return view('admin.reports.index', compact('reports'));
    }

    public function show(Report $report)
    {
        return view('admin.reports.show', compact('report'));
    }

    public function pending()
    {
        $reports = Report::whereIn('status', ['open', 'in_progress'])->latest()->paginate(10);
        return view('admin.reports.pending', compact('reports'));
    }

    public function resolved()
    {
        $reports = Report::whereIn('status', ['resolved', 'closed'])->latest()->paginate(10);
        return view('admin.reports.resolved', compact('reports'));
    }

    public function resolve(Report $report)
    {
        $report->update(['status' => 'resolved']);
        return back()->with('success', 'Report marked as resolved.');
    }

    public function assign(Request $request, Report $report)
    {
        $report->update(['assigned_to' => $request->user_id]);
        return back()->with('success', 'Report assigned successfully.');
    }

    public function history(Report $report)
    {
        $history = $report->history();
        return view('admin.reports.history', compact('report', 'history'));
    }

    public function export()
    {
        return view('admin.reports.export');
    }

    public function exportCsv()
    {
        $reports = Report::with('user')->get();
        $filename = 'reports_' . now()->format('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($reports) {
            $handle = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($handle, [
                'ID',
                'Campus',
                'Location',
                'Severity',
                'Status',
                'Description',
                'Reporter',
                'Submitted',
                'Last Updated'
            ]);

            // Add data
            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report->id,
                    $report->campus ?? 'N/A',
                    $report->location,
                    $report->severity,
                    $report->status,
                    $report->description,
                    $report->anonymous ? 'Anonymous' : ($report->user ? $report->user->name : 'N/A'),
                    $report->created_at->format('Y-m-d H:i:s'),
                    $report->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $reports = Report::with('user')->get();
        $filename = 'reports_' . now()->format('Ymd_His') . '.pdf';

        // Create PDF view
        $pdf = PDF::loadView('admin.exports.reports_pdf', compact('reports'));

        return $pdf->download($filename);
    }

    public function updateStatus(Request $request, Report $report)
    {
        $report->update(['status' => $request->status]);
        return back()->with('success', 'Report status updated.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\ReportCategory::orderBy('name')->get();
        $teams = \App\Models\SecurityTeam::where('active', true)->get();
        $users = \App\Models\User::whereIn('role', ['security', 'admin'])->where('is_active', true)->get();
        return view('admin.reports.create', compact('categories', 'teams', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'location' => 'required|string',
            'severity' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $report = Report::create($validated);
        
        return redirect()->route('admin.reports.show', $report)
            ->with('success', 'Report created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $users = User::where('role', 'security')->get();
        $teams = SecurityTeam::where('active', true)->get();
        
        return view('admin.reports.edit', compact('report', 'users', 'teams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'severity' => 'sometimes|in:low,medium,high',
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_team_id' => 'nullable|exists:security_teams,id',
            'resolution_notes' => 'nullable|string',
        ]);

        $report->update($validated);
        
        return redirect()->route('admin.reports.show', $report)
            ->with('success', 'Report updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $report->delete();
        
        return redirect()->route('admin.reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    /**
     * Add a comment to the report.
     */
    public function addComment(Request $request, Report $report)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $report->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }
} 