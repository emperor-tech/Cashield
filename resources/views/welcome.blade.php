<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cashield - Campus Security Shield</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased h-full bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-gray-100">
        <div class="min-h-screen">
            @if (Route::has('login'))
                <div class="p-6 text-right">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="flex flex-col items-center justify-center flex-1 p-6">
                <div class="w-full max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Content -->
                        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Welcome to Cashield</h1>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Your safety is our priority. Report incidents, get help, and stay connected with our community.</p>
                            <div class="space-y-4">
                                <a href="https://laravel.com/docs" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Documentation</h2>
                                    <p class="text-gray-600 dark:text-gray-300">Find in-depth information about our features and API.</p>
                                </a>
                                <a href="https://laracasts.com" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Video Tutorials</h2>
                                    <p class="text-gray-600 dark:text-gray-300">Watch tutorials on how to use our platform effectively.</p>
                                </a>
                            </div>
                        </div>
                        <!-- Right Content -->
                        <div class="bg-gradient-to-br from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 p-6 rounded-lg shadow-xl text-white">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="w-20 h-20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <h2 class="text-2xl font-bold mb-2">Emergency? Need Help?</h2>
                                    <p class="mb-4">Report an incident now - no login required</p>
                                    <div class="space-y-3">
                                        <button onclick="window.location.href='{{ route('reports.anonymous.create') }}'" class="w-full bg-white text-red-600 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                                            Report Anonymously
                                        </button>
                                        <button onclick="window.location.href='{{ route('reports.create') }}'" class="w-full bg-red-700 text-white border border-white px-6 py-2 rounded-lg font-semibold hover:bg-red-800 transition">
                                            Report as User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
