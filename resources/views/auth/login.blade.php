<x-guest-layout>
    <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mt-12">
        <h1 class="text-2xl font-bold mb-6 text-blue-900 dark:text-blue-200">Sign In</h1>
        <x-auth-session-status class="mb-4" :status="session('status')" />
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="remember">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('Remember me') }}</label>
                </div>
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-blue-900 dark:hover:text-blue-200" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            </div>
            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
        <div class="mt-6 text-center">
            <span class="text-gray-600 dark:text-gray-400">{{ __('Don\'t have an account?') }}</span>
            <a href="{{ route('register') }}" class="ml-2 underline text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-200">{{ __('Register') }}</a>
        </div>
    </div>
</x-guest-layout>
