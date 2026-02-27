<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warehouse Kiosk - GFSD Food Drive</title>
    <script>document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-red-400">Warehouse Scanner</h1>
        <a href="{{ route('warehouse.index') }}" class="text-sm text-gray-400 hover:text-gray-200 transition">Exit Kiosk</a>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col items-center justify-center p-8 space-y-8">

        <!-- Barcode Input -->
        <div class="w-full max-w-lg">
            <input type="text" id="kiosk-barcode" autofocus autocomplete="off"
                class="w-full text-center text-3xl py-6 rounded-xl bg-gray-800 border-2 border-gray-600 text-gray-100 placeholder-gray-500 focus:border-green-500 focus:ring-green-500 transition"
                placeholder="Scan barcode...">
        </div>

        <!-- Feedback Area -->
        <div id="kiosk-feedback" class="w-full max-w-lg text-center text-lg min-h-[60px]"></div>

        <!-- Category Quick-Select -->
        <div class="w-full max-w-2xl">
            <p class="text-sm text-gray-500 mb-3 text-center">Or select a category:</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3" id="kiosk-categories">
                @foreach($categories as $cat)
                    <button data-category-id="{{ $cat->id }}" data-category-name="{{ $cat->name }}"
                        class="kiosk-cat-btn p-4 rounded-xl text-center transition font-medium text-sm
                            {{ $cat->type === 'food' ? 'bg-amber-900/40 border border-amber-700 hover:bg-amber-900/60 text-amber-200' : '' }}
                            {{ $cat->type === 'gift' ? 'bg-purple-900/40 border border-purple-700 hover:bg-purple-900/60 text-purple-200' : '' }}
                            {{ $cat->type === 'baby' ? 'bg-pink-900/40 border border-pink-700 hover:bg-pink-900/60 text-pink-200' : '' }}
                            {{ $cat->type === 'supply' ? 'bg-blue-900/40 border border-blue-700 hover:bg-blue-900/60 text-blue-200' : '' }}
                        ">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Last 5 Scans -->
        <div class="w-full max-w-lg">
            <h3 class="text-sm text-gray-500 mb-2">Recent Scans</h3>
            <div id="kiosk-recent" class="space-y-2 text-sm">
                <p class="text-gray-600">No scans yet.</p>
            </div>
        </div>

        <!-- Session Totals -->
        <div class="w-full max-w-lg bg-gray-800 rounded-xl p-4 border border-gray-700">
            <h3 class="text-sm text-gray-500 mb-2">Session Totals</h3>
            <div id="kiosk-totals" class="text-sm text-gray-400">0 items scanned</div>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const input = document.getElementById('kiosk-barcode');
        const feedback = document.getElementById('kiosk-feedback');
        const recentEl = document.getElementById('kiosk-recent');
        const totalsEl = document.getElementById('kiosk-totals');
        const totals = {};
        const recentScans = [];
        let selectedCategoryId = null;

        // Category button click
        document.querySelectorAll('.kiosk-cat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                selectedCategoryId = this.dataset.categoryId;
                const catName = this.dataset.categoryName;

                // Highlight selected
                document.querySelectorAll('.kiosk-cat-btn').forEach(b => b.classList.remove('ring-2', 'ring-green-500'));
                this.classList.add('ring-2', 'ring-green-500');

                // Submit directly
                submitReceipt(selectedCategoryId, catName, null);
            });
        });

        // Barcode scan (Enter key)
        input.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const barcode = this.value.trim();
            if (!barcode) return;

            // Look up barcode
            fetch('{{ url("/warehouse/barcode") }}/' + encodeURIComponent(barcode), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.found) {
                    submitReceipt(data.item.category_id, data.item.category.name, barcode, data.item.id);
                } else if (selectedCategoryId) {
                    submitReceipt(selectedCategoryId, null, barcode);
                } else {
                    showFeedback('Unknown barcode. Select a category first.', 'yellow');
                }
            })
            .catch(() => showFeedback('Lookup failed. Try again.', 'red'));

            this.value = '';
        });

        function submitReceipt(categoryId, catName, barcode, itemId) {
            const body = new FormData();
            body.append('category_id', categoryId);
            body.append('quantity', '1');
            if (barcode) body.append('barcode_scanned', barcode);
            if (itemId) body.append('item_id', itemId);

            fetch('{{ route("warehouse.store") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const name = data.transaction.category.name;
                    totals[name] = (totals[name] || 0) + 1;

                    recentScans.unshift({ name: name, barcode: barcode, time: new Date() });
                    if (recentScans.length > 5) recentScans.pop();

                    updateDisplay();
                    showFeedback('1x ' + name, 'green');
                }
            })
            .catch(() => showFeedback('Error saving.', 'red'));

            input.focus();
        }

        function showFeedback(msg, color) {
            const colors = { green: 'text-green-400', red: 'text-red-400', yellow: 'text-yellow-400' };
            feedback.className = 'w-full max-w-lg text-center text-lg min-h-[60px] font-medium ' + (colors[color] || 'text-gray-400');
            feedback.textContent = msg;
            setTimeout(() => { feedback.textContent = ''; feedback.className = 'w-full max-w-lg text-center text-lg min-h-[60px]'; }, 3000);
        }

        function updateDisplay() {
            // Totals
            const total = Object.values(totals).reduce((a, b) => a + b, 0);
            const lines = Object.entries(totals).map(([k, v]) => v + 'x ' + k);
            totalsEl.textContent = total + ' items: ' + lines.join(', ');

            // Recent
            if (recentScans.length) {
                recentEl.innerHTML = recentScans.map(s =>
                    '<div class="flex justify-between py-1 border-b border-gray-700/50">' +
                    '<span class="text-gray-300">' + s.name + (s.barcode ? ' <span class="text-gray-600">[' + s.barcode + ']</span>' : '') + '</span>' +
                    '<span class="text-gray-600 text-xs">' + s.time.toLocaleTimeString() + '</span>' +
                    '</div>'
                ).join('');
            }
        }

        // Keep focus on input
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('kiosk-cat-btn')) {
                input.focus();
            }
        });
        input.focus();
    </script>
</body>
</html>
