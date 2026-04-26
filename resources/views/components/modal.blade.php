@props(['name', 'title' => null, 'maxWidth' => 'md'])

@php
$maxWidthClass = match($maxWidth) {
    'sm' => 'max-w-sm',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    default => 'max-w-md',
};
@endphp

<div
    x-data="{ show: false }"
    x-on:open-modal-{{ $name }}.window="show = true"
    x-on:close-modal-{{ $name }}.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="modal modal-open"
    {{ $attributes }}
>
    <div class="modal-box {{ $maxWidthClass }}" x-on:click.outside="show = false">
        @if($title)
            <h3 class="font-bold text-lg mb-4">{{ $title }}</h3>
        @endif
        {{ $slot }}
        @isset($actions)
            <div class="modal-action">{{ $actions }}</div>
        @endisset
    </div>
</div>
