<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div x-data="{ activeTab: window.location.hash ? window.location.hash.substring(1) : 'profile' }" class="max-w-xl">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                        <nav class="flex space-x-8" aria-label="Profile sections">
                            <button @click="activeTab = 'profile'; window.location.hash = 'profile'"
                                    :class="{'border-b-2 border-blue-500': activeTab === 'profile'}"
                                    class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                {{ __('Profile Information') }}
                            </button>
                            
                            <button @click="activeTab = 'achievements'; window.location.hash = 'achievements'"
                                    :class="{'border-b-2 border-blue-500': activeTab === 'achievements'}"
                                    class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                {{ __('Achievements') }}
                            </button>
                            
                            <button @click="activeTab = 'notifications'; window.location.hash = 'notifications'"
                                    :class="{'border-b-2 border-blue-500': activeTab === 'notifications'}"
                                    class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                {{ __('Notifications') }}
                            </button>

                            <button @click="activeTab = 'password'; window.location.hash = 'password'"
                                    :class="{'border-b-2 border-blue-500': activeTab === 'password'}"
                                    class="py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                {{ __('Password') }}
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Panels -->
                    <div x-show="activeTab === 'profile'">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div x-show="activeTab === 'achievements'" class="max-w-4xl">
                        <user-achievements :user="{{ json_encode(auth()->user()) }}"></user-achievements>
                    </div>

                    <div x-show="activeTab === 'notifications'">
                        @include('profile.partials.notifications-form')
                    </div>

                    <div x-show="activeTab === 'password'">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/components/NotificationPreferences.vue', 'resources/js/components/UserAchievements.vue'])
    @endpush
</x-app-layout>
