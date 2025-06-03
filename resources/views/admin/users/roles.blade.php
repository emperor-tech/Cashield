@extends('layouts.admin')

@section('page_title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Roles & Permissions</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage user roles and their associated permissions</p>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($roles as $roleKey => $role)
            <div class="admin-card p-6">
                <div class="space-y-4">
                    <!-- Role Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $role['name'] }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $role['description'] }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full
                            @if($roleKey === 'admin')
                                bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                            @elseif($roleKey === 'security')
                                bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                            @elseif($roleKey === 'staff')
                                bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                            @else
                                bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300
                            @endif">
                            {{ isset($users[$roleKey]) ? $users[$roleKey]->count() : 0 }} Users
                        </span>
                    </div>

                    <!-- Permissions List -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Permissions</h4>
                        <div class="space-y-2">
                            @foreach($role['permissions'] as $permission => $granted)
                                <div class="flex items-center">
                                    <span class="mr-2">
                                        @if($granted)
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        @else
                                            <i class="fas fa-times-circle text-red-500"></i>
                                        @endif
                                    </span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ ucwords(str_replace('_', ' ', $permission)) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Users with this Role -->
                    @if(isset($users[$roleKey]) && $users[$roleKey]->count() > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Users</h4>
                            <div class="space-y-2">
                                @foreach($users[$roleKey]->take(5) as $user)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <img class="h-6 w-6 rounded-full" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" 
                                                 alt="{{ $user->name }}">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user->name }}</span>
                                        </div>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </a>
                                    </div>
                                @endforeach
                                @if($users[$roleKey]->count() > 5)
                                    <div class="text-center">
                                        <a href="{{ route('admin.users.index', ['role' => $roleKey]) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            View all {{ $users[$roleKey]->count() }} users
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Role Management Tips -->
    <div class="admin-card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Role Management Tips</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-shield-alt text-blue-500"></i>
                    <h4 class="font-medium text-gray-900 dark:text-white">Security Best Practices</h4>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li>Regularly review user roles</li>
                    <li>Follow the principle of least privilege</li>
                    <li>Document role changes</li>
                    <li>Audit role assignments periodically</li>
                </ul>
            </div>

            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-user-lock text-blue-500"></i>
                    <h4 class="font-medium text-gray-900 dark:text-white">Access Control</h4>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li>Limit administrative access</li>
                    <li>Implement role-based access control</li>
                    <li>Monitor privileged actions</li>
                    <li>Regular permission reviews</li>
                </ul>
            </div>

            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-users-cog text-blue-500"></i>
                    <h4 class="font-medium text-gray-900 dark:text-white">Role Assignment</h4>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1">
                    <li>Assign roles based on job function</li>
                    <li>Regularly update role assignments</li>
                    <li>Document role changes</li>
                    <li>Review inactive users</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection 