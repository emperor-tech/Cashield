@extends('admin.security.layout')

@section('security_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Security Teams</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage and monitor security teams</p>
        </div>
        <div>
            <a href="{{ route('admin.security.teams.create') }}" class="admin-btn-primary">
                <i class="fas fa-plus mr-2"></i>
                New Team
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filter">
        <form action="{{ route('admin.security.teams.index') }}" method="GET" class="admin-filter-group">
            <div class="flex-1 min-w-[200px]">
                <div class="admin-search">
                    <div class="admin-search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="admin-search-input"
                        placeholder="Search teams...">
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
                <a href="{{ route('admin.security.teams.index') }}" class="admin-btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Teams Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($teams as $team)
            <div class="admin-card hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <!-- Team Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <img class="h-10 w-10 rounded-full" 
                                     src="https://ui-avatars.com/api/?name={{ urlencode($team->name) }}" 
                                     alt="{{ $team->name }}">
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $team->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $team->team_type }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="admin-badge {{ $team->active ? 'admin-badge-green' : 'admin-badge-red' }}">
                                {{ $team->active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <!-- Team Info -->
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-user-shield text-gray-400 w-5"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                Leader: {{ $team->leader->name }}
                            </span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                Zone: {{ $team->zone->name }}
                            </span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-users text-gray-400 w-5"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                Members: {{ $team->members->count() }}
                            </span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-clock text-gray-400 w-5"></i>
                            <span class="text-gray-600 dark:text-gray-300">
                                Shift: {{ $team->shift }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.security.teams.show', $team) }}" 
                               class="admin-btn-secondary">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>
                            <a href="{{ route('admin.security.teams.edit', $team) }}" 
                               class="admin-btn-secondary">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </a>
                        </div>
                        <form action="{{ route('admin.security.teams.toggle-status', $team) }}" 
                              method="POST" 
                              class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="admin-btn-secondary {{ $team->active ? 'text-red-600' : 'text-green-600' }}">
                                <i class="fas {{ $team->active ? 'fa-ban' : 'fa-check' }} mr-1"></i>
                                {{ $team->active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <i class="fas fa-users text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No teams found</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Get started by creating a new security team.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.security.teams.create') }}" class="admin-btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Create Team
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($teams->hasPages())
        <div class="admin-pagination">
            {{ $teams->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .dropdown-toggle::after {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Handle filter form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault()
        const formData = new FormData(this)
        const searchParams = new URLSearchParams()
        
        for (const pair of formData.entries()) {
            if (pair[1]) {
                searchParams.append(pair[0], pair[1])
            }
        }

        window.location.search = searchParams.toString()
    })

    // Handle status toggle confirmation
    document.querySelectorAll('form[action*="toggle-status"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault()
            if (confirm('Are you sure you want to change this team\'s status?')) {
                this.submit()
            }
        })
    })
</script>
@endpush 