@extends('admin.security.layout')

@section('security_content') 
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $team->name }}</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $team->team_type }} Team - {{ $team->zone->name }}
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.security.teams.edit', $team) }}" class="admin-btn-secondary">
                <i class="fas fa-edit mr-2"></i>
                Edit Team
            </a>
            <form action="{{ route('admin.security.teams.toggle-status', $team) }}" 
                  method="POST" 
                  class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        class="admin-btn-{{ $team->active ? 'danger' : 'success' }}">
                    <i class="fas {{ $team->active ? 'fa-ban' : 'fa-check' }} mr-2"></i>
                    {{ $team->active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Team Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Team Info Card -->
        <div class="admin-card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Team Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Team Leader</label>
                    <div class="mt-1 flex items-center space-x-3">
                        <img class="h-8 w-8 rounded-full" 
                             src="https://ui-avatars.com/api/?name={{ urlencode($team->leader->name) }}" 
                             alt="{{ $team->leader->name }}">
                        <span class="text-gray-900 dark:text-white">{{ $team->leader->name }}</span>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Zone Assignment</label>
                    <div class="mt-1 flex items-center space-x-2">
                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                        <span class="text-gray-900 dark:text-white">{{ $team->zone->name }}</span>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Shift Schedule</label>
                    <div class="mt-1 flex items-center space-x-2">
                        <i class="fas fa-clock text-gray-400"></i>
                        <span class="text-gray-900 dark:text-white">{{ $team->shift }}</span>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Information</label>
                    <div class="mt-1 space-y-2">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-phone text-gray-400"></i>
                            <span class="text-gray-900 dark:text-white">{{ $team->contact_number }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-broadcast-tower text-gray-400"></i>
                            <span class="text-gray-900 dark:text-white">Radio: {{ $team->radio_channel }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="admin-card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Performance Metrics</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $metrics['completed_shifts'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Completed Shifts</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-2xl font-semibold text-green-600 dark:text-green-400">
                        {{ $metrics['incidents_handled'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Incidents Handled</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">
                        {{ number_format($metrics['response_rate'], 1) }}%
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Response Rate</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-2xl font-semibold text-purple-600 dark:text-purple-400">
                        {{ number_format($metrics['checkpoint_compliance'], 1) }}%
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Checkpoint Compliance</div>
                </div>
            </div>
        </div>

        <!-- Active Shift -->
        <div class="admin-card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Active Shift</h3>
            @if($activeShifts->isNotEmpty())
                @foreach($activeShifts as $shift)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Started</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ $shift->start_time->format('H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ $shift->start_time->diffForHumans(null, true) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Checkpoints</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ $shift->checkpoints->count() }} scanned
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Incidents</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ $shift->incidents->count() }} reported
                            </span>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.security.shifts.show', $shift) }}" 
                               class="admin-btn-secondary w-full text-center">
                                <i class="fas fa-eye mr-2"></i>
                                View Shift Details
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-6">
                    <i class="fas fa-clock text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No active shifts</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Team Members -->
    <div class="admin-card p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Team Members</h3>
            <button type="button" 
                    class="admin-btn-primary" 
                    onclick="document.getElementById('addMemberModal').classList.remove('hidden')">
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
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($team->members as $member)
                        <tr>
                            <td class="flex items-center space-x-3">
                                <img class="h-8 w-8 rounded-full" 
                                     src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}" 
                                     alt="{{ $member->name }}">
                                <span>{{ $member->name }}</span>
                            </td>
                            <td>
                                <span class="admin-badge {{ $member->pivot->role === 'leader' ? 'admin-badge-blue' : 'admin-badge-gray' }}">
                                    {{ ucfirst($member->pivot->role) }}
                                </span>
                            </td>
                            <td>{{ $member->pivot->joined_at->format('M d, Y') }}</td>
                            <td>
                                <span class="admin-badge {{ $member->pivot->active ? 'admin-badge-green' : 'admin-badge-red' }}">
                                    {{ $member->pivot->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @if($member->id !== $team->leader_id)
                                    <form action="{{ route('admin.security.teams.members.remove', [$team, $member]) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to remove this member?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="admin-modal hidden">
    <div class="admin-modal-overlay"></div>
    <div class="admin-modal-container">
        <div class="admin-modal-header">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add Team Member</h3>
            <button type="button" 
                    class="admin-modal-close" 
                    onclick="this.closest('.admin-modal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('admin.security.teams.members.add', $team) }}" method="POST">
            @csrf
            <div class="admin-modal-body">
                <div class="space-y-4">
                    <div>
                        <label for="user_id" class="admin-label">Select Member</label>
                        <select name="user_id" id="user_id" class="admin-select" required>
                            <option value="">Select a member...</option>
                            @foreach($securityPersonnel as $person)
                                @if(!$team->members->contains($person))
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="role" class="admin-label">Role</label>
                        <select name="role" id="role" class="admin-select" required>
                            <option value="member">Member</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" 
                        class="admin-btn-secondary" 
                        onclick="this.closest('.admin-modal').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="admin-btn-primary">
                    Add Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 