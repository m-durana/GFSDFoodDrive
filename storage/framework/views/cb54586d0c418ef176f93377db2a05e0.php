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
            Adopt-a-Tag Dashboard
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($stats['available']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Available</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-yellow-200 dark:border-yellow-700 p-4 text-center">
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo e($stats['adopted']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Adopted (pending)</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-green-200 dark:border-green-700 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($stats['dropped_off']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dropped Off</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-700 p-4 text-center">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo e($stats['overdue']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Overdue</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <form method="GET" action="<?php echo e(route('santa.adoptions')); ?>" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" onchange="this.form.submit()"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            <option value="adopted" <?php echo e($status === 'adopted' ? 'selected' : ''); ?>>Adopted (pending drop-off)</option>
                            <option value="dropped_off" <?php echo e($status === 'dropped_off' ? 'selected' : ''); ?>>Dropped Off</option>
                            <option value="overdue" <?php echo e($status === 'overdue' ? 'selected' : ''); ?>>Overdue</option>
                            <option value="available" <?php echo e($status === 'available' ? 'selected' : ''); ?>>Available</option>
                            <option value="all" <?php echo e($status === 'all' ? 'selected' : ''); ?>>All</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Child</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopter</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopted</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Deadline</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__empty_1 = true; $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-sm font-mono">
                                        <?php if($child->family): ?>
                                            <?php echo e($child->family->family_number); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo e($child->gender); ?>, <?php echo e($child->age); ?>

                                        <?php if($child->school): ?>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(<?php echo e($child->school); ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?php echo e($child->adopter_name ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo e($child->adopter_contact_info ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo e($child->adopted_at ? $child->adopted_at->format('M j') : '—'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php if($child->adoption_deadline): ?>
                                            <span class="<?php echo e($child->adoption_deadline->isPast() && !$child->gift_dropped_off ? 'text-red-600 dark:text-red-400 font-semibold' : ''); ?>">
                                                <?php echo e($child->adoption_deadline->format('M j')); ?>

                                            </span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php if($child->gift_dropped_off): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                Dropped off
                                            </span>
                                        <?php elseif($child->isAdopted() && $child->adoption_deadline?->isPast()): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                Overdue
                                            </span>
                                        <?php elseif($child->isAdopted()): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                                Adopted
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                Available
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <?php if($child->isAdopted()): ?>
                                            <div class="flex justify-end space-x-2">
                                                <form method="POST" action="<?php echo e(route('santa.releaseAdoption', $child)); ?>" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                                            onclick="return confirm('Release this tag back to the pool? The adopter will lose their claim.')">
                                                        Release
                                                    </button>
                                                </form>
                                                <?php if($child->gift_level?->value < 3): ?>
                                                    <form method="POST" action="<?php echo e(route('santa.completeAdoption', $child)); ?>" class="inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:underline"
                                                                onclick="return confirm('Mark as complete? Gift level will be set to Full.')">
                                                            Complete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No children match this filter.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('santa.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($children->count()); ?> results</p>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/adoptions.blade.php ENDPATH**/ ?>