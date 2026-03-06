<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Shopping Day — Assignments
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- What Needs to be Purchased (Deficit Overview) -->
            @if(!empty($deficits))
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">What Needs to be Purchased</h3>
                @php
                    $totalToBuy = collect($deficits)->sum('deficit');
                    $totalNeeded = collect($deficits)->sum('total_needed');
                    $totalOnHand = collect($deficits)->sum('on_hand');
                    $byCategory = collect($deficits)->groupBy('category');
                @endphp
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $totalToBuy }}</span> total items to buy
                    &middot; {{ $totalNeeded }} needed &middot; {{ $totalOnHand }} on hand
                </p>
                <div class="space-y-3">
                    @foreach($byCategory as $catName => $items)
                        @php
                            $catNeeded = $items->sum('total_needed');
                            $catOnHand = $items->sum('on_hand');
                            $catDeficit = $items->sum('deficit');
                            $pct = $catNeeded > 0 ? round(($catOnHand / $catNeeded) * 100) : 100;
                            $barColor = $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500');
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($catName) }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $catOnHand }} on hand / {{ $catNeeded }} needed &middot; <span class="font-semibold {{ $catDeficit > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $catDeficit > 0 ? $catDeficit . ' to buy' : 'Stocked' }}</span></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            @if($catDeficit > 0)
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($items->where('deficit', '>', 0) as $item)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                        {{ $item['grocery_item_name'] }} &times;{{ $item['deficit'] }}
                                    </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Current Assignments -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Assignments</h3>

                @if($assignments->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($assignments as $assignment)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $assignment->getDisplayName() }}
                                    </h4>
                                    <form method="POST" action="{{ route('santa.deleteAssignment', $assignment) }}" onsubmit="return confirm('Remove this assignment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                    </form>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $assignment->getDescription() }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Type: <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $assignment->split_type)) }}</span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $assignment->getTotalItems() }} total items
                                    @php $checked = $assignment->checks()->count(); @endphp
                                    @if($checked > 0)
                                        &middot; <span class="text-green-600 dark:text-green-400">{{ $checked }} checked</span>
                                    @endif
                                </p>
                                @if($assignment->notes)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">{{ $assignment->notes }}</p>
                                @endif
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('shopping.assignment', $assignment->token) }}" target="_blank"
                                           class="inline-flex items-center px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-600 transition">
                                            Open Checklist
                                        </a>
                                        <button type="button" onclick="copyLink('{{ route('shopping.assignment', $assignment->token) }}')"
                                                class="inline-flex items-center px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                            Copy URL
                                        </button>
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-mono break-all select-all">
                                        {{ route('shopping.assignment', $assignment->token) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No assignments yet. Create one below.</p>
                @endif
            </div>

            <!-- Coverage Indicator -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Family Range Coverage</h3>
                @if(count($assignedRanges) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($assignedRanges as $range)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                #{{ $range['start'] }}–#{{ $range['end'] }} &#10003;
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400">No family range assignments yet.</p>
                @endif
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Highest family number: {{ $maxFamilyNumber }}</p>
            </div>

            <!-- Add Assignment -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6" x-data="{
                splitType: '{{ old('split_type', 'family_range') }}',
                subcatCategory: '{{ old('subcategory_category', '') }}',
                groceryItemsByCategory: @js($groceryItemsByCategory ?? []),
                get subcatItems() {
                    return this.groceryItemsByCategory[this.subcatCategory] || [];
                }
            }">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add Assignment</h3>
                <form method="POST" action="{{ route('santa.createAssignment') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ninja_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shopper Name</label>
                            <input type="text" name="ninja_name" id="ninja_name" placeholder="e.g. Jake, Sarah, Team Alpha"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                value="{{ old('ninja_name') }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">For NINJAs who don't have accounts</p>
                        </div>

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Or Assign to Coordinator</label>
                            <select name="user_id" id="user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">— None (use name above) —</option>
                                @foreach($coordinators as $coord)
                                    <option value="{{ $coord->id }}">{{ $coord->first_name }} {{ $coord->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Split Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assignment Type</label>
                        <div class="flex flex-wrap gap-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="split_type" value="family_range" x-model="splitType"
                                    class="text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">By Family Range</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="split_type" value="category" x-model="splitType"
                                    class="text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">By Category</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="split_type" value="deficit" x-model="splitType"
                                    class="text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Full Deficit Buy</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="split_type" value="smart_split" x-model="splitType"
                                    class="text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Smart Split</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="split_type" value="subcategory" x-model="splitType"
                                    class="text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">By Subcategory</span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="splitType === 'deficit'">This shopper will see all items that still need to be purchased.</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="splitType === 'smart_split'">Auto-divide all families so each shopper gets roughly equal item counts.</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="splitType === 'subcategory'">Select specific items within a category for this shopper.</p>
                    </div>

                    <!-- Family Range fields (shown when split_type = family_range) -->
                    <div x-show="splitType === 'family_range'" x-cloak class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School (auto-fills range)</label>
                                <select id="sd_school_select" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="">Select a school...</option>
                                    @foreach($schoolRanges as $range)
                                        <option value="{{ $range->id }}" data-start="{{ $range->range_start }}" data-end="{{ $range->range_end }}">{{ $range->school_name }} ({{ $range->range_start }}&ndash;{{ $range->range_end }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="family_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Family #</label>
                                <input type="number" name="family_start" id="family_start" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                    value="{{ old('family_start') }}">
                            </div>
                            <div>
                                <label for="family_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Family #</label>
                                <input type="number" name="family_end" id="family_end" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                    value="{{ old('family_end') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Category fields (shown when split_type = category) -->
                    <div x-show="splitType === 'category'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categories to Shop</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($groceryCategories as $cat)
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="categories[]" value="{{ $cat }}"
                                        class="rounded text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600"
                                        {{ in_array($cat, old('categories', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($cat) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Smart Split fields (shown when split_type = smart_split) -->
                    <div x-show="splitType === 'smart_split'" x-cloak class="space-y-3">
                        <div class="max-w-xs">
                            <label for="num_shoppers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number of Shoppers</label>
                            <input type="number" name="num_shoppers" id="num_shoppers" min="2" max="10"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                value="{{ old('num_shoppers', 3) }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Families will be divided into this many groups, balanced by item count. One assignment per group will be created.
                            </p>
                        </div>
                    </div>

                    <!-- Subcategory fields (shown when split_type = subcategory) -->
                    <div x-show="splitType === 'subcategory'" x-cloak class="space-y-3">
                        <div>
                            <label for="subcategory_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select name="subcategory_category" id="subcategory_category" x-model="subcatCategory"
                                class="mt-1 block w-full max-w-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Select a category...</option>
                                @foreach($groceryCategories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="subcatCategory" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Items to Include</label>
                            <div class="flex flex-wrap gap-3 max-h-48 overflow-y-auto p-2 border border-gray-200 dark:border-gray-600 rounded-md">
                                <template x-for="item in subcatItems" :key="item.id">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="subcategory_items[]" :value="item.id"
                                            class="rounded text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300" x-text="item.name"></span>
                                    </label>
                                </template>
                                <p x-show="subcatItems.length === 0" class="text-sm text-gray-400 dark:text-gray-500 italic">No items in this category.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (optional)</label>
                        <input type="text" name="notes" id="notes" placeholder="e.g. Meet at checkout lane 5"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                            value="{{ old('notes') }}">
                    </div>

                    @if($errors->any())
                        <div class="text-sm text-red-600 dark:text-red-400">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Create Assignment
                    </button>
                </form>
            </div>

            <!-- Reconciliation Panel -->
            @if(!empty($reconciliation))
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Reconciliation</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Comparing what NINJAs checked off vs what was received at the kiosk.</p>

                @php
                    $hasDiscrepancies = collect($reconciliation)->where('discrepancy', '!=', 0)->count();
                @endphp

                @if($hasDiscrepancies > 0)
                    <div class="mb-3 p-3 rounded bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                        <p class="text-sm text-amber-700 dark:text-amber-300 font-medium">{{ $hasDiscrepancies }} item(s) with discrepancies detected.</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Category</th>
                                <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Item</th>
                                <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Checked Off</th>
                                <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Received</th>
                                <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Discrepancy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reconciliation as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 {{ $row['discrepancy'] != 0 ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-400">{{ $row['category_name'] }}</td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-gray-100">{{ $row['item_name'] }}</td>
                                    <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-300">{{ $row['purchased_qty'] }}</td>
                                    <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-300">{{ $row['received_qty'] }}</td>
                                    <td class="text-right py-2 px-3 font-medium {{ $row['discrepancy'] != 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $row['discrepancy'] > 0 ? '+' . $row['discrepancy'] : $row['discrepancy'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        }

        document.getElementById('sd_school_select')?.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('family_start').value = opt.dataset.start || '';
            document.getElementById('family_end').value = opt.dataset.end || '';
        });
    </script>
</x-app-layout>
