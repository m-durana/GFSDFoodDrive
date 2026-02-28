<?php if($transactions->isEmpty()): ?>
    <p class="text-sm text-gray-500 dark:text-gray-400">No transactions recorded yet.</p>
<?php else: ?>
    <div class="space-y-2">
        <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 text-sm">
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        <?php echo e($txn->transaction_type->color() === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ''); ?>

                        <?php echo e($txn->transaction_type->color() === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : ''); ?>

                        <?php echo e($txn->transaction_type->color() === 'blue' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : ''); ?>

                        <?php echo e($txn->transaction_type->color() === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : ''); ?>

                    "><?php echo e($txn->transaction_type->label()); ?></span>
                    <span class="text-gray-900 dark:text-gray-100">
                        <?php echo e($txn->quantity); ?>x <?php echo e($txn->category->name); ?>

                        <?php if($txn->item): ?> (<?php echo e($txn->item->name); ?>) <?php endif; ?>
                    </span>
                    <?php if($txn->source): ?>
                        <span class="text-gray-400 dark:text-gray-500">via <?php echo e($txn->source); ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                    <?php echo e($txn->scanned_at?->diffForHumans() ?? $txn->created_at->diffForHumans()); ?>

                    <?php if($txn->scanner): ?> &middot; <?php echo e($txn->scanner->first_name); ?> <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/warehouse/_feed.blade.php ENDPATH**/ ?>