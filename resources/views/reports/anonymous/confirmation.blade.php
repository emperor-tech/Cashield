@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Report Submitted Successfully</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Thank you for helping keep our community safe.</p>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your Report Tracking Code</h2>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md p-4 text-center">
                <span class="text-2xl font-mono font-bold tracking-wider text-red-600 dark:text-red-400">{{ $trackingCode }}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">
                Please save this tracking code. You can use it to check the status of your report later.
            </p>
        </div>
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Report ID</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $report->id }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Submitted On</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $report->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p class="font-medium">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                            {{ ucfirst($report->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Severity</p>
                    <p class="font-medium">
                        @if($report->severity == 'high')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                High
                            </span>
                        @elseif($report->severity == 'medium')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100">
                                Medium
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                Low
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">What Happens Next?</h2>
            <ul class="space-y-3 text-gray-600 dark:text-gray-400">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Our security team will review your report</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>If you provided contact information, we may reach out for more details</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Appropriate action will be taken based on the severity and nature of the report</span>
                </li>
            </ul>
        </div>
        
        <div class="mt-8 flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('home') }}" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Return to Home
            </a>
            <a href="{{ route('reports.anonymous.create') }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Submit Another Report
            </a>
        </div>
    </div>
</div>
@endsection