@props(['type' => 'info'])

@php
$typeClass = match($type) {
    'success' => 'alert-success',
    'warning' => 'alert-warning',
    'error', 'danger' => 'alert-error',
    default => 'alert-info',
};
@endphp

<div role="alert" {{ $attributes->merge(['class' => "alert $typeClass"]) }}>
    {{ $slot }}
</div>
