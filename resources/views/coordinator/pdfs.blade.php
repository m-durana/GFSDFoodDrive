<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">Generate PDF</h2>
    </x-slot>

    {{-- REL-42: unified PDF landing. Replaces the previous "two URL variants per
         PDF" confusion (`?sync=1` returned the binary while no-arg returned a
         job ID). Now each PDF has one button that streams the file directly. --}}
    <div class="py-8" x-data="pdfLanding()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <p class="text-sm text-base-content/70">
                    Generate any of the operational PDFs below. Generation runs in the
                    background — you'll see a progress chip while it renders, then
                    a download button appears.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button @click="start('{{ route('coordinator.giftTags') }}', 'gift_tags')"
                        :disabled="busy === 'gift_tags'"
                        class="text-left block bg-base-100 hover:shadow-md transition shadow-xs sm:rounded-lg p-5 border border-base-300 hover:border-primary/40 disabled:opacity-50">
                    <div class="text-2xl mb-2">🎁</div>
                    <h3 class="font-semibold text-base-content">Gift Tags</h3>
                    <p class="text-xs text-base-content/60 mt-1">Printable gift-tag sheets for Giving Tree.</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-primary" x-text="label('gift_tags')">Generate PDF →</span>
                </button>

                <button @click="start('{{ route('coordinator.familySummary') }}', 'family_summary')"
                        :disabled="busy === 'family_summary'"
                        class="text-left block bg-base-100 hover:shadow-md transition shadow-xs sm:rounded-lg p-5 border border-base-300 hover:border-primary/40 disabled:opacity-50">
                    <div class="text-2xl mb-2">📋</div>
                    <h3 class="font-semibold text-base-content">Family Summary</h3>
                    <p class="text-xs text-base-content/60 mt-1">One-page summary per family for packing day.</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-primary" x-text="label('family_summary')">Generate PDF →</span>
                </button>

                <button @click="start('{{ route('coordinator.deliveryDay') }}', 'delivery_day')"
                        :disabled="busy === 'delivery_day'"
                        class="text-left block bg-base-100 hover:shadow-md transition shadow-xs sm:rounded-lg p-5 border border-base-300 hover:border-primary/40 disabled:opacity-50">
                    <div class="text-2xl mb-2">🚚</div>
                    <h3 class="font-semibold text-base-content">Delivery Day Sheet</h3>
                    <p class="text-xs text-base-content/60 mt-1">Driver routes + per-stop family details.</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-primary" x-text="label('delivery_day')">Generate PDF →</span>
                </button>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-xs text-amber-700 dark:text-amber-300">
                Heads-up: gift-tag generation marks tags as <code class="font-mono">mail_merged=true</code> when generated with <code class="font-mono">?mark_merged=1</code>.
                The buttons above do <strong>not</strong> mark them — use the dedicated Gift Tags admin flow if you need that side-effect.
            </div>

            <template x-if="error">
                <div class="bg-primary/10 dark:bg-primary/20 border border-primary/30 rounded-lg p-3 text-sm text-primary">
                    <span x-text="error"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- REL-13: async polling. Calls the existing coordinator.{view} endpoint
         without ?sync=1, gets a job key, polls /pdf-status until ready, then
         redirects to /pdf-download. Falls back to sync mode if the backend
         returns binary directly (queue not running). --}}
    <script>
        function pdfLanding() {
            return {
                busy: null,
                status: {},
                error: null,
                label(key) {
                    if (this.busy === key) return this.status[key] || 'Queued…';
                    return 'Generate PDF →';
                },
                async start(url, key) {
                    this.busy = key;
                    this.error = null;
                    this.status[key] = 'Queued…';
                    try {
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const ct = res.headers.get('content-type') || '';
                        if (ct.includes('application/pdf') || ct.includes('octet-stream')) {
                            // Backend chose sync mode — open as download.
                            window.location.href = url + (url.includes('?') ? '&' : '?') + 'sync=1';
                            this.busy = null;
                            return;
                        }
                        const data = await res.json();
                        if (!data.status_url || !data.download_url) {
                            throw new Error('Unexpected response shape');
                        }
                        await this.poll(key, data.status_url, data.download_url);
                    } catch (e) {
                        this.error = 'PDF generation failed: ' + e.message;
                        this.busy = null;
                    }
                },
                async poll(key, statusUrl, downloadUrl) {
                    for (let i = 0; i < 60; i++) {  // 60 × 3s = 3 min cap
                        await new Promise(r => setTimeout(r, 3000));
                        const r = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                        const s = await r.json();
                        this.status[key] = s.message || s.status;
                        if (s.status === 'complete') {
                            window.location.href = downloadUrl;
                            this.busy = null;
                            return;
                        }
                        if (s.status === 'error') {
                            this.error = s.message || 'PDF generation failed';
                            this.busy = null;
                            return;
                        }
                    }
                    this.error = 'PDF generation timed out after 3 minutes';
                    this.busy = null;
                },
            };
        }
    </script>
</x-app-layout>
