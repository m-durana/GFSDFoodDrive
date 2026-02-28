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
            Access Database &mdash; Select Table (<?php echo e($seasonYear); ?>)
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('import_errors') && count(session('import_errors')) > 0): ?>
                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded">
                    <h4 class="font-medium mb-2">Import Errors:</h4>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6">
                <h3 class="text-lg font-medium text-green-900 dark:text-green-100 mb-2">Quick Import</h3>
                <p class="text-sm text-green-700 dark:text-green-300 mb-4">
                    Import both Family Table and Child Table in one click. Families are imported first, then children are linked automatically.
                </p>
                <form method="POST" action="<?php echo e(route('santa.seasons.importAllAccess')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="path" value="<?php echo e($path); ?>">
                    <input type="hidden" name="season_year" value="<?php echo e($seasonYear); ?>">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-700 text-white rounded-md hover:bg-green-600 text-sm font-medium transition"
                            onclick="this.textContent='Importing all tables...'; this.disabled=true; this.form.submit();">
                        Import All (Families + Children)
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Tables Found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Or import tables individually. Import <strong>Family Table first</strong>, then Child Table (children link to families by family number or Access ID).
                </p>

                <div class="space-y-3">
                    <?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($table); ?></h4>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="POST" action="<?php echo e(route('santa.seasons.previewAccessTable')); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="path" value="<?php echo e($path); ?>">
                                        <input type="hidden" name="table" value="<?php echo e($table); ?>">
                                        <input type="hidden" name="season_year" value="<?php echo e($seasonYear); ?>">
                                        <input type="hidden" name="type" value="family">
                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-500 transition">
                                            Import as Families
                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('santa.seasons.previewAccessTable')); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="path" value="<?php echo e($path); ?>">
                                        <input type="hidden" name="table" value="<?php echo e($table); ?>">
                                        <input type="hidden" name="season_year" value="<?php echo e($seasonYear); ?>">
                                        <input type="hidden" name="type" value="child">
                                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-500 transition">
                                            Import as Children
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div>
                <a href="<?php echo e(route('santa.seasons.import')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Import
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/seasons/access-tables.blade.php ENDPATH**/ ?>