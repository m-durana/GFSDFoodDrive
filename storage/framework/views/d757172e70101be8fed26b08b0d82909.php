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
                <?php echo e($current['title']); ?>

            </h2>
            <a href="<?php echo e(route('help.index')); ?>" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                &larr; All Topics
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-8">
                
                <nav class="hidden lg:block w-52 shrink-0">
                    <div class="sticky top-20 space-y-1">
                        <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('help.show', $topic['slug'])); ?>"
                               class="block px-3 py-1.5 rounded-md text-sm transition
                                   <?php echo e($topic['slug'] === $current['slug']
                                       ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 font-medium'
                                       : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'); ?>">
                                <?php echo e($topic['title']); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </nav>

                
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 sm:p-8">
                        <style>
                            .wiki-content { color: #374151; line-height: 1.85; font-size: 0.95rem; }
                            .wiki-content h2, .wiki-content h3, .wiki-content h4 { color: #111827; font-weight: 600; margin-top: 2em; margin-bottom: 0.6em; }
                            .wiki-content h2 { font-size: 1.6em; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.4em; }
                            .wiki-content h3 { font-size: 1.3em; }
                            .wiki-content h4 { font-size: 1.1em; }
                            .wiki-content p { margin-bottom: 1.1em; }
                            .wiki-content ul, .wiki-content ol { margin-bottom: 1.1em; padding-left: 1.8em; }
                            .wiki-content li { margin-bottom: 0.35em; }
                            .wiki-content strong { color: #111827; font-weight: 600; }
                            .wiki-content code { color: #dc2626; background: #fef2f2; padding: 0.15em 0.4em; border-radius: 0.25em; font-size: 0.875em; }
                            .wiki-content a { color: #2563eb; text-decoration: underline; }
                            .wiki-content blockquote { border-left: 4px solid #3b82f6; background: #eff6ff; padding: 0.8em 1.2em; margin: 1.2em 0; border-radius: 0 0.5em 0.5em 0; color: #1e40af; font-size: 0.9em; }
                            .wiki-content table { border-collapse: collapse; width: 100%; margin: 1em 0; }
                            .wiki-content th, .wiki-content td { border: 1px solid #e5e7eb; padding: 0.5em 0.8em; text-align: left; }
                            .wiki-content th { background: #f9fafb; font-weight: 600; }
                            .dark .wiki-content { color: #d1d5db; }
                            .dark .wiki-content h2, .dark .wiki-content h3, .dark .wiki-content h4 { color: #f3f4f6; }
                            .dark .wiki-content h2 { border-bottom-color: #374151; }
                            .dark .wiki-content strong { color: #f3f4f6; }
                            .dark .wiki-content code { color: #f87171; background: rgba(127,29,29,0.2); }
                            .dark .wiki-content a { color: #60a5fa; }
                            .dark .wiki-content blockquote { border-left-color: #3b82f6; background: rgba(59,130,246,0.1); color: #93c5fd; }
                            .dark .wiki-content th { background: #1f2937; }
                            .dark .wiki-content th, .dark .wiki-content td { border-color: #374151; }
                        </style>
                        <div class="wiki-content max-w-none">
                            <?php echo \Illuminate\Support\Str::markdown($current['content']); ?>

                        </div>
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/help/show.blade.php ENDPATH**/ ?>