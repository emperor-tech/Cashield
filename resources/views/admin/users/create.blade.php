@extends('layouts.admin')

@section('page_title', 'Create New User')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create New User</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add a new user to the system</p>
    </div>

    <!-- Form Card -->
    <div class="admin-card p-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Name
                </label>
                <div class="mt-1">
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                        placeholder="Enter user's full name">
                </div>
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Email Address
                </label>
                <div class="mt-1">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                        placeholder="user@example.com">
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Role
                </label>
                <div class="mt-1">
                    <select name="role" id="role" required
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('role')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Password
                </label>
                <div class="mt-1">
                    <input type="password" name="password" id="password" required
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                        placeholder="Enter password">
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Confirm Password
                </label>
                <div class="mt-1">
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                        placeholder="Confirm password">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create User
                </button>
            </div>
        </form>
    </div>

    <!-- Password Requirements -->
    <div class="admin-card p-6">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Password Requirements</h3>
        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <li>Minimum 8 characters long</li>
            <li>Must contain at least one uppercase letter</li>
            <li>Must contain at least one lowercase letter</li>
            <li>Must contain at least one number</li>
            <li>Must contain at least one special character</li>
        </ul>
    </div>
</div>
@endsection 