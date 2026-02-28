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
            Reports & Statistics
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Top-level stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($totalFamilies); ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($totalChildren); ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Children</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400"><?php echo e($familiesDone); ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Families Complete</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?php echo e($assignedFamilies); ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Numbers Assigned</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Gift Level Breakdown -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Gift Levels</h3>
                    <div class="space-y-3">
                        <?php
                            $giftTotal = max(array_sum($giftLevels), 1);
                        ?>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-red-600 dark:text-red-400 font-medium">No Gifts</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($giftLevels['none']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-red-500 h-2.5 rounded-full" style="width: <?php echo e(($giftLevels['none'] / $giftTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-yellow-600 dark:text-yellow-400 font-medium">Partial</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($giftLevels['partial']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-yellow-500 h-2.5 rounded-full" style="width: <?php echo e(($giftLevels['partial'] / $giftTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-yellow-600 dark:text-yellow-400 font-medium">Moderate</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($giftLevels['moderate']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: <?php echo e(($giftLevels['moderate'] / $giftTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-green-600 dark:text-green-400 font-medium">Fully Gifted</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($giftLevels['full']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: <?php echo e(($giftLevels['full'] / $giftTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php if($totalChildren > 0): ?>
                        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            <?php echo e(round(($giftLevels['full'] / $totalChildren) * 100)); ?>% fully gifted
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Delivery Status -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Delivery Status</h3>
                    <div class="space-y-3">
                        <?php
                            $deliveryTotal = max(array_sum($deliveryStats), 1);
                        ?>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Pending</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($deliveryStats['pending']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-gray-400 h-2.5 rounded-full" style="width: <?php echo e(($deliveryStats['pending'] / $deliveryTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-blue-600 dark:text-blue-400 font-medium">In Transit</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($deliveryStats['in_transit']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?php echo e(($deliveryStats['in_transit'] / $deliveryTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-green-600 dark:text-green-400 font-medium">Delivered</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($deliveryStats['delivered']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: <?php echo e(($deliveryStats['delivered'] / $deliveryTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-purple-600 dark:text-purple-400 font-medium">Picked Up</span>
                                <span class="text-gray-600 dark:text-gray-300"><?php echo e($deliveryStats['picked_up']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-purple-500 h-2.5 rounded-full" style="width: <?php echo e(($deliveryStats['picked_up'] / $deliveryTotal) * 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php if($totalFamilies > 0): ?>
                        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            <?php echo e(round((($deliveryStats['delivered'] + $deliveryStats['picked_up']) / $totalFamilies) * 100)); ?>% delivery complete
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Children by Age Group -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Children by Age Group</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Infants (0-2)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e($ageGroups['infants']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Young Children (3-7)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e($ageGroups['young_children']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Children (8-12)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e($ageGroups['children']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Tweens (13-14)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e($ageGroups['tweens']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Teenagers (15-17)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e($ageGroups['teenagers']); ?></td>
                            </tr>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">Total</td>
                                <td class="py-2 text-right font-bold text-gray-900 dark:text-gray-100"><?php echo e(array_sum($ageGroups)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Children by School -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Children by School</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__currentLoopData = $childrenBySchool; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="py-2 text-gray-600 dark:text-gray-300"><?php echo e($row->school); ?></td>
                                    <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e($row->total); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tag & Adopter Stats -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tags & Adopters</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($tagStats['merged']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tags Printed</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo e($tagStats['unmerged']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Unprinted</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo e($tagStats['adopted']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Adopted</div>
                        </div>
                    </div>
                </div>

                <!-- Special Needs -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Special Needs</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400"><?php echo e($needsStats['baby_supplies']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Need Baby Supplies</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo e($needsStats['severe_need']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Severe Need</div>
                        </div>
                    </div>

                    <?php if($languages->count() > 0): ?>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-6 mb-2">Language Breakdown</h4>
                        <div class="space-y-1">
                            <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300"><?php echo e($lang->preferred_language); ?></span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($lang->total); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <a href="<?php echo e(route('santa.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/reports.blade.php ENDPATH**/ ?>