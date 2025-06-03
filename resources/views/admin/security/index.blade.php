@extends('layouts.admin')

@section('page_title', 'Security Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Security Management</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage security teams and resources</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="admin-btn-primary" onclick="document.getElementById('createTeamModal').classList.remove('hidden')">
                <i class="fas fa-plus mr-2"></i>
                Create Team
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stats-card">
            <div class="flex items-center">
                <div class="stats-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Teams</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_teams'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center">
                <div class="stats-icon green">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">On Duty</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['on_duty'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center">
                <div class="stats-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Shifts</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_shifts'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center">
                <div class="stats-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Alerts</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_alerts'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Teams -->
    <div class="admin-card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Security Teams</h3>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Leader</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Zone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teams as $team)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <span class="font-medium">{{ $team->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full mr-2" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($team->leader->name) }}" 
                                         alt="{{ $team->leader->name }}">
                                    <span>{{ $team->leader->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex -space-x-2">
                                    @foreach($team->members->take(3) as $member)
                                        <img class="h-8 w-8 rounded-full border-2 border-white dark:border-gray-800" 
                                             src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}" 
                                             alt="{{ $member->name }}"
                                             title="{{ $member->name }}">
                                    @endforeach
                                    @if($team->members->count() > 3)
                                        <div class="h-8 w-8 rounded-full border-2 border-white dark:border-gray-800 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-xs text-gray-600 dark:text-gray-300">+{{ $team->members->count() - 3 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="admin-badge {{ $team->active ? 'admin-badge-green' : 'admin-badge-red' }}">
                                    {{ $team->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                    {{ $team->zone->name ?? 'Unassigned' }}
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button type="button" 
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                            onclick="showTeamDetails({{ $team->id }})"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button"
                                            class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300"
                                            onclick="editTeam({{ $team->id }})"
                                            title="Edit Team">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.security.teams.toggle', $team) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                            class="{{ $team->active ? 'text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300' : 'text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300' }}"
                                            title="{{ $team->active ? 'Deactivate Team' : 'Activate Team' }}">
                                            <i class="fas {{ $team->active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No security teams found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teams->hasPages())
            <div class="mt-4">
                {{ $teams->links() }}
            </div>
        @endif
    </div>

    <!-- Active Shifts -->
    <div class="admin-card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Active Shifts</h3>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Personnel</th>
                        <th>Team</th>
                        <th>Zone</th>
                        <th>Started At</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeShifts as $shift)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full mr-2" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($shift->user->name) }}" 
                                         alt="{{ $shift->user->name }}">
                                    <span>{{ $shift->user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $shift->team->name }}</td>
                            <td>{{ $shift->zone->name }}</td>
                            <td>{{ $shift->started_at->format('M d, Y H:i') }}</td>
                            <td>{{ $shift->started_at->diffForHumans(null, true) }}</td>
                            <td>
                                <span class="admin-badge admin-badge-green">On Duty</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No active shifts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Team Modal -->
<div id="createTeamModal" class="admin-modal hidden" x-cloak>
    <div class="admin-modal-overlay"></div>
    <div class="admin-modal-content">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create Security Team</h3>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="this.closest('.admin-modal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.security.teams.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Team Name</label>
                    <input type="text" name="name" id="name" required class="admin-input mt-1" placeholder="Enter team name">
                </div>

                <div>
                    <label for="leader_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Team Leader</label>
                    <select name="leader_id" id="leader_id" required class="admin-select mt-1">
                        <option value="">Select a leader</option>
                        @foreach($securityPersonnel as $person)
                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="zone_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Zone</label>
                    <select name="zone_id" id="zone_id" required class="admin-select mt-1">
                        <option value="">Select a zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="admin-btn-secondary" onclick="this.closest('.admin-modal').classList.add('hidden')">
                        Cancel
                    </button>
                    <button type="submit" class="admin-btn-primary">
                        Create Team
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showTeamDetails(teamId) {
    // Implement team details view
}

function editTeam(teamId) {
    // Implement team edit functionality
}
</script>
@endpush
@endsection 