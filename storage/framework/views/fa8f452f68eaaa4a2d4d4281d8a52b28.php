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
            Season History
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Current Season Stats -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Current Season: <?php echo e($currentYear); ?>

                    </h3>
                    <div class="flex space-x-3">
                        <a href="<?php echo e(route('santa.seasons.import')); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">
                            Import Data
                        </a>
                        <form method="POST" action="<?php echo e(route('santa.seasons.archive')); ?>" onsubmit="return confirm('Archive season <?php echo e($currentYear); ?> and start <?php echo e($currentYear + 1); ?>? This will preserve all current data and start a fresh season.')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-500 text-sm font-medium transition">
                                Archive & Start <?php echo e($currentYear + 1); ?>

                            </button>
                        </form>
                    </div>
                </div>

                <?php if($currentStats): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($currentStats['total_families']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Families</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($currentStats['total_children']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Children</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($currentStats['total_family_members']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total People</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($currentStats['tags_adopted']); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tags Adopted</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Year-over-Year Chart -->
            <?php if(count($chartYears) > 0): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Year-over-Year</h3>
                    <canvas id="seasonChart" height="100"></canvas>
                </div>
            <?php endif; ?>

            <!-- Past Seasons Table -->
            <?php if($seasons->count() > 0): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Archived Seasons</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Families</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Children</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">People</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Delivered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Archived</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($season->year); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300"><?php echo e($season->total_families); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300"><?php echo e($season->total_children); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300"><?php echo e($season->total_family_members); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300"><?php echo e($season->deliveries_completed + $season->pickups_completed); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"><?php echo e($season->archived_at?->format('M j, Y') ?? 'Imported'); ?></td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        <a href="<?php echo e(route('santa.seasons.show', $season)); ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Details</a>
                                        <a href="<?php echo e(route('santa.seasons.families', $season)); ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Families</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No archived seasons yet. Archive the current season or import historical data to see history here.
                </div>
            <?php endif; ?>

            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('santa.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if(count($chartYears) > 0): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script>
            new Chart(document.getElementById('seasonChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_reverse($chartYears), 15, 512) ?>,
                    datasets: [
                        {
                            label: 'Families',
                            data: <?php echo json_encode(array_reverse($chartFamilies), 15, 512) ?>,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        },
                        {
                            label: 'Children',
                            data: <?php echo json_encode(array_reverse($chartChildren), 15, 512) ?>,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
    <?php endif; ?>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/seasons/index.blade.php ENDPATH**/ ?>