@if($mode === 'packing' && $packingList)
    {{-- Standalone mobile packing view — no app layout, QR token auth --}}
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Pack - Family #{{ $packingList->family?->family_number }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <style>
            body { overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
            .item-packed { opacity: 0.5; }
            .flash-green { animation: flashGreen 0.5s ease; }
            .flash-red { animation: flashRed 0.5s ease; }
            @keyframes flashGreen { 0%,100% { background: inherit; } 50% { background: #dcfce7; } }
            @keyframes flashRed { 0%,100% { background: inherit; } 50% { background: #fecaca; } }
            #reader { width: 100%; }
            #reader video { border-radius: 0.5rem; }
        </style>
    </head>
    @php
        $itemsJson = $packingList->items->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'category' => $item->category?->name,
                'category_type' => $item->category?->type,
                'quantity_needed' => $item->quantity_needed,
                'quantity_packed' => $item->quantity_packed,
                'status' => $item->status->value,
                'child_id' => $item->child_id,
                'sort_order' => $item->sort_order,
            ];
        })->values();
    @endphp
    <body class="bg-gray-50 min-h-screen" x-data="packingApp()" x-init="init()">
        {{-- Header --}}
        <div class="bg-white border-b sticky top-0 z-40 px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">
                        Family #{{ $packingList->family?->family_number }}
                    </h1>
                    <p class="text-xs text-gray-500">{{ $packingList->items->count() }} items to pack</p>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium" :class="statusColor" x-text="statusLabel"></div>
                    <div class="text-xs text-gray-500">
                        <span x-text="packedCount"></span>/<span x-text="totalCount"></span> items
                    </div>
                </div>
            </div>
            {{-- Progress bar --}}
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + progressPct + '%'"></div>
            </div>
        </div>

        {{-- Mode Toggle --}}
        <div class="bg-white border-b px-4 py-2 flex gap-2">
            <button @click="mode = 'quick'"
                    :class="mode === 'quick' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition">
                Quick Pack
            </button>
            <button @click="toggleScanner()"
                    :class="mode === 'scan' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition">
                Scan Mode
            </button>
        </div>

        {{-- Scanner Area --}}
        <div x-show="mode === 'scan'" x-transition class="px-4 py-3">
            <div id="reader" class="rounded-lg overflow-hidden"></div>
            <div class="mt-2 flex gap-2">
                <input type="text" x-model="manualBarcode" placeholder="Enter barcode manually..."
                       @keydown.enter="scanBarcode(manualBarcode); manualBarcode = ''"
                       class="flex-1 text-sm border rounded-lg px-3 py-2">
                <button @click="scanBarcode(manualBarcode); manualBarcode = ''"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Go
                </button>
            </div>
        </div>

        {{-- Scan result toast --}}
        <div x-show="toast.show" x-transition
             :class="toast.success ? 'bg-green-500' : 'bg-red-500'"
             class="fixed top-16 left-4 right-4 z-50 text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-center"
             x-text="toast.message">
        </div>

        {{-- Items List --}}
        <div class="px-4 py-3 space-y-2 pb-24">
            {{-- Food Items --}}
            <template x-if="foodItems.length > 0">
                <div>
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        Food Items (<span x-text="foodItems.length"></span>)
                    </h2>
                    <template x-for="item in foodItems" :key="item.id">
                        <div :class="isPacked(item) ? 'item-packed' : (item.status === 'unfulfilled' ? 'border-red-200 bg-red-50' : '')"
                             :id="'item-' + item.id"
                             class="bg-white rounded-lg border px-3 py-2.5 flex items-center gap-3">
                            <button @click="quickPackItem(item)"
                                    :disabled="isPacked(item) || item.status === 'unfulfilled'"
                                    class="flex-shrink-0 w-7 h-7 rounded-full border-2 flex items-center justify-center transition"
                                    :class="isPacked(item) ? 'bg-green-500 border-green-500 text-white' : (item.status === 'unfulfilled' ? 'border-red-300 bg-red-100' : 'border-gray-300 hover:border-blue-400')">
                                <svg x-show="isPacked(item)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span x-show="item.status === 'unfulfilled' && !isPacked(item)" class="text-red-400 text-xs font-bold">!</span>
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="item.description"></p>
                                <p class="text-xs text-gray-500" x-text="item.category || ''"></p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button x-show="!isPacked(item)" @click="openSubstitutionDrawer(item)"
                                        class="text-xs text-yellow-600 hover:text-yellow-700 font-medium px-1.5 py-0.5 border border-yellow-300 rounded">
                                    Sub
                                </button>
                                <div class="text-sm font-medium text-right"
                                     :class="item.quantity_packed >= item.quantity_needed ? 'text-green-600' : 'text-gray-500'">
                                    <span x-text="item.quantity_packed"></span>/<span x-text="item.quantity_needed"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Gift Items --}}
            <template x-if="giftItems.length > 0">
                <div>
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4">
                        Gifts (<span x-text="giftItems.length"></span>)
                    </h2>
                    <template x-for="item in giftItems" :key="item.id">
                        <div :class="isPacked(item) ? 'item-packed' : (item.status === 'unfulfilled' ? 'border-red-200 bg-red-50' : '')"
                             :id="'item-' + item.id"
                             class="bg-white rounded-lg border px-3 py-2.5 flex items-center gap-3">
                            <button @click="quickPackItem(item)"
                                    :disabled="isPacked(item) || item.status === 'unfulfilled'"
                                    class="flex-shrink-0 w-7 h-7 rounded-full border-2 flex items-center justify-center transition"
                                    :class="isPacked(item) ? 'bg-green-500 border-green-500 text-white' : (item.status === 'unfulfilled' ? 'border-red-300 bg-red-100' : 'border-gray-300 hover:border-blue-400')">
                                <svg x-show="isPacked(item)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span x-show="item.status === 'unfulfilled' && !isPacked(item)" class="text-red-400 text-xs font-bold">!</span>
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="item.description"></p>
                                <span x-show="item.status === 'unfulfilled'" class="text-xs text-red-600 font-medium">Unfulfilled</span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button x-show="!isPacked(item)" @click="openSubstitutionDrawer(item)"
                                        class="text-xs text-yellow-600 hover:text-yellow-700 font-medium px-1.5 py-0.5 border border-yellow-300 rounded">
                                    Sub
                                </button>
                                <div class="text-sm font-medium text-right"
                                     :class="item.quantity_packed >= item.quantity_needed ? 'text-green-600' : 'text-gray-500'">
                                    <span x-text="item.quantity_packed"></span>/<span x-text="item.quantity_needed"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Baby Items --}}
            <template x-if="babyItems.length > 0">
                <div>
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4">
                        Baby Supplies (<span x-text="babyItems.length"></span>)
                    </h2>
                    <template x-for="item in babyItems" :key="item.id">
                        <div :class="isPacked(item) ? 'item-packed' : ''"
                             :id="'item-' + item.id"
                             class="bg-white rounded-lg border px-3 py-2.5 flex items-center gap-3">
                            <button @click="quickPackItem(item)"
                                    :disabled="isPacked(item)"
                                    class="flex-shrink-0 w-7 h-7 rounded-full border-2 flex items-center justify-center transition"
                                    :class="isPacked(item) ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-blue-400'">
                                <svg x-show="isPacked(item)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="item.description"></p>
                            </div>
                            <div class="text-sm font-medium text-right flex-shrink-0"
                                 :class="item.quantity_packed >= item.quantity_needed ? 'text-green-600' : 'text-gray-500'">
                                <span x-text="item.quantity_packed"></span>/<span x-text="item.quantity_needed"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Substitution Drawer Backdrop --}}
        <div x-show="substitution.active" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="closeSubstitutionDrawer()"
             class="fixed inset-0 bg-black/30 z-40"></div>

        {{-- Substitution Drawer --}}
        <div x-show="substitution.active" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-2xl border-t max-h-[80vh] overflow-y-auto">
            <div class="px-4 py-3 border-b flex justify-between items-center sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="font-semibold text-gray-900">Substitute Item</h3>
                <button @click="closeSubstitutionDrawer()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-4 py-3">
                <p class="text-sm text-gray-500 mb-1">Original item:</p>
                <p class="text-sm font-medium text-gray-900 mb-3" x-text="substitution.item?.description"></p>

                <template x-if="substitution.loading">
                    <div class="text-center py-6 text-gray-400 text-sm">Loading candidates...</div>
                </template>
                <template x-if="!substitution.loading && substitution.candidates.length === 0">
                    <div class="text-center py-6 text-gray-400 text-sm">No substitute candidates found in this category.</div>
                </template>
                <template x-if="!substitution.loading && substitution.candidates.length > 0">
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Select a substitute:</p>
                        <div class="space-y-2 mb-4">
                            <template x-for="candidate in substitution.candidates" :key="candidate.id">
                                <label class="flex items-center gap-3 p-2.5 border rounded-lg cursor-pointer transition"
                                       :class="substitution.selectedId === candidate.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" :value="candidate.id" x-model.number="substitution.selectedId"
                                           class="text-blue-600 focus:ring-blue-500">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="candidate.name"></p>
                                        <p class="text-xs text-gray-400" x-text="candidate.barcode || 'No barcode'"></p>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-red-500">*</span></label>
                    <textarea x-model="substitution.notes" rows="2" placeholder="Why this substitution?"
                              class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <button @click="confirmSubstitution()"
                        :disabled="!substitution.notes.trim() || substitution.loading"
                        class="w-full py-3 rounded-lg font-semibold text-sm transition"
                        :class="substitution.notes.trim() ? 'bg-yellow-500 text-white hover:bg-yellow-600' : 'bg-gray-200 text-gray-400'">
                    Confirm Substitution
                </button>
            </div>
        </div>

        {{-- Bottom Action Bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t px-4 py-3 z-30">
            <button @click="markComplete()"
                    :disabled="!canComplete"
                    class="w-full py-3 rounded-lg font-semibold text-sm transition"
                    :class="canComplete ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-200 text-gray-400'">
                <span x-show="!canComplete">Pack all items to complete</span>
                <span x-show="canComplete">Mark Complete</span>
            </button>
        </div>

        <script>
        function packingApp() {
            return {
                listId: {{ $packingList->id }},
                token: '{{ $token }}',
                mode: 'quick',
                manualBarcode: '',
                scanner: null,
                scannerActive: false,
                items: @json($itemsJson),
                toast: { show: false, message: '', success: true },
                substitution: { active: false, item: null, candidates: [], selectedId: null, notes: '', loading: false },
                audioCtx: null,

                init() {
                    // Refresh data from API periodically
                    setInterval(() => this.refreshData(), 30000);
                    // Initialize audio context on first user interaction
                    document.addEventListener('click', () => {
                        if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }, { once: true });
                },

                playBeep(success) {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const ctx = this.audioCtx;
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    gain.gain.value = 0.3;
                    if (success) {
                        osc.frequency.value = 880;
                        osc.type = 'sine';
                        osc.start();
                        osc.stop(ctx.currentTime + 0.15);
                    } else {
                        osc.frequency.value = 300;
                        osc.type = 'square';
                        osc.start();
                        osc.stop(ctx.currentTime + 0.3);
                    }
                },

                get foodItems() {
                    return this.items.filter(i => !i.child_id && i.category_type !== 'baby').sort((a, b) => a.sort_order - b.sort_order);
                },
                get giftItems() {
                    return this.items.filter(i => i.child_id !== null).sort((a, b) => a.sort_order - b.sort_order);
                },
                get babyItems() {
                    return this.items.filter(i => !i.child_id && i.category_type === 'baby').sort((a, b) => a.sort_order - b.sort_order);
                },
                get packedCount() {
                    return this.items.filter(i => this.isPacked(i)).length;
                },
                get totalCount() {
                    return this.items.length;
                },
                get progressPct() {
                    return this.totalCount > 0 ? Math.round((this.packedCount / this.totalCount) * 100) : 0;
                },
                get canComplete() {
                    return this.items.every(i => this.isPacked(i) || i.status === 'unfulfilled');
                },
                get statusLabel() {
                    if (this.progressPct === 0) return 'Not Started';
                    if (this.canComplete) return 'Ready to Complete';
                    return 'In Progress';
                },
                get statusColor() {
                    if (this.progressPct === 0) return 'text-gray-500';
                    if (this.canComplete) return 'text-green-600';
                    return 'text-blue-600';
                },

                isPacked(item) {
                    return ['packed', 'verified', 'substituted'].includes(item.status);
                },

                showToast(message, success = true) {
                    this.toast = { show: true, message, success };
                    setTimeout(() => this.toast.show = false, 2500);
                },

                async quickPackItem(item) {
                    if (this.isPacked(item) || item.status === 'unfulfilled') return;

                    try {
                        const res = await fetch(`/api/packing/${this.listId}/item/${item.id}/pack`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.playBeep(true);
                            item.quantity_packed = Math.min(item.quantity_packed + 1, item.quantity_needed);
                            if (item.quantity_packed >= item.quantity_needed) {
                                item.status = 'packed';
                            }
                            if (data.warning) {
                                this.showToast(data.message, true);
                            } else {
                                const el = document.getElementById('item-' + item.id);
                                if (el) { el.classList.add('flash-green'); setTimeout(() => el.classList.remove('flash-green'), 500); }
                            }
                        } else {
                            this.playBeep(false);
                            this.showToast(data.message || 'Failed to pack item', false);
                        }
                    } catch (e) {
                        this.playBeep(false);
                        this.showToast('Network error', false);
                    }
                },

                async scanBarcode(barcode) {
                    if (!barcode || !barcode.trim()) return;

                    try {
                        const res = await fetch(`/api/packing/${this.listId}/scan`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ barcode: barcode.trim() }),
                        });
                        const data = await res.json();

                        if (data.match) {
                            this.playBeep(true);
                            this.showToast(data.scanned_item + ' packed!', true);
                            // Update local item state
                            if (data.item) {
                                const localItem = this.items.find(i => i.id === data.item.id);
                                if (localItem) {
                                    localItem.quantity_packed = data.item.quantity_packed;
                                    localItem.status = data.item.status;
                                    const el = document.getElementById('item-' + localItem.id);
                                    if (el) { el.classList.add('flash-green'); setTimeout(() => el.classList.remove('flash-green'), 500); }
                                }
                            }
                        } else {
                            this.playBeep(false);
                            let msg = data.message || 'No match found';
                            if (data.suggestion) {
                                msg += ' ' + data.suggestion.message;
                                // Auto-open substitution drawer with the scanned item pre-selected
                                const suggestedItem = this.items.find(i => i.id === data.suggestion.item_id);
                                if (suggestedItem) {
                                    this.openSubstitutionDrawer(suggestedItem, data.suggestion.substitute_id);
                                }
                            }
                            this.showToast(msg, false);
                        }
                    } catch (e) {
                        this.showToast('Network error', false);
                    }
                },

                async openSubstitutionDrawer(item, preSelectedId = null) {
                    this.substitution = { active: true, item, candidates: [], selectedId: preSelectedId, notes: '', loading: true };
                    try {
                        const res = await fetch(`/api/packing/${this.listId}/item/${item.id}/substitutes`);
                        const data = await res.json();
                        this.substitution.candidates = data;
                        if (preSelectedId && data.some(c => c.id === preSelectedId)) {
                            this.substitution.selectedId = preSelectedId;
                        }
                    } catch (e) {
                        this.showToast('Failed to load substitutes', false);
                    }
                    this.substitution.loading = false;
                },

                closeSubstitutionDrawer() {
                    this.substitution.active = false;
                },

                async confirmSubstitution() {
                    if (!this.substitution.notes.trim() || !this.substitution.item) return;

                    try {
                        const body = { notes: this.substitution.notes };
                        if (this.substitution.selectedId) {
                            body.new_item_id = this.substitution.selectedId;
                        }
                        const res = await fetch(`/api/packing/${this.listId}/item/${this.substitution.item.id}/substitute`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(body),
                        });
                        const data = await res.json();

                        if (data.success) {
                            const localItem = this.items.find(i => i.id === this.substitution.item.id);
                            if (localItem) {
                                localItem.status = 'substituted';
                                localItem.quantity_packed = localItem.quantity_needed;
                                if (data.item?.description) localItem.description = data.item.description;
                            }
                            this.showToast('Substitution recorded!', true);
                            this.closeSubstitutionDrawer();
                        } else {
                            this.showToast(data.message || 'Substitution failed', false);
                        }
                    } catch (e) {
                        this.showToast('Network error', false);
                    }
                },

                toggleScanner() {
                    if (this.mode === 'scan') {
                        this.stopScanner();
                        this.mode = 'quick';
                    } else {
                        this.mode = 'scan';
                        this.$nextTick(() => this.startScanner());
                    }
                },

                startScanner() {
                    if (this.scannerActive) return;

                    if (typeof Html5Qrcode === 'undefined') {
                        this.showToast('Scanner library not loaded', false);
                        return;
                    }

                    this.scanner = new Html5Qrcode('reader');
                    this.scanner.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 150 } },
                        (decodedText) => {
                            this.scanBarcode(decodedText);
                            // Brief pause to avoid rapid-fire scans
                            this.scanner.pause(true);
                            setTimeout(() => {
                                if (this.scannerActive) this.scanner.resume();
                            }, 1500);
                        },
                        () => {} // ignore errors (no code in frame)
                    ).then(() => {
                        this.scannerActive = true;
                    }).catch(err => {
                        this.showToast('Camera access denied or unavailable', false);
                        this.mode = 'quick';
                    });
                },

                stopScanner() {
                    if (this.scanner && this.scannerActive) {
                        this.scanner.stop().then(() => {
                            this.scannerActive = false;
                        }).catch(() => {
                            this.scannerActive = false;
                        });
                    }
                },

                async markComplete() {
                    if (!this.canComplete) return;

                    try {
                        const res = await fetch(`/api/packing/${this.listId}/complete`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.showToast('Packing complete! Ready for verification.', true);
                        } else {
                            this.showToast(data.message || 'Cannot complete yet', false);
                        }
                    } catch (e) {
                        this.showToast('Network error', false);
                    }
                },

                async refreshData() {
                    try {
                        const res = await fetch(`/api/packing/${this.token}`);
                        if (!res.ok) return;
                        const data = await res.json();
                        // Update items from server
                        this.items = data.items;
                    } catch (e) {
                        // Silent fail on refresh
                    }
                },
            };
        }
        </script>
    </body>
    </html>
