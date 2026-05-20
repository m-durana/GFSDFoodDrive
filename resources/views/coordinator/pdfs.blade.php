<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">Generate PDF</h2>
    </x-slot>

    {{-- REL-42: unified PDF landing. Replaces the previous "two URL variants per
         PDF" confusion (`?sync=1` returned the binary while no-arg returned a
         job ID). Now each PDF has one button that streams the file directly. --}}
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <p class="text-sm text-base-content/70">
                    Generate any of the operational PDFs below. Each button streams the
                    file directly to your browser. Large batches may take 30–60 seconds
                    while the PDF renders.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('coordinator.giftTags') }}?sync=1"
                   class="block bg-base-100 hover:shadow-md transition shadow-xs sm:rounded-lg p-5 border border-base-300 hover:border-primary/40">
                    <div class="text-2xl mb-2">🎁</div>
                    <h3 class="font-semibold text-base-content">Gift Tags</h3>
                    <p class="text-xs text-base-content/60 mt-1">Printable gift-tag sheets for Giving Tree.</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-primary">Generate PDF →</span>
                </a>

                <a href="{{ route('coordinator.familySummary') }}?sync=1"
                   class="block bg-base-100 hover:shadow-md transition shadow-xs sm:rounded-lg p-5 border border-base-300 hover:border-primary/40">
                    <div class="text-2xl mb-2">📋</div>
                    <h3 class="font-semibold text-base-content">Family Summary</h3>
                    <p class="text-xs text-base-content/60 mt-1">One-page summary per family for packing day.</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-primary">Generate PDF →</span>
                </a>

                <a href="{{ route('coordinator.deliveryDay') }}?sync=1"
                   class="block bg-base-100 hover:shadow-md transition shadow-xs sm:rounded-lg p-5 border border-base-300 hover:border-primary/40">
                    <div class="text-2xl mb-2">🚚</div>
                    <h3 class="font-semibold text-base-content">Delivery Day Sheet</h3>
                    <p class="text-xs text-base-content/60 mt-1">Driver routes + per-stop family details.</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-primary">Generate PDF →</span>
                </a>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-xs text-amber-700 dark:text-amber-300">
                Heads-up: gift-tag generation marks tags as <code class="font-mono">mail_merged=true</code> when generated with <code class="font-mono">?mark_merged=1</code>.
                The button above does <strong>not</strong> mark them — use the dedicated Gift Tags admin flow if you need that side-effect.
            </div>
        </div>
    </div>
</x-app-layout>
