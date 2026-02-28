@php
    $status = $family->delivery_status?->value ?? 'pending';
    $isDone = in_array($status, ['delivered', 'picked_up']);
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'in_transit' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'picked_up' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    ];
@endphp
<div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 {{ $isDone ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}"
     data-family-id="{{ $family->id }}">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <!-- Family info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                @if($family->route_order)
                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-bold {{ $isDone ? 'bg-green-500 text-white' : 'bg-red-700 text-white' }}">
                        {{ $isDone ? '&#10003;' : $family->route_order }}
                    </span>
                @endif
                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">#{{ $family->family_number }}</span>
                <a href="{{ route('family.show', $family) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">{{ $family->family_name }}</a>
                <span class="status-badge inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$status] ?? '' }}">
                    {{ $family->delivery_status?->label() ?? 'Pending' }}
                </span>
                @if($family->deliveryRoute)
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $family->deliveryRoute->name }}</span>
                @endif
            </div>
            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                <div>{{ $family->address }}</div>
                <div>{{ $family->phone1 }}@if($family->phone2) / {{ $family->phone2 }}@endif</div>
                @if($family->delivery_reason)
                    <div class="text-red-600 dark:text-red-400">{{ $family->delivery_reason }}</div>
                @endif
                @if($family->pet_information)
                    <div class="text-amber-600 dark:text-amber-400">Pets: {{ $family->pet_information }}</div>
                @endif
                @if($family->preferred_language && $family->preferred_language !== 'English')
                    <div class="text-blue-600 dark:text-blue-400">{{ $family->preferred_language }}</div>
                @endif
            </div>
            @if($family->deliveryLogs && $family->deliveryLogs->count() > 0)
                <div class="mt-1.5 space-y-0.5">
                    @foreach($family->deliveryLogs->take(3) as $log)
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            {{ $log->created_at->format('M j g:ia') }}
                            — {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                            @if($log->user) by {{ $log->user->first_name }}@endif
                            @if($log->notes) — {{ $log->notes }}@endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Status action -->
        <div class="shrink-0">
            <select onchange="updateStatusAjax({{ $family->id }}, this)"
                class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs pl-2 pr-6 py-1">
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_transit" {{ $status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="picked_up" {{ $status === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
            </select>
        </div>
    </div>
</div>
