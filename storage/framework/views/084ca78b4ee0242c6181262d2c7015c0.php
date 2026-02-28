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
            Season <?php echo e($season->year); ?> Details
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Stats -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Overview</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($season->total_families); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Families</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($season->total_children); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Children</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($season->total_family_members); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total People</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($season->tags_adopted); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Tags Adopted</div>
                    </div>
                </div>
            </div>

            <!-- Gift Levels -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Gift Levels</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-red-700 dark:text-red-400"><?php echo e($season->gifts_level_0); ?></div>
                        <div class="text-xs text-red-600 dark:text-red-400">No Gifts</div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400"><?php echo e($season->gifts_level_1); ?></div>
                        <div class="text-xs text-yellow-600 dark:text-yellow-400">Partial</div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400"><?php echo e($season->gifts_level_2); ?></div>
                        <div class="text-xs text-yellow-600 dark:text-yellow-400">Moderate</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-400"><?php echo e($season->gifts_level_3); ?></div>
                        <div class="text-xs text-green-600 dark:text-green-400">Fully Gifted</div>
                    </div>
                </div>
            </div>

            <!-- Delivery Breakdown -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Delivery</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($season->deliveries_completed); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Deliveries Completed</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($season->pickups_completed); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Pickups Completed</div>
                    </div>
                </div>
            </div>

            <?php if($season->notes): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Notes</h3>
                    <p class="text-gray-700 dark:text-gray-300"><?php echo e($season->notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($season->archived_at): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Archived on <?php echo e($season->archived_at->format('F j, Y \a\t g:i A')); ?>

                </p>
            <?php endif; ?>

            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('santa.seasons.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Season History
                </a>
                <a href="<?php echo e(route('santa.seasons.families', $season)); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">
                    Browse Families
                </a>
            </div>
        </div>
    </div>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/seasons/show.blade.php ENDPATH**/ ?>