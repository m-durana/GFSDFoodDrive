<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            PDF Generation — <?php echo e(ucfirst(str_replace('-', ' ', $status['type']))); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span><?php echo e($status['completed']); ?> of <?php echo e($status['total_batches']); ?> batches complete</span>
                        <span><?php echo e($status['total']); ?> total items</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <?php $pct = $status['total_batches'] > 0 ? round(($status['completed'] / $status['total_batches']) * 100) : 0; ?>
                        <div class="bg-red-600 h-3 rounded-full transition-all duration-500" style="width: <?php echo e($pct); ?>%"></div>
                    </div>
                </div>

                <div class="space-y-3">
                    <?php $__currentLoopData = $status['batches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-lg <?php echo e($batch['status'] === 'completed' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700'); ?>">
                            <div class="flex items-center space-x-3">
                                <?php if($batch['status'] === 'completed'): ?>
                                    <span class="text-green-600 dark:text-green-400 font-bold">&#10003;</span>
                                <?php else: ?>
                                    <span class="text-gray-400 animate-pulse">&#9679;</span>
                                <?php endif; ?>
                                <span class="text-sm text-gray-900 dark:text-gray-100">Batch <?php echo e($num); ?></span>
                            </div>
                            <?php if($batch['status'] === 'completed'): ?>
                                <a href="<?php echo e(route('coordinator.pdfDownload', [$batchId, $num])); ?>"
                                   class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                                    Download PDF
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Processing...</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($status['completed'] < $status['total_batches']): ?>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                        Page refreshes automatically every 5 seconds.
                    </p>
                <?php else: ?>
                    <p class="mt-4 text-sm text-green-600 dark:text-green-400 text-center font-medium">
                        All batches complete! Download above.
                    </p>
                <?php endif; ?>

                <div class="mt-6 text-center">
                    <a href="<?php echo e(route('coordinator.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Coordinator Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if($status['completed'] < $status['total_batches']): ?>
        <script>
            setTimeout(function() { window.location.reload(); }, 5000);
        </script>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/coordinator/pdf-status.blade.php ENDPATH**/ ?>