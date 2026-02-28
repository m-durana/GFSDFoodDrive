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
            Warehouse Dashboard
            <?php if (isset($component)) { $__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hint','data' => ['key' => 'warehouse-dashboard','text' => 'Track all incoming donations here. The deficit table shows what\'s still needed. Use Receive Items to scan in donations, or Kiosk Mode for volunteer intake stations.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hint'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['key' => 'warehouse-dashboard','text' => 'Track all incoming donations here. The deficit table shows what\'s still needed. Use Receive Items to scan in donations, or Kiosk Mode for volunteer intake stations.']); ?>
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <a href="<?php echo e(route('warehouse.kiosk')); ?>" class="block p-4 bg-green-50 dark:bg-green-900/20 rounded-lg shadow-sm hover:bg-green-100 dark:hover:bg-green-900/30 transition border border-green-200 dark:border-green-800 text-center">
                    <div class="text-2xl mb-1">📦</div>
                    <h4 class="font-medium text-green-800 dark:text-green-300">Scanner</h4>
                    <p class="text-xs text-green-700/70 dark:text-green-400/60 mt-1">Scan barcodes and log donations</p>
                </a>
                <a href="<?php echo e(route('warehouse.inventory')); ?>" class="block p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 transition border border-blue-200 dark:border-blue-800 text-center">
                    <div class="text-2xl mb-1">📋</div>
                    <h4 class="font-medium text-blue-800 dark:text-blue-300">Inventory</h4>
                    <p class="text-xs text-blue-700/70 dark:text-blue-400/60 mt-1">View stock levels vs. family needs</p>
                </a>
                <a href="<?php echo e(route('warehouse.transactions')); ?>" class="block p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg shadow-sm hover:bg-amber-100 dark:hover:bg-amber-900/30 transition border border-amber-200 dark:border-amber-800 text-center">
                    <div class="text-2xl mb-1">📜</div>
                    <h4 class="font-medium text-amber-800 dark:text-amber-300">Transaction Log</h4>
                    <p class="text-xs text-amber-700/70 dark:text-amber-400/60 mt-1">Full audit trail of all items</p>
                </a>
                <a href="<?php echo e(route('warehouse.kiosk')); ?>" target="_blank" class="block p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg shadow-sm hover:bg-purple-100 dark:hover:bg-purple-900/30 transition border border-purple-200 dark:border-purple-800 text-center">
                    <div class="text-2xl mb-1">🖥️</div>
                    <h4 class="font-medium text-purple-800 dark:text-purple-300">Kiosk (New Tab)</h4>
                    <p class="text-xs text-purple-700/70 dark:text-purple-400/60 mt-1">Open scanner in fullscreen for volunteers</p>
                </a>
            </div>

            <!-- Inventory Deficit Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Inventory vs. Needs</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Needed</th>
                                    <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">On Hand</th>
                                    <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Surplus / Deficit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $deficits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                        <td class="py-2 px-3 text-gray-900 dark:text-gray-100">
                                            <span class="inline-block w-2 h-2 rounded-full mr-2 <?php echo e($row['category']->type === 'food' ? 'bg-amber-400' : ($row['category']->type === 'gift' ? 'bg-purple-400' : ($row['category']->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400'))); ?>"></span>
                                            <?php echo e($row['category']->name); ?>

                                        </td>
                                        <td class="text-right py-2 px-3 text-gray-600 dark:text-gray-400"><?php echo e($row['needed']); ?></td>
                                        <td class="text-right py-2 px-3 text-gray-600 dark:text-gray-400"><?php echo e($row['on_hand']); ?></td>
                                        <td class="text-right py-2 px-3 font-medium <?php echo e($row['deficit'] > 0 ? 'text-red-600 dark:text-red-400' : ($row['deficit'] < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400')); ?>">
                                            <?php if($row['deficit'] > 0): ?>
                                                -<?php echo e($row['deficit']); ?>

                                            <?php elseif($row['deficit'] < 0): ?>
                                                +<?php echo e(abs($row['deficit'])); ?>

                                            <?php else: ?>
                                                &mdash;
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gift Progress by Age Group -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Gift Progress</h3>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $giftProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                        <span><?php echo e($gp['category']); ?></span>
                                        <span><?php echo e($gp['on_hand']); ?>/<?php echo e($gp['needed']); ?> (<?php echo e($gp['percent']); ?>%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full <?php echo e($gp['percent'] >= 100 ? 'bg-green-500' : ($gp['percent'] >= 50 ? 'bg-yellow-500' : 'bg-red-500')); ?>" style="width: <?php echo e($gp['percent']); ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <!-- Donation Source Breakdown -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Donation Sources</h3>
                        <?php if($sourceBreakdown->isEmpty()): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No donations recorded yet.</p>
                        <?php else: ?>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">Source</th>
                                        <th class="text-right py-2 text-gray-500 dark:text-gray-400">Items</th>
                                        <th class="text-right py-2 text-gray-500 dark:text-gray-400">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $sourceBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $src): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                            <td class="py-2 text-gray-900 dark:text-gray-100"><?php echo e($src->source ?? 'Unknown'); ?></td>
                                            <td class="text-right py-2 text-gray-600 dark:text-gray-400"><?php echo e($src->count); ?></td>
                                            <td class="text-right py-2 text-gray-600 dark:text-gray-400"><?php echo e($src->total_qty); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Feed -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Live Intake Feed</h3>
                        <a href="<?php echo e(route('warehouse.transactions')); ?>" class="text-sm text-red-600 dark:text-red-400 hover:underline">View All</a>
                    </div>
                    <div id="live-feed">
                        <?php echo $__env->make('warehouse._feed', ['transactions' => $recentTransactions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Poll for new transactions every 15 seconds
        setInterval(function() {
            fetch('<?php echo e(route('warehouse.index')); ?>?_feed=1', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.ok ? r.text() : ''; })
              .then(function(html) { if (html) document.getElementById('live-feed').innerHTML = html; })
              .catch(function() {});
        }, 15000);
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/warehouse/index.blade.php ENDPATH**/ ?>