@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gradient-to-r from-blue-900 to-blue-700 dark:from-gray-900 dark:to-blue-900 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:w-full lg:pb-28 xl:pb-32 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center pt-10 md:pt-16 lg:pt-20 xl:pt-28">
                <div class="text-center md:text-left md:w-1/2 md:pr-8">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                        <span class="block">Campus Shield</span>
                        <span class="block text-blue-300">Real-Time Safety Platform</span>
                    </h1>
                    <p class="mt-3 text-base text-blue-100 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Empowering AFIT students and staff to report incidents, respond to emergencies, and foster a safer academic environment.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center md:justify-start">
                        <div class="rounded-md shadow">
                            <a href="/register" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 md:py-4 md:text-lg md:px-10 transition duration-300">
                                Get Started
                            </a>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <a href="#how-it-works" class="w-full flex items-center justify-center px-8 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-blue-800 hover:bg-opacity-30 md:py-4 md:text-lg md:px-10 transition duration-300">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2 mt-10 md:mt-0 flex justify-center">
                    <div class="relative w-full max-w-lg">
                        <!-- Animated background elements -->
                        <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob pointer-events-none"></div>
                        <div class="absolute top-0 -right-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000 pointer-events-none"></div>
                        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-4000 pointer-events-none"></div>
                        <!-- Hero SVG -->
                        <div class="relative">
                            <img src="{{ asset('images/hero-shield.svg') }}" alt="Campus Shield" class="w-full h-auto transform hover:scale-105 transition duration-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Wave Divider -->
    <div class="absolute bottom-0 left-0 right-0 pointer-events-none">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-auto">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
        </svg>
    </div>
</div>

<!-- Emergency Reporting Section -->
<div class="py-12 bg-red-600 dark:bg-red-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white">
                Emergency? Need Help?
            </h2>
            <p class="mt-4 max-w-2xl text-xl text-red-100 lg:mx-auto">
                Report an incident now - no login required
            </p>
        </div>
        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
            <a href="{{ route('reports.anonymous.create') }}" class="inline-flex justify-center items-center px-8 py-4 border border-transparent rounded-md shadow-lg text-lg font-medium text-red-600 bg-white hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
                Report Anonymously
            </a>
            <a href="{{ route('reports.create') }}" class="inline-flex justify-center items-center px-8 py-4 border border-white rounded-md shadow-lg text-lg font-medium text-white hover:bg-red-700 transition duration-300 transform hover:scale-105">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Report as User
            </a>
        </div>
    </div>
</div>

