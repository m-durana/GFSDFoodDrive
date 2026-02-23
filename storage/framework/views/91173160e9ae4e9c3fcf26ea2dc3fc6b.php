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
            Santa Dashboard
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Welcome, Santa!</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="<?php echo e(route('family.index')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">All Families</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View all registered families</p>
                        </a>
                        <a href="<?php echo e(route('santa.users')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Manage Users</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add and edit system users</p>
                        </a>
                        <a href="<?php echo e(route('santa.gifts')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Gift Tracking</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track gift levels and adopters</p>
                        </a>
                        <a href="<?php echo e(route('santa.numberAssignment')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Number Assignment</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign family numbers by school</p>
                        </a>
                        <a href="<?php echo e(route('delivery.index')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Delivery Day</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage delivery logistics</p>
                        </a>
                        <a href="<?php echo e(route('santa.volunteers')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Volunteer Assignment</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign families to volunteers</p>
                        </a>
                        <a href="<?php echo e(route('santa.shoppingList')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Shopping Lists</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Grocery lists by family size</p>
                        </a>
                        <a href="<?php echo e(route('santa.shoppingDay')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Shopping Day</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign coordinators to shop</p>
                        </a>
                        <a href="<?php echo e(route('santa.export')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Filter & Export</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Smart filters and CSV export</p>
                        </a>
                        <a href="<?php echo e(route('santa.reports')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Reports</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stats, progress, and analytics</p>
                        </a>
                        <a href="<?php echo e(route('santa.duplicates')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Duplicate Detection</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Find and merge duplicate families</p>
                        </a>
                        <a href="<?php echo e(route('santa.settings')); ?>" class="block p-6 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Settings</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Self-registration, season config</p>
                        </a>
                    </div>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/index.blade.php ENDPATH**/ ?>