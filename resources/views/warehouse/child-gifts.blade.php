<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gift History — #{{ $child->family->family_number ?? '?' }} {{ $child->gender }}, age {{ $child->age }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Back links -->
            <div class="flex gap-4">
                <a href="{{ route('santa.gifts') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&larr; Back to Gift Overview</a>
                <a href="{{ route('family.show', $child->family) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View Family</a>
            </div>

            <!-- Child Info Banner -->
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-5">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-purple-600 dark:text-purple-400 uppercase tracking-wider">Family #</span>
                        <p class="font-bold text-purple-900 dark:text-purple-200">{{ $child->family->family_number ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-purple-600 dark:text-purple-400 uppercase tracking-wider">Gender</span>
                        <p class="font-medium text-purple-900 dark:text-purple-200">{{ $child->gender }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-purple-600 dark:text-purple-400 uppercase tracking-wider">Age</span>
                        <p class="font-medium text-purple-900 dark:text-purple-200">{{ $child->age }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-purple-600 dark:text-purple-400 uppercase tracking-wider">Gift Level</span>
                        <p class="font-medium text-purple-900 dark:text-purple-200">{{ ($child->gift_level ?? \App\Enums\GiftLevel::None)->label() }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-purple-600 dark:text-purple-400 uppercase tracking-wider">Adopter</span>
                        <p class="font-medium text-purple-900 dark:text-purple-200">{{ $child->adopter_name ?? '—' }}</p>
                    </div>
                </div>
                @if($child->gifts_received)
                    <div class="mt-3 pt-3 border-t border-purple-200 dark:border-purple-700">
                        <span class="text-xs text-purple-600 dark:text-purple-400 uppercase tracking-wider">Gifts Received (stored)</span>
                        <p class="text-sm text-purple-900 dark:text-purple-200 mt-1">{{ $child->gifts_received }}</p>
                    </div>
                @endif
            </div>

            <!-- Transaction History -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Warehouse Transaction History</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">All warehouse transactions linked to this child</p>
                </div>

                @if($transactions->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No warehouse transactions recorded for this child.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Source</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Scanned By</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $tx->created_at->format('M j, g:ia') }}</td>
                                        <td class="py-2 px-4 text-gray-900 dark:text-gray-100">{{ $tx->category?->name ?? '—' }}</td>
                                        <td class="text-right py-2 px-4 font-medium text-gray-900 dark:text-gray-100">{{ $tx->quantity }}</td>
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $tx->source ?? '—' }}</td>
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $tx->scanner?->username ?? '—' }}</td>
                                        <td class="py-2 px-4 text-gray-500 dark:text-gray-400">{{ $tx->notes ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
