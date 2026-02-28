<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Adopted Tag - GFSD Food Drive</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-red-700 dark:bg-red-900 text-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
                <h1 class="text-2xl sm:text-3xl font-bold">Thank you, <?php echo e($child->adopter_name); ?>!</h1>
                <p class="text-red-200 mt-1">You're making a difference for a child in Granite Falls.</p>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- What you claimed -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Your Adopted Tag</h3>
                <div class="flex items-center space-x-4 mb-4">
                    <?php if (isset($component)) { $__componentOriginal9f9deed277bf5246fa0f5e280951620e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f9deed277bf5246fa0f5e280951620e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gender-icon','data' => ['gender' => $child->gender,'size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gender-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['gender' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($child->gender),'size' => 'md']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f9deed277bf5246fa0f5e280951620e)): ?>
<?php $attributes = $__attributesOriginal9f9deed277bf5246fa0f5e280951620e; ?>
<?php unset($__attributesOriginal9f9deed277bf5246fa0f5e280951620e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f9deed277bf5246fa0f5e280951620e)): ?>
<?php $component = $__componentOriginal9f9deed277bf5246fa0f5e280951620e; ?>
<?php unset($__componentOriginal9f9deed277bf5246fa0f5e280951620e); ?>
<?php endif; ?>
                    <div>
                        <p class="font-semibold"><?php echo e(strtolower($child->gender ?? '') === 'other' ? 'Child' : ($child->gender ?? 'Child')); ?>, Age <?php echo e($child->age ?? '?'); ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Family #<?php echo e($child->family->family_number); ?></p>
                        <?php if($child->adopter_email): ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($child->adopter_email); ?></p>
                        <?php endif; ?>
                        <?php if($child->adopter_phone): ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($child->adopter_phone); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <?php if($child->clothes_size): ?>
                        <div><span class="text-gray-500 dark:text-gray-400">Size:</span> <?php echo e($child->clothes_size); ?></div>
                    <?php endif; ?>
                    <?php if($child->toy_ideas): ?>
                        <div class="sm:col-span-2"><span class="text-gray-500 dark:text-gray-400">Interests:</span> <?php echo e($child->toy_ideas); ?></div>
                    <?php endif; ?>
                    <?php if($child->gift_preferences): ?>
                        <div class="sm:col-span-2"><span class="text-gray-500 dark:text-gray-400">Gift preferences:</span> <?php echo e($child->gift_preferences); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deadline & Drop-off -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Drop-off Information</h3>

                <?php if($child->adoption_deadline): ?>
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">
                            Please drop off the gift by <span class="text-red-600 dark:text-red-400"><?php echo e($child->adoption_deadline->format('F j, Y')); ?></span>
                        </p>
                    </div>
                <?php endif; ?>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    When dropping off the gift, please label it with <strong>Family #<?php echo e($child->family->family_number); ?></strong> so we can match it to the right child.
                </p>

                <?php if($child->gift_dropped_off): ?>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 flex items-center space-x-3">
                        <svg class="w-8 h-8 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-green-700 dark:text-green-300">Gift dropped off — Thank you!</p>
                            <p class="text-sm text-green-600 dark:text-green-400">Your generosity will make this child's holiday special.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('adopt.markDelivered', $child->adoption_token)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-500 font-medium transition text-center"
                                onclick="return confirm('Mark this gift as dropped off?')">
                            Mark as Dropped Off
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Bookmark reminder -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-sm text-yellow-700 dark:text-yellow-300">
                <strong>Bookmark this page!</strong> This is your private link to track your adopted tag. You can come back anytime to check the status or mark the gift as dropped off.
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                Organized by Granite Falls School District Food Drive
            </div>
        </footer>
    </div>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/adopt/confirmation.blade.php ENDPATH**/ ?>