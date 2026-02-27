<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Receive Items
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Barcode Scanner Input -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form id="receive-form" method="POST" action="{{ route('warehouse.store') }}">
                        @csrf

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
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }})</option>
                                @endforeach
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

                fetch('{{ url("/warehouse/barcode") }}/' + encodeURIComponent(barcode), {
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
                    } else {
                        document.getElementById('item-id').value = '';
                        document.getElementById('item-info').classList.add('hidden');
                        document.getElementById('unknown-info').classList.remove('hidden');
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
</x-app-layout>
