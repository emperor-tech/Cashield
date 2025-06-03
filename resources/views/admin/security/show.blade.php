@extends('layouts.admin')

@section('page_title', 'Team Details - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $team->name }}</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Team Details and Management</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="admin-btn-secondary" onclick="document.getElementById('editTeamModal').classList.remove('hidden')">
                <i class="fas fa-edit mr-2"></i>
                Edit Team
            </button>
            <form action="{{ route('admin.security.teams.toggle', $team) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" 
                    class="admin-btn-{{ $team->active ? 'red' : 'green' }}">
                    <i class="fas {{ $team->active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                    {{ $team->active ? 'Deactivate' : 'Activate' }} Team
                </button>
            </form>
        </div>
    </div>

    <!-- Team Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Info -->
        <div class="admin-card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd>
                        <span class="admin-badge {{ $team->active ? 'admin-badge-green' : 'admin-badge-red' }}">
                            {{ $team->active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Team Leader</dt>
                    <dd class="flex items-center mt-1">
                        <img class="h-8 w-8 rounded-full mr-2" 
                             src="https://ui-avatars.com/api/?name={{ urlencode($team->leader->name) }}" 
                             alt="{{ $team->leader->name }}">
                        <span class="text-gray-900 dark:text-white">{{ $team->leader->name }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Zone</dt>
                    <dd class="flex items-center mt-1">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                        <span class="text-gray-900 dark:text-white">{{ $team->zone->name }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Members</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $team->members->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $team->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Team Stats -->
        <div class="admin-card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Team Statistics</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Shifts</dt>
                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $team->activeShifts->count() }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Shifts</dt>
                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $team->completed_shifts_count }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Patrol Hours</dt>
                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($team->total_shift_duration / 60, 1) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Team Members -->
    <div class="admin-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Team Members</h3>
            <button type="button" class="admin-btn-secondary" onclick="document.getElementById('addMemberModal').classList.remove('hidden')">
                <i class="fas fa-user-plus mr-2"></i>
                Add Member
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Last Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($team->members as $member)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full mr-2" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}" 
                                         alt="{{ $member->name }}">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="admin-badge {{ $member->pivot->role === 'leader' ? 'admin-badge-blue' : 'admin-badge-gray' }}">
                                    {{ ucfirst($member->pivot->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="admin-badge {{ $member->pivot->active ? 'admin-badge-green' : 'admin-badge-red' }}">
                                    {{ $member->pivot->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $member->pivot->joined_at->format('M d, Y') }}</td>
                            <td>{{ $member->last_active_at ? $member->last_active_at->diffForHumans() : 'Never' }}</td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.users.show', $member) }}" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                       title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($member->id !== $team->leader_id)
                                        <form action="{{ route('admin.security.teams.members.remove', [$team, $member]) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to remove this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                title="Remove Member">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Shifts -->
    <div class="admin-card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Active Shifts</h3>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Officer</th>
                        <th>Started</th>
                        <th>Duration</th>
                        <th>Checkpoints Scanned</th>
                        <th>Last Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($team->activeShifts as $shift)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full mr-2" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($shift->user->name) }}" 
                                         alt="{{ $shift->user->name }}">
                                    <span>{{ $shift->user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $shift->started_at->format('M d, Y H:i') }}</td>
                            <td>{{ $shift->formatted_duration }}</td>
                            <td>{{ $shift->checkpointScans->count() }}</td>
                            <td>
                                @if($shift->route_data && !empty($shift->route_data['coordinates']))
                                    {{ end($shift->route_data['coordinates'])['lat'] }}, 
                                    {{ end($shift->route_data['coordinates'])['lng'] }}
                                @else
                                    No location data
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <button type="button" 
                                            onclick="viewShiftDetails({{ $shift->id }})"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route('admin.shifts.end', $shift) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to end this shift?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                            title="End Shift">
                                            <i class="fas fa-stop-circle"></i>
                                        </button>
                                    </form>
                                </div>
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

<!-- Edit Team Modal -->
<div id="editTeamModal" class="admin-modal hidden" x-cloak>
    <div class="admin-modal-overlay"></div>
    <div class="admin-modal-content">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Team</h3>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="this.closest('.admin-modal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.security.teams.update', $team) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Team Name</label>
                    <input type="text" name="name" id="name" value="{{ $team->name }}" required class="admin-input mt-1">
                </div>

                <div>
                    <label for="leader_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Team Leader</label>
                    <select name="leader_id" id="leader_id" required class="admin-select mt-1">
                        @foreach($team->members as $member)
                            <option value="{{ $member->id }}" {{ $member->id === $team->leader_id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="zone_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Zone</label>
                    <select name="zone_id" id="zone_id" required class="admin-select mt-1">
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ $zone->id === $team->zone_id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="admin-btn-secondary" onclick="this.closest('.admin-modal').classList.add('hidden')">
                        Cancel
                    </button>
                    <button type="submit" class="admin-btn-primary">
                        Update Team
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="admin-modal hidden" x-cloak>
    <div class="admin-modal-overlay"></div>
    <div class="admin-modal-content">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add Team Member</h3>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="this.closest('.admin-modal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.security.teams.members.add', $team) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Security Officer</label>
                    <select name="user_id" id="user_id" required class="admin-select mt-1">
                        <option value="">Select an officer</option>
                        @foreach($securityPersonnel as $person)
                            @if(!$team->hasMember($person))
                                <option value="{{ $person->id }}">{{ $person->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select name="role" id="role" required class="admin-select mt-1">
                        <option value="member">Member</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="admin-btn-secondary" onclick="this.closest('.admin-modal').classList.add('hidden')">
                        Cancel
                    </button>
                    <button type="submit" class="admin-btn-primary">
                        Add Member
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function viewShiftDetails(shiftId) {
    // Implement shift details view
}
</script>
@endpush
@endsection 