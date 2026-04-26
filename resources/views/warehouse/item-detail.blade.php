<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
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
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Product Name</span>
                            <p class="text-lg font-semibold text-base-content">{{ $item->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Barcode</span>
                            <p class="text-base-content font-mono">{{ $item->barcode ?? 'None' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Category</span>
                            <p class="text-base-content">
                                <span class="inline-block w-2 h-2 rounded-full mr-1 {{ $item->category->type === 'food' ? 'bg-amber-400' : ($item->category->type === 'gift' ? 'bg-purple-400' : ($item->category->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400')) }}"></span>
                                {{ $item->category->name }}
                                <span class="text-base-content/60 text-sm">({{ ucfirst($item->category->type) }})</span>
                            </p>
                        </div>
                        @if($item->description)
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Description</span>
                            <p class="text-base-content/80">{{ $item->description }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Stock on Hand ({{ $seasonYear }})</span>
                            <p class="text-3xl font-bold {{ $stockOnHand > 0 ? 'text-green-600 dark:text-green-400' : ($stockOnHand < 0 ? 'text-primary dark:text-primary' : 'text-gray-400') }}">{{ $stockOnHand }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Unit</span>
                            <p class="text-base-content">{{ $item->category->unit }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/60 uppercase tracking-wider">Status</span>
                            <p>
                                @if($item->active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-base-200 text-base-content/70">Inactive</span>
                                @endif
                                @if($item->is_generic)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 ml-1">Generic</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Warehouse Location -->
                <div class="mt-4 pt-4 border-t border-base-300">
                    <h4 class="text-sm font-medium text-base-content/80 mb-3">Warehouse Location</h4>
                    <form method="POST" action="{{ route('warehouse.item.location', $item) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="location_zone" class="block text-xs text-base-content/60">Zone</label>
                            <input type="text" name="location_zone" id="location_zone" value="{{ $item->location_zone }}" maxlength="10" placeholder="e.g. A"
                                class="mt-1 block w-20 rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="location_shelf" class="block text-xs text-base-content/60">Shelf</label>
                            <input type="text" name="location_shelf" id="location_shelf" value="{{ $item->location_shelf }}" maxlength="10" placeholder="e.g. 2"
                                class="mt-1 block w-20 rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="location_bin" class="block text-xs text-base-content/60">Bin</label>
                            <input type="text" name="location_bin" id="location_bin" value="{{ $item->location_bin }}" maxlength="20" placeholder="e.g. 03"
                                class="mt-1 block w-24 rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                                Save Location
                            </button>
                        </div>
                        <div class="text-sm text-base-content/60">
                            Current: <span class="font-mono">{{ $item->locationLabel() }}</span>
                        </div>
                    </form>
                </div>

                @if($item->active)
                    <div class="mt-4 pt-4 border-t border-base-300">
                        <form method="POST" action="{{ route('warehouse.item.remove', $item) }}" class="inline"
                              onsubmit="return confirm('Remove this item from inventory? Any pending packing items using this item will be auto-substituted or marked unfulfilled.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary text-white text-sm font-medium rounded-md transition">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                Remove from Inventory
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if(!empty($offData))
            <!-- Open Food Facts Product Info -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-base-content mb-4">Product Information <span class="text-xs text-gray-400 font-normal">(via Open Food Facts)</span></h3>
                <div class="flex flex-col sm:flex-row gap-6">
                    @if(!empty($offData['image']))
                        <div class="shrink-0">
                            <img src="{{ $offData['image'] }}" alt="{{ $offData['name'] ?? '' }}" class="w-32 h-32 object-contain rounded-lg border border-base-300 bg-white">
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 grow">
                        @if(!empty($offData['name']))
                            <div>
                                <span class="text-xs text-base-content/60 uppercase tracking-wider">Product Name</span>
                                <p class="text-base-content font-medium">{{ $offData['name'] }}</p>
                            </div>
                        @endif
                        @if(!empty($offData['brand']))
                            <div>
                                <span class="text-xs text-base-content/60 uppercase tracking-wider">Brand</span>
                                <p class="text-base-content">{{ $offData['brand'] }}</p>
                            </div>
                        @endif
                        @if(!empty($offData['barcode_normalized']))
                            <div>
                                <span class="text-xs text-base-content/60 uppercase tracking-wider">Normalized Barcode</span>
                                <p class="text-base-content font-mono text-sm">{{ $offData['barcode_normalized'] }}</p>
                            </div>
                        @endif
                        @if(!empty($offData['suggested_category_name']))
                            <div>
                                <span class="text-xs text-base-content/60 uppercase tracking-wider">Suggested Category</span>
                                <p class="text-base-content">{{ $offData['suggested_category_name'] }}</p>
                            </div>
                        @endif
                        @if(!empty($offData['categories_tags']))
                            <div class="sm:col-span-2">
                                <span class="text-xs text-base-content/60 uppercase tracking-wider">Categories</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach(array_slice($offData['categories_tags'], 0, 10) as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs bg-base-200 text-base-content/70">{{ str_replace('en:', '', $tag) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Transaction History -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg">
                <div class="p-4 border-b border-base-300">
                    <h3 class="text-lg font-medium text-base-content">Transaction History</h3>
                    <p class="text-sm text-base-content/60">Last 50 transactions for this item</p>
                </div>

                @if($transactions->isEmpty())
                    <div class="p-8 text-center text-base-content/60">
                        No transactions recorded for this item.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-base-300">
                                    <th class="text-left py-3 px-4 text-base-content/60">Date</th>
                                    <th class="text-left py-3 px-4 text-base-content/60">Type</th>
                                    <th class="text-right py-3 px-4 text-base-content/60">Qty</th>
                                    <th class="text-left py-3 px-4 text-base-content/60">Source</th>
                                    <th class="text-left py-3 px-4 text-base-content/60">Donor</th>
                                    <th class="text-left py-3 px-4 text-base-content/60">Family</th>
                                    <th class="text-left py-3 px-4 text-base-content/60">Scanned By</th>
                                    <th class="text-left py-3 px-4 text-base-content/60">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-2 px-4 text-base-content/70 whitespace-nowrap">{{ $tx->created_at->format('M j, g:ia') }}</td>
                                        <td class="py-2 px-4">
                                            @php
                                                $typeEnum = \App\Enums\TransactionType::tryFrom($tx->transaction_type);
                                                $typeLabel = $typeEnum ? $typeEnum->label() : $tx->transaction_type;
                                                $typeColor = match($tx->transaction_type) {
                                                    'in', 'return' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                                    'out', 'distributed' => 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary',
                                                    default => 'bg-base-200 text-base-content/70',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium {{ $typeColor }}">{{ $typeLabel }}</span>
                                        </td>
                                        <td class="text-right py-2 px-4 font-medium text-base-content">{{ $tx->quantity }}</td>
                                        <td class="py-2 px-4 text-base-content/70">{{ $tx->source ?? '&mdash;' }}</td>
                                        <td class="py-2 px-4 text-base-content/70">{{ $tx->donor_name ?? '&mdash;' }}</td>
                                        <td class="py-2 px-4 text-base-content/70">
                                            @if($tx->family)
                                                #{{ $tx->family->family_number }} {{ $tx->family->family_name }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 text-base-content/70">{{ $tx->scanner?->username ?? '&mdash;' }}</td>
                                        <td class="py-2 px-4 text-base-content/60 text-xs max-w-xs truncate">{{ $tx->notes ?? '' }}</td>
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
