<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Notification Settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Manage your notification preferences and alert settings.') }}
        </p>
    </header>

    <div class="mt-6">
        <notification-preferences></notification-preferences>
    </div>

    <div class="mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('Active Area Subscriptions') }}</h3>
            
            <div class="space-y-4">
                @forelse (auth()->user()->areaSubscriptions as $subscription)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <span class="font-medium">{{ $subscription->area_name }}</span>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Subscribed since: ') }} {{ $subscription->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        
                        <form method="POST" action="{{ route('profile.unsubscribe-area', $subscription->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                {{ __('Unsubscribe') }}
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('You are not subscribed to any areas yet.') }}
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</section>