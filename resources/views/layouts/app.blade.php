<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cashield') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans dark:bg-gray-900" x-data="{ dark: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('dark', val => localStorage.setItem('darkMode', val));" :class="{ 'dark': dark }">
        <nav class="bg-blue-800 text-white p-4 flex items-center justify-between shadow-lg border-b border-blue-900/20 sticky top-0 z-40 transition-all duration-200" aria-label="Main navigation">
            <div class="flex items-center gap-4">
                <a href="/" class="font-bold text-2xl tracking-tight mr-4 hover:text-blue-200 transition">Cashield</a>
                <a href="/reports" class="mr-4 hover:text-blue-200 transition">Reports</a>
                @auth
                <a href="/reports/create" class="hover:text-blue-200 transition">New Report</a>
                @endauth
            </div>
            <div class="flex items-center gap-4">
                <button @click="dark = !dark" class="ml-4 px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" :aria-pressed="dark.toString()" aria-label="Toggle dark mode">
                    <span x-show="!dark">🌙</span>
                    <span x-show="dark">☀️</span>
                </button>
                @auth
                <button class="relative focus:outline-none hover:bg-blue-700 rounded-full p-1 transition" aria-label="Notifications">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-xs rounded-full px-1">!</span>
                </button>
                <a href="/profile" class="flex items-center gap-2 ml-4 group">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(App\Helpers\AuthHelper::user()->name) }}&background=0D8ABC&color=fff" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-white group-hover:border-blue-300 transition">
                    <span class="hidden md:inline group-hover:text-blue-200 transition">{{ App\Helpers\AuthHelper::user()->name }}</span>
                </a>
                @endauth
            </div>
        </nav>
        <div id="toast-area" class="fixed top-4 right-4 z-50"></div>
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
                <div x-show="open" class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-auto transition-all" role="dialog" aria-modal="true" style="display: none;">
                    <button @click="cancelPanic()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                   
                    <!-- Panic Form -->
                    <div x-show="!showConfirm">
                         <h2 class="text-xl font-bold mb-2 text-red-700">Panic Alert</h2>
                        <p class="mb-4">Are you in danger? <span x-show="countdown > 0">Sending in <span class="font-bold" x-text="countdown"></span> seconds...</span></p>
                        <form x-ref="panicForm" method="POST" action="/panic" @submit="loading = true" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="location" :value="location">
                            <div class="mb-3">
                                <label class="block text-sm">Optional Note</label>
                                <textarea name="note" x-model="note" class="w-full border p-2" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm">Attach Audio/Video (optional)</label>
                                <input type="file" accept="audio/*,video/*" @change="handleMedia($event, $event.target.files[0].type.startsWith('audio') ? 'audio' : 'video')" name="media">
                            </div>
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="cancelPanic()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                <button type="submit" :disabled="loading || countdown > 0" class="px-4 py-2 bg-red-600 text-white rounded">Send Now</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Confirmation Screen -->
                    <div x-show="showConfirm" class="text-center">
                        <h2 class="text-xl font-bold text-green-700 mb-2">Alert Sent!</h2>
                        <p class="mb-4">Help is on the way. Stay safe. <br> You can chat with responders or update your location below.</p>
                        <div x-data="chatComponent(window.lastPanicReportId, {{ App\Helpers\AuthHelper::id() ?? 'null' }}, '{{ App\Helpers\AuthHelper::user() ? App\Helpers\AuthHelper::user()->name : '' }}')" class="mt-4">
                            <h3 class="text-lg font-semibold mb-2">Live Chat</h3>
                            <div class="border rounded p-2 bg-gray-50 dark:bg-gray-800 max-h-40 overflow-y-auto mb-2" id="panic-chat-messages">
                                <template x-for="msg in messages" :key="msg.id">
                                    <div class="mb-1 text-left">
                                        <span class="font-semibold" x-text="msg.user.name"></span>:
                                        <span x-text="msg.message"></span>
                                        <span class="text-xs text-gray-400 ml-2" x-text="msg.created_at"></span>
                                    </div>
                                </template>
                            </div>
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input x-model="newMessage" type="text" class="flex-1 border rounded p-2" placeholder="Type your message..." required>
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Send</button>
                            </form>
                        </div>
                        <button @click="open = false" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Close</button>
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
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </body>
</html>
