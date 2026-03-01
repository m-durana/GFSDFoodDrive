<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $item->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Back link -->
            <div>
                <a href="{{ route('warehouse.inventory') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&larr; Back to Inventory</a>
            </div>

            <!-- Item Details Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product Name</span>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $item->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barcode</span>
                            <p class="text-gray-900 dark:text-gray-100 font-mono">{{ $item->barcode ?? 'None' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</span>
                            <p class="text-gray-900 dark:text-gray-100">
                                <span class="inline-block w-2 h-2 rounded-full mr-1 {{ $item->category->type === 'food' ? 'bg-amber-400' : ($item->category->type === 'gift' ? 'bg-purple-400' : ($item->category->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400')) }}"></span>
                                {{ $item->category->name }}
                                <span class="text-gray-500 dark:text-gray-400 text-sm">({{ ucfirst($item->category->type) }})</span>
                            </p>
                        </div>
                        @if($item->description)
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</span>
                            <p class="text-gray-700 dark:text-gray-300">{{ $item->description }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock on Hand ({{ $seasonYear }})</span>
                            <p class="text-3xl font-bold {{ $stockOnHand > 0 ? 'text-green-600 dark:text-green-400' : ($stockOnHand < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400') }}">{{ $stockOnHand }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</span>
                            <p class="text-gray-900 dark:text-gray-100">{{ $item->category->unit }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</span>
                            <p>
                                @if($item->active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Inactive</span>
                                @endif
                                @if($item->is_generic)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 ml-1">Generic</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Transaction History</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Last 50 transactions for this item</p>
                </div>

                @if($transactions->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No transactions recorded for this item.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Type</th>
                                    <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Source</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Donor</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Family</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Scanned By</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $tx->created_at->format('M j, g:ia') }}</td>
                                        <td class="py-2 px-4">
                                            @php
                                                $typeEnum = \App\Enums\TransactionType::tryFrom($tx->transaction_type);
                                                $typeLabel = $typeEnum ? $typeEnum->label() : $tx->transaction_type;
                                                $typeColor = match($tx->transaction_type) {
                                                    'in', 'return' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                                    'out', 'distributed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                                    default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeColor }}">{{ $typeLabel }}</span>
                                        </td>
                                        <td class="text-right py-2 px-4 font-medium text-gray-900 dark:text-gray-100">{{ $tx->quantity }}</td>
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $tx->source ?? '&mdash;' }}</td>
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $tx->donor_name ?? '&mdash;' }}</td>
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400">
                                            @if($tx->family)
                                                #{{ $tx->family->family_number }} {{ $tx->family->family_name }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $tx->scanner?->username ?? '&mdash;' }}</td>
                                        <td class="py-2 px-4 text-gray-500 dark:text-gray-400 text-xs max-w-xs truncate">{{ $tx->notes ?? '' }}</td>
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
