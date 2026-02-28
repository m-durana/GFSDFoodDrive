<?php
    $status = $family->delivery_status?->value ?? 'pending';
    $isDone = in_array($status, ['delivered', 'picked_up']);
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'in_transit' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'picked_up' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    ];
?>
<div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 <?php echo e($isDone ? 'bg-green-50/50 dark:bg-green-900/10' : ''); ?>"
     data-family-id="<?php echo e($family->id); ?>">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <!-- Family info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <?php if($family->route_order): ?>
                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-bold <?php echo e($isDone ? 'bg-green-500 text-white' : 'bg-red-700 text-white'); ?>">
                        <?php echo e($isDone ? '&#10003;' : $family->route_order); ?>

                    </span>
                <?php endif; ?>
                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">#<?php echo e($family->family_number); ?></span>
                <a href="<?php echo e(route('family.show', $family)); ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium"><?php echo e($family->family_name); ?></a>
                <span class="status-badge inline-flex px-2 py-0.5 text-xs font-medium rounded-full <?php echo e($statusColors[$status] ?? ''); ?>">
                    <?php echo e($family->delivery_status?->label() ?? 'Pending'); ?>

                </span>
                <?php if($family->deliveryRoute): ?>
                    <span class="text-xs text-gray-400 dark:text-gray-500"><?php echo e($family->deliveryRoute->name); ?></span>
                <?php endif; ?>
            </div>
            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                <div><?php echo e($family->address); ?></div>
                <div><?php echo e($family->phone1); ?><?php if($family->phone2): ?> / <?php echo e($family->phone2); ?><?php endif; ?></div>
                <?php if($family->delivery_reason): ?>
                    <div class="text-red-600 dark:text-red-400"><?php echo e($family->delivery_reason); ?></div>
                <?php endif; ?>
                <?php if($family->pet_information): ?>
                    <div class="text-amber-600 dark:text-amber-400">Pets: <?php echo e($family->pet_information); ?></div>
                <?php endif; ?>
                <?php if($family->preferred_language && $family->preferred_language !== 'English'): ?>
                    <div class="text-blue-600 dark:text-blue-400"><?php echo e($family->preferred_language); ?></div>
                <?php endif; ?>
            </div>
            <?php if($family->deliveryLogs && $family->deliveryLogs->count() > 0): ?>
                <div class="mt-1.5 space-y-0.5">
                    <?php $__currentLoopData = $family->deliveryLogs->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            <?php echo e($log->created_at->format('M j g:ia')); ?>

                            — <?php echo e(ucfirst(str_replace('_', ' ', $log->status))); ?>

                            <?php if($log->user): ?> by <?php echo e($log->user->first_name); ?><?php endif; ?>
                            <?php if($log->notes): ?> — <?php echo e($log->notes); ?><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status action -->
        <div class="shrink-0">
            <select onchange="updateStatusAjax(<?php echo e($family->id); ?>, this)"
                class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs pl-2 pr-6 py-1">
                <option value="pending" <?php echo e($status === 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="in_transit" <?php echo e($status === 'in_transit' ? 'selected' : ''); ?>>In Transit</option>
                <option value="delivered" <?php echo e($status === 'delivered' ? 'selected' : ''); ?>>Delivered</option>
                <option value="picked_up" <?php echo e($status === 'picked_up' ? 'selected' : ''); ?>>Picked Up</option>
            </select>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/delivery-day/_family-card.blade.php ENDPATH**/ ?>