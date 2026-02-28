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
            Receive Items
            <?php if (isset($component)) { $__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hint','data' => ['key' => 'warehouse-receive','text' => 'Scan barcodes with the USB scanner or type them manually. Unknown barcodes can be registered on the fly. The input re-focuses after each scan for continuous intake.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hint'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['key' => 'warehouse-receive','text' => 'Scan barcodes with the USB scanner or type them manually. Unknown barcodes can be registered on the fly. The input re-focuses after each scan for continuous intake.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180)): ?>
<?php $attributes = $__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180; ?>
<?php unset($__attributesOriginalb86a1c4f5b33e8f7759cb0ca50ac2180); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180)): ?>
<?php $component = $__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180; ?>
<?php unset($__componentOriginalb86a1c4f5b33e8f7759cb0ca50ac2180); ?>
<?php endif; ?>
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Barcode Scanner Input -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form id="receive-form" method="POST" action="<?php echo e(route('warehouse.store')); ?>">
                        <?php echo csrf_field(); ?>

                        <!-- Barcode Input -->
                        <div class="mb-4">
                            <label for="barcode-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Scan Barcode</label>
                            <input type="text" id="barcode-input" autocomplete="off" autofocus
                                class="w-full text-lg rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Scan or type barcode...">
                            <input type="hidden" name="barcode_scanned" id="barcode-scanned">
                            <input type="hidden" name="item_id" id="item-id">
                        </div>

                        <!-- Item Info (shown after barcode lookup) -->
                        <div id="item-info" class="hidden mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <p class="text-sm font-medium text-green-800 dark:text-green-300" id="item-name"></p>
                            <p class="text-xs text-green-600 dark:text-green-400" id="item-category"></p>
                        </div>

                        <!-- Unknown barcode prompt -->
                        <div id="unknown-info" class="hidden mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Unknown barcode. Select a category below.</p>
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select name="category_id" id="category_id" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Select category...</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?> (<?php echo e($cat->unit); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-4">
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="9999" required
                                class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>

                        <!-- Source -->
                        <div class="mb-4">
                            <label for="source" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Source</label>
                            <select name="source" id="source"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Not specified</option>
                                <option value="School Drive">School Drive</option>
                                <option value="Adopt-a-Tag">Adopt-a-Tag</option>
                                <option value="Community Donation">Community Donation</option>
                                <option value="Store Purchase">Store Purchase</option>
                            </select>
                        </div>

                        <!-- Donor Name -->
                        <div class="mb-4">
                            <label for="donor_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Donor Name <span class="text-gray-400">(optional)</span></label>
                            <input type="text" name="donor_name" id="donor_name" maxlength="200"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"
                                placeholder="e.g. Smith Family">
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes <span class="text-gray-400">(optional)</span></label>
                            <input type="text" name="notes" id="notes" maxlength="1000"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>

                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 text-white rounded-md hover:bg-green-500 text-sm font-medium transition">
                            Record Receipt
                        </button>
                    </form>
                </div>
            </div>

            <!-- Toast -->
            <div id="toast" class="hidden fixed bottom-6 right-6 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50 transition-opacity"></div>

            <!-- Session Running Total -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Session Total</h3>
                    <div id="session-totals" class="text-sm text-gray-500 dark:text-gray-400">
                        No items scanned yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sessionTotals = {};
        const barcodeInput = document.getElementById('barcode-input');
        const form = document.getElementById('receive-form');

        // Barcode lookup on Enter
        barcodeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.value.trim();
                if (!barcode) return;

                document.getElementById('barcode-scanned').value = barcode;

                fetch('<?php echo e(url("/warehouse/barcode")); ?>/' + encodeURIComponent(barcode), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.found) {
                        document.getElementById('item-id').value = data.item.id;
                        document.getElementById('item-name').textContent = data.item.name;
                        document.getElementById('item-category').textContent = data.item.category.name;
                        document.getElementById('item-info').classList.remove('hidden');
                        document.getElementById('unknown-info').classList.add('hidden');
                        document.getElementById('category_id').value = data.item.category_id;
                    } else if (data.external) {
                        document.getElementById('item-id').value = '';
                        const extName = data.external.name + (data.external.brand ? ' (' + data.external.brand + ')' : '');
                        document.getElementById('item-info').classList.add('hidden');
                        document.getElementById('unknown-info').classList.remove('hidden');
                        document.getElementById('unknown-info').querySelector('p').textContent =
                            'Found in UPC database: ' + extName + '. Select a category below to log it.';
                    } else {
                        document.getElementById('item-id').value = '';
                        document.getElementById('item-info').classList.add('hidden');
                        document.getElementById('unknown-info').classList.remove('hidden');
                        document.getElementById('unknown-info').querySelector('p').textContent =
                            'Unknown barcode. Select a category below.';
                    }
                })
                .catch(() => {
                    document.getElementById('unknown-info').classList.remove('hidden');
                });
            }
        });

        // AJAX form submit
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update session totals
                    const catName = data.transaction.category.name;
                    const qty = parseInt(formData.get('quantity')) || 1;
                    sessionTotals[catName] = (sessionTotals[catName] || 0) + qty;
                    updateTotalsDisplay();

                    // Show toast
                    showToast(qty + 'x ' + catName + ' received');

                    // Reset form for next scan
                    barcodeInput.value = '';
                    document.getElementById('barcode-scanned').value = '';
                    document.getElementById('item-id').value = '';
                    document.getElementById('quantity').value = '1';
                    document.getElementById('donor_name').value = '';
                    document.getElementById('notes').value = '';
                    document.getElementById('item-info').classList.add('hidden');
                    document.getElementById('unknown-info').classList.add('hidden');
                    barcodeInput.focus();
                }
            })
            .catch(() => showToast('Error saving. Please try again.', true));
        });

        function showToast(msg, isError) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50 text-white ' + (isError ? 'bg-red-600' : 'bg-green-600');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function updateTotalsDisplay() {
            const el = document.getElementById('session-totals');
            const lines = Object.entries(sessionTotals).map(([cat, qty]) => qty + 'x ' + cat);
            el.textContent = lines.join(' | ');
        }

        // Auto-focus barcode input
        barcodeInput.focus();
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
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/warehouse/receive.blade.php ENDPATH**/ ?>