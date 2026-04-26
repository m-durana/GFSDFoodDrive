@props(['name' => null, 'label' => null, 'error' => null, 'rows' => 3])

<label class="form-control w-full">
    @if($label)
        <span class="label-text mb-1">{{ $label }}</span>
    @endif
    <textarea
        @if($name) name="{{ $name }}" id="{{ $name }}" @endif
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full' . ($error ? ' textarea-error' : '')]) }}
    >{{ $slot }}</textarea>
    @if($error)
        <span class="label-text-alt text-error mt-1">{{ $error }}</span>
    @endif
</label>
