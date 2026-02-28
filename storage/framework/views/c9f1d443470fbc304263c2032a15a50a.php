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
            Transaction Log
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <form method="GET" action="<?php echo e(route('warehouse.transactions')); ?>" class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Type</label>
                            <select name="type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Types</option>
                                <?php $__currentLoopData = \App\Enums\TransactionType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type->value); ?>" <?php echo e(request('type') === $type->value ? 'selected' : ''); ?>><?php echo e($type->label()); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Category</label>
                            <select name="category_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Categories</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Source</label>
                            <select name="source" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Sources</option>
                                <option value="School Drive" <?php echo e(request('source') === 'School Drive' ? 'selected' : ''); ?>>School Drive</option>
                                <option value="Adopt-a-Tag" <?php echo e(request('source') === 'Adopt-a-Tag' ? 'selected' : ''); ?>>Adopt-a-Tag</option>
                                <option value="Community Donation" <?php echo e(request('source') === 'Community Donation' ? 'selected' : ''); ?>>Community Donation</option>
                                <option value="Store Purchase" <?php echo e(request('source') === 'Store Purchase' ? 'selected' : ''); ?>>Store Purchase</option>
                                <option value="Gift Drop-off" <?php echo e(request('source') === 'Gift Drop-off' ? 'selected' : ''); ?>>Gift Drop-off</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date From</label>
                            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date To</label>
                            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1">
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search donor name or barcode..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">Filter</button>
                        <a href="<?php echo e(route('warehouse.transactions')); ?>" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Transaction Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Time</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Type</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Category</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Item</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Source</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Donor</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Scanned By</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Volunteer</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="py-2 px-4 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                        <?php echo e($txn->scanned_at?->format('M j, g:ia') ?? $txn->created_at->format('M j, g:ia')); ?>

                                    </td>
                                    <td class="py-2 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            <?php echo e($txn->transaction_type->color() === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ''); ?>

                                            <?php echo e($txn->transaction_type->color() === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : ''); ?>

                                            <?php echo e($txn->transaction_type->color() === 'blue' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : ''); ?>

                                            <?php echo e($txn->transaction_type->color() === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : ''); ?>

                                        "><?php echo e($txn->transaction_type->label()); ?></span>
                                    </td>
                                    <td class="py-2 px-4 text-gray-900 dark:text-gray-100"><?php echo e($txn->category->name); ?></td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400"><?php echo e($txn->item?->name ?? '—'); ?></td>
                                    <td class="text-right py-2 px-4 text-gray-900 dark:text-gray-100 font-medium"><?php echo e($txn->quantity); ?></td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400"><?php echo e($txn->source ?? '—'); ?></td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400"><?php echo e($txn->donor_name ?? '—'); ?></td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400"><?php echo e($txn->scanner?->first_name ?? '—'); ?></td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400"><?php echo e($txn->volunteer_name ?? '—'); ?></td>
                                    <td class="py-2 px-4 text-gray-500 dark:text-gray-500 text-xs font-mono"><?php echo e($txn->ip_address ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-gray-500 dark:text-gray-400">No transactions found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($transactions->hasPages()): ?>
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <?php echo e($transactions->links()); ?>

                    </div>
                <?php endif; ?>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/warehouse/transactions.blade.php ENDPATH**/ ?>