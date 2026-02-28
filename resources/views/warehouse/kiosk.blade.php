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

    @guest
    <!-- Volunteer Name Prompt (shown once per session, only for non-logged-in users) -->
    <div id="volunteer-prompt" class="fixed inset-0 bg-gray-900/95 z-50 flex items-center justify-center" style="display:none;">
        <div class="bg-gray-800 rounded-xl p-8 max-w-md w-full border border-gray-700 text-center">
            <h2 class="text-xl font-bold text-gray-100 mb-2">Welcome, Volunteer!</h2>
            <p class="text-sm text-gray-400 mb-6">Enter your name so we can track who scanned what.</p>
            <input type="text" id="volunteer-name-input" autofocus
                class="w-full text-center text-lg py-3 rounded-lg bg-gray-700 border border-gray-600 text-gray-100 placeholder-gray-500 focus:border-green-500 focus:ring-green-500"
                placeholder="Your name">
            <button onclick="saveVolunteerName()" class="mt-4 w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-500 transition">
                Start Scanning
            </button>
        </div>
    </div>
    @endguest

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

        <!-- Optional Details (collapsible) -->
        <div class="w-full max-w-lg">
            <button type="button" id="details-toggle" onclick="document.getElementById('details-panel').classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180');"
                class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-300 transition mb-2">
                <svg class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                Additional Details (optional)
            </button>
            <div id="details-panel" class="hidden space-y-3 bg-gray-800 rounded-xl p-4 border border-gray-700">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                    <input type="number" id="detail-quantity" value="1" min="1" max="9999"
                        class="w-24 rounded-lg bg-gray-700 border border-gray-600 text-gray-100 text-sm py-1.5 px-3 focus:border-green-500 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Source</label>
                    <select id="detail-source" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-gray-100 text-sm py-1.5 px-3 focus:border-green-500 focus:ring-green-500">
                        <option value="">Not specified</option>
                        <option value="School Drive">School Drive</option>
                        <option value="Adopt-a-Tag">Adopt-a-Tag</option>
                        <option value="Community Donation">Community Donation</option>
                        <option value="Store Purchase">Store Purchase</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Donor Name</label>
                    <input type="text" id="detail-donor" maxlength="200"
                        class="w-full rounded-lg bg-gray-700 border border-gray-600 text-gray-100 text-sm py-1.5 px-3 focus:border-green-500 focus:ring-green-500"
                        placeholder="e.g. Smith Family">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Notes</label>
                    <input type="text" id="detail-notes" maxlength="1000"
                        class="w-full rounded-lg bg-gray-700 border border-gray-600 text-gray-100 text-sm py-1.5 px-3 focus:border-green-500 focus:ring-green-500">
                </div>
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
        @auth
        let volunteerName = @json(Auth::user()->name);
        @else
        let volunteerName = sessionStorage.getItem('kiosk_volunteer_name') || '';

        // Show volunteer name prompt if not set
        if (!volunteerName) {
            document.getElementById('volunteer-prompt').style.display = '';
            document.getElementById('volunteer-name-input').focus();
        }

        function saveVolunteerName() {
            const name = document.getElementById('volunteer-name-input').value.trim();
            if (!name) return;
            volunteerName = name;
            sessionStorage.setItem('kiosk_volunteer_name', name);
            document.getElementById('volunteer-prompt').style.display = 'none';
            input.focus();
        }

        document.getElementById('volunteer-name-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); saveVolunteerName(); }
        });
        @endauth

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
                } else if (data.external) {
                    const extName = data.external.name + (data.external.brand ? ' (' + data.external.brand + ')' : '');
                    showFeedback('Found: ' + extName + '. Select a category to log it.', 'yellow');
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
            const qty = document.getElementById('detail-quantity')?.value || '1';
            const source = document.getElementById('detail-source')?.value || '';
            const donor = document.getElementById('detail-donor')?.value || '';
            const notes = document.getElementById('detail-notes')?.value || '';

            const body = new FormData();
            body.append('category_id', categoryId);
            body.append('quantity', qty);
            if (barcode) body.append('barcode_scanned', barcode);
            if (itemId) body.append('item_id', itemId);
            if (volunteerName) body.append('volunteer_name', volunteerName);
            if (source) body.append('source', source);
            if (donor) body.append('donor_name', donor);
            if (notes) body.append('notes', notes);

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

        // Focus barcode input on page load (not on every click - that steals focus from other inputs)
        input.focus();
    </script>
</body>
</html>
