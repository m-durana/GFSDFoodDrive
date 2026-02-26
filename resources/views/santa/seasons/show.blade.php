<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Season {{ $season->year }} Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Stats -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Overview</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $season->total_families }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Families</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $season->total_children }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Children</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $season->total_family_members }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total People</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $season->tags_adopted }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Tags Adopted</div>
                    </div>
                </div>
            </div>

            <!-- Gift Levels -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Gift Levels</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $season->gifts_level_0 }}</div>
                        <div class="text-xs text-red-600 dark:text-red-400">No Gifts</div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $season->gifts_level_1 }}</div>
                        <div class="text-xs text-yellow-600 dark:text-yellow-400">Partial</div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $season->gifts_level_2 }}</div>
                        <div class="text-xs text-yellow-600 dark:text-yellow-400">Moderate</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $season->gifts_level_3 }}</div>
                        <div class="text-xs text-green-600 dark:text-green-400">Fully Gifted</div>
                    </div>
                </div>
            </div>

            <!-- Delivery Breakdown -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Delivery</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $season->deliveries_completed }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Deliveries Completed</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $season->pickups_completed }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Pickups Completed</div>
                    </div>
                </div>
            </div>

            @if($season->notes)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Notes</h3>
                    <p class="text-gray-700 dark:text-gray-300">{{ $season->notes }}</p>
                </div>
            @endif

            @if($season->archived_at)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Archived on {{ $season->archived_at->format('F j, Y \a\t g:i A') }}
                </p>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ route('santa.seasons.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Season History
                </a>
                <a href="{{ route('santa.seasons.families', $season) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">
                    Browse Families
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
