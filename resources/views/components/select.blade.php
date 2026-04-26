@props(['name' => null, 'label' => null, 'error' => null])

<label class="form-control w-full">
    @if($label)
        <span class="label-text mb-1">{{ $label }}</span>
    @endif
    <select
        @if($name) name="{{ $name }}" id="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($error ? ' select-error' : '')]) }}
    >
        {{ $slot }}
    </select>
    @if($error)
        <span class="label-text-alt text-error mt-1">{{ $error }}</span>
    @endif
</label>
