<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Inventory
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ activeTab: 'all', expanded: {} }">

            <!-- Inventory Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <!-- Type Filter Tabs -->
                <div class="flex space-x-1 p-4 border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">All</button>
                    <button @click="activeTab = 'food'" :class="activeTab === 'food' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Food</button>
                    <button @click="activeTab = 'gift'" :class="activeTab === 'gift' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Gifts</button>
                    <button @click="activeTab = 'baby'" :class="activeTab === 'baby' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Baby</button>
                    <button @click="activeTab = 'supply'" :class="activeTab === 'supply' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Supply</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Category</th>
                                <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400">Unit</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">On Hand</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Needed</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Deficit/Surplus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deficits as $i => $row)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    x-show="activeTab === 'all' || activeTab === '{{ $row['category']->type }}'"
                                    @click="expanded[{{ $i }}] = !expanded[{{ $i }}]">
                                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100 font-medium">
                                        <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $row['category']->type === 'food' ? 'bg-amber-400' : ($row['category']->type === 'gift' ? 'bg-purple-400' : ($row['category']->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400')) }}"></span>
                                        {{ $row['category']->name }}
                                        @if($row['category']->items->count())
                                            <svg class="inline h-4 w-4 text-gray-400 ml-1 transition-transform" :class="expanded[{{ $i }}] && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    </td>
                                    <td class="text-center py-3 px-4 text-gray-500 dark:text-gray-400">{{ $row['category']->unit }}</td>
                                    <td class="text-right py-3 px-4 text-gray-900 dark:text-gray-100 font-medium">{{ $row['on_hand'] }}</td>
                                    <td class="text-right py-3 px-4 text-gray-600 dark:text-gray-400">{{ $row['needed'] }}</td>
                                    <td class="text-right py-3 px-4 font-medium {{ $row['deficit'] > 0 ? 'text-red-600 dark:text-red-400' : ($row['deficit'] < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400') }}">
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
                                        class="bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700/50">
                                        <td class="py-2 px-4 pl-10 text-gray-600 dark:text-gray-400 text-xs">
                                            {{ $item->name }}
                                            @if($item->barcode) <span class="text-gray-400 dark:text-gray-500 ml-1">[{{ $item->barcode }}]</span> @endif
                                        </td>
                                        <td colspan="4" class="py-2 px-4 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $item->description ?? '' }}
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
