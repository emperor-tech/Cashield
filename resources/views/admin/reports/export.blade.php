@extends('layouts.admin')

@section('page_title', 'Export Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Export Reports</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Export reports in various formats</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Export Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- CSV Export -->
        <div class="admin-card p-6">
            <div class="flex items-center space-x-4">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <i class="fas fa-file-csv text-xl text-green-600 dark:text-green-300"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">CSV Export</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Export reports in CSV format for spreadsheet applications
                    </p>
                </div>
                <a href="{{ route('admin.reports.export.csv') }}" class="admin-btn-primary">
                    <i class="fas fa-download mr-2"></i>
                    Export CSV
                </a>
            </div>
            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <h4 class="font-medium mb-2">Includes:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>Report ID</li>
                    <li>Campus</li>
                    <li>Location</li>
                    <li>Severity</li>
                    <li>Status</li>
                    <li>Description</li>
                    <li>Reporter</li>
                    <li>Submission Date</li>
                    <li>Last Update</li>
                </ul>
            </div>
        </div>

        <!-- PDF Export -->
        <div class="admin-card p-6">
            <div class="flex items-center space-x-4">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-file-pdf text-xl text-blue-600 dark:text-blue-300"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">PDF Export</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Export reports in PDF format for printing and sharing
                    </p>
                </div>
                <a href="{{ route('admin.reports.export.pdf') }}" class="admin-btn-primary">
                    <i class="fas fa-download mr-2"></i>
                    Export PDF
                </a>
            </div>
            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <h4 class="font-medium mb-2">Features:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>Professional formatting</li>
                    <li>Detailed report information</li>
                    <li>Organized tables</li>
                    <li>Easy to read layout</li>
                    <li>Print-ready format</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Export Tips -->
    <div class="admin-card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Export Tips</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <h4 class="font-medium text-gray-900 dark:text-white">When to use CSV?</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Use CSV format when you need to:
                </p>
                <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li>Import data into spreadsheets</li>
                    <li>Perform data analysis</li>
                    <li>Create custom reports</li>
                </ul>
            </div>

            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <h4 class="font-medium text-gray-900 dark:text-white">When to use PDF?</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Use PDF format when you need to:
                </p>
                <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li>Share reports with stakeholders</li>
                    <li>Print physical copies</li>
                    <li>Archive reports</li>
                </ul>
            </div>

            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <h4 class="font-medium text-gray-900 dark:text-white">Best Practices</h4>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li>Export regularly for backup</li>
                    <li>Use filters before exporting</li>
                    <li>Check exported data accuracy</li>
                    <li>Store exports securely</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection 