<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Shopping Lists
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('santa.shoppingList', ['manage' => '1']) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Manage Items
                </a>
                @if($families->count() > 0)
                    <a href="{{ route('santa.shoppingList', array_merge(request()->query(), ['format' => 'csv'])) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                        Export CSV
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Generate Shopping List</h3>
                <form method="GET" action="{{ route('santa.shoppingList') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Single Family</label>
                            <select name="family_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All numbered families</option>
                                @foreach($allFamilies as $f)
                                    <option value="{{ $f->id }}" {{ request('family_id') == $f->id ? 'selected' : '' }}>
                                        #{{ $f->family_number }} — {{ $f->family_name }} ({{ $f->number_of_family_members }} members)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School</label>
                            <select id="sl_school_select" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Schools</option>
                                @foreach($schoolRanges as $range)
                                    <option value="{{ $range->id }}" data-start="{{ $range->range_start }}" data-end="{{ $range->range_end }}">{{ $range->school_name }} ({{ $range->range_start }}–{{ $range->range_end }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Family # Range Start</label>
                            <input type="number" name="family_number_start" id="sl_range_start" value="{{ request('family_number_start') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm" placeholder="e.g. 1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Family # Range End</label>
                            <input type="number" name="family_number_end" id="sl_range_end" value="{{ request('family_number_end') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm" placeholder="e.g. 99">
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Generate
                        </button>
                        <a href="{{ route('santa.shoppingList') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Reset</a>
                    </div>
                </form>
            </div>

            @if($families->count() > 0)
                <!-- Summary -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Aggregate Totals ({{ $families->count() }} {{ Str::plural('family', $families->count()) }})
                    </h3>

                    @php
                        $categories = ['canned' => 'Canned Goods', 'dry' => 'Dry Goods', 'personal' => 'Personal Care', 'condiment' => 'Condiments & Other'];
                    @endphp

                    @foreach($categories as $catKey => $catLabel)
                        @php
                            $catItems = $groceryItems->where('category', $catKey);
                            $catTotals = $catItems->mapWithKeys(fn($item) => [$item->name => $totals[$item->name] ?? 0])->filter(fn($qty) => $qty > 0);
                        @endphp
                        @if($catTotals->count() > 0)
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">{{ $catLabel }}</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                    @foreach($catTotals as $name => $qty)
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded px-3 py-2 text-sm">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $qty }}</span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-1">{{ $name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Grand Total: {{ array_sum($totals) }} items
                        </span>
                    </div>
                </div>

                <!-- Per-family breakdown -->
                @foreach($families as $family)
                    @php $list = $shoppingLists[$family->id] ?? []; @endphp
                    @if(count($list) > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                    #{{ $family->family_number }} — {{ $family->family_name }}
                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                        ({{ $family->number_of_family_members }} members, {{ array_sum(array_column($list, 'quantity')) }} items)
                                    </span>
                                </h4>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-1">
                                @foreach($list as $itemName => $info)
                                    <div class="text-xs px-2 py-1 bg-gray-50 dark:bg-gray-700 rounded">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $info['quantity'] }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $itemName }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @elseif(request()->anyFilled(['family_id', 'family_number_start']))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No families match the selected filter.
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    Select a family or range above to generate a shopping list.
                    <br>
                    <span class="text-sm">{{ $groceryItems->count() }} grocery items configured in the formula.</span>
                </div>
            @endif

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sl_school_select').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('sl_range_start').value = opt.dataset.start || '';
            document.getElementById('sl_range_end').value = opt.dataset.end || '';
        });
    </script>
</x-app-layout>
