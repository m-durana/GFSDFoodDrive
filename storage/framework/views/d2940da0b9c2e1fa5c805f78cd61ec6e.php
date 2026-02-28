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
            Inventory
            <?php if (isset($component)) { $__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hint','data' => ['key' => 'warehouse-inventory','text' => 'Filter by type using the tabs. Click a category row to expand and see individual items. Green = surplus, Red = deficit compared to what families need.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hint'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['key' => 'warehouse-inventory','text' => 'Filter by type using the tabs. Click a category row to expand and see individual items. Green = surplus, Red = deficit compared to what families need.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180)): ?>
<?php $attributes = $__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180; ?>
<?php unset($__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180)): ?>
<?php $component = $__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180; ?>
<?php unset($__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180); ?>
<?php endif; ?>
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ activeTab: 'all', expanded: {} }">

            <!-- Inventory Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <!-- Type Filter Tabs -->
                <div class="flex space-x-1 p-4 border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">All</button>
                    <button @click="activeTab = 'food'" :class="activeTab === 'food' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Food</button>
                    <button @click="activeTab = 'gift'" :class="activeTab === 'gift' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Gifts</button>
                    <button @click="activeTab = 'baby'" :class="activeTab === 'baby' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Baby</button>
                    <button @click="activeTab = 'supply'" :class="activeTab === 'supply' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium transition">Supply</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Category</th>
                                <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400">Unit</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">On Hand</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Needed</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Deficit/Surplus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $deficits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    x-show="activeTab === 'all' || activeTab === '<?php echo e($row['category']->type); ?>'"
                                    @click="expanded[<?php echo e($i); ?>] = !expanded[<?php echo e($i); ?>]">
                                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100 font-medium">
                                        <span class="inline-block w-2 h-2 rounded-full mr-2 <?php echo e($row['category']->type === 'food' ? 'bg-amber-400' : ($row['category']->type === 'gift' ? 'bg-purple-400' : ($row['category']->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400'))); ?>"></span>
                                        <?php echo e($row['category']->name); ?>

                                        <?php if($row['category']->items->count()): ?>
                                            <svg class="inline h-4 w-4 text-gray-400 ml-1 transition-transform" :class="expanded[<?php echo e($i); ?>] && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center py-3 px-4 text-gray-500 dark:text-gray-400"><?php echo e($row['category']->unit); ?></td>
                                    <td class="text-right py-3 px-4 text-gray-900 dark:text-gray-100 font-medium"><?php echo e($row['on_hand']); ?></td>
                                    <td class="text-right py-3 px-4 text-gray-600 dark:text-gray-400"><?php echo e($row['needed']); ?></td>
                                    <td class="text-right py-3 px-4 font-medium <?php echo e($row['deficit'] > 0 ? 'text-red-600 dark:text-red-400' : ($row['deficit'] < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400')); ?>">
                                        <?php if($row['deficit'] > 0): ?>
                                            -<?php echo e($row['deficit']); ?>

                                        <?php elseif($row['deficit'] < 0): ?>
                                            +<?php echo e(abs($row['deficit'])); ?>

                                        <?php else: ?>
                                            &mdash;
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $__currentLoopData = $row['category']->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr x-show="(activeTab === 'all' || activeTab === '<?php echo e($row['category']->type); ?>') && expanded[<?php echo e($i); ?>]" x-cloak
                                        class="bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-600/30 cursor-pointer"
                                        onclick="window.location='<?php echo e(route('warehouse.transactions', ['search' => $item->name])); ?>'">
                                        <td class="py-2 px-4 pl-10 text-gray-600 dark:text-gray-400 text-xs">
                                            <?php echo e($item->name); ?>

                                            <?php if($item->barcode): ?> <span class="text-gray-400 dark:text-gray-500 ml-1">[<?php echo e($item->barcode); ?>]</span> <?php endif; ?>
                                        </td>
                                        <td colspan="4" class="py-2 px-4 text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo e($item->description ?? ''); ?>

                                            <span class="text-blue-500 dark:text-blue-400 ml-2">View log &rarr;</span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/warehouse/inventory.blade.php ENDPATH**/ ?>