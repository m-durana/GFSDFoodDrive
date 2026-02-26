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
            Coordinator Dashboard
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($stats['total_families']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($stats['total_children']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Children</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400"><?php echo e($stats['families_done']); ?></div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">Families Complete</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-700 dark:text-red-400"><?php echo e($stats['unmerged_tags']); ?></div>
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">Unprinted Tags</div>
                </div>
            </div>

            <!-- Document Generation -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Generate Documents</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Gift Tags (706) -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Gift Tags</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Avery 8163 labels (2"x4", 10/page)</p>
                            <form method="GET" action="<?php echo e(route('coordinator.giftTags')); ?>" target="_blank" class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Filter</label>
                                    <select name="filter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                        <option value="unmerged">Unprinted Only (<?php echo e($stats['unmerged_tags']); ?>)</option>
                                        <option value="all">All Children</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">School</label>
                                    <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm js-school-select" data-start="gift_tag_range_start" data-end="gift_tag_range_end">
                                        <option value="">All Schools</option>
                                        <?php $__currentLoopData = $schoolRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($range->id); ?>" data-start="<?php echo e($range->range_start); ?>" data-end="<?php echo e($range->range_end); ?>"><?php echo e($range->school_name); ?> (<?php echo e($range->range_start); ?>–<?php echo e($range->range_end); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Range Start</label>
                                        <input type="number" name="range_start" id="gift_tag_range_start" placeholder="1" min="1" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Range End</label>
                                        <input type="number" name="range_end" id="gift_tag_range_end" placeholder="599" min="1" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="mark_merged" value="1" class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                        <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Mark as printed after generating</span>
                                    </label>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Generate Gift Tags PDF
                                </button>
                            </form>
                        </div>

                        <!-- Family Summary (708) -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Family Summary Sheets</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">One page per family with demographics</p>
                            <form method="GET" action="<?php echo e(route('coordinator.familySummary')); ?>" target="_blank" class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">School</label>
                                    <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm js-school-select" data-start="summary_range_start" data-end="summary_range_end">
                                        <option value="">All Schools</option>
                                        <?php $__currentLoopData = $schoolRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($range->id); ?>" data-start="<?php echo e($range->range_start); ?>" data-end="<?php echo e($range->range_end); ?>"><?php echo e($range->school_name); ?> (<?php echo e($range->range_start); ?>–<?php echo e($range->range_end); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Range Start</label>
                                        <input type="number" name="range_start" id="summary_range_start" placeholder="All" min="1" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Range End</label>
                                        <input type="number" name="range_end" id="summary_range_end" placeholder="All" min="1" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Generate Summary PDF
                                </button>
                            </form>
                        </div>

                        <!-- Delivery Day (709) -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Delivery Day Sheets</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Contact and delivery info per family</p>
                            <form method="GET" action="<?php echo e(route('coordinator.deliveryDay')); ?>" target="_blank" class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Delivery Date</label>
                                    <select name="delivery_date" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                        <option value="">All Dates</option>
                                        <?php $__currentLoopData = array_filter(array_map('trim', explode(',', \App\Models\Setting::get('delivery_dates', 'December 18th,December 19th')))); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($date); ?>"><?php echo e($date); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Delivery Team</label>
                                    <input type="text" name="delivery_team" placeholder="All teams" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Generate Delivery PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.js-school-select').forEach(function(select) {
            select.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                var startId = this.dataset.start;
                var endId = this.dataset.end;
                document.getElementById(startId).value = opt.dataset.start || '';
                document.getElementById(endId).value = opt.dataset.end || '';
            });
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/coordinator/index.blade.php ENDPATH**/ ?>