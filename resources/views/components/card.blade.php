@props(['title' => null, 'compact' => false])

<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-sm border border-base-300' . ($compact ? ' card-compact' : '')]) }}>
    <div class="card-body">
        @if($title)
            <h2 class="card-title">{{ $title }}</h2>
        @endif
        {{ $slot }}
        @isset($actions)
            <div class="card-actions justify-end mt-2">{{ $actions }}</div>
        @endisset
    </div>
</div>
