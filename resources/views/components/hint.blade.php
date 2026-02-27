@props(['key', 'text'])
@if(\App\Models\Setting::get('hints_enabled', '1') === '1')
<span class="hint-bubble" data-hint-key="{{ $key }}">
    <span class="hint-icon" tabindex="0">?</span>
    <span class="hint-popup">
        <span class="hint-dismiss" onclick="dismissHint('{{ $key }}', this)">&times; dismiss</span>
        {{ $text }}
    </span>
</span>
@endif
