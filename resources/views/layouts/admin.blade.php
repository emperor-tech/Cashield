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
                                <a href="{{ route('admin.security.teams.index') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.security.teams.*') ? 'active' : '' }}">
                                    Teams
                                </a>
                                <a href="{{ route('admin.security.zones.index') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.security.zones.*') ? 'active' : '' }}">
                                    Zones
                                </a>
                                <a href="{{ route('admin.security.shifts.index') }}" 
                                   class="admin-nav-link-child {{ request()->routeIs('admin.security.shifts.*') ? 'active' : '' }}">
                                    Shifts
                                </a>
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
            <!-- Top Navigation -->
            <header class="admin-header-nav">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="hidden md:block">
                            <div class="relative">
                                <input type="text" 
                                       class="admin-search-input" 
                                       placeholder="Search...">
                                <div class="admin-search-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white relative">
                                <i class="fas fa-bell"></i>
                                <span x-show="notificationCount > 0"
                                      class="absolute top-0 right-0 -mt-1 -mr-1 px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white">
                                    {{ 3 }}
                                </span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 rounded-lg shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <!-- Notification Items -->
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <p class="text-sm text-gray-900 dark:text-white">New report submitted</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">2 minutes ago</p>
                                    </a>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                                    <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        View all notifications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="admin-btn-secondary">
                                <i class="fas fa-plus mr-2"></i>
                                Quick Actions
                            </button>

                            <!-- Quick Actions Dropdown -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                                <a href="{{ route('admin.reports.create') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    New Report
                                </a>
                                <a href="{{ route('admin.users.create') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Add User
                                </a>
                                <a href="{{ route('admin.security.teams.create') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-users mr-2"></i>
                                    New Team
                                </a>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                <i class="fas fa-user-circle text-xl"></i>
                            </button>

                            <!-- Profile Dropdown Menu -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                                <a href="{{ route('profile.edit') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Your Profile
                                </a>
                                <a href="{{ route('admin.settings.general') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Sign out
                                    </button>
                                </form>
                            </div>
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