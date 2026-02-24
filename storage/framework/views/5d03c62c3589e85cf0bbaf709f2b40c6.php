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
                Delivery Day
            </h2>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('delivery.map')); ?>"
                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium transition">
                    Live Map
                </a>
                <a href="<?php echo e(route('delivery.track')); ?>"
                   class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                    Share Location
                </a>
                <a href="<?php echo e(route('delivery.logs')); ?>"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    View All Logs
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

            <!-- Stats cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($stats['total']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo e($stats['needs_delivery']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Need Delivery</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo e($stats['pending']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400"><?php echo e($stats['in_transit']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">In Transit</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($stats['delivered']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Delivered</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($stats['picked_up']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Picked Up</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="<?php echo e(route('delivery.index')); ?>" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Team</label>
                        <select name="team" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All teams</option>
                            <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($team); ?>" <?php echo e(request('team') == $team ? 'selected' : ''); ?>><?php echo e($team); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="needs_delivery" <?php echo e(request('status') == 'needs_delivery' ? 'selected' : ''); ?>>Needs Delivery</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="in_transit" <?php echo e(request('status') == 'in_transit' ? 'selected' : ''); ?>>In Transit</option>
                            <option value="delivered" <?php echo e(request('status') == 'delivered' ? 'selected' : ''); ?>>Delivered</option>
                            <option value="picked_up" <?php echo e(request('status') == 'picked_up' ? 'selected' : ''); ?>>Picked Up</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Delivery Date</label>
                        <select name="date" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All dates</option>
                            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($date); ?>" <?php echo e(request('date') == $date ? 'selected' : ''); ?>><?php echo e($date); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Filter
                    </button>
                    <a href="<?php echo e(route('delivery.index')); ?>" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700">Reset</a>
                </form>
            </div>

            <!-- Families grouped by team -->
            <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teamName => $teamFamilies): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        <?php echo e($teamName); ?>

                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?php echo e($teamFamilies->count()); ?> <?php echo e(Str::plural('family', $teamFamilies->count())); ?>)</span>
                    </h3>

                    <div class="space-y-4">
                        <?php $__currentLoopData = $teamFamilies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 <?php echo e($family->delivery_status?->value === 'delivered' || $family->delivery_status?->value === 'picked_up' ? 'bg-green-50 dark:bg-green-900/20' : ''); ?>">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <!-- Family info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">#<?php echo e($family->family_number); ?></span>
                                            <a href="<?php echo e(route('family.show', $family)); ?>" class="text-blue-600 dark:text-blue-400 hover:underline font-medium"><?php echo e($family->family_name); ?></a>
                                            <?php if($family->delivery_status): ?>
                                                <?php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        'in_transit' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                                        'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                        'picked_up' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                    ];
                                                ?>
                                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full <?php echo e($statusColors[$family->delivery_status->value] ?? ''); ?>">
                                                    <?php echo e($family->delivery_status->label()); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 space-y-0.5">
                                            <div><?php echo e($family->address); ?></div>
                                            <div><?php echo e($family->phone1); ?><?php if($family->phone2): ?> / <?php echo e($family->phone2); ?><?php endif; ?></div>
                                            <?php if($family->delivery_preference): ?>
                                                <div><span class="font-medium">Pref:</span> <?php echo e($family->delivery_preference); ?><?php if($family->delivery_date): ?> — <?php echo e($family->delivery_date); ?><?php endif; ?> <?php if($family->delivery_time): ?> <?php echo e($family->delivery_time); ?><?php endif; ?></div>
                                            <?php endif; ?>
                                            <?php if($family->delivery_reason): ?>
                                                <div class="text-red-600 dark:text-red-400"><span class="font-medium">Reason:</span> <?php echo e($family->delivery_reason); ?></div>
                                            <?php endif; ?>
                                            <?php if($family->pet_information): ?>
                                                <div class="text-amber-600 dark:text-amber-400">Pets: <?php echo e($family->pet_information); ?></div>
                                            <?php endif; ?>
                                            <?php if($family->preferred_language && $family->preferred_language !== 'English'): ?>
                                                <div class="text-blue-600 dark:text-blue-400">Language: <?php echo e($family->preferred_language); ?></div>
                                            <?php endif; ?>
                                            <div class="text-gray-500 dark:text-gray-400">
                                                <?php echo e($family->number_of_family_members); ?> members (<?php echo e($family->number_of_children); ?> children)
                                                <?php if($family->volunteer): ?> — Vol: <?php echo e($family->volunteer->first_name); ?> <?php echo e($family->volunteer->last_name); ?><?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Recent logs -->
                                        <?php if($family->deliveryLogs->count() > 0): ?>
                                            <div class="mt-2 space-y-1">
                                                <?php $__currentLoopData = $family->deliveryLogs->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="font-medium"><?php echo e($log->created_at->format('M j g:ia')); ?></span>
                                                        — <?php echo e(ucfirst(str_replace('_', ' ', $log->status))); ?>

                                                        <?php if($log->user): ?> by <?php echo e($log->user->first_name); ?><?php endif; ?>
                                                        <?php if($log->notes): ?> — <?php echo e($log->notes); ?><?php endif; ?>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col space-y-2 w-52 shrink-0">
                                        <!-- Team assignment -->
                                        <form method="POST" action="<?php echo e(route('delivery.updateTeam', $family)); ?>" class="flex items-center gap-1">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <input type="text" name="delivery_team" value="<?php echo e($family->delivery_team); ?>" placeholder="Team..."
                                                   class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs px-2 py-1">
                                            <button type="submit" class="shrink-0 px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200">Set</button>
                                        </form>

                                        <!-- Quick status update -->
                                        <form method="POST" action="<?php echo e(route('delivery.updateStatus', $family)); ?>" class="flex items-center gap-1">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <select name="delivery_status" class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs pl-2 pr-7 py-1">
                                                <option value="pending" <?php echo e($family->delivery_status?->value === 'pending' ? 'selected' : ''); ?>>Pending</option>
                                                <option value="in_transit" <?php echo e($family->delivery_status?->value === 'in_transit' ? 'selected' : ''); ?>>In Transit</option>
                                                <option value="delivered" <?php echo e($family->delivery_status?->value === 'delivered' ? 'selected' : ''); ?>>Delivered</option>
                                                <option value="picked_up" <?php echo e($family->delivery_status?->value === 'picked_up' ? 'selected' : ''); ?>>Picked Up</option>
                                            </select>
                                            <button type="submit" class="shrink-0 px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-600">Go</button>
                                        </form>

                                        <!-- Add log note -->
                                        <form method="POST" action="<?php echo e(route('delivery.addLog', $family)); ?>" class="flex flex-col gap-1">
                                            <?php echo csrf_field(); ?>
                                            <select name="status" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs pl-2 pr-7 py-1">
                                                <option value="delivered">Delivered</option>
                                                <option value="left_at_door">Left at door</option>
                                                <option value="no_answer">No answer</option>
                                                <option value="attempted">Attempted</option>
                                                <option value="picked_up">Picked up</option>
                                                <option value="note">Note</option>
                                            </select>
                                            <div class="flex items-center gap-1">
                                                <input type="text" name="notes" placeholder="Notes..."
                                                       class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs px-2 py-1">
                                                <button type="submit" class="shrink-0 px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-500">Log</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No families match the selected filters. Assign family numbers first.
                </div>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/delivery-day/index.blade.php ENDPATH**/ ?>