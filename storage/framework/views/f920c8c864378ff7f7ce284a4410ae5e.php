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
                Manage Grocery Items
            </h2>
            <div class="flex items-center space-x-3">
                <a href="<?php echo e(route('santa.exportGroceryFormula')); ?>"
                   class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                    Export Formula CSV
                </a>
                <a href="<?php echo e(route('santa.shoppingList')); ?>"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Back to Shopping Lists
                </a>
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

            <?php if(session('error')): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <!-- Import from CSV -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Import Formula from CSV</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Upload a shopping list CSV (like the ones exported from Access) to update all item quantities.
                    The importer reads all families, groups by family size, and calculates the median quantity per item per size bracket (1-8 members).
                    Existing items are updated; new items are created.
                </p>
                <form method="POST" action="<?php echo e(route('santa.importGroceryItems')); ?>" enctype="multipart/form-data" class="flex items-end space-x-3">
                    <?php echo csrf_field(); ?>
                    <div class="flex-1">
                        <input type="file" name="csv_file" accept=".csv,.txt" required
                               class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-red-50 dark:file:bg-red-900/30 file:text-red-700 dark:file:text-red-300 hover:file:bg-red-100 dark:hover:file:bg-red-900/50">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Import
                    </button>
                </form>
            </div>

            <!-- Add new item -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add New Item</h3>
                <form method="POST" action="<?php echo e(route('santa.storeGroceryItem')); ?>" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <?php echo csrf_field(); ?>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Category</label>
                        <select name="category" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="canned">Canned</option>
                            <option value="dry">Dry</option>
                            <option value="personal">Personal</option>
                            <option value="condiment">Condiment</option>
                        </select>
                    </div>
                    <?php for($s = 1; $s <= 8; $s++): ?>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Size <?php echo e($s); ?></label>
                            <input type="number" name="qty_<?php echo e($s); ?>" value="0" min="0"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                    <?php endfor; ?>
                    <div>
                        <button type="submit" class="w-full px-3 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                            Add
                        </button>
                    </div>
                </form>
            </div>

            <!-- Items table -->
            <?php
                $categories = ['canned' => 'Canned Goods', 'dry' => 'Dry Goods', 'personal' => 'Personal Care', 'condiment' => 'Condiments & Other'];
            ?>

            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $catLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $catItems = $groceryItems->where('category', $catKey); ?>
                <?php if($catItems->count() > 0): ?>
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            <?php echo e($catLabel); ?>

                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?php echo e($catItems->count()); ?> items)</span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Item</th>
                                        <?php for($s = 1; $s <= 8; $s++): ?>
                                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-14"><?php echo e($s); ?></th>
                                        <?php endfor; ?>
                                        <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php $__currentLoopData = $catItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <form method="POST" action="<?php echo e(route('santa.updateGroceryItem', $item)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <input type="hidden" name="category" value="<?php echo e($item->category); ?>">
                                                <td class="px-2 py-1">
                                                    <input type="text" name="name" value="<?php echo e($item->name); ?>"
                                                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs px-1 py-0.5">
                                                </td>
                                                <?php for($s = 1; $s <= 8; $s++): ?>
                                                    <td class="px-1 py-1 text-center">
                                                        <input type="number" name="qty_<?php echo e($s); ?>" value="<?php echo e($item->{'qty_'.$s}); ?>" min="0"
                                                               class="w-12 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs text-center px-0.5 py-0.5">
                                                    </td>
                                                <?php endfor; ?>
                                                <td class="px-2 py-1 text-center whitespace-nowrap">
                                                    <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline text-xs mr-2">Save</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('santa.destroyGroceryItem', $item)); ?>" class="inline" onsubmit="return confirm('Delete <?php echo e($item->name); ?>?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs">Del</button>
                                            </form>
                                                </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/shopping-manage.blade.php ENDPATH**/ ?>