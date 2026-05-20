<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Coordinator Dashboard
            <x-hint key="coordinator-dashboard" text="Print gift tags and family summaries from here. Gift tags include QR codes for adopt-a-tag tracking." />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-base-100 shadow-xs sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-base-content">{{ $stats['total_families'] }}</div>
                    <div class="text-xs text-base-content/60 mt-1">Total Families</div>
                </div>
                <div class="bg-base-100 shadow-xs sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-base-content">{{ $stats['total_children'] }}</div>
                    <div class="text-xs text-base-content/60 mt-1">Total Children</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 shadow-xs sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['families_done'] }}</div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">Families Complete</div>
                </div>
                <div class="bg-primary/5 dark:bg-primary/20 shadow-xs sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['unmerged_tags'] }}</div>
                    <div class="text-xs text-primary dark:text-primary mt-1">Unprinted Tags</div>
                </div>
            </div>

            {{-- REL-10: per-section KPIs, scoped to the sections the viewer can access. --}}
            @if(!empty($sectionKpis))
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-base-content">Your Sections</h3>
                    @foreach($sectionKpis as $slug => $block)
                        <div class="bg-base-100 shadow-xs sm:rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-base-content">{{ $block['label'] }}</h4>
                                <span class="text-[10px] uppercase tracking-wider text-base-content/40">{{ $slug }}</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($block['tiles'] as $tile)
                                    <div class="bg-base-200 rounded-md p-3 text-center">
                                        <div class="text-xl font-bold text-base-content">{{ $tile['v'] }}</div>
                                        <div class="text-xs text-base-content/60 mt-0.5">{{ $tile['l'] }}</div>
                                        @if(!empty($tile['sub']))
                                            <div class="text-[10px] text-base-content/40 mt-0.5">{{ $tile['sub'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Document Generation -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-base-content mb-6">Generate Documents</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Gift Tags (706) -->
                        <div class="border border-base-300 rounded-lg p-4">
                            <h4 class="font-medium text-base-content mb-2">Gift Tags</h4>
                            <p class="text-xs text-base-content/60 mb-4">Avery 8163 labels (2"x4", 10/page)</p>
                            <form method="GET" action="{{ route('coordinator.giftTags') }}" target="_blank" class="space-y-3">
                                <div>
                                    <label class="block text-xs text-base-content/60 mb-1">Filter</label>
                                    <select name="filter" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                        <option value="unmerged">Unprinted Only ({{ $stats['unmerged_tags'] }})</option>
                                        <option value="all">All Children</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-base-content/60 mb-1">School</label>
                                    <select class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm js-school-select" data-start="gift_tag_range_start" data-end="gift_tag_range_end">
                                        <option value="">All Schools</option>
                                        @foreach($schoolRanges as $range)
                                            <option value="{{ $range->id }}" data-start="{{ $range->range_start }}" data-end="{{ $range->range_end }}">{{ $range->school_name }} ({{ $range->range_start }}–{{ $range->range_end }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-base-content/60 mb-1">Range Start</label>
                                        <input type="number" name="range_start" id="gift_tag_range_start" placeholder="1" min="1" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-base-content/60 mb-1">Range End</label>
                                        <input type="number" name="range_end" id="gift_tag_range_end" placeholder="599" min="1" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="mark_merged" value="1" class="rounded-sm border-base-300 text-primary shadow-xs focus:ring-primary">
                                        <span class="ml-2 text-xs text-base-content/70">Mark as printed after generating</span>
                                    </label>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                                    Generate Gift Tags PDF
                                </button>
                            </form>
                        </div>

                        <!-- Family Summary (708) -->
                        <div class="border border-base-300 rounded-lg p-4">
                            <h4 class="font-medium text-base-content mb-2">Family Summary Sheets</h4>
                            <p class="text-xs text-base-content/60 mb-4">One page per family with demographics</p>
                            <form method="GET" action="{{ route('coordinator.familySummary') }}" target="_blank" class="space-y-3">
                                <div>
                                    <label class="block text-xs text-base-content/60 mb-1">School</label>
                                    <select class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm js-school-select" data-start="summary_range_start" data-end="summary_range_end">
                                        <option value="">All Schools</option>
                                        @foreach($schoolRanges as $range)
                                            <option value="{{ $range->id }}" data-start="{{ $range->range_start }}" data-end="{{ $range->range_end }}">{{ $range->school_name }} ({{ $range->range_start }}–{{ $range->range_end }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-base-content/60 mb-1">Range Start</label>
                                        <input type="number" name="range_start" id="summary_range_start" placeholder="All" min="1" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-base-content/60 mb-1">Range End</label>
                                        <input type="number" name="range_end" id="summary_range_end" placeholder="All" min="1" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                                    Generate Summary PDF
                                </button>
                            </form>
                        </div>

                        <!-- Delivery Day (709) -->
                        <div class="border border-base-300 rounded-lg p-4">
                            <h4 class="font-medium text-base-content mb-2">Delivery Day Sheets</h4>
                            <p class="text-xs text-base-content/60 mb-4">Contact and delivery info per family</p>
                            <form method="GET" action="{{ route('coordinator.deliveryDay') }}" target="_blank" class="space-y-3">
                                <div>
                                    <label class="block text-xs text-base-content/60 mb-1">Delivery Date</label>
                                    <select name="delivery_date" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                        <option value="">All Dates</option>
                                        @foreach(array_filter(array_map('trim', explode(',', \App\Models\Setting::get('delivery_dates', 'December 18th,December 19th')))) as $date)
                                            <option value="{{ $date }}">{{ $date }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-base-content/60 mb-1">Delivery Team</label>
                                    <input type="text" name="delivery_team" placeholder="All teams" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                                    Generate Delivery PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Generation Modal -->
    <div id="pdf-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-base-100 rounded-xl shadow-2xl max-w-sm w-full p-6 text-center">
                <div id="pdf-spinner" class="inline-block w-10 h-10 border-4 border-primary/30 border-t-primary rounded-full animate-spin mb-4"></div>
                <h3 class="text-lg font-semibold text-base-content mb-1" id="pdf-modal-title">Generating PDF...</h3>
                <p class="text-sm text-base-content/60" id="pdf-modal-message">This may take a moment for large batches.</p>
                <button onclick="closePdfModal()" class="mt-4 hidden px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium" id="pdf-close-btn">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.js-school-select').forEach(function(select) {
            select.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                var startId = this.dataset.start;
                var endId = this.dataset.end;
                document.getElementById(startId).value = opt.dataset.start || '';
                document.getElementById(endId).value = opt.dataset.end || '';
            });
        });

        // Intercept PDF forms to handle async generation
        document.querySelectorAll('form[target="_blank"]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const url = new URL(form.action);
                const formData = new FormData(form);
                formData.forEach((v, k) => { if (v) url.searchParams.set(k, v); });

                showPdfModal('Generating PDF...', 'This may take a moment for large batches.');

                fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => {
                    const ct = r.headers.get('content-type') || '';
                    if (ct.includes('application/json')) return r.json();
                    // Sync response (direct PDF or HTML) — open in new tab
                    window.open(url.toString(), '_blank');
                    closePdfModal();
                    return null;
                })
                .then(data => {
                    if (!data) return;
                    if (data.status_url) {
                        pollPdfStatus(data.status_url, data.download_url);
                    } else {
                        updatePdfModal('Error', data.message || 'Unexpected response.', true);
                    }
                })
                .catch(() => {
                    updatePdfModal('Error', 'Failed to start PDF generation.', true);
                });
            });
        });

        function showPdfModal(title, msg) {
            document.getElementById('pdf-modal').classList.remove('hidden');
            document.getElementById('pdf-spinner').classList.remove('hidden');
            document.getElementById('pdf-close-btn').classList.add('hidden');
            document.getElementById('pdf-modal-title').textContent = title;
            document.getElementById('pdf-modal-message').textContent = msg;
        }

        function updatePdfModal(title, msg, showClose) {
            document.getElementById('pdf-modal-title').textContent = title;
            document.getElementById('pdf-modal-message').textContent = msg;
            if (showClose) {
                document.getElementById('pdf-spinner').classList.add('hidden');
                document.getElementById('pdf-close-btn').classList.remove('hidden');
            }
        }

        function closePdfModal() {
            document.getElementById('pdf-modal').classList.add('hidden');
        }

        function pollPdfStatus(statusUrl, downloadUrl) {
            let attempts = 0;
            const maxAttempts = 120; // 2 minutes max
            const interval = setInterval(() => {
                attempts++;
                fetch(statusUrl)
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'complete') {
                            clearInterval(interval);
                            updatePdfModal('PDF Ready!', 'Opening download...', true);
                            window.open(downloadUrl, '_blank');
                            setTimeout(closePdfModal, 1500);
                        } else if (data.status === 'failed') {
                            clearInterval(interval);
                            updatePdfModal('Generation Failed', data.message || 'The PDF could not be generated.', true);
                        } else if (attempts >= maxAttempts) {
                            clearInterval(interval);
                            updatePdfModal('Timeout', 'PDF generation took too long. Please try again.', true);
                        } else {
                            updatePdfModal('Generating PDF...', data.message || 'Working...');
                        }
                    })
                    .catch(() => {
                        clearInterval(interval);
                        updatePdfModal('Error', 'Lost connection to server.', true);
                    });
            }, 1000);
        }
    </script>
</x-app-layout>