<!-- Key Stats Section -->
<div class="py-12 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                <div class="text-blue-600 dark:text-blue-400 text-4xl font-bold">98%</div>
                <div class="mt-2 text-gray-600 dark:text-gray-300">Response Rate</div>
            </div>
            <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                <div class="text-blue-600 dark:text-blue-400 text-4xl font-bold">&lt;5min</div>
                <div class="mt-2 text-gray-600 dark:text-gray-300">Avg. Response Time</div>
            </div>
            <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                <div class="text-blue-600 dark:text-blue-400 text-4xl font-bold">500+</div>
                <div class="mt-2 text-gray-600 dark:text-gray-300">Active Users</div>
            </div>
            <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                <div class="text-blue-600 dark:text-blue-400 text-4xl font-bold">24/7</div>
                <div class="mt-2 text-gray-600 dark:text-gray-300">Monitoring</div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div id="how-it-works" class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-base text-blue-600 dark:text-blue-400 font-semibold tracking-wide uppercase">Simple Process</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                How Cashield Works
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-300 mx-auto">
                Our platform makes campus safety accessible and efficient for everyone.
            </p>
        </div>
        
        <div class="relative">
            <img src="{{ asset('images/how-it-works.svg') }}" alt="How Cashield Works" class="w-full h-auto mx-auto">
        </div>
        
        <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-5">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">Report an Incident</h3>
                <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                    Submit detailed reports with location, media attachments, and descriptions. Use the panic button for emergencies.
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-5">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">Immediate Response</h3>
                <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                    Security personnel are instantly notified and can coordinate a rapid response to address the situation.
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-5">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">Real-time Communication</h3>
                <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                    Chat with responders, receive status updates, and track the resolution of your reported incident.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="py-16 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-16">
            <h2 class="text-base text-blue-600 dark:text-blue-400 font-semibold tracking-wide uppercase">Features</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                A Comprehensive Safety Platform
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-300 lg:mx-auto">
                Designed specifically for the unique needs of AFIT's campus community.
            </p>
        </div>

        <div class="mt-16">
            <div class="space-y-12 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-x-8 lg:gap-y-12">
                <!-- Feature 1 -->
                <div class="relative p-6 bg-gray-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="absolute top-6 left-6 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>
                    <div class="ml-20">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Real-time Reporting</h3>
                        <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                            Submit detailed incident reports with location data, media attachments, and descriptions.
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="relative p-6 bg-gray-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="absolute top-6 left-6 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="ml-20">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Panic Button</h3>
                        <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                            One-tap emergency alert with countdown, location sharing, and live chat with responders.
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="relative p-6 bg-gray-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="absolute top-6 left-6 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-20">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Community Watch</h3>
                        <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                            Subscribe to area-based alerts and receive notifications about incidents in your subscribed areas.
                        </p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="relative p-6 bg-gray-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="absolute top-6 left-6 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div class="ml-20">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Live Chat</h3>
                        <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                            Real-time communication between reporters and campus security after a panic alert or on report details.
                        </p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="relative p-6 bg-gray-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="absolute top-6 left-6 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="ml-20">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Anonymous Reporting</h3>
                        <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                            Option to submit reports anonymously for sensitive cases while still allowing follow-up communication.
                        </p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="relative p-6 bg-gray-50 dark:bg-gray-800 rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="absolute top-6 left-6 flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-20">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Analytics Dashboard</h3>
                        <p class="mt-4 text-base text-gray-500 dark:text-gray-300">
                            Visual analytics with charts for incident severity, frequency, and interactive heatmap showing campus hotspots.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-16">
            <h2 class="text-base text-blue-600 dark:text-blue-400 font-semibold tracking-wide uppercase">Impact</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                Making AFIT Safer Together
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-300 lg:mx-auto">
                Our platform has already made a significant impact on campus safety.
            </p>
        </div>
        
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0 md:pr-8">
                <img src="{{ asset('images/stats-graphic.svg') }}" alt="Safety Statistics" class="w-full h-auto rounded-lg shadow-lg">
            </div>
            
            <div class="md:w-1/2">
                <div class="space-y-8">
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">42% Faster Response Times</h3>
                                <p class="mt-2 text-base text-gray-500 dark:text-gray-300">
                                    Security teams respond to incidents nearly twice as fast with our real-time alert system.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">93% Resolution Rate</h3>
                                <p class="mt-2 text-base text-gray-500 dark:text-gray-300">
                                    Nearly all reported incidents are successfully resolved and documented.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">78% Community Participation</h3>
                                <p class="mt-2 text-base text-gray-500 dark:text-gray-300">
                                    Over three-quarters of campus community members actively use the platform.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="py-16 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-16">
            <h2 class="text-base text-blue-600 dark:text-blue-400 font-semibold tracking-wide uppercase">Testimonials</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                What Our Users Say
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-blue-50 dark:bg-gray-800 p-8 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-200 flex items-center justify-center text-blue-600 font-bold text-xl">
                        S
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sarah M.</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Student, Computer Science</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    "The panic button feature gave me peace of mind when walking back to my hostel late at night. When I felt unsafe, I was able to alert security instantly."
                </p>
                <div class="mt-4 flex text-yellow-400">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-gray-800 p-8 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-200 flex items-center justify-center text-blue-600 font-bold text-xl">
                        J
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">John D.</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Campus Security Officer</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    "Cashield has revolutionized how we respond to campus incidents. The real-time reporting and location data help us deploy resources more efficiently than ever before."
                </p>
                <div class="mt-4 flex text-yellow-400">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-gray-800 p-8 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-200 flex items-center justify-center text-blue-600 font-bold text-xl">
                        P
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Prof. Adebayo</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Faculty Member</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    "The analytics dashboard has provided valuable insights into campus safety patterns. This data-driven approach has helped us implement targeted security measures."
                </p>
                <div class="mt-4 flex text-yellow-400">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-blue-600 dark:bg-blue-800">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
            <span class="block">Ready to make AFIT safer?</span>
            <span class="block text-blue-200">Join the Cashield community today.</span>
        </h2>
        <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
            <div class="inline-flex rounded-md shadow">
                <a href="/register" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 transition duration-300">
                    Sign up now
                </a>
            </div>
            <div class="ml-3 inline-flex rounded-md shadow">
                <a href="/login" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-800 hover:bg-blue-700 transition duration-300">
                    Log in
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
        <div class="xl:grid xl:grid-cols-3 xl:gap-8">
            <div class="space-y-8 xl:col-span-1">
                <div class="flex items-center">
                    <svg class="h-10 w-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    <h2 class="ml-3 text-2xl font-bold text-gray-900 dark:text-white">Cashield</h2>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-base">
                    A final year project by Abdullateef Babatunde, Air Force Institute of Technology, Kaduna.
                </p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Twitter</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">GitHub</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="mt-12 grid grid-cols-2 gap-8 xl:mt-0 xl:col-span-2">
                <div class="md:grid md:grid-cols-2 md:gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Features</h3>
                        <ul role="list" class="mt-4 space-y-4">
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Real-time Reporting
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Panic Button
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Live Chat
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Analytics Dashboard
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-12 md:mt-0">
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Support</h3>
                        <ul role="list" class="mt-4 space-y-4">
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Help Center
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Contact Us
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Terms of Service
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-12 border-t border-gray-200 dark:border-gray-700 pt-8">
            <p class="text-base text-gray-400 xl:text-center">
                &copy; {{ date('Y') }} Cashield. All rights reserved. A project by Abdullateef Babatunde, AFIT.
            </p>
        </div>
    </div>
</footer>

<!-- Add custom styles for animations -->
<style>
    @keyframes blob {
        0% {
            transform: translate(0px, 0px) scale(1);
        }
        33% {
            transform: translate(30px, -50px) scale(1.1);
        }
        66% {
            transform: translate(-20px, 20px) scale(0.9);
        }
        100% {
            transform: translate(0px, 0px) scale(1);
        }
    }
    
    .animate-blob {
        animation: blob 7s infinite;
    }
    
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    
    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
@endsection