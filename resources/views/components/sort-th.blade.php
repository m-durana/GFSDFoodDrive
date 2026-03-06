@props(['key', 'class' => ''])
<th data-sort-key="{{ $key }}" @click="sort('{{ $key }}')"
    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200 transition {{ $class }}">
    <span class="inline-flex items-center gap-1">
        {{ $slot }}
        <svg x-show="sortKey === '{{ $key }}'" x-cloak class="h-3 w-3" :class="sortAsc ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
    </span>
</th>
