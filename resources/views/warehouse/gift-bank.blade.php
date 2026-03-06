<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gift Bank
            </h2>
            <div class="flex gap-2">
                <button onclick="document.getElementById('add-gift-modal').classList.remove('hidden')"
                    class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white rounded-md hover:bg-purple-500 text-xs font-medium transition">
                    Add Gift
                </button>
                <a href="{{ route('warehouse.index') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Back to Warehouse
                </a>
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

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totals['total'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Items</div>
                </div>
                <a href="{{ route('warehouse.gift-bank', ['status' => 'unassigned']) }}" class="bg-yellow-50 dark:bg-yellow-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-yellow-300 transition {{ request('status') === 'unassigned' ? 'ring-2 ring-yellow-500' : '' }}">
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $totals['unassigned'] }}</div>
                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Unassigned</div>
                </a>
                <a href="{{ route('warehouse.gift-bank', ['status' => 'assigned']) }}" class="bg-green-50 dark:bg-green-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-green-300 transition {{ request('status') === 'assigned' ? 'ring-2 ring-green-500' : '' }}">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $totals['assigned'] }}</div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">Assigned</div>
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('warehouse.gift-bank') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="unassigned" {{ request('status') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Age Range</label>
                        <select name="age_range" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="0-5" {{ request('age_range') === '0-5' ? 'selected' : '' }}>0-5</option>
                            <option value="6-12" {{ request('age_range') === '6-12' ? 'selected' : '' }}>6-12</option>
                            <option value="13-17" {{ request('age_range') === '13-17' ? 'selected' : '' }}>13-17</option>
                            <option value="any" {{ request('age_range') === 'any' ? 'selected' : '' }}>Any</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gender</label>
                        <select name="gender" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="neutral" {{ request('gender') === 'neutral' ? 'selected' : '' }}>Neutral</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">Filter</button>
                        <a href="{{ route('warehouse.gift-bank') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium transition">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Gift Bank Items ({{ $items->total() }})
                    </h3>

                    @if($items->count() > 0)
                        <div class="overflow-x-auto" x-data="sortTable()">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <x-sort-th key="description" class="px-3 py-2">Description</x-sort-th>
                                        <x-sort-th key="age_range" class="px-3 py-2">Age Range</x-sort-th>
                                        <x-sort-th key="gender" class="px-3 py-2">Gender</x-sort-th>
                                        <x-sort-th key="type" class="px-3 py-2">Type</x-sort-th>
                                        <x-sort-th key="donor" class="px-3 py-2">Donor</x-sort-th>
                                        <x-sort-th key="qty" class="px-3 py-2">Qty</x-sort-th>
                                        <x-sort-th key="status" class="px-3 py-2">Status</x-sort-th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assigned To</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($items as $item)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[200px] truncate" title="{{ $item->description }}" data-sort-value="{{ $item->description }}">{{ $item->description }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->age_range ?? '' }}">{{ $item->age_range ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->gender_suitability ?? '' }}">{{ ucfirst($item->gender_suitability ?? '—') }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->gift_type ?? '' }}">{{ $item->gift_type ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->donor_name ?? '' }}">{{ $item->donor_name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->quantity }}">{{ $item->quantity }}</td>
                                            <td class="px-3 py-2 text-sm" data-sort-value="{{ $item->assigned_child_id ? 'assigned' : 'available' }}">
                                                @if($item->assigned_child_id)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Assigned</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">Available</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                @if($item->assignedChild)
                                                    <a href="{{ route('warehouse.child.gifts', $item->assignedChild) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        #{{ $item->assignedChild->family->family_number ?? '?' }} — {{ $item->assignedChild->gender }}, {{ $item->assignedChild->age }}
                                                    </a>
                                                @else
                                                    <button type="button" onclick="openAssignModal({{ $item->id }})" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Assign...</button>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                                @if($item->assigned_child_id)
                                                    <form method="POST" action="{{ route('warehouse.gift-bank.unassign', $item) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:underline text-xs">Unassign</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('warehouse.gift-bank.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this gift?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs ml-2">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No items in the Gift Bank yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Gift Modal -->
    <div id="add-gift-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg mx-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Gift to Bank</h3>
            <form method="POST" action="{{ route('warehouse.gift-bank.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" required placeholder="e.g., Lego set, Board game, Stuffed animal..."
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Age Range</label>
                            <select name="age_range" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                <option value="any">Any age</option>
                                <option value="0-5">0-5</option>
                                <option value="6-12">6-12</option>
                                <option value="13-17">13-17</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gender Suitability</label>
                            <select name="gender_suitability" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                <option value="neutral">Neutral</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gift Type</label>
                            <input type="text" name="gift_type" placeholder="e.g., Toy, Book, Clothing..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Donor Name (optional)</label>
                        <input type="text" name="donor_name" placeholder="Anonymous"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Notes (optional)</label>
                        <textarea name="notes" rows="2" placeholder="Any additional notes..."
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" onclick="document.getElementById('add-gift-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-500 text-sm font-medium">Add Gift</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Modal -->
    <div id="assign-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Assign Gift to Child</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Search by Family #</label>
                    <input type="text" id="assign-search" placeholder="Type family number..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm"
                        oninput="searchChildren(this.value)">
                </div>
                <div id="assign-results" class="space-y-1.5 max-h-[40vh] overflow-y-auto">
                    <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">Type a family number to search</p>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeAssignModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let assignItemId = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function openAssignModal(itemId) {
            assignItemId = itemId;
            document.getElementById('assign-modal').classList.remove('hidden');
            document.getElementById('assign-search').value = '';
            document.getElementById('assign-results').innerHTML =
                '<p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">Type a family number to search</p>';
            document.getElementById('assign-search').focus();
        }

        function closeAssignModal() {
            document.getElementById('assign-modal').classList.add('hidden');
            assignItemId = null;
        }

        // Simple client-side children data for assign search
        const childrenData = @json($childrenForAssign);

        function searchChildren(query) {
            const results = document.getElementById('assign-results');
            if (!query.trim()) {
                results.innerHTML = '<p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">Type a family number to search</p>';
                return;
            }
            const q = query.toLowerCase();
            const matches = childrenData.filter(c =>
                String(c.family_number).includes(q) || c.family_name.toLowerCase().includes(q)
            ).slice(0, 15);

            if (matches.length === 0) {
                results.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">No matches found</p>';
                return;
            }

            results.innerHTML = matches.map(c => `
                <form method="POST" action="/warehouse/gift-bank/${assignItemId}/assign/${c.id}" class="inline w-full">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-900 dark:text-gray-100">#${c.family_number}</span>
                            <span class="text-gray-500 dark:text-gray-400">${c.gender}, age ${c.age}</span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 truncate">${c.family_name}</div>
                    </button>
                </form>
            `).join('');
        }
    </script>
</x-app-layout>
