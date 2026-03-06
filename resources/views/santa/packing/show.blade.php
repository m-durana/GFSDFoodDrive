<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Packing List:
                    @if(\App\Models\Setting::get('packing_show_names', '1') === '1')
                        {{ $packingList->family?->family_name }}
                    @else
                        Family #{{ $packingList->family?->family_number }}
                    @endif
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">#{{ $packingList->family?->family_number }}</span>
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('packing.print', $packingList) }}?type=food" target="_blank"
                   class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-sm rounded-md transition">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12Zm-3 0h.008v.008h-.008V12Z" /></svg>
                    Print Food
                </a>
                <a href="{{ route('packing.print', $packingList) }}?type=gift" target="_blank"
                   class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-md transition">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12Zm-3 0h.008v.008h-.008V12Z" /></svg>
                    Print Gift
                </a>
                <a href="{{ route('packing.print', $packingList) }}?type=both" target="_blank"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12Zm-3 0h.008v.008h-.008V12Z" /></svg>
                    Print Both
                </a>
                <a href="{{ route('warehouse.mobile-scan', ['token' => $packingList->qr_token]) }}" target="_blank"
                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition"
                   title="Open mobile packing view (no QR needed)">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                    Mobile
                </a>
                <form method="POST" action="{{ route('packing.refresh', $packingList) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm rounded-md transition"
                        onclick="return confirm('Refresh this packing list? Unpacked items will be regenerated.')">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                        Refresh
                    </button>
                </form>
                @if($packingList->status === \App\Enums\PackingStatus::Complete)
                    <form method="POST" action="{{ route('packing.verify', $packingList) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Verify
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Family info + progress -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Family</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            @if(\App\Models\Setting::get('packing_show_names', '1') === '1')
                                {{ $packingList->family?->family_name }}
                            @else
                                Family #{{ $packingList->family?->family_number }}
                            @endif
                            @if($packingList->family?->is_severe_need)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Severe Need</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Family #{{ $packingList->family?->family_number }} |
                            {{ $packingList->family?->number_of_family_members ?? '?' }} members |
                            {{ $packingList->family?->children?->count() ?? 0 }} children
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                        @php
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300',
                                'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'complete' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'verified' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                            ];
                        @endphp
                        <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$packingList->status->value] ?? $statusColors['pending'] }}">
                            {{ $packingList->status->label() }}
                        </span>
                        @if($packingList->verified_at)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Verified by {{ $packingList->verifier?->first_name }} at {{ $packingList->verified_at->format('M j, g:ia') }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Progress</h3>
                        <div class="mt-2">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-3">
                                    <div class="bg-green-500 h-3 rounded-full transition-all" style="width: {{ $progress['percentage'] }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $progress['percentage'] }}%</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $progress['packed'] }} of {{ $progress['total'] }} items packed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form method="POST" action="{{ route('packing.updateNotes', $packingList) }}" class="flex gap-3">
                    @csrf
                    <input type="text" name="notes" value="{{ $packingList->notes }}" placeholder="Coordinator notes (special requirements, dietary restrictions, etc.)"
                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition">Save Notes</button>
                </form>
            </div>

            <!-- Food Items -->
            @if($foodItems->count())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden" x-data="sortTable()">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Food Items
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ $foodItems->filter(fn($i) => $i->isPacked())->count() }}/{{ $foodItems->count() }} packed)
                            </span>
                        </h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <x-sort-th key="category">Category</x-sort-th>
                                <x-sort-th key="item">Item</x-sort-th>
                                <x-sort-th key="qty">Qty</x-sort-th>
                                <x-sort-th key="status">Status</x-sort-th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($foodItems->sortBy('sort_order') as $item)
                                @include('santa.packing._item-row', ['item' => $item, 'packingList' => $packingList])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Gift Items -->
            @if($giftItems->count())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden" x-data="sortTable()">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Gift Items
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ $giftItems->filter(fn($i) => $i->isPacked())->count() }}/{{ $giftItems->count() }} packed)
                            </span>
                        </h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <x-sort-th key="child">Child</x-sort-th>
                                <x-sort-th key="description">Description</x-sort-th>
                                <x-sort-th key="qty">Qty</x-sort-th>
                                <x-sort-th key="status">Status</x-sort-th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($giftItems->sortBy('sort_order') as $item)
                                <tr class="{{ $item->isPacked() ? 'bg-green-50/50 dark:bg-green-900/10' : ($item->status === \App\Enums\PackingItemStatus::Unfulfilled ? 'bg-red-50/50 dark:bg-red-900/10' : '') }}">
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->child ? ($item->child->gender ?? 'Child') . ' ' . $item->child->age : '' }}">
                                        @if($item->child)
                                            {{ $item->child->gender ?? 'Child' }}, age {{ $item->child->age }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300" data-sort-value="{{ $item->description }}">{{ $item->description }}</td>
                                    <td class="px-4 py-2 text-center text-sm" data-sort-value="{{ $item->quantity_packed }}">
                                        <span class="{{ $item->quantity_packed >= $item->quantity_needed ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $item->quantity_packed }}/{{ $item->quantity_needed }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center" data-sort-value="{{ $item->status->value }}">
                                        @include('santa.packing._status-badge', ['status' => $item->status])
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        @if(!$item->isPacked() && $item->status !== \App\Enums\PackingItemStatus::Unfulfilled)
                                            <form method="POST" action="{{ route('packing.packItem', [$packingList, $item]) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Pack</button>
                                            </form>
                                        @elseif($item->isPacked())
                                            <span class="text-xs text-gray-400">{{ $item->packer?->first_name }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Baby Supplies -->
            @if($babyItems->count())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden" x-data="sortTable()">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Baby Supplies
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ $babyItems->filter(fn($i) => $i->isPacked())->count() }}/{{ $babyItems->count() }} packed)
                            </span>
                        </h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <x-sort-th key="category">Category</x-sort-th>
                                <x-sort-th key="item">Item</x-sort-th>
                                <x-sort-th key="qty">Qty</x-sort-th>
                                <x-sort-th key="status">Status</x-sort-th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($babyItems->sortBy('sort_order') as $item)
                                @include('santa.packing._item-row', ['item' => $item, 'packingList' => $packingList])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex justify-between items-center">
                <a href="{{ route('packing.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">&larr; Back to all lists</a>
                <div class="text-xs text-gray-400 dark:text-gray-500">
                    QR Token: {{ $packingList->qr_token }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
