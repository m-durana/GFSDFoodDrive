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
            Database Backups
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Backup Management</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Automatic backups run every 4 hours. Unchanged databases are skipped.
                        </p>
                    </div>
                    <form method="POST" action="<?php echo e(route('santa.createBackup')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>
                            Create Backup Now
                        </button>
                    </form>
                </div>

                <?php if(count($backups) === 0): ?>
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                        <p>No backups yet. Click "Create Backup Now" to create the first one.</p>
                    </div>
                <?php else: ?>
                    <?php $currentDate = null; ?>
                    <?php $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($backup['date'] !== $currentDate): ?>
                            <?php $currentDate = $backup['date']; ?>
                            <div class="<?php if(!$loop->first): ?> mt-6 <?php endif; ?> mb-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2">
                                    <?php echo e(\Carbon\Carbon::parse($currentDate)->format('l, F j, Y')); ?>

                                </h4>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between py-3 px-4 rounded-lg mb-2 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-sm font-bold">
                                        <?php echo e(preg_replace('/[^0-9]/', '', $backup['label'])); ?>

                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($backup['label']); ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo e($backup['created_at']->format('g:i A')); ?>

                                        <span class="text-gray-400 dark:text-gray-500">&middot; <?php echo e($backup['created_at']->diffForHumans()); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="<?php echo e(route('santa.downloadBackup', $backup['filename'])); ?>"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition"
                                    title="Download backup file">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    Download
                                </a>
                                <button type="button"
                                    onclick="openRollbackModal('<?php echo e($backup['filename']); ?>', '<?php echo e($backup['label']); ?> (<?php echo e($backup['created_at']->format('M j, g:i A')); ?>)')"
                                    class="inline-flex items-center px-3 py-1.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-md hover:bg-amber-200 dark:hover:bg-amber-900/50 text-xs font-medium transition"
                                    title="Rollback database to this backup">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                                    Rollback
                                </button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>

            <div>
                <a href="<?php echo e(route('santa.settings')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Rollback Confirmation Modal -->
    <div id="rollback-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeRollbackModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Rollback Database</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    You are about to rollback to: <strong id="rollback-target" class="text-gray-900 dark:text-gray-100"></strong>
                </p>
                <p class="text-sm text-amber-600 dark:text-amber-400 mb-4">
                    A snapshot of the current database will be saved before rolling back.
                </p>

                <form method="POST" action="<?php echo e(route('santa.rollbackBackup')); ?>" id="rollback-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="filename" id="rollback-filename">

                    <div class="space-y-3 mb-6">
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <input type="radio" name="data_only" value="0" checked class="mt-0.5 text-red-600 focus:ring-red-500">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Full Rollback</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Replace everything including users, settings, and all data</div>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <input type="radio" name="data_only" value="1" class="mt-0.5 text-red-600 focus:ring-red-500">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Data Only</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Restore families, children, and seasons but keep current users and settings</div>
                            </div>
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeRollbackModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-medium transition">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-500 text-sm font-medium transition">
                            Confirm Rollback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRollbackModal(filename, label) {
            document.getElementById('rollback-filename').value = filename;
            document.getElementById('rollback-target').textContent = label;
            document.getElementById('rollback-modal').classList.remove('hidden');
        }

        function closeRollbackModal() {
            document.getElementById('rollback-modal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeRollbackModal();
        });
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/backups.blade.php ENDPATH**/ ?>