<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['key', 'text']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['key', 'text']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php if(\App\Models\Setting::get('hints_enabled', '1') === '1'): ?>
<span class="hint-bubble" data-hint-key="<?php echo e($key); ?>">
    <span class="hint-icon" tabindex="0">?</span>
    <span class="hint-popup">
        <span class="hint-dismiss" onclick="dismissHint('<?php echo e($key); ?>', this)">&times; dismiss</span>
        <?php echo e($text); ?>

    </span>
</span>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/components/hint.blade.php ENDPATH**/ ?>