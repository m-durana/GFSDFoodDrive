<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gift Box Scanner
            </h2>
            <a href="{{ route('warehouse.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                Back to Warehouse
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-4">

                <!-- Left: Child Selector -->
                <div class="lg:w-80 shrink-0 space-y-3" id="child-panel">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Select a Child</h4>
                        <input type="text" id="child-search" placeholder="Family # or gender/age..."
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm mb-2"
                            autofocus>
                        <div class="space-y-1.5 max-h-[60vh] overflow-y-auto" id="child-list">
                            @foreach($children as $child)
                                <button type="button"
                                    class="child-btn w-full text-left px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition text-xs"
                                    data-child-id="{{ $child->id }}"
                                    data-family-number="{{ $child->family->family_number ?? '' }}"
                                    data-family-name=""
                                    data-gender="{{ $child->gender ?? 'Unknown' }}"
                                    data-age="{{ $child->age ?? '?' }}"
                                    data-toy-ideas="{{ $child->toy_ideas ?? '' }}"
                                    data-gift-prefs="{{ $child->gift_preferences ?? '' }}"
                                    data-gifts-received="{{ $child->gifts_received ?? '' }}"
                                    data-gift-level="{{ $child->gift_level?->value ?? 0 }}"
                                    data-adopter="{{ $child->adopter_name ?? '' }}"
                                    data-dropped-off="{{ $child->gift_dropped_off ? '1' : '0' }}"
                                    data-search="{{ strtolower(($child->family->family_number ?? '') . ' ' . ($child->gender ?? '') . ' ' . ($child->age ?? '')) }}">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 dark:text-gray-100">#{{ $child->family->family_number ?? '?' }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $child->gender ?? '?' }}, {{ $child->age ?? '?' }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right: Gift Box Scanner -->
                <div class="flex-1">
                    <!-- No child selected state -->
                    <div id="no-child-state" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-12 text-center">
                        <p class="text-lg text-gray-400 dark:text-gray-500">Select a child from the list to start scanning gift items</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">You can also type a family number in the search box</p>
                        <div class="mt-6">
                            <button onclick="openGeneralDonation()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-500 text-sm font-medium transition">
                                Record General Gift Donation
                            </button>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">For gift donations not linked to a specific child</p>
                        </div>
                    </div>

                    <!-- General donation modal — enhanced with Gift Bank metadata -->
                    <div id="general-donation-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">General Gift Donation</h3>
                            <form method="POST" action="{{ route('warehouse.gift-bank.store') }}">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Description <span class="text-red-500">*</span></label>
                                        <input type="text" name="description" required placeholder="e.g., Assorted toys, Board games..."
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Age Range</label>
                                            <select name="age_range" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                <option value="any">Any age</option>
                                                <option value="0-5">0-5</option>
                                                <option value="6-12">6-12</option>
                                                <option value="13-17">13-17</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gender</label>
                                            <select name="gender_suitability" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                <option value="neutral">Neutral</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gift Type</label>
                                            <input type="text" name="gift_type" placeholder="e.g., Toy, Book..."
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Quantity</label>
                                            <input type="number" name="quantity" value="1" min="1"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Donor Name (optional)</label>
                                        <input type="text" name="donor_name" placeholder="Anonymous"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2 mt-5">
                                    <button type="button" onclick="closeGeneralDonation()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-500 text-sm font-medium">Add to Gift Bank</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Active scan mode -->
                    <div id="scan-mode" class="hidden space-y-4">
                        <!-- Child info banner -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-purple-900 dark:text-purple-200">
                                        Gift Box for <span id="active-child-label">—</span>
                                    </h3>
                                    <p class="text-sm text-purple-700 dark:text-purple-300 mt-1" id="active-child-details"></p>
                                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-1" id="active-child-prefs"></p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="finishBox()" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-500 transition">
                                        Finish Box
                                    </button>
                                    <button onclick="nextChild()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-500 transition">
                                        Next Child
                                    </button>
                                    <button onclick="deselectChild()" class="px-3 py-1.5 bg-gray-500 text-white text-xs font-medium rounded-md hover:bg-gray-400 transition">
                                        Back to General
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Barcode input -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                            <div class="flex gap-2">
                                <input type="text" id="barcode-input" placeholder="Scan barcode or type product name..."
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-purple-500 focus:ring-purple-500">
                                <button onclick="addManualItem()" class="px-4 py-2 bg-gray-600 text-white text-xs font-medium rounded-md hover:bg-gray-500 transition">
                                    Add Manual
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Scan a UPC barcode to auto-identify the item, or type a description and click "Add Manual"</p>
                            <div id="lookup-status" class="text-xs mt-2 hidden"></div>
                        </div>

                        <!-- Current box items -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Items in This Box <span id="item-count" class="text-purple-600 dark:text-purple-400">(0)</span>
                                </h4>
                                <button onclick="clearBox()" class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400">Clear All</button>
                            </div>
                            <div id="box-items" class="space-y-2">
                                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4" id="empty-box-msg">No items scanned yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 z-[2000] hidden">
        <div class="px-4 py-2 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        let activeChildId = null;
        let activeChildData = null;
        let boxItems = [];

        // Child search filter
        document.getElementById('child-search').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.child-btn').forEach(btn => {
                btn.style.display = btn.dataset.search.includes(q) ? '' : 'none';
            });
        });

        // Child selection
        document.querySelectorAll('.child-btn').forEach(btn => {
            btn.addEventListener('click', () => selectChild(btn));
        });

        function selectChild(btn) {
            // If there are unsaved items, confirm
            if (boxItems.length > 0 && activeChildId && activeChildId !== btn.dataset.childId) {
                if (!confirm('You have unsaved items in the current box. Switch child anyway?')) return;
                boxItems = [];
                renderBoxItems();
            }

            // Highlight
            document.querySelectorAll('.child-btn').forEach(b => b.classList.remove('ring-2', 'ring-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20'));
            btn.classList.add('ring-2', 'ring-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');

            activeChildId = btn.dataset.childId;
            activeChildData = { ...btn.dataset };

            // Update banner
            document.getElementById('active-child-label').textContent =
                `#${btn.dataset.familyNumber} — ${btn.dataset.gender}, age ${btn.dataset.age}`;
            document.getElementById('active-child-details').textContent =
                btn.dataset.adopter ? `Adopter: ${btn.dataset.adopter}` : '';

            const prefs = [];
            if (btn.dataset.toyIdeas) prefs.push(`Toy ideas: ${btn.dataset.toyIdeas}`);
            if (btn.dataset.giftPrefs) prefs.push(`Preferences: ${btn.dataset.giftPrefs}`);
            document.getElementById('active-child-prefs').textContent = prefs.join(' | ') || 'No preferences listed';

            // Show scan mode
            document.getElementById('no-child-state').classList.add('hidden');
            document.getElementById('scan-mode').classList.remove('hidden');

            // Focus barcode input
            document.getElementById('barcode-input').focus();
        }

        // Barcode scanning — Enter triggers lookup
        const barcodeInput = document.getElementById('barcode-input');
        barcodeInput.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const val = this.value.trim();
            if (!val) return;

            // Check if it looks like a barcode (all digits, 6-14 chars)
            if (/^\d{6,14}$/.test(val)) {
                lookupBarcode(val);
            } else {
                // Treat as manual description
                addItemToBox({ name: val, barcode: null, image: null, source: 'manual' });
                this.value = '';
            }
        });

        function lookupBarcode(barcode) {
            const status = document.getElementById('lookup-status');
            status.classList.remove('hidden');
            status.className = 'text-xs mt-2 text-blue-600 dark:text-blue-400';
            status.textContent = 'Looking up barcode...';

            fetch(`{{ url('/warehouse/barcode') }}/${barcode}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.found && data.item) {
                    addItemToBox({
                        name: data.item.name,
                        barcode: barcode,
                        category: data.item.category?.name,
                        image: null,
                        source: 'local'
                    });
                    status.className = 'text-xs mt-2 text-green-600 dark:text-green-400';
                    status.textContent = `Found: ${data.item.name}`;
                } else if (data.external) {
                    addItemToBox({
                        name: data.external.name || 'Unknown Product',
                        barcode: barcode,
                        category: data.external.suggested_category,
                        image: data.external.image_url || null,
                        source: 'off'
                    });
                    status.className = 'text-xs mt-2 text-green-600 dark:text-green-400';
                    status.textContent = `Found (OFF): ${data.external.name || 'Unknown'}`;
                } else {
                    // Not found — add with barcode as identifier
                    status.className = 'text-xs mt-2 text-yellow-600 dark:text-yellow-400';
                    status.textContent = `Barcode ${barcode} not found. Added as unknown item.`;
                    addItemToBox({
                        name: `Unknown (${barcode})`,
                        barcode: barcode,
                        image: null,
                        source: 'unknown'
                    });
                }
                barcodeInput.value = '';
                barcodeInput.focus();
                setTimeout(() => status.classList.add('hidden'), 3000);
            })
            .catch(() => {
                status.className = 'text-xs mt-2 text-red-600 dark:text-red-400';
                status.textContent = 'Lookup failed. Try again or add manually.';
                barcodeInput.focus();
                setTimeout(() => status.classList.add('hidden'), 3000);
            });
        }

        function addManualItem() {
            const val = barcodeInput.value.trim();
            if (!val) return;
            addItemToBox({ name: val, barcode: null, image: null, source: 'manual' });
            barcodeInput.value = '';
            barcodeInput.focus();
        }

        function addItemToBox(item) {
            item.id = Date.now() + Math.random();
            boxItems.push(item);
            renderBoxItems();
        }

        function removeItem(itemId) {
            boxItems = boxItems.filter(i => i.id !== itemId);
            renderBoxItems();
        }

        function clearBox() {
            if (boxItems.length && !confirm('Clear all items from this box?')) return;
            boxItems = [];
            renderBoxItems();
        }

        function renderBoxItems() {
            const container = document.getElementById('box-items');
            const countEl = document.getElementById('item-count');
            const emptyMsg = document.getElementById('empty-box-msg');

            countEl.textContent = `(${boxItems.length})`;

            if (boxItems.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4" id="empty-box-msg">No items scanned yet</p>';
                return;
            }

            container.innerHTML = boxItems.map((item, idx) => `
                <div class="flex items-center gap-3 px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-md">
                    ${item.image ? `<img src="${item.image}" class="w-8 h-8 object-contain rounded" alt="">` : `<div class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center text-xs text-gray-400">${idx + 1}</div>`}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">${escHtml(item.name)}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            ${item.barcode ? `UPC: ${item.barcode}` : 'Manual entry'}
                            ${item.category ? ` | ${escHtml(item.category)}` : ''}
                        </p>
                    </div>
                    <button onclick="removeItem(${item.id})" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                </div>
            `).join('');
        }

        function escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function finishBox() {
            if (!activeChildId) return;

            const giftsText = boxItems.map(i => i.name).join(', ');

            // Confirm gift drop-off via existing endpoint
            fetch(`{{ url('/warehouse/gift-dropoff') }}/${activeChildId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    gifts_received: giftsText || null,
                    items: boxItems.map(i => ({ name: i.name, barcode: i.barcode || null })),
                    _token: csrfToken
                })
            })
            .then(r => {
                if (!r.ok) throw new Error('Failed');
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(`Box saved for #${activeChildData.familyNumber} (${boxItems.length} items)`, 'green');

                    // Mark child as done in sidebar
                    const btn = document.querySelector(`.child-btn[data-child-id="${activeChildId}"]`);
                    if (btn) {
                        btn.classList.add('opacity-50');
                        btn.dataset.droppedOff = '1';
                    }

                    // Reset
                    boxItems = [];
                    renderBoxItems();
                    nextChild();
                }
            })
            .catch(() => {
                showToast('Failed to save box. Try again.', 'red');
            });
        }

        function deselectChild() {
            if (boxItems.length > 0) {
                if (!confirm('You have unsaved items in the current box. Deselect child anyway?')) return;
            }
            boxItems = [];
            activeChildId = null;
            activeChildData = null;

            // Remove highlight from all child buttons
            document.querySelectorAll('.child-btn').forEach(b => b.classList.remove('ring-2', 'ring-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20'));

            // Show no-child state, hide scan mode
            document.getElementById('scan-mode').classList.add('hidden');
            document.getElementById('no-child-state').classList.remove('hidden');
        }

        function nextChild() {
            if (boxItems.length > 0) {
                if (!confirm('You have unsaved items. Move to the next child anyway?')) return;
            }

            boxItems = [];
            renderBoxItems();

            // Find next visible child that hasn't been dropped off
            const allBtns = [...document.querySelectorAll('.child-btn')];
            const currentIdx = allBtns.findIndex(b => b.dataset.childId === activeChildId);
            const nextBtn = allBtns.slice(currentIdx + 1).find(b =>
                b.style.display !== 'none' && b.dataset.droppedOff !== '1'
            );

            if (nextBtn) {
                selectChild(nextBtn);
                nextBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                showToast('No more children to process!', 'blue');
                activeChildId = null;
                document.getElementById('scan-mode').classList.add('hidden');
                document.getElementById('no-child-state').classList.remove('hidden');
                document.getElementById('no-child-state').innerHTML =
                    '<p class="text-lg text-green-600 dark:text-green-400 font-medium">All done! No more children pending.</p>';
            }
        }

        function showToast(msg, color = 'green') {
            const t = document.getElementById('toast');
            const inner = t.querySelector('div');
            inner.className = `bg-${color}-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium`;
            inner.textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        // General donation functions
        function openGeneralDonation() {
            document.getElementById('general-donation-modal').classList.remove('hidden');
        }
        function closeGeneralDonation() {
            document.getElementById('general-donation-modal').classList.add('hidden');
        }

        // Auto-select first child if search matches exactly one on Enter
        document.getElementById('child-search').addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const visible = [...document.querySelectorAll('.child-btn')].filter(b => b.style.display !== 'none');
            if (visible.length === 1) {
                selectChild(visible[0]);
            } else if (visible.length > 0) {
                selectChild(visible[0]);
            }
        });
    </script>
</x-app-layout>
