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
            Gift Tracking Overview
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($counts['total']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Children</div>
                </div>
                <a href="<?php echo e(route('santa.gifts', ['level' => 0])); ?>" class="bg-red-50 dark:bg-red-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-red-300 transition <?php echo e(request('level') === '0' ? 'ring-2 ring-red-500' : ''); ?>">
                    <div class="text-2xl font-bold text-red-700 dark:text-red-400"><?php echo e($counts['no_gifts']); ?></div>
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">No Gifts</div>
                </a>
                <a href="<?php echo e(route('santa.gifts', ['level' => 1])); ?>" class="bg-yellow-50 dark:bg-yellow-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-yellow-300 transition <?php echo e(in_array(request('level'), ['1', '2']) ? 'ring-2 ring-yellow-500' : ''); ?>">
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400"><?php echo e($counts['partial']); ?></div>
                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">In Progress</div>
                </a>
                <a href="<?php echo e(route('santa.gifts', ['level' => 3])); ?>" class="bg-green-50 dark:bg-green-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-green-300 transition <?php echo e(request('level') === '3' ? 'ring-2 ring-green-500' : ''); ?>">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400"><?php echo e($counts['complete']); ?></div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">Fully Gifted</div>
                </a>
                <a href="<?php echo e(route('santa.gifts', ['merged' => '0'])); ?>" class="bg-blue-50 dark:bg-blue-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-blue-300 transition <?php echo e(request('merged') === '0' ? 'ring-2 ring-blue-500' : ''); ?>">
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-400"><?php echo e($counts['unmerged']); ?></div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">Unprinted Tags</div>
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="<?php echo e(route('santa.gifts')); ?>" class="flex flex-wrap items-center gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gift Level</label>
                        <select name="level" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All Levels</option>
                            <?php $__currentLoopData = \App\Enums\GiftLevel::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($gl->value); ?>" <?php echo e(request('level') == (string)$gl->value ? 'selected' : ''); ?>><?php echo e($gl->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Tag Status</label>
                        <select name="merged" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="0" <?php echo e(request('merged') === '0' ? 'selected' : ''); ?>>Unprinted</option>
                            <option value="1" <?php echo e(request('merged') === '1' ? 'selected' : ''); ?>>Printed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Adopted</label>
                        <select name="adopted" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="0" <?php echo e(request('adopted') === '0' ? 'selected' : ''); ?>>Not Adopted</option>
                            <option value="1" <?php echo e(request('adopted') === '1' ? 'selected' : ''); ?>>Adopted</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Filter
                        </button>
                        <a href="<?php echo e(route('santa.gifts')); ?>" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium transition">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Children Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Children (<?php echo e($children->count()); ?> <?php echo e(request()->hasAny(['level', 'merged', 'adopted']) ? 'filtered' : 'total'); ?>)
                    </h3>

                    <?php if($children->count() > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family #</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gender</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Age</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">School</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gift Level</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gifts Received</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopter</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tag</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Where</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="<?php echo e($child->family->family_done ? 'bg-green-50/50 dark:bg-green-900/10' : ''); ?>">
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                <?php echo e($child->family->family_number ?? '—'); ?>

                                            </td>
                                            <td class="px-3 py-2 text-sm">
                                                <a href="<?php echo e(route('family.show', $child->family)); ?>" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    <?php echo e($child->family->family_name); ?>

                                                </a>
                                                <?php if($child->family->family_done): ?>
                                                    <span class="ml-1 text-green-600 dark:text-green-400 text-xs">&check;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->gender); ?></td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->age); ?></td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->school ?? '—'); ?></td>
                                            <td class="px-3 py-2 text-sm">
                                                <?php $level = $child->gift_level ?? \App\Enums\GiftLevel::None; ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    <?php echo e($level->color() === 'red' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : ''); ?>

                                                    <?php echo e($level->color() === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : ''); ?>

                                                    <?php echo e($level->color() === 'green' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : ''); ?>

                                                "><?php echo e($level->label()); ?></span>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[150px] truncate" title="<?php echo e($child->gifts_received); ?>">
                                                <?php echo e($child->gifts_received ?? '—'); ?>

                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                <?php if($child->adopter_name): ?>
                                                    <span title="<?php echo e($child->adopter_contact_info); ?>"><?php echo e($child->adopter_name); ?></span>
                                                <?php else: ?>
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2 text-sm">
                                                <?php if($child->mail_merged): ?>
                                                    <span class="text-green-600 dark:text-green-400">Printed</span>
                                                <?php else: ?>
                                                    <span class="text-gray-400 dark:text-gray-500">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->where_is_tag ?? '—'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400">No children match the selected filters.</p>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/gifts.blade.php ENDPATH**/ ?>