<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;

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
        $reports = Report::where('status', 'pending')->latest()->paginate(10);
        return view('admin.reports.pending', compact('reports'));
    }

    public function resolved()
    {
        $reports = Report::where('status', 'resolved')->latest()->paginate(10);
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
        $history = $report->history()->latest()->get();
        return view('admin.reports.history', compact('report', 'history'));
    }

    public function export()
    {
        return view('admin.reports.export');
    }

    public function exportCsv()
    {
        $reports = Report::all();
        // CSV export logic here
        return response()->download('reports.csv');
    }

    public function exportPdf()
    {
        $reports = Report::all();
        // PDF export logic here
        return response()->download('reports.pdf');
    }

    public function updateStatus(Request $request, Report $report)
    {
        $report->update(['status' => $request->status]);
        return back()->with('success', 'Report status updated.');
    }
} 