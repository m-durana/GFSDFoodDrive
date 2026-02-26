<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Status - GFSD Food Drive</title>
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
            <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold">GFSD Food Drive</h1>
                <p class="text-red-200 text-lg mt-1">Family Status</p>
            </div>
        </header>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 space-y-6">
            <!-- Family Greeting -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Hello, <?php echo e($family->family_name); ?> family!
                </h2>
                <?php if($family->family_number): ?>
                    <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                        Family #<?php echo e($family->family_number); ?>

                    </span>
                <?php endif; ?>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Your Status</h3>
                <div class="relative">
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start mb-8 last:mb-0">
                            <!-- Timeline line -->
                            <?php if(!$loop->last): ?>
                                <div class="absolute ml-[15px] mt-8 w-0.5 h-[calc(100%/<?php echo e(count($steps)); ?>)]
                                    <?php echo e($step['complete'] ? 'bg-green-400 dark:bg-green-500' : 'bg-gray-200 dark:bg-gray-600'); ?>"></div>
                            <?php endif; ?>

                            <!-- Status dot -->
                            <div class="flex-shrink-0 relative z-10">
                                <?php if($step['complete']): ?>
                                    
                                    <div class="w-8 h-8 rounded-full bg-green-500 dark:bg-green-600 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                <?php elseif($i === $currentStepIndex + 1): ?>
                                    
                                    <div class="w-8 h-8 rounded-full bg-blue-500 dark:bg-blue-600 flex items-center justify-center animate-pulse">
                                        <div class="w-3 h-3 rounded-full bg-white"></div>
                                    </div>
                                <?php else: ?>
                                    
                                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                        <div class="w-3 h-3 rounded-full bg-gray-400 dark:bg-gray-500"></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Step content -->
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-semibold <?php echo e($step['complete'] ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300'); ?>">
                                    <?php echo e($step['label']); ?>

                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    <?php echo e($step['description']); ?>

                                </p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Children Summary Card -->
            <?php if($totalChildren > 0): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Gift Collection</h3>
                    <?php if($childrenWithGifts > 0): ?>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300">
                                Gifts are being collected for
                                <span class="font-bold text-green-600 dark:text-green-400"><?php echo e($childrenWithGifts); ?></span>
                                of
                                <span class="font-bold"><?php echo e($totalChildren); ?></span>
                                <?php echo e($totalChildren === 1 ? 'child' : 'children'); ?>.
                            </p>
                        </div>
                        <!-- Progress bar -->
                        <div class="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-green-500 dark:bg-green-600 h-2.5 rounded-full transition-all" style="width: <?php echo e(($childrenWithGifts / $totalChildren) * 100); ?>%"></div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400">
                            Gift collection has not started yet for your <?php echo e($totalChildren); ?> <?php echo e($totalChildren === 1 ? 'child' : 'children'); ?>. Check back soon!
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Delivery Info Card -->
            <?php if($family->delivery_date): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Delivery Information</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Date</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_date); ?></dd>
                        </div>
                        <?php if($family->delivery_time): ?>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Time Window</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_time); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($family->delivery_preference): ?>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Preference</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_preference); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($family->delivery_status): ?>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                                <dd>
                                    <?php $ds = $family->delivery_status; ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo e($ds === \App\Enums\DeliveryStatus::Pending ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : ''); ?>

                                        <?php echo e($ds === \App\Enums\DeliveryStatus::InTransit ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : ''); ?>

                                        <?php echo e($ds === \App\Enums\DeliveryStatus::Delivered ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : ''); ?>

                                        <?php echo e($ds === \App\Enums\DeliveryStatus::PickedUp ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : ''); ?>

                                    "><?php echo e($ds->label()); ?></span>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            <?php endif; ?>

            <!-- Contact Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 text-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Questions?</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    Contact us at
                    <a href="mailto:fooddrive@gfalls.wednet.edu" class="text-red-600 dark:text-red-400 hover:underline font-medium">
                        fooddrive@gfalls.wednet.edu
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-gray-700 mt-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 text-center text-xs text-gray-400 dark:text-gray-500">
                Granite Falls School District Food Drive
            </div>
        </footer>
    </div>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/family/status.blade.php ENDPATH**/ ?>