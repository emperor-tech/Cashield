@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Access Denied</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $message ?? 'You do not have permission to access this resource.' }}</p>
        <div class="flex justify-center space-x-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Go Back
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Home
            </a>
        </div>
    </div>
</div>
@endsection 