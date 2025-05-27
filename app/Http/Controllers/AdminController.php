<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Report;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $reports = Report::latest()->get();
        $users = User::all();
        $stats = [
            'total_reports' => $reports->count(),
            'total_users' => $users->count(),
            'panic_alerts' => $reports->where('severity', 'high')->count(),
            'today_reports' => $reports->where('created_at', '>=', now()->startOfDay())->count(),
        ];
        return view('admin.index', compact('reports', 'users', 'stats'));
    }

    public function updateReportStatus(Request $request, Report $report)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved',
        ]);
        $old_status = $report->getOriginal('status');
        $report->status = $request->status;
        $report->save();
        AuditLog::create([
            'user_id' => AuthHelper::id(),
            'action' => 'update_status',
            'subject_type' => 'report',
            'subject_id' => $report->id,
            'details' => ['old_status' => $old_status, 'new_status' => $request->status],
        ]);
        return back()->with('success', 'Report status updated.');
    }

    public function showReport(Report $report)
    {
        $report->load(['user', 'comments.user']);
        return view('admin.report', compact('report'));
    }

    public function addComment(Request $request, Report $report)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);
        $report->comments()->create([
            'user_id' => AuthHelper::id(),
            'comment' => $request->comment,
        ]);
        AuditLog::create([
            'user_id' => AuthHelper::id(),
            'action' => 'add_comment',
            'subject_type' => 'report',
            'subject_id' => $report->id,
            'details' => ['comment' => $request->comment],
        ]);
        return back()->with('success', 'Comment added.');
    }

    public function exportCsv()
    {
        $reports = \App\Models\Report::with('user')->get();
        $filename = 'reports_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function() use ($reports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Campus', 'Location', 'Severity', 'Status', 'Description', 'User', 'Date']);
            foreach ($reports as $r) {
                fputcsv($handle, [
                    $r->id, $r->campus, $r->location, $r->severity, $r->status, $r->description, $r->user ? $r->user->name : 'Guest', $r->created_at
                ]);
            }
            fclose($handle);
        };
        AuditLog::create([
            'user_id' => AuthHelper::id(),
            'action' => 'export_reports',
            'subject_type' => 'report',
            'subject_id' => 0,
            'details' => ['format' => 'csv'],
        ]);
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $reports = \App\Models\Report::with('user')->get();
        $pdf = \PDF::loadView('admin.exports.reports_pdf', compact('reports'));
        AuditLog::create([
            'user_id' => AuthHelper::id(),
            'action' => 'export_reports',
            'subject_type' => 'report',
            'subject_id' => 0,
            'details' => ['format' => 'pdf'],
        ]);
        return $pdf->download('reports_' . now()->format('Ymd_His') . '.pdf');
    }
} 