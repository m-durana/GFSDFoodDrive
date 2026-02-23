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
            Potential Duplicate Families
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(count($pairs) === 0): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No potential duplicates found.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">All families appear to be unique entries.</p>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Found <strong><?php echo e(count($pairs)); ?></strong> potential duplicate <?php echo e(Str::plural('pair', count($pairs))); ?>.
                        Review each pair and take action.
                    </p>
                </div>

                <?php $__currentLoopData = $pairs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-700">
                            <span class="text-sm font-medium text-yellow-700 dark:text-yellow-300">
                                Match Score: <?php echo e($pair['score']); ?>

                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">
                            <?php $__currentLoopData = ['family_a', 'family_b']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $family = $pair[$key]; ?>
                                <div class="p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            <?php echo e($family->family_name); ?>

                                            <?php if($family->family_number): ?>
                                                <span class="text-sm font-normal text-gray-500">#<?php echo e($family->family_number); ?></span>
                                            <?php endif; ?>
                                        </h3>
                                        <a href="<?php echo e(route('family.show', $family)); ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                    </div>

                                    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                        <?php if($family->address): ?>
                                            <div><span class="font-medium">Address:</span> <?php echo e($family->address); ?></div>
                                        <?php endif; ?>
                                        <?php if($family->phone1): ?>
                                            <div><span class="font-medium">Phone:</span> <?php echo e($family->phone1); ?></div>
                                        <?php endif; ?>
                                        <?php if($family->phone2): ?>
                                            <div><span class="font-medium">Alt Phone:</span> <?php echo e($family->phone2); ?></div>
                                        <?php endif; ?>
                                        <?php if($family->email): ?>
                                            <div><span class="font-medium">Email:</span> <?php echo e($family->email); ?></div>
                                        <?php endif; ?>
                                        <div><span class="font-medium">Members:</span> <?php echo e($family->number_of_family_members); ?> (<?php echo e($family->children->count()); ?> children)</div>
                                    </div>

                                    <?php if($family->children->count() > 0): ?>
                                        <div class="mt-2">
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Children:</span>
                                            <ul class="mt-1 space-y-0.5">
                                                <?php $__currentLoopData = $family->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="text-xs text-gray-600 dark:text-gray-300">
                                                        <?php echo e($child->gender); ?>, age <?php echo e($child->age); ?><?php echo e($child->school ? ' — '.$child->school : ''); ?>

                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <!-- Actions -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 flex flex-wrap items-center gap-3 border-t border-gray-200 dark:border-gray-700">
                            <form method="POST" action="<?php echo e(route('santa.dismissDuplicate')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="family_a_id" value="<?php echo e($pair['family_a']->id); ?>">
                                <input type="hidden" name="family_b_id" value="<?php echo e($pair['family_b']->id); ?>">
                                <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium transition">
                                    Not Duplicates
                                </button>
                            </form>

                            <form method="POST" action="<?php echo e(route('santa.mergeFamilies')); ?>" onsubmit="return confirm('Merge into <?php echo e($pair['family_a']->family_name); ?>? Children will be transferred and the other record deleted.')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="keep_id" value="<?php echo e($pair['family_a']->id); ?>">
                                <input type="hidden" name="merge_id" value="<?php echo e($pair['family_b']->id); ?>">
                                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Keep <?php echo e(Str::limit($pair['family_a']->family_name, 15)); ?>, Merge Other
                                </button>
                            </form>

                            <form method="POST" action="<?php echo e(route('santa.mergeFamilies')); ?>" onsubmit="return confirm('Merge into <?php echo e($pair['family_b']->family_name); ?>? Children will be transferred and the other record deleted.')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="keep_id" value="<?php echo e($pair['family_b']->id); ?>">
                                <input type="hidden" name="merge_id" value="<?php echo e($pair['family_a']->id); ?>">
                                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Keep <?php echo e(Str::limit($pair['family_b']->family_name, 15)); ?>, Merge Other
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/duplicates.blade.php ENDPATH**/ ?>