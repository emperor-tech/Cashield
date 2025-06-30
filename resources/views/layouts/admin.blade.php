<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_title', 'Admin Dashboard') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="h-full antialiased" 
      x-data="{ 
          sidebarOpen: false, 
          darkMode: localStorage.getItem('darkMode') === 'true',
          notificationCount: 3,
          notifications: []
      }" 
      :class="{ 'dark': darkMode }">
    
    <div class="admin-layout">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 transition-opacity lg:hidden"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside class="admin-sidebar" :class="{ '-translate-x-full': !sidebarOpen }">
            <!-- Logo -->
            <div class="admin-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                    <span class="text-xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="admin-sidebar-nav">
                <div class="px-3 py-4">
                    <div class="space-y-1">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>

                        <!-- Reports -->
                        <div x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" 
                                    class="admin-nav-link w-full {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span>Reports</span>
                                <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="mt-2 space-y-1 px-3">
                                <a href="{{ route('admin.reports.index') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                    All Reports
                                </a>
                                <a href="{{ route('admin.reports.pending') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.reports.pending') ? 'active' : '' }}">
                                    Pending Reports
                                </a>
                                <a href="{{ route('admin.reports.resolved') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.reports.resolved') ? 'active' : '' }}">
                                    Resolved Reports
                                </a>
                            </div>
                        </div>

                        <!-- Security -->
                        <div x-data="{ open: {{ request()->routeIs('admin.security.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" 
                                    class="admin-nav-link w-full {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security</span>
                                <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="mt-2 space-y-1 px-3">
                                <!-- Teams Submenu -->
                                <div x-data="{ open: {{ request()->routeIs('admin.security.teams.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="admin-nav-link-child w-full flex items-center justify-between">
                                        Teams <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </button>
                                    <div x-show="open" class="ml-4 space-y-1">
                                        <a href="{{ route('admin.security.teams.index') }}" class="admin-nav-link-child">All Teams</a>
                                        <a href="{{ route('admin.security.teams.create') }}" class="admin-nav-link-child">Create Team</a>
                                    </div>
                                </div>
                                <!-- Zones Submenu -->
                                <div x-data="{ open: {{ request()->routeIs('admin.security.zones.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="admin-nav-link-child w-full flex items-center justify-between">
                                        Zones <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </button>
                                    <div x-show="open" class="ml-4 space-y-1">
                                        <a href="{{ route('admin.security.zones.index') }}" class="admin-nav-link-child">All Zones</a>
                                        <a href="{{ route('admin.security.zones.create') }}" class="admin-nav-link-child">Create Zone</a>
                                    </div>
                                </div>
                                <!-- Shifts Submenu -->
                                <div x-data="{ open: {{ request()->routeIs('admin.security.shifts.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="admin-nav-link-child w-full flex items-center justify-between">
                                        Shifts <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </button>
                                    <div x-show="open" class="ml-4 space-y-1">
                                        <a href="{{ route('admin.security.shifts.index') }}" class="admin-nav-link-child">All Shifts</a>
                                        <a href="{{ route('admin.security.shifts.create') }}" class="admin-nav-link-child">Create Shift</a>
                                    </div>
                                </div>
                                <!-- Checkpoints Submenu -->
                                <div x-data="{ open: {{ request()->routeIs('admin.security.checkpoints.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="admin-nav-link-child w-full flex items-center justify-between">
                                        Checkpoints <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </button>
                                    <div x-show="open" class="ml-4 space-y-1">
                                        <a href="{{ route('admin.security.checkpoints.index') }}" class="admin-nav-link-child">All Checkpoints</a>
                                        <a href="{{ route('admin.security.checkpoints.create') }}" class="admin-nav-link-child">Create Checkpoint</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users -->
                        <div x-data="{ open: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" 
                                    class="admin-nav-link w-full {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                                <i class="fas fa-chevron-down ml-auto transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="mt-2 space-y-1 px-3">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                    All Users
                                </a>
                                <a href="{{ route('admin.users.create') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                    Add User
                                </a>
                                <a href="{{ route('admin.users.roles') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.users.roles') ? 'active' : '' }}">
                                    Roles
                                </a>
                            </div>
                        </div>

                        <!-- Analytics -->
                        <a href="{{ route('admin.analytics.dashboard') }}" 
                           class="admin-nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </a>

                        <!-- Settings -->
                        <a href="{{ route('admin.settings.general') }}" 
                           class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- User Profile -->
            <div class="admin-sidebar-footer">
                <div class="flex items-center p-4 border-t border-gray-200 dark:border-gray-700">
                    <img class="h-10 w-10 rounded-full" 
                         src="{{ auth()->user()->profile_photo_url }}" 
                         alt="{{ auth()->user()->name }}">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                    </div>
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                            class="ml-auto p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:pl-0 flex flex-col flex-1">
            <!-- Top Bar -->
            <header class="admin-topbar flex items-center justify-between px-6 py-3 bg-white dark:bg-gray-900 shadow">
                <div class="flex items-center space-x-4">
                    <!-- Sidebar Toggle (Mobile) -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 dark:text-gray-300 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <!-- Quick Actions Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="admin-btn-secondary flex items-center space-x-2">
                            <i class="fas fa-bolt"></i>
                            <span>Quick Actions</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded shadow-lg z-50">
                            <a href='{{ route('admin.security.teams.create') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>New Team</a>
                            <a href='{{ route('admin.security.zones.create') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>New Zone</a>
                            <a href='{{ route('admin.security.shifts.create') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>New Shift</a>
                            <a href='{{ route('admin.security.checkpoints.create') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>New Checkpoint</a>
                            <a href='{{ route('admin.reports.create') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>Create Report</a>
                            <a href='{{ route('admin.users.create') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>Add User</a>
                            <a href='{{ route('admin.reports.index') }}' class='block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700'>All Reports</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div x-data="{
                        open: false,
                        notifications: [],
                        unreadCount: 0,
                        fetchNotifications() {
                            fetch('/admin/notifications/json')
                                .then(res => res.json())
                                .then(data => {
                                    this.notifications = data.notifications;
                                    this.unreadCount = data.unread_count;
                                });
                        }
                    }" x-init="fetchNotifications(); setInterval(fetchNotifications, 30000)" class="relative">
                        <button @click="open = !open; if(open) fetchNotifications()" class="relative text-gray-500 dark:text-gray-300 focus:outline-none">
                            <i class="fas fa-bell fa-lg"></i>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5"></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded shadow-lg z-50 max-h-96 overflow-y-auto">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 font-semibold">Notifications</div>
                            <template x-if="notifications.length === 0">
                                <div class="p-4 text-gray-500 dark:text-gray-400">No notifications.</div>
                            </template>
                            <template x-for="notification in notifications" :key="notification.id">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-900 dark:text-white" x-text="notification.data.title || notification.title"></span>
                                        <span class="text-xs text-gray-400" x-text="new Date(notification.created_at).toLocaleString()"></span>
                                    </div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 mt-1" x-text="notification.data.message || notification.message"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center space-x-2 focus:outline-none">
                            <img src="{{ asset('images/avatar.png') }}" alt="Avatar" class="h-8 w-8 rounded-full">
                            <span class="hidden md:inline text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded shadow-lg z-50">
                            <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">Your Profile</a>
                            <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        @if(session('success'))
                            <div class="admin-alert-success" x-data="{ show: true }" x-show="show">
                                <i class="fas fa-check-circle"></i>
                                <p>{{ session('success') }}</p>
                                <button @click="show = false" class="ml-auto">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="admin-alert-error" x-data="{ show: true }" x-show="show">
                                <i class="fas fa-exclamation-circle"></i>
                                <p>{{ session('error') }}</p>
                                <button @click="show = false" class="ml-auto">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html> 