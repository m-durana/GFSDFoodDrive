<tr class="{{ $item->isPacked() ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}">
    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400" data-sort-value="{{ $item->category?->name ?? '' }}">{{ $item->category?->name ?? '—' }}</td>
    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100" data-sort-value="{{ $item->description }}">
        {{ $item->description }}
        @if($item->warehouseItem && $item->warehouseItem->locationLabel() !== 'Unassigned')
            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">{{ $item->warehouseItem->locationLabel() }}</span>
        @endif
    </td>
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
