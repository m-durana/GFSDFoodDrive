@props(['type' => 'text', 'name' => null, 'label' => null, 'error' => null])

<label class="form-control w-full">
    @if($label)
        <span class="label-text mb-1">{{ $label }}</span>
    @endif
    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" id="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($error ? ' input-error' : '')]) }}
    />
    @if($error)
        <span class="label-text-alt text-error mt-1">{{ $error }}</span>
    @endif
</label>
