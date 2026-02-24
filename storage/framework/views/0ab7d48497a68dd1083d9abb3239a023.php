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
            Shopping Day — Assignments
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Current Assignments -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Assignments</h3>

                <?php if($assignments->count() > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        <?php echo e($assignment->getDisplayName()); ?>

                                    </h4>
                                    <form method="POST" action="<?php echo e(route('santa.deleteAssignment', $assignment)); ?>" onsubmit="return confirm('Remove this assignment?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                    </form>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300"><?php echo e($assignment->getDescription()); ?></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <?php echo e($assignment->getTotalItems()); ?> total items
                                    <?php $checked = $assignment->checks()->count(); ?>
                                    <?php if($checked > 0): ?>
                                        &middot; <span class="text-green-600 dark:text-green-400"><?php echo e($checked); ?> checked</span>
                                    <?php endif; ?>
                                </p>
                                <?php if($assignment->notes): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic"><?php echo e($assignment->notes); ?></p>
                                <?php endif; ?>
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?php echo e(route('shopping.assignment', $assignment->token)); ?>" target="_blank"
                                           class="inline-flex items-center px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-600 transition">
                                            Open Checklist
                                        </a>
                                        <button type="button" onclick="copyLink('<?php echo e(route('shopping.assignment', $assignment->token)); ?>')"
                                                class="inline-flex items-center px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                            Copy URL
                                        </button>
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-mono break-all select-all">
                                        <?php echo e(route('shopping.assignment', $assignment->token)); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No assignments yet. Create one below.</p>
                <?php endif; ?>
            </div>

            <!-- Coverage Indicator -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Family Range Coverage</h3>
                <?php if(count($assignedRanges) > 0): ?>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = $assignedRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                #<?php echo e($range['start']); ?>–#<?php echo e($range['end']); ?> &#10003;
                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400">No family range assignments yet.</p>
                <?php endif; ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Highest family number: <?php echo e($maxFamilyNumber); ?></p>
            </div>

            <!-- Add Assignment -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add Assignment</h3>
                <form method="POST" action="<?php echo e(route('santa.createAssignment')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ninja_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shopper Name</label>
                            <input type="text" name="ninja_name" id="ninja_name" placeholder="e.g. Jake, Sarah, Team Alpha"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                value="<?php echo e(old('ninja_name')); ?>">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">For NINJAs who don't have accounts</p>
                        </div>

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Or Assign to Coordinator</label>
                            <select name="user_id" id="user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">— None (use name above) —</option>
                                <?php $__currentLoopData = $coordinators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($coord->id); ?>"><?php echo e($coord->first_name); ?> <?php echo e($coord->last_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <!-- Family range with school selector -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School (auto-fills range)</label>
                            <select id="sd_school_select" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Select a school...</option>
                                <?php $__currentLoopData = $schoolRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($range->id); ?>" data-start="<?php echo e($range->range_start); ?>" data-end="<?php echo e($range->range_end); ?>"><?php echo e($range->school_name); ?> (<?php echo e($range->range_start); ?>–<?php echo e($range->range_end); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="family_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Family #</label>
                                <input type="number" name="family_start" id="family_start" min="1" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                    value="<?php echo e(old('family_start')); ?>">
                            </div>
                            <div>
                                <label for="family_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Family #</label>
                                <input type="number" name="family_end" id="family_end" min="1" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                    value="<?php echo e(old('family_end')); ?>">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (optional)</label>
                        <input type="text" name="notes" id="notes" placeholder="e.g. Meet at checkout lane 5"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                            value="<?php echo e(old('notes')); ?>">
                    </div>

                    <?php if($errors->any()): ?>
                        <div class="text-sm text-red-600 dark:text-red-400">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p><?php echo e($error); ?></p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Create Assignment
                    </button>
                </form>
            </div>

            <div>
                <a href="<?php echo e(route('santa.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        }

        document.getElementById('sd_school_select').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('family_start').value = opt.dataset.start || '';
            document.getElementById('family_end').value = opt.dataset.end || '';
        });
    </script>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/shopping-day.blade.php ENDPATH**/ ?>