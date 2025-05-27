<section class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mt-8">
    <header class="mb-6">
        <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-200">Profile Information</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update your account's profile information and email address.</p>
    </header>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>
    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="underline text-sm text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">{{ __('Click here to re-send the verification email.') }}</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">{{ __('A new verification link has been sent to your email address.') }}</p>
                    @endif
                </div>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
        <div class="mt-8 flex items-center gap-4">
            <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-20 h-20 rounded-full border-2 border-blue-600">
            <input type="file" name="avatar" accept="image/*" class="mb-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload Avatar</button>
        </div>
    </form>
    <div class="mt-8">
        <h2 class="text-lg font-bold mb-2">Notification Preferences</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="flex flex-col gap-2">
            @csrf
            @method('patch')
            <label class="flex items-center gap-2"><input type="checkbox" name="notification_prefs[email]" value="1" @if($user->notification_prefs['email']) checked @endif class="rounded"> Email Alerts</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="notification_prefs[push]" value="1" @if($user->notification_prefs['push']) checked @endif class="rounded"> Push Notifications</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="notification_prefs[area]" value="1" @if($user->notification_prefs['area']) checked @endif class="rounded"> Area Alerts</label>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Save Preferences</button>
        </form>
    </div>
    <div class="mt-8">
        <h2 class="text-lg font-bold mb-2">Community Watch: Area Alerts</h2>
        <form method="POST" action="{{ route('profile.subscribe-area') }}" class="flex gap-2 mb-4">
            @csrf
            <input type="text" name="area" class="border rounded p-2" placeholder="Enter area/campus/city..." required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Subscribe</button>
        </form>
        <ul>
            @foreach(auth()->user()->subscriptions as $sub)
                <li class="flex items-center gap-2 mb-2">
                    <span class="font-semibold">{{ $sub->area }}</span>
                    <form method="POST" action="{{ route('profile.unsubscribe-area', $sub->area) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Unsubscribe</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="mt-8">
        <h2 class="text-lg font-bold mb-2">Your Badges</h2>
        <div class="flex flex-wrap gap-4 mb-4">
            @foreach(auth()->user()->badges as $badge)
                <div class="flex flex-col items-center bg-blue-50 dark:bg-blue-900 p-2 rounded shadow w-24">
                    <span class="text-3xl">{{ $badge->icon }}</span>
                    <span class="font-semibold text-xs mt-1">{{ $badge->name }}</span>
                    <span class="text-xs text-gray-500">{{ $badge->description }}</span>
                </div>
            @endforeach
            @if(auth()->user()->badges->isEmpty())
                <span class="text-gray-500">No badges yet. Start reporting to earn badges!</span>
            @endif
        </div>
    </div>
    <div class="mt-8">
        <h2 class="text-lg font-bold mb-2">Your Reports & Alerts</h2>
        <ul class="divide-y divide-blue-100 dark:divide-blue-900">
            @foreach($user->reports()->latest()->take(10)->get() as $r)
                <li class="py-2 flex items-center justify-between">
                    <div>
                        <span class="font-semibold">[{{ ucfirst($r->severity) }}]</span>
                        <span>{{ $r->description }}</span>
                        <span class="text-xs text-gray-400">({{ $r->created_at->format('M d, Y H:i') }})</span>
                    </div>
                    <a href="{{ route('reports.show', $r->id) }}" class="text-blue-600 hover:underline ml-2">View</a>
                </li>
            @endforeach
            @if($user->reports()->count() === 0)
                <li class="text-gray-500">No reports or alerts yet.</li>
            @endif
        </ul>
    </div>
</section>
