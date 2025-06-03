<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ dark: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Cashield') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Dark mode scrollbar */
        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #1f2937;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Table styles */
        .admin-table {
            @apply w-full border-collapse;
        }
        .admin-table th {
            @apply px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-700;
        }
        .admin-table td {
            @apply px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300;
        }
        .admin-table tr {
            @apply hover:bg-gray-50 dark:hover:bg-gray-700/50 transition;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900" x-data="{ mobileMenuOpen: false }" x-init="$watch('dark', val => localStorage.setItem('darkMode', val))">

        <!-- Main Navigation -->
        <nav class="bg-blue-800 text-white shadow-lg border-b border-blue-900/20 sticky top-0 z-40 transition-all duration-200" aria-label="Main navigation">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo and Primary Navigation -->
                    <div class="flex items-center">
                        <a href="/" class="flex items-center font-bold text-2xl tracking-tight hover:text-blue-200 transition">
                            <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Cashield
                        </a>
                        <div class="hidden md:flex items-center space-x-4 ml-10">
                            <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 hover:text-white transition {{ request()->routeIs('reports.*') ? 'bg-blue-900 text-white' : 'text-blue-100' }}">
                                Report a Crime
                            </a>
                            @auth
                            <a href="{{ route('reports.create') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 hover:text-white transition {{ request()->routeIs('reports.create') ? 'bg-blue-900 text-white' : 'text-blue-100' }}">
                                Submit Report
                            </a>
                            @endauth
                            <div class="relative" x-data="{ resourcesOpen: false }">
                                <button @click="resourcesOpen = !resourcesOpen" @click.away="resourcesOpen = false" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 hover:text-white transition text-blue-100">
                                    Resources
                                    <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="resourcesOpen" class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5" style="display: none;">
                                    <div class="py-1">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Safety Guidelines</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Emergency Contacts</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">FAQs</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side Navigation -->
                    <div class="flex items-center space-x-4">
                        <button @click="dark = !dark" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition" :aria-pressed="dark.toString()" aria-label="Toggle dark mode">
                            <span x-show="!dark" class="flex items-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                            </span>
                            <span x-show="dark" class="flex items-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"></path>
                                </svg>
                            </span>
                        </button>

                        @auth
                        <button class="relative focus:outline-none hover:bg-blue-700 rounded-full p-1 transition" aria-label="Notifications">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute -top-1 -right-1 bg-red-600 text-xs rounded-full px-1">!</span>
                        </button>
                        <div class="relative" x-data="{ profileOpen: false }">
                            <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="flex items-center space-x-2 hover:bg-blue-700 rounded-md p-2 transition">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(App\Helpers\AuthHelper::user()->name) }}&background=0D8ABC&color=fff" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-white">
                                <span class="hidden md:inline">{{ App\Helpers\AuthHelper::user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="profileOpen" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5" style="display: none;">
                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Profile Settings</a>
                                    <a href="{{ route('reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">My Reports</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Sign Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-blue-100 hover:bg-blue-700 hover:text-white rounded-md transition">Log in</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white hover:bg-blue-500 rounded-md transition">Sign up</a>
                        </div>
                        @endauth

                        <!-- Mobile menu button -->
                        <div class="flex md:hidden">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-blue-100 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                                <svg class="h-6 w-6" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                <svg class="h-6 w-6" x-show="mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div x-show="mobileMenuOpen" class="md:hidden" style="display: none;">
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('reports.*') ? 'bg-blue-900 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' }}">Reports Dashboard</a>
                        @auth
                        <a href="{{ route('reports.create') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('reports.create') ? 'bg-blue-900 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' }}">Submit Report</a>
                        @endauth
                        
                        <!-- Mobile Resources Dropdown -->
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">
                                Resources
                                <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" class="pl-4">
                                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">Safety Guidelines</a>
                                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">Emergency Contacts</a>
                                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">FAQs</a>
                            </div>
                        </div>

                        @auth
                        <div class="border-t border-blue-700 pt-4 pb-3">
                            <div class="px-4">
                                <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
                                <div class="text-sm font-medium text-blue-200">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="mt-3 space-y-1">
                                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">Profile Settings</a>
                                <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">My Reports</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">Sign Out</button>
                                </form>
                            </div>
                        </div>
                        @else
                        <div class="pt-4 pb-3 border-t border-blue-700">
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">Log in</a>
                            <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:bg-blue-700 hover:text-white">Sign up</a>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Toast Area -->
        <div id="toast-area" class="fixed top-4 right-4 z-50"></div>

        <!-- Main Content -->
        <main id="main-content" aria-label="Main content">
            <div class="container mx-auto p-6">
                @if(session('success'))
                <div class="bg-green-200 text-green-800 p-3 mb-4 rounded" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="bg-red-200 text-red-800 p-3 mb-4 rounded" role="alert">
                    {{ session('error') }}
                </div>
                @endif
                @yield('content')
            </div>
        </main>

        <!-- Panic Button -->
        <div id="panic-button"></div>

        <!-- Panic Button and Modal -->
        <div x-data="{
            open: false,
            loading: false,
            location: '',
            note: '',
            countdown: 0,
            showConfirm: false,
            media: null,
            mediaType: '',
            startPanic() {
                this.countdown = 5;
                this.open = true;
                this.showConfirm = false;
                this.tick();
            },
            tick() {
                if (this.countdown > 0) {
                    setTimeout(() => { this.countdown--; this.tick(); }, 1000);
                } else if (this.open && !this.showConfirm) {
                    this.sendPanic();
                }
            },
            cancelPanic() {
                this.open = false;
                this.countdown = 0;
                this.media = null;
                this.mediaType = '';
            },
            sendPanic() {
                this.loading = true;
                // Submit the form programmatically
                this.$refs.panicForm.submit();
            },
            handleMedia(e, type) {
                this.media = e.target.files[0];
                this.mediaType = type;
            },
            confirmSent() {
                this.showConfirm = true;
                this.loading = false;
                setTimeout(() => { this.open = false; this.showConfirm = false; }, 3000);
            },
            init() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        this.location = `${pos.coords.latitude},${pos.coords.longitude}`;
                    });
                }
            }
        }" x-init="init()" class="">
            <!-- Floating Button -->
            <button @click="startPanic()" class="fixed z-50 bottom-6 right-6 md:right-6 md:bottom-6 bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-full shadow-lg flex items-center space-x-2 md:bottom-6 md:right-6 w-16 h-16 md:w-auto md:h-auto">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 inline' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 5.636l-1.414 1.414A9 9 0 105.636 18.364l1.414-1.414M15 12a3 3 0 11-6 0 3 3 0 016 0z' /></svg>
                <span class="hidden md:inline ml-2">Panic</span>
            </button>
            <!-- Modal -->
            <div x-show="open" :class="open ? 'pointer-events-auto' : 'pointer-events-none'" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 transition-all">
                <div x-show="open" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md mx-auto transition-all" role="dialog" aria-modal="true" style="display: none;">
                    <button @click="cancelPanic()" class="absolute top-2 right-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                   
                    <!-- Panic Form -->
                    <div x-show="!showConfirm">
                         <h2 class="text-xl font-bold mb-2 text-red-700 dark:text-red-500">Panic Alert</h2>
                        <p class="mb-4">Are you in danger? <span x-show="countdown > 0">Sending in <span class="font-bold" x-text="countdown"></span> seconds...</span></p>
                        <form x-ref="panicForm" method="POST" action="/api/panic" @submit="loading = true" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="location" :value="location">
                            <div class="mb-3">
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Optional Note</label>
                                <textarea name="note" x-model="note" class="w-full border dark:border-gray-600 p-2 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Attach Audio/Video (optional)</label>
                                <input type="file" accept="audio/*,video/*" @change="handleMedia($event, $event.target.files[0].type.startsWith('audio') ? 'audio' : 'video')" name="media" class="w-full text-gray-700 dark:text-gray-300">
                            </div>
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="cancelPanic()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-400 dark:hover:bg-gray-500">Cancel</button>
                                <button type="submit" :disabled="loading || countdown > 0" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">Send Now</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Confirmation Screen -->
                    <div x-show="showConfirm" class="text-center">
                        <h2 class="text-xl font-bold text-green-700 dark:text-green-500 mb-2">Alert Sent!</h2>
                        <p class="mb-4 text-gray-700 dark:text-gray-300">Help is on the way. <br> You can chat with responders or update your location below.</p>
                        <div x-data="chatComponent(window.lastPanicReportId, {{ App\Helpers\AuthHelper::id() ?? 'null' }}, '{{ App\Helpers\AuthHelper::user() ? App\Helpers\AuthHelper::user()->name : '' }}')" class="mt-4">
                            <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Live Chat</h3>
                            <div class="border rounded p-2 bg-gray-50 dark:bg-gray-800 max-h-40 overflow-y-auto mb-2" id="panic-chat-messages">
                                <template x-for="msg in messages" :key="msg.id">
                                    <div class="mb-1 text-left">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="msg.user.name"></span>:
                                        <span class="text-gray-800 dark:text-gray-200" x-text="msg.message"></span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2" x-text="msg.created_at"></span>
                                    </div>
                                </template>
                            </div>
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input x-model="newMessage" type="text" class="flex-1 border dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="Type your message..." required>
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Send</button>
                            </form>
                        </div>
                        <button @click="open = false" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @if(App\Helpers\AuthHelper::check())
        <script src="https://cdn.jsdelivr.net/npm/@web-push-libs/laravel-web-push@1.0.0/dist/laravel-web-push.js"></script>
        <script>
            window.LaravelWebPush && window.LaravelWebPush.init({
                userId: {{ App\Helpers\AuthHelper::id() }},
                vapidPublicKey: '{{ config('webpush.vapid.public_key') }}',
                serviceWorkerPath: '/service-worker.js',
                onPermissionGranted: function() {
                    console.log('Push notifications enabled!');
                },
                onError: function(e) {
                    console.error('Push error:', e);
                }
            });
        </script>
        @endif
        @if(session('success'))
        <script>window.addEventListener('DOMContentLoaded',()=>showToast(@json(session('success')), 'success'));</script>
        @endif
        @if(session('error'))
        <script>window.addEventListener('DOMContentLoaded',()=>showToast(@json(session('error')), 'error'));</script>
        @endif
        <script>
        window.lastPanicReportId = null;
        function chatComponent(reportId, userId, userName) {
            return {
                messages: [],
                newMessage: '',
                fetchMessages() {
                    if (!reportId) return;
                    fetch(`/reports/${reportId}/chat`).then(r => r.json()).then(data => { 
                        this.messages = data; 
                        this.scrollToBottom(); 
                    });
                },
                sendMessage() {
                    if (!this.newMessage.trim() || !reportId) return;
                    fetch(`/reports/${reportId}/chat`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ message: this.newMessage })
                    }).then(r => r.json()).then(msg => {
                        this.messages.push(msg);
                        this.newMessage = '';
                        this.scrollToBottom();
                    });
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = document.getElementById('panic-chat-messages');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },
                init() {
                    if (!reportId) return;
                    this.fetchMessages();
                    window.Echo.join(`report-chat.${reportId}`)
                        .here(() => {})
                        .joining(() => {})
                        .leaving(() => {})
                        .listen('ChatMessageSent', (e) => {
                            this.messages.push(e);
                            this.scrollToBottom();
                        });
                }
            }
        }
        </script>
    </body>
</html>
