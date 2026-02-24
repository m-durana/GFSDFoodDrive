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
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Shopping Lists
            </h2>
            <div class="flex items-center space-x-3">
                <a href="<?php echo e(route('santa.shoppingList', ['manage' => '1'])); ?>"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Manage Items
                </a>
                <?php if($families->count() > 0): ?>
                    <a href="<?php echo e(route('santa.shoppingList', array_merge(request()->query(), ['format' => 'csv']))); ?>"
                       class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                        Export CSV
                    </a>
                <?php endif; ?>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Generate Shopping List</h3>
                <form method="GET" action="<?php echo e(route('santa.shoppingList')); ?>" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Single Family</label>
                            <select name="family_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All numbered families</option>
                                <?php $__currentLoopData = $allFamilies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($f->id); ?>" <?php echo e(request('family_id') == $f->id ? 'selected' : ''); ?>>
                                        #<?php echo e($f->family_number); ?> — <?php echo e($f->family_name); ?> (<?php echo e($f->number_of_family_members); ?> members)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School</label>
                            <select id="sl_school_select" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Schools</option>
                                <?php $__currentLoopData = $schoolRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($range->id); ?>" data-start="<?php echo e($range->range_start); ?>" data-end="<?php echo e($range->range_end); ?>"><?php echo e($range->school_name); ?> (<?php echo e($range->range_start); ?>–<?php echo e($range->range_end); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Family # Range Start</label>
                            <input type="number" name="family_number_start" id="sl_range_start" value="<?php echo e(request('family_number_start')); ?>"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm" placeholder="e.g. 1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Family # Range End</label>
                            <input type="number" name="family_number_end" id="sl_range_end" value="<?php echo e(request('family_number_end')); ?>"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm" placeholder="e.g. 99">
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Generate
                        </button>
                        <a href="<?php echo e(route('santa.shoppingList')); ?>" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Reset</a>
                    </div>
                </form>
            </div>

            <?php if($families->count() > 0): ?>
                <!-- Summary -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Aggregate Totals (<?php echo e($families->count()); ?> <?php echo e(Str::plural('family', $families->count())); ?>)
                    </h3>

                    <?php
                        $categories = ['canned' => 'Canned Goods', 'dry' => 'Dry Goods', 'personal' => 'Personal Care', 'condiment' => 'Condiments & Other'];
                    ?>

                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $catLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $catItems = $groceryItems->where('category', $catKey);
                            $catTotals = $catItems->mapWithKeys(fn($item) => [$item->name => $totals[$item->name] ?? 0])->filter(fn($qty) => $qty > 0);
                        ?>
                        <?php if($catTotals->count() > 0): ?>
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2"><?php echo e($catLabel); ?></h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                    <?php $__currentLoopData = $catTotals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded px-3 py-2 text-sm">
                                            <span class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($qty); ?></span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-1"><?php echo e($name); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Grand Total: <?php echo e(array_sum($totals)); ?> items
                        </span>
                    </div>
                </div>

                <!-- Per-family breakdown -->
                <?php $__currentLoopData = $families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $list = $shoppingLists[$family->id] ?? []; ?>
                    <?php if(count($list) > 0): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                    #<?php echo e($family->family_number); ?> — <?php echo e($family->family_name); ?>

                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                        (<?php echo e($family->number_of_family_members); ?> members, <?php echo e(array_sum(array_column($list, 'quantity'))); ?> items)
                                    </span>
                                </h4>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-1">
                                <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemName => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="text-xs px-2 py-1 bg-gray-50 dark:bg-gray-700 rounded">
                                        <span class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($info['quantity']); ?></span>
                                        <span class="text-gray-500 dark:text-gray-400"><?php echo e($itemName); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php elseif(request()->anyFilled(['family_id', 'family_number_start'])): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No families match the selected filter.
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    Select a family or range above to generate a shopping list.
                    <br>
                    <span class="text-sm"><?php echo e($groceryItems->count()); ?> grocery items configured in the formula.</span>
                </div>
            <?php endif; ?>

            <div>
                <a href="<?php echo e(route('santa.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sl_school_select').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('sl_range_start').value = opt.dataset.start || '';
            document.getElementById('sl_range_end').value = opt.dataset.end || '';
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/shopping-list.blade.php ENDPATH**/ ?>