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
            Help &amp; Documentation
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <p class="text-gray-600 dark:text-gray-400 mb-8">
                Welcome to the help center. Select a topic below to learn how each feature works.
            </p>

            <?php $topics = \App\Http\Controllers\HelpController::topics(); ?>

            <?php
                $iconMap = [
                    'rocket' => '🚀', 'users' => '👨‍👩‍👧‍👦', 'tag' => '🏷️', 'truck' => '🚚',
                    'cart' => '🛒', 'monitor' => '📺', 'cog' => '⚙️', 'database' => '🗄️', 'archive' => '📦',
                ];
                $descMap = [
                    'getting-started' => 'Log in, navigate the dashboard, and understand your role.',
                    'family-management' => 'Add families, assign numbers, manage children and self-registration.',
                    'gift-tags' => 'Print gift tags, set up Adopt-a-Tag, and manage tag distribution.',
                    'delivery-day' => 'Dispatch board, live map, driver views, and location sharing.',
                    'shopping' => 'Grocery formulas, shopping assignments, and NINJA progress tracking.',
                    'command-center' => 'Full-screen dashboard for TVs — overview, shopping, and delivery modes.',
                    'settings' => 'Configure registration, notifications, branding, and integrations.',
                    'legacy-import' => 'Import historical data from Access databases (_be vs _fe files).',
                    'warehouse' => 'Track donations, scan barcodes, manage inventory and gift drop-offs.',
                ];
            ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('help.show', $topic['slug'])); ?>"
                       class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition group">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl"><?php echo e($iconMap[$topic['icon']] ?? '📄'); ?></span>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-red-700 dark:group-hover:text-red-400 transition">
                                <?php echo e($topic['title']); ?>

                            </h3>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($descMap[$topic['slug']] ?? ''); ?></p>
                        <?php if($topic['role'] !== 'all'): ?>
                            <span class="inline-flex mt-2 px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                <?php echo e(ucfirst($topic['role'])); ?>+
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/help/index.blade.php ENDPATH**/ ?>