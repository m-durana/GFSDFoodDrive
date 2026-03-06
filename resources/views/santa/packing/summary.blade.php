<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                End-of-Day Summary
            </h2>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('packing.summary') }}" class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ $date->toDateString() }}"
                           class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                           onchange="this.form.submit()">
                </form>
                <button onclick="window.print()" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-gray-600 text-white hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 9H5.25" /></svg>
                    Print
                </button>
                <a href="{{ route('packing.dashboard') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-gray-600 text-white hover:bg-gray-700 transition">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $summary['families_packed_count'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Families Packed</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['total_volunteers'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Volunteers</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['total_items_packed'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Items</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $summary['substitutions_count'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Substitutions</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary['unfulfilled_count'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unfulfilled</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['total_hours'] }}h</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteer Hours</div>
                </div>
            </div>

            {{-- Category Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Category Breakdown</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['category_breakdown']['food'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Food Items</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['category_breakdown']['gift'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gift Items</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['category_breakdown']['baby'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Baby Items</div>
                    </div>
                </div>
            </div>

            {{-- Volunteers Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 pb-2">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Volunteer Sessions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volunteer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items Packed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items/Hour</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($summary['volunteers'] as $vol)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $vol['name'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $vol['hours'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $vol['items_packed'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $vol['items_per_hour'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No volunteer sessions recorded for this date.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Families Packed --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 pb-2">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Families Packed</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Family</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($summary['families_packed'] as $fam)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $fam['family_number'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $fam['family_name'] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $fam['completed_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No families packed on this date.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
