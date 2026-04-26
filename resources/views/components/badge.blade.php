@props(['type' => 'neutral', 'size' => 'md'])

@php
$typeClass = match($type) {
    'primary' => 'badge-primary',
    'secondary' => 'badge-secondary',
    'accent' => 'badge-accent',
    'success' => 'badge-success',
    'warning' => 'badge-warning',
    'error', 'danger' => 'badge-error',
    'info' => 'badge-info',
    'ghost' => 'badge-ghost',
    'outline' => 'badge-outline',
    default => '',
};
$sizeClass = match($size) {
    'xs' => 'badge-xs',
    'sm' => 'badge-sm',
    'lg' => 'badge-lg',
    default => '',
};
@endphp

<span {{ $attributes->merge(['class' => "badge $typeClass $sizeClass"]) }}>
    {{ $slot }}
</span>
