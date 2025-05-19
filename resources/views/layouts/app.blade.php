<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cashield') }}</title>
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <script src="{{ mix('js/app.js') }}" defer></script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans dark:bg-gray-900" x-data="{ dark: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('dark', val => localStorage.setItem('darkMode', val));" :class="{ 'dark': dark }">
        <nav class="bg-blue-800 text-white p-4 flex items-center justify-between" aria-label="Main navigation">
            <div>
                <a href="{{ route('reports.index') }}" class="mr-4">Reports</a>
                @auth
                <a href="{{ route('reports.create') }}">New Report</a>
                @endauth
            </div>
            <button @click="dark = !dark" class="ml-4 px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 focus:outline-none" :aria-pressed="dark.toString()" aria-label="Toggle dark mode">
                <span x-show="!dark">🌙</span>
                <span x-show="dark">☀️</span>
            </button>
        </nav>
        <main id="main-content" aria-label="Main content">
            <div class="container mx-auto p-6">
                @if(session('success'))
                <div class="bg-green-200 text-green-800 p-3 mb-4 rounded" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                @yield('content')
            </div>
        </main>

        <!-- Panic Button and Modal -->
        <div x-data="{ open: false, loading: false, location: '', note: '' }" class="">
            <!-- Floating Button -->
            <button @click="open = true" class="fixed z-50 bottom-6 right-6 md:right-6 md:bottom-6 bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-full shadow-lg flex items-center space-x-2 md:bottom-6 md:right-6 w-16 h-16 md:w-auto md:h-auto">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 inline' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 5.636l-1.414 1.414A9 9 0 105.636 18.364l1.414-1.414M15 12a3 3 0 11-6 0 3 3 0 016 0z' /></svg>
                <span class="hidden md:inline ml-2">Panic</span>
            </button>
            <!-- Modal -->
            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-auto">
                    <h2 class="text-xl font-bold mb-2 text-red-700">Panic Alert</h2>
                    <p class="mb-4">Are you in danger? Send a panic alert now!</p>
                    <form method="POST" action="{{ route('panic.report') }}" @submit="loading = true" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm">Location</label>
                            <input type="text" name="location" x-model="location" class="w-full border p-2" required readonly>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm">Optional Note</label>
                            <textarea name="note" x-model="note" class="w-full border p-2" rows="2"></textarea>
                        </div>
                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="button" @click="open = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                            <button type="submit" :disabled="loading" class="px-4 py-2 bg-red-600 text-white rounded">Send Panic Alert</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('panicButton', () => ({
                open: false,
                loading: false,
                location: '',
                note: '',
                init() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((pos) => {
                            this.location = `${pos.coords.latitude},${pos.coords.longitude}`;
                        });
                    }
                }
            }));
        });
        </script>
        @if(auth()->check())
        <script src="https://cdn.jsdelivr.net/npm/@web-push-libs/laravel-web-push@1.0.0/dist/laravel-web-push.js"></script>
        <script>
            window.LaravelWebPush && window.LaravelWebPush.init({
                userId: {{ auth()->id() }},
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
    </body>
</html>
