@extends('admin.security.layout')

@section('security_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Checkpoints</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage and monitor security checkpoints</p>
        </div>
        <div>
            <a href="{{ route('admin.security.checkpoints.create') }}" class="admin-btn-primary">
                <i class="fas fa-plus mr-2"></i>
                New Checkpoint
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filter">
        <form action="{{ route('admin.security.checkpoints.index') }}" method="GET" class="admin-filter-group">
            <div class="flex-1 min-w-[200px]">
                <div class="admin-search">
                    <div class="admin-search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="admin-search-input"
                        placeholder="Search checkpoints...">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <select name="zone" class="admin-select">
                    <option value="">All Zones</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ request('zone') == $zone->id ? 'selected' : '' }}>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <select name="status" class="admin-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="admin-btn-secondary">
                <i class="fas fa-filter mr-2"></i>
                Filter
            </button>
            @if(request()->hasAny(['search', 'zone', 'status']))
                <a href="{{ route('admin.security.checkpoints.index') }}" class="admin-btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Checkpoints Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($checkpoints as $checkpoint)
            <div class="admin-card hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $checkpoint->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Zone: {{ $checkpoint->zone->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <span class="admin-badge {{ $checkpoint->active ? 'admin-badge-green' : 'admin-badge-red' }}">
                                {{ $checkpoint->active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                Lat: {{ $checkpoint->latitude }}, Lng: {{ $checkpoint->longitude }}
                            </span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-align-left text-gray-400 w-5"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                {{ $checkpoint->description }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.security.checkpoints.show', $checkpoint) }}" class="admin-btn-secondary">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <a href="{{ route('admin.security.checkpoints.edit', $checkpoint) }}" class="admin-btn-secondary">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </div>
                        <form action="{{ route('admin.security.checkpoints.toggle-status', $checkpoint) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="admin-btn-secondary {{ $checkpoint->active ? 'text-red-600' : 'text-green-600' }}">
                                <i class="fas {{ $checkpoint->active ? 'fa-ban' : 'fa-check' }} mr-1"></i>
                                {{ $checkpoint->active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <i class="fas fa-map-marker-alt text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No checkpoints found</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Get started by creating a new checkpoint.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.security.checkpoints.create') }}" class="admin-btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Create Checkpoint
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    @if($checkpoints->hasPages())
        <div class="admin-pagination">
            {{ $checkpoints->links() }}
        </div>
    @endif
</div>
@endsection 