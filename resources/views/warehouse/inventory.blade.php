<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Inventory
            <x-hint key="warehouse-inventory" text="Filter by type using the tabs. Click a category row to expand and see individual items. Green = surplus, Red = deficit compared to what families need." />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="Object.assign(sortTable(), { activeTab: 'all', expanded: {} })">

            <!-- Inventory Table -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg">
                <!-- Type Filter Tabs -->
                <div class="flex space-x-1 p-4 border-b border-base-300">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary' : 'text-base-content/60 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">All</button>
                    <button @click="activeTab = 'food'" :class="activeTab === 'food' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'text-base-content/60 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Food</button>
                    <button @click="activeTab = 'gift'" :class="activeTab === 'gift' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'text-base-content/60 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Gifts</button>
                    <button @click="activeTab = 'baby'" :class="activeTab === 'baby' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300' : 'text-base-content/60 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Baby</button>
                    <button @click="activeTab = 'supply'" :class="activeTab === 'supply' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-base-content/60 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Supply</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-base-300">
                                <x-sort-th key="category" class="text-left">Category</x-sort-th>
                                <x-sort-th key="unit" class="text-center">Unit</x-sort-th>
                                <x-sort-th key="on_hand" class="text-right">On Hand</x-sort-th>
                                <x-sort-th key="needed" class="text-right">Needed</x-sort-th>
                                <x-sort-th key="deficit" class="text-right">Deficit/Surplus</x-sort-th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deficits as $i => $row)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    x-show="activeTab === 'all' || activeTab === '{{ $row['category']->type }}'"
                                    @click="expanded[{{ $i }}] = !expanded[{{ $i }}]">
                                    <td data-col="category" class="py-3 px-4 text-base-content font-medium">
                                        <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $row['category']->type === 'food' ? 'bg-amber-400' : ($row['category']->type === 'gift' ? 'bg-purple-400' : ($row['category']->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400')) }}"></span>
                                        {{ $row['category']->name }}
                                        @if($row['category']->items->count())
                                            <svg class="inline h-4 w-4 text-gray-400 ml-1 transition-transform" :class="expanded[{{ $i }}] && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    </td>
                                    <td data-col="unit" class="text-center py-3 px-4 text-base-content/60">{{ $row['category']->unit }}</td>
                                    <td data-col="on_hand" data-sort-value="{{ $row['on_hand'] }}" class="text-right py-3 px-4 text-base-content font-medium">{{ $row['on_hand'] }}</td>
                                    <td data-col="needed" data-sort-value="{{ $row['needed'] }}" class="text-right py-3 px-4 text-base-content/70">{{ $row['needed'] }}</td>
                                    <td data-col="deficit" data-sort-value="{{ $row['deficit'] }}" class="text-right py-3 px-4 font-medium {{ $row['deficit'] > 0 ? 'text-primary dark:text-primary' : ($row['deficit'] < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400') }}">
                                        @if($row['deficit'] > 0)
                                            -{{ $row['deficit'] }}
                                        @elseif($row['deficit'] < 0)
                                            +{{ abs($row['deficit']) }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                </tr>
                                @foreach($row['category']->items as $item)
                                    <tr x-show="(activeTab === 'all' || activeTab === '{{ $row['category']->type }}') && expanded[{{ $i }}]" x-cloak
                                        class="bg-base-200/30 border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-600/30 cursor-pointer"
                                        onclick="window.location='{{ route('warehouse.item.detail', $item) }}'">
                                        <td class="py-2 px-4 pl-10 text-base-content/70 text-xs" colspan="2">
                                            <span class="font-medium text-base-content">{{ $item->name }}</span>
                                            @if($item->barcode) <span class="text-base-content/50 ml-1 font-mono">[{{ $item->barcode }}]</span> @endif
                                            @if($item->brand ?? null) <span class="text-base-content/50 ml-1">&middot; {{ $item->brand }}</span> @endif
                                        </td>
                                        <td class="text-right py-2 px-4 text-xs font-medium text-base-content/80">{{ $item->stock_quantity ?? 0 }}</td>
                                        <td class="text-right py-2 px-4 text-xs text-base-content/50">
                                            @if($item->latestTransaction)
                                                {{ $item->latestTransaction->created_at->diffForHumans() }}
                                            @endif
                                        </td>
                                        <td class="text-right py-2 px-4 text-xs">
                                            <span class="text-blue-500 dark:text-blue-400">Detail &rarr;</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
