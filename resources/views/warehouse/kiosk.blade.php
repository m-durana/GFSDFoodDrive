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

    <!-- Main Content — sidebar layout -->
    <div class="flex-1 flex gap-0">

        {{-- Left: Scanner Area --}}
        <div class="flex-1 flex flex-col items-center justify-start p-8 space-y-6 overflow-y-auto">

            <!-- Barcode Input -->
            <div class="w-full max-w-lg">
                <input type="text" id="kiosk-barcode" autofocus autocomplete="off"
                    class="w-full text-center text-3xl py-6 rounded-xl bg-gray-800 border-2 border-gray-600 text-gray-100 placeholder-gray-500 focus:border-green-500 focus:ring-green-500 transition"
                    placeholder="Scan or type barcode...">
                <p class="text-xs text-gray-600 mt-1 text-center">Press Enter to submit. Keys (1-9, Q-P) to quick-select categories.</p>
            </div>

            <!-- Feedback Area -->
            <div id="kiosk-feedback" class="w-full max-w-lg text-center text-lg min-h-[60px]"></div>

            <!-- Category Quick-Select -->
            <div class="w-full max-w-2xl">
                <p class="text-sm text-gray-500 mb-3 text-center">Select a category (or press number key):</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3" id="kiosk-categories">
                    @php $shortcutKeys = ['1','2','3','4','5','6','7','8','9','q','w','e','r','t','y','u','i','o','p']; @endphp
                    @foreach($categories as $i => $cat)
                        <button data-category-id="{{ $cat->id }}" data-category-name="{{ $cat->name }}" data-shortcut="{{ $shortcutKeys[$i] ?? '' }}"
                            class="kiosk-cat-btn p-4 rounded-xl text-center transition font-medium text-sm relative
                                {{ $cat->type === 'food' ? 'bg-amber-900/40 border border-amber-700 hover:bg-amber-900/60 text-amber-200' : '' }}
                                {{ $cat->type === 'gift' ? 'bg-purple-900/40 border border-purple-700 hover:bg-purple-900/60 text-purple-200' : '' }}
                                {{ $cat->type === 'baby' ? 'bg-pink-900/40 border border-pink-700 hover:bg-pink-900/60 text-pink-200' : '' }}
                                {{ $cat->type === 'supply' ? 'bg-blue-900/40 border border-blue-700 hover:bg-blue-900/60 text-blue-200' : '' }}
                            ">
                            @if(isset($shortcutKeys[$i]))
                                <span class="absolute top-1 left-2 text-xs opacity-50">{{ $shortcutKeys[$i] }}</span>
                            @endif
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Recent Scans -->
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

        {{-- Right: Persistent Sidebar — Additional Details --}}
        <div id="details-sidebar" class="w-72 flex-shrink-0 bg-gray-800 border-l border-gray-700 p-4 space-y-4 overflow-y-auto sticky top-0 h-screen">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-300">Additional Details</h3>
                <span id="details-active-badge" class="hidden px-2 py-0.5 bg-green-600 text-white text-xs rounded-full">Active</span>
            </div>
            <p class="text-xs text-gray-500">These settings apply to all subsequent scans until cleared.</p>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                <input type="number" id="detail-quantity" value="1" min="1" max="9999"
                    class="w-full rounded-lg bg-gray-700 border border-gray-600 text-gray-100 text-sm py-1.5 px-3 focus:border-green-500 focus:ring-green-500">
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

            <button type="button" onclick="clearDetails()" class="w-full py-2 bg-gray-700 text-gray-400 rounded-lg text-xs font-medium hover:bg-gray-600 hover:text-gray-200 transition border border-gray-600">
                Clear All Details
            </button>
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
                selectCategory(this);
                submitReceipt(selectedCategoryId, this.dataset.categoryName, null);
            });
        });

        function selectCategory(btn) {
            selectedCategoryId = btn.dataset.categoryId;
            document.querySelectorAll('.kiosk-cat-btn').forEach(b => b.classList.remove('ring-2', 'ring-green-500'));
            btn.classList.add('ring-2', 'ring-green-500');
        }

        // Keyboard shortcuts: 1-9 then q,w,e,r,t,y,u,i,o,p for categories
        const shortcutKeys = ['1','2','3','4','5','6','7','8','9','q','w','e','r','t','y','u','i','o','p'];
        document.addEventListener('keydown', function(e) {
            // Only when barcode input is focused or no specific input is focused
            if (document.activeElement && document.activeElement !== input && document.activeElement.tagName === 'INPUT') return;
            const key = e.key.toLowerCase();
            if (shortcutKeys.includes(key)) {
                const btn = document.querySelector(`.kiosk-cat-btn[data-shortcut="${key}"]`);
                if (btn) {
                    e.preventDefault();
                    selectCategory(btn);
                    submitReceipt(selectedCategoryId, btn.dataset.categoryName, null);
                }
            }
        });

        function findCategoryBtnById(id) {
            return document.querySelector(`.kiosk-cat-btn[data-category-id="${id}"]`);
        }

        // Barcode scan (Enter key)
        input.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const barcode = this.value.trim();
            if (!barcode) return;

            fetch('{{ url("/warehouse/barcode") }}/' + encodeURIComponent(barcode), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.found) {
                    submitReceipt(data.item.category_id, data.item.category.name, barcode, data.item.id);
                } else if (data.error) {
                    showFeedback('OFF lookup failed: ' + data.error, 'red');
                } else if (data.external) {
                    const extName = data.external.name + (data.external.brand ? ' (' + data.external.brand + ')' : '');
                    // Use server-side category mapping
                    let catBtn = null;
                    if (data.external.suggested_category_id) {
                        catBtn = findCategoryBtnById(data.external.suggested_category_id);
                    }
                    if (catBtn) {
                        selectCategory(catBtn);
                        showFeedback(extName + ' → ' + catBtn.dataset.categoryName, 'green');
                        submitReceipt(selectedCategoryId, catBtn.dataset.categoryName, barcode);
                    } else if (selectedCategoryId) {
                        showFeedback(extName + ' → using selected category', 'green');
                        submitReceipt(selectedCategoryId, null, barcode);
                    } else {
                        showFeedback('Found: ' + extName + '. Select a category.', 'yellow');
                    }
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

        // Clear additional details
        function clearDetails() {
            document.getElementById('detail-quantity').value = 1;
            document.getElementById('detail-source').value = '';
            document.getElementById('detail-donor').value = '';
            document.getElementById('detail-notes').value = '';
            updateDetailsBadge();
            input.focus();
        }

        // Show/hide "Active" badge when details are set
        function updateDetailsBadge() {
            const badge = document.getElementById('details-active-badge');
            const sidebar = document.getElementById('details-sidebar');
            const hasDetails = document.getElementById('detail-source').value ||
                document.getElementById('detail-donor').value ||
                document.getElementById('detail-notes').value ||
                parseInt(document.getElementById('detail-quantity').value) > 1;
            badge.classList.toggle('hidden', !hasDetails);
            sidebar.style.borderLeftColor = hasDetails ? '#16a34a' : '';
            sidebar.style.borderLeftWidth = hasDetails ? '3px' : '';
        }

        ['detail-source', 'detail-donor', 'detail-notes', 'detail-quantity'].forEach(id => {
            document.getElementById(id).addEventListener('change', updateDetailsBadge);
            document.getElementById(id).addEventListener('input', updateDetailsBadge);
        });

        // Focus barcode input on page load
        input.focus();
    </script>
</body>
</html>
