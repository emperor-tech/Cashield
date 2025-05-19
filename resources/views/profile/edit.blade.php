<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Your Reports</h3>
            <table class="w-full bg-white dark:bg-gray-800 shadow rounded" aria-label="Your Reports Table">
                <thead>
                    <tr class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <th class="p-2">Campus</th>
                        <th class="p-2">Location</th>
                        <th class="p-2">Severity</th>
                        <th class="p-2">Date</th>
                        <th class="p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reports as $r)
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td class="p-2">{{ $r->campus }}</td>
                        <td class="p-2">{{ $r->location }}</td>
                        <td class="p-2 capitalize @if($r->severity=='high')text-red-600 @elseif($r->severity=='medium')text-yellow-600 @else text-green-600 @endif">{{ $r->severity }}</td>
                        <td class="p-2">{{ $r->created_at->format('M d, Y H:i') }}</td>
                        <td class="p-2 capitalize">{{ $r->status ?? 'open' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-2 text-center">No reports yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
