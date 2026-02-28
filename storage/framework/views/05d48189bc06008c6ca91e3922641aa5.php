<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['gender' => '', 'size' => 'md']));

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

foreach (array_filter((['gender' => '', 'size' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $g = strtolower($gender ?? '');
    $sizes = [
        'sm' => ['outer' => 'w-10 h-10', 'icon' => 'w-5 h-5'],
        'md' => ['outer' => 'w-12 h-12', 'icon' => 'w-6 h-6'],
        'lg' => ['outer' => 'w-14 h-14', 'icon' => 'w-7 h-7'],
    ];
    $s = $sizes[$size] ?? $sizes['md'];

    if ($g === 'male') {
        $bgClass = 'bg-blue-100 dark:bg-blue-900/30';
        $iconClass = 'text-blue-500';
    } elseif ($g === 'female') {
        $bgClass = 'bg-pink-100 dark:bg-pink-900/30';
        $iconClass = 'text-pink-500';
    } else {
        $bgClass = 'bg-purple-100 dark:bg-purple-900/30';
        $iconClass = 'text-purple-500';
    }
?>

<div <?php echo e($attributes->merge(['class' => "rounded-full {$bgClass} flex items-center justify-center {$s['outer']}"])); ?>>
    <?php if($g === 'male'): ?>
        
        <svg class="<?php echo e($s['icon']); ?> <?php echo e($iconClass); ?>" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
    <?php elseif($g === 'female'): ?>
        
        <svg class="<?php echo e($s['icon']); ?> <?php echo e($iconClass); ?>" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
    <?php else: ?>
        
        <svg class="<?php echo e($s['icon']); ?> <?php echo e($iconClass); ?>" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
        </svg>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/components/gender-icon.blade.php ENDPATH**/ ?>