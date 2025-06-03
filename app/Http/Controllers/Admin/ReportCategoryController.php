<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportCategory;
use Illuminate\Http\Request;

class ReportCategoryController extends Controller
{
    /**
     * Display a listing of report categories.
     */
    public function index()
    {
        $categories = ReportCategory::withCount('reports')
            ->orderBy('name')
            ->get();

        return view('admin.reports.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('admin.reports.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:report_categories'],
            'description' => ['nullable', 'string', 'max:1000'],
            'severity_level' => ['required', 'in:low,medium,high'],
            'response_time' => ['nullable', 'integer', 'min:1'],
            'requires_approval' => ['boolean'],
        ]);

        ReportCategory::create($request->all());

        return redirect()->route('admin.reports.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(ReportCategory $category)
    {
        return view('admin.reports.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, ReportCategory $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:report_categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'severity_level' => ['required', 'in:low,medium,high'],
            'response_time' => ['nullable', 'integer', 'min:1'],
            'requires_approval' => ['boolean'],
        ]);

        $category->update($request->all());

        return redirect()->route('admin.reports.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(ReportCategory $category)
    {
        if ($category->reports()->exists()) {
            return back()->with('error', 'Cannot delete category with associated reports.');
        }

        $category->delete();

        return redirect()->route('admin.reports.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
} 