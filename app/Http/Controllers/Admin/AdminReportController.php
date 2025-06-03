<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PDF;

class AdminReportController extends Controller
{
    /**
     * Display a listing of the reports.
     */
    public function index(Request $request)
    {
        $query = Report::query();
        
        // Apply filters
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get paginated reports
        $reports = $query->with(['user'])->latest()->paginate(10);

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Show the report details.
     */
    public function show(Report $report)
    {
        $report->load(['user', 'comments.user']);
        return view('admin.report', compact('report'));
    }

    /**
     * Update the report status.
     */
    public function updateStatus(Request $request, Report $report)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved',
        ]);

        $oldStatus = $report->status;
        $report->status = $request->status;
        $report->save();

        // Log the status change
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_status',
            'subject_type' => 'report',
            'subject_id' => $report->id,
            'details' => [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ],
        ]);

        return back()->with('success', 'Report status updated successfully.');
    }

    /**
     * Export reports as CSV.
     */
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
                    $report->campus,
                    $report->location,
                    $report->severity,
                    $report->status,
                    $report->description,
                    $report->anonymous ? 'Anonymous' : $report->user->name,
                    $report->created_at->format('Y-m-d H:i:s'),
                    $report->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        };

        // Log the export
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'export_reports',
            'subject_type' => 'report',
            'subject_id' => 0,
            'details' => ['format' => 'csv'],
        ]);

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export reports as PDF.
     */
    public function exportPdf(Request $request)
    {
        // If a specific report ID is provided, export just that report
        if ($request->has('id')) {
            $reports = Report::with('user')->where('id', $request->id)->get();
            $filename = 'report_' . $request->id . '_' . now()->format('Ymd_His') . '.pdf';
        } else {
            $reports = Report::with('user')->get();
            $filename = 'reports_' . now()->format('Ymd_His') . '.pdf';
        }

        $pdf = PDF::loadView('admin.exports.reports_pdf', compact('reports'));

        // Log the export
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'export_reports',
            'subject_type' => 'report',
            'subject_id' => $request->id ?? 0,
            'details' => ['format' => 'pdf'],
        ]);

        return $pdf->download($filename);
    }

    /**
     * Show pending reports.
     */
    public function pending()
    {
        $reports = Report::where('status', 'open')
            ->orWhere('status', 'in_progress')
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.reports.pending', compact('reports'));
    }

    /**
     * Show resolved reports.
     */
    public function resolved()
    {
        $reports = Report::where('status', 'resolved')
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.reports.resolved', compact('reports'));
    }

    /**
     * Mark a report as resolved.
     */
    public function resolve(Report $report)
    {
        $oldStatus = $report->status;
        $report->status = 'resolved';
        $report->resolved_at = now();
        $report->save();

        // Log the resolution
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'resolve_report',
            'subject_type' => 'report',
            'subject_id' => $report->id,
            'details' => [
                'old_status' => $oldStatus,
                'new_status' => 'resolved',
            ],
        ]);

        return back()->with('success', 'Report marked as resolved.');
    }

    /**
     * Assign a report to a user or team.
     */
    public function assign(Request $request, Report $report)
    {
        $request->validate([
            'assignee_type' => 'required|in:user,team',
            'assignee_id' => 'required|integer',
        ]);

        if ($request->assignee_type === 'user') {
            $report->assigned_user_id = $request->assignee_id;
            $report->assigned_team_id = null;
        } else {
            $report->assigned_team_id = $request->assignee_id;
            $report->assigned_user_id = null;
        }

        $report->save();

        // Log the assignment
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'assign_report',
            'subject_type' => 'report',
            'subject_id' => $report->id,
            'details' => [
                'assignee_type' => $request->assignee_type,
                'assignee_id' => $request->assignee_id,
            ],
        ]);

        return back()->with('success', 'Report assigned successfully.');
    }

    /**
     * Show report history.
     */
    public function history(Report $report)
    {
        $history = AuditLog::where('subject_type', 'report')
            ->where('subject_id', $report->id)
            ->with('user')
            ->latest()
            ->get();

        return view('admin.reports.history', compact('report', 'history'));
    }

    /**
     * Export report data.
     */
    public function export(Request $request)
    {
        return view('admin.reports.export');
    }
} 