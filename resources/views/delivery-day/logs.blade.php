<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Delivery Logs
            </h2>
            <a href="{{ route('delivery.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                Back to Delivery Day
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Date filter -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('delivery.logs') }}" class="flex items-end space-x-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date</label>
                        <select name="date" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All dates</option>
                            @foreach($logDates as $date)
                                <option value="{{ $date }}" {{ request('date') == $date ? 'selected' : '' }}>{{ $date }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Filter
                    </button>
                    <a href="{{ route('delivery.logs') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700">Reset</a>
                </form>
            </div>

            <!-- Logs table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @if($logs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">By</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($logs as $log)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                            {{ $log->created_at->format('M j, Y g:ia') }}
                                        </td>
                                        <td class="px-3 py-2 text-sm">
                                            @if($log->family)
                                                <a href="{{ route('family.show', $log->family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    #{{ $log->family->family_number }} {{ $log->family->family_name }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">Deleted</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-sm">
                                            @php
                                                $logStatusColors = [
                                                    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                    'left_at_door' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                    'picked_up' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                    'no_answer' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                    'attempted' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                    'in_transit' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                                    'note' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                ];
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $logStatusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $log->user?->first_name ?? 'System' }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $log->notes ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->withQueryString()->links() }}
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">No delivery logs yet.</p>
                @endif
            </div>

            <div>
                <a href="{{ route('delivery.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Delivery Day
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
