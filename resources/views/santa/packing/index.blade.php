<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Packing Lists
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('packing.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    Dashboard
                </a>
                <form method="POST" action="{{ route('packing.generate') }}">
                    @csrf
                    <input type="hidden" name="status_filter" value="{{ $statusFilter ?? 'all' }}">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm transition"
                        onclick="return confirm('Generate packing lists for all families in the current season?')">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Generate All
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if(($counts['complete'] ?? 0) > 0)
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                        </span>
                        <span class="text-sm font-medium text-blue-700 dark:text-blue-300">{{ $counts['complete'] }} packing list(s) ready for QA verification</span>
                    </div>
                    <a href="{{ route('packing.index', ['status' => 'complete']) }}" class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">
                        Review Now
                    </a>
                </div>
            @endif

            <!-- Status filter tabs -->
            <div class="mb-6 flex flex-wrap gap-2">
                @php
                    $tabs = [
                        'all' => 'All',
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'complete' => 'Complete',
                        'verified' => 'Verified',
                        'unfulfilled' => 'Unfulfilled',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('packing.index', ['status' => $key]) }}"
                       class="px-4 py-2 text-sm font-medium rounded-full transition
                           {{ ($statusFilter ?? 'all') === $key
                               ? 'bg-red-600 text-white'
                               : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                        <span class="ml-1 text-xs opacity-75">({{ $counts[$key] ?? 0 }})</span>
                    </a>
                @endforeach
            </div>

            <!-- Search -->
            <form method="GET" action="{{ route('packing.index') }}" class="mb-4">
                <input type="hidden" name="status" value="{{ $statusFilter ?? 'all' }}">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by family name or number..."
                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition">Search</button>
                </div>
            </form>

            <!-- Packing lists table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                 x-data="Object.assign(sortTable(), { selectedIds: [], toggleAll(ids) { if (this.selectedIds.length === ids.length) { this.selectedIds = []; } else { this.selectedIds = [...ids]; } } })">

                <!-- Batch action bar -->
                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3 bg-gray-50 dark:bg-gray-700/50"
                     x-show="selectedIds.length > 0" x-cloak>
                    <span class="text-sm text-gray-600 dark:text-gray-300" x-text="selectedIds.length + ' selected'"></span>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-sm rounded-md transition">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12Zm-3 0h.008v.008h-.008V12Z" /></svg>
                            Print Selected
                            <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                             class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg z-10">
                            <form method="POST" action="{{ route('packing.printBatch') }}" target="_blank" x-ref="batchFormFood">
                                @csrf
                                <template x-for="id in selectedIds" :key="id">
                                    <input type="hidden" name="list_ids[]" :value="id">
                                </template>
                                <input type="hidden" name="type" value="food">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Print Food</button>
                            </form>
                            <form method="POST" action="{{ route('packing.printBatch') }}" target="_blank">
                                @csrf
                                <template x-for="id in selectedIds" :key="id">
                                    <input type="hidden" name="list_ids[]" :value="id">
                                </template>
                                <input type="hidden" name="type" value="gift">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Print Gift</button>
                            </form>
                            <form method="POST" action="{{ route('packing.printBatch') }}" target="_blank">
                                @csrf
                                <template x-for="id in selectedIds" :key="id">
                                    <input type="hidden" name="list_ids[]" :value="id">
                                </template>
                                <input type="hidden" name="type" value="both">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Print Both</button>
                            </form>
                        </div>
                    </div>
                    <button @click="selectedIds = []" class="text-xs text-gray-500 dark:text-gray-400 hover:underline">Clear</button>
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 w-8">
                                @php $allIds = $packingLists->pluck('id')->toArray(); @endphp
                                <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-red-600"
                                    :checked="selectedIds.length === {{ count($allIds) }} && {{ count($allIds) }} > 0"
                                    @change="toggleAll({{ json_encode($allIds) }})">
                            </th>
                            <x-sort-th key="family" class="!px-4 !py-3">Family</x-sort-th>
                            <x-sort-th key="status" class="!px-4 !py-3">Status</x-sort-th>
                            <x-sort-th key="progress" class="!px-4 !py-3">Progress</x-sort-th>
                            <x-sort-th key="volunteer" class="!px-4 !py-3">Volunteer</x-sort-th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($packingLists as $list)
                            @php
                                $progress = $list->progressSummary();
                                $statusColors = [
                                    'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'complete' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'verified' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                :class="selectedIds.includes({{ $list->id }}) ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''">
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-red-600"
                                        :checked="selectedIds.includes({{ $list->id }})"
                                        @change="selectedIds.includes({{ $list->id }}) ? selectedIds = selectedIds.filter(i => i !== {{ $list->id }}) : selectedIds.push({{ $list->id }})">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if(\App\Models\Setting::get('packing_show_names', '1') === '1')
                                            {{ $list->family?->family_name ?? 'Unknown' }}
                                        @else
                                            Family #{{ $list->family?->family_number ?? '—' }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        #{{ $list->family?->family_number ?? '—' }}
                                        @if($list->family?->is_severe_need)
                                            <span class="ml-1 text-red-500 font-semibold">Severe Need</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$list->status->value] ?? $statusColors['pending'] }}">
                                        {{ $list->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $progress['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $progress['packed'] }}/{{ $progress['total'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $list->volunteer?->first_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('packing.show', $list) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                    <a href="{{ route('packing.print', $list) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline" target="_blank">Print</a>
                                    @if($list->status === \App\Enums\PackingStatus::Complete)
                                        <form method="POST" action="{{ route('packing.verify', $list) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm text-green-600 dark:text-green-400 hover:underline">Verify</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No packing lists found. Click "Generate All" to create packing lists for all families.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $packingLists->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