@else
    {{-- Authenticated general scanner — show list of active packing lists --}}
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Mobile Packing Scanner
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8 space-y-6">
                {{-- QR scan option --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400 mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                        Scan a packing list QR code to begin
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Or select a list below to start packing</p>
                </div>

                {{-- Active packing lists --}}
                @php
                    $activeLists = \App\Models\PackingList::with('family')
                        ->whereIn('status', [\App\Enums\PackingStatus::Pending, \App\Enums\PackingStatus::InProgress])
                        ->orderBy('updated_at', 'desc')
                        ->limit(50)
                        ->get();
                @endphp

                @if($activeLists->count())
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Active Packing Lists ({{ $activeLists->count() }})</h3>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($activeLists as $list)
                                <a href="{{ route('warehouse.mobile-scan', ['token' => $list->qr_token]) }}"
                                   class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Family #{{ $list->family?->family_number }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $list->items_count ?? $list->items()->count() }} items
                                            &middot;
                                            {{ $list->status->label() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @php
                                            $packed = $list->items()->whereIn('status', ['packed','verified','substituted'])->count();
                                            $total = $list->items()->count();
                                            $pct = $total > 0 ? round(($packed / $total) * 100) : 0;
                                        @endphp
                                        <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                            <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 w-8 text-right">{{ $pct }}%</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No active packing lists. Generate packing lists from the
                            <a href="{{ route('packing.index') }}" class="text-red-600 dark:text-red-400 hover:underline">packing dashboard</a>.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </x-app-layout>
@endif
