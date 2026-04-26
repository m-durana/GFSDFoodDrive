@props(['type' => 'submit', 'size' => 'md'])

@php
$sizeClass = match($size) {
    'xs' => 'btn-xs',
    'sm' => 'btn-sm',
    'lg' => 'btn-lg',
    default => '',
};
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => "btn btn-error $sizeClass"]) }}>
    {{ $slot }}
</button>
