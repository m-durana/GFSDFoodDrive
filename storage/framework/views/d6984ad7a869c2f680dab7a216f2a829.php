<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Assignment — <?php echo e($assignment->user->first_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-4">
        <!-- Header -->
        <div class="bg-red-700 text-white rounded-xl p-4 mb-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold"><?php echo e($assignment->user->first_name); ?> <?php echo e($assignment->user->last_name); ?></h1>
                    <p class="text-red-200 text-sm"><?php echo e($assignment->getDescription()); ?></p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold" id="progress-count">0</div>
                    <div class="text-red-200 text-xs">of <?php echo e($totalItems); ?> items</div>
                </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-3 bg-red-900 rounded-full h-2">
                <div class="bg-white rounded-full h-2 transition-all duration-300" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>

        <?php if($assignment->notes): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 text-sm text-yellow-800">
                <?php echo e($assignment->notes); ?>

            </div>
        <?php endif; ?>

        <?php if(count($shoppingList) === 0): ?>
            <div class="bg-white rounded-xl p-6 text-center text-gray-500 shadow-sm">
                No items in this assignment.
            </div>
        <?php else: ?>
            <?php
                $categoryLabels = [
                    'canned' => 'Canned Goods',
                    'dry' => 'Dry Goods',
                    'personal' => 'Personal Care',
                    'condiment' => 'Condiments & Extras',
                ];
                $categoryColors = [
                    'canned' => 'bg-orange-100 text-orange-800 border-orange-200',
                    'dry' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'personal' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'condiment' => 'bg-green-100 text-green-800 border-green-200',
                ];
            ?>

            <?php $__currentLoopData = $shoppingList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-2 px-1">
                        <?php echo e($categoryLabels[$category] ?? ucfirst($category)); ?>

                        <span class="text-xs font-normal">(<?php echo e(array_sum($items)); ?> items)</span>
                    </h2>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden divide-y divide-gray-100">
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemName => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="shopping-item flex items-center px-4 py-3 cursor-pointer active:bg-gray-50 transition"
                                   data-key="<?php echo e(md5('assignment_' . $assignment->id . '_' . $itemName)); ?>"
                                   data-qty="<?php echo e($qty); ?>">
                                <input type="checkbox" class="item-checkbox sr-only">
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3 flex-shrink-0 check-circle transition-colors">
                                    <svg class="w-4 h-4 text-white hidden check-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="flex-1 text-sm font-medium text-gray-900 item-name"><?php echo e($itemName); ?></span>
                                <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full text-sm font-bold <?php echo e($categoryColors[$category] ?? 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($qty); ?>

                                </span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <!-- Reset button -->
        <div class="mt-6 mb-8 text-center">
            <button onclick="resetChecklist()" class="text-sm text-gray-400 hover:text-red-600 transition">
                Reset Checklist
            </button>
        </div>
    </div>

    <script>
        const STORAGE_KEY = 'assignment_<?php echo e($assignment->id); ?>';

        function getCheckedItems() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch { return {}; }
        }

        function saveCheckedItems(items) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        }

        function updateProgress() {
            const checked = getCheckedItems();
            const items = document.querySelectorAll('.shopping-item');
            let collected = 0;
            const total = <?php echo e($totalItems); ?>;

            items.forEach(item => {
                const key = item.dataset.key;
                const qty = parseInt(item.dataset.qty) || 0;
                const isChecked = !!checked[key];
                const circle = item.querySelector('.check-circle');
                const icon = item.querySelector('.check-icon');
                const name = item.querySelector('.item-name');

                if (isChecked) {
                    circle.classList.add('bg-green-500', 'border-green-500');
                    circle.classList.remove('border-gray-300');
                    icon.classList.remove('hidden');
                    name.classList.add('line-through', 'text-gray-400');
                    name.classList.remove('text-gray-900');
                    collected += qty;
                } else {
                    circle.classList.remove('bg-green-500', 'border-green-500');
                    circle.classList.add('border-gray-300');
                    icon.classList.add('hidden');
                    name.classList.remove('line-through', 'text-gray-400');
                    name.classList.add('text-gray-900');
                }
            });

            document.getElementById('progress-count').textContent = collected;
            const pct = total > 0 ? (collected / total * 100) : 0;
            document.getElementById('progress-bar').style.width = pct + '%';
        }

        function resetChecklist() {
            if (confirm('Reset all checked items?')) {
                localStorage.removeItem(STORAGE_KEY);
                updateProgress();
            }
        }

        document.addEventListener('click', function(e) {
            const item = e.target.closest('.shopping-item');
            if (!item) return;

            const key = item.dataset.key;
            const checked = getCheckedItems();
            checked[key] = !checked[key];
            if (!checked[key]) delete checked[key];
            saveCheckedItems(checked);
            updateProgress();
        });

        updateProgress();
    </script>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/shopping/assignment.blade.php ENDPATH**/ ?>