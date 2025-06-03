@extends('layouts.admin')

@section('page_title', 'Report Categories')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Report Categories</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage report categories and their settings</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.categories.create') }}" class="admin-btn-primary">
                <i class="fas fa-plus mr-2"></i>
                New Category
            </a>
        </div>
    </div>

    <!-- Categories List -->
    <div class="admin-card">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Severity</th>
                        <th>Response Time</th>
                        <th>Reports</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="font-medium">{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 50) }}</td>
                            <td>
                                <span class="admin-badge
                                    @if($category->severity_level === 'high')
                                        admin-badge-red
                                    @elseif($category->severity_level === 'medium')
                                        admin-badge-yellow
                                    @else
                                        admin-badge-green
                                    @endif">
                                    {{ ucfirst($category->severity_level) }}
                                </span>
                            </td>
                            <td>
                                @if($category->response_time)
                                    {{ $category->response_time }} minutes
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td>{{ $category->reports_count }}</td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.reports.categories.edit', $category) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($category->reports_count === 0)
                                        <form action="{{ route('admin.reports.categories.destroy', $category) }}" 
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-folder-open text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-500">No categories found</p>
                                    <a href="{{ route('admin.reports.categories.create') }}" 
                                       class="mt-3 admin-btn-secondary">
                                        Create First Category
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 