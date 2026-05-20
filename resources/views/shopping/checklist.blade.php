<!DOCTYPE html>
<html lang="en" id="shopping-html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping List — Family #{{ $family->family_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- REL-40: opt into Tailwind class-based dark mode so prefers-color-scheme
         and the manual toggle below can both flip via the `dark` class on <html>. --}}
    <script>tailwind.config = { darkMode: 'class' };</script>
    <script>
        (function () {
            try {
                var pref = localStorage.getItem('shopping_dark');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var enable = pref === '1' || (pref === null && prefersDark);
                if (enable) document.documentElement.classList.add('dark');
            } catch (_) { /* ignore */ }
        })();
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors">
    <div class="max-w-lg mx-auto px-4 py-4">
        <!-- Header. Uses explicit colors because this page loads tailwindcss via CDN
             without daisyUI, so `bg-primary` / `text-primary-content` don't resolve. -->
        <div class="bg-blue-700 dark:bg-blue-900 text-white rounded-xl p-4 mb-4 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Family #{{ $family->family_number }}</h1>
                    <p class="text-white/80 text-sm">{{ $family->number_of_family_members }} members</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="dark-toggle" aria-label="Toggle dark mode"
                        class="text-white/80 hover:text-white text-lg leading-none w-8 h-8 rounded-full hover:bg-white/10 transition">
                        <span class="dark:hidden">&#9790;</span>
                        <span class="hidden dark:inline">&#9728;</span>
                    </button>
                    <div class="text-right">
                        <div class="text-3xl font-bold" id="progress-count">0</div>
                        <div class="text-white/80 text-xs">of {{ $totalItems }} items</div>
                    </div>
                </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-3 bg-blue-900/30 dark:bg-black/40 rounded-full h-2">
                <div class="bg-white rounded-full h-2 transition-all duration-300" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>

        @if(count($grouped) === 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center text-gray-500 dark:text-gray-400 shadow-xs">
                No items in the shopping list for this family.
            </div>
        @else
            @php
                $categoryLabels = [
                    'canned' => 'Canned Goods',
                    'dry' => 'Dry Goods',
                    'personal' => 'Personal Care',
                    'condiment' => 'Condiments & Extras',
                ];
                $categoryColors = [
                    'canned' => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/40 dark:text-orange-200 dark:border-orange-800',
                    'dry' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800',
                    'personal' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-800',
                    'condiment' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-200 dark:border-green-800',
                ];
            @endphp

            @foreach($grouped as $category => $items)
                <div class="mb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2 px-1">
                        {{ $categoryLabels[$category] ?? ucfirst($category) }}
                    </h2>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($items as $itemName => $qty)
                            <label class="shopping-item flex items-center px-4 py-3 cursor-pointer active:bg-gray-50 dark:active:bg-gray-700 transition"
                                   data-key="{{ md5($family->family_number . $itemName) }}"
                                   data-qty="{{ $qty }}">
                                <input type="checkbox" class="item-checkbox sr-only">
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center mr-3 shrink-0 check-circle transition-colors">
                                    <svg class="w-4 h-4 text-white hidden check-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="flex-1 text-sm font-medium text-gray-900 dark:text-gray-100 item-name">{{ $itemName }}</span>
                                <span class="inline-flex items-center justify-center min-w-8 h-7 px-2 rounded-full text-sm font-bold {{ $categoryColors[$category] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $qty }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Reset button -->
        <div class="mt-6 mb-8 text-center">
            <button onclick="resetChecklist()" class="text-sm text-gray-400 dark:text-gray-500 hover:text-primary dark:hover:text-gray-200 transition">
                Reset Checklist
            </button>
        </div>
    </div>

    <script>
        const STORAGE_KEY = 'shopping_{{ $family->family_number }}';

        function getCheckedItems() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch { return {}; }
        }

        function saveCheckedItems(items) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        }

        function updateProgress() {
            const checked = getCheckedItems();
            const items = document.querySelectorAll('.shopping-item');
            let collected = 0;
            const total = {{ $totalItems }};

            items.forEach(item => {
                const key = item.dataset.key;
                const qty = parseInt(item.dataset.qty) || 0;
                const isChecked = !!checked[key];
                const checkbox = item.querySelector('.item-checkbox');
                const circle = item.querySelector('.check-circle');
                const icon = item.querySelector('.check-icon');
                const name = item.querySelector('.item-name');

                checkbox.checked = isChecked;

                if (isChecked) {
                    circle.classList.add('bg-green-500', 'border-green-500');
                    circle.classList.remove('border-gray-300', 'dark:border-gray-500');
                    icon.classList.remove('hidden');
                    name.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                    name.classList.remove('text-gray-900', 'dark:text-gray-100');
                    collected += qty;
                } else {
                    circle.classList.remove('bg-green-500', 'border-green-500');
                    circle.classList.add('border-gray-300', 'dark:border-gray-500');
                    icon.classList.add('hidden');
                    name.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                    name.classList.add('text-gray-900', 'dark:text-gray-100');
                }
            });

            document.getElementById('progress-count').textContent = collected;
            const pct = total > 0 ? (collected / total * 100) : 0;
            document.getElementById('progress-bar').style.width = pct + '%';
        }

        function resetChecklist() {
            if (confirm('Reset all checked items?')) {
                localStorage.removeItem(STORAGE_KEY);
                updateProgress();
            }
        }

        // Event delegation
        document.addEventListener('click', function(e) {
            const item = e.target.closest('.shopping-item');
            if (!item) return;

            const key = item.dataset.key;
            const checked = getCheckedItems();
            checked[key] = !checked[key];
            if (!checked[key]) delete checked[key];
            saveCheckedItems(checked);
            updateProgress();
        });

        // REL-40: manual dark-mode override. Persists per device so a volunteer
        // who prefers light at noon and dark in the warehouse evening can pin it.
        const darkToggle = document.getElementById('dark-toggle');
        if (darkToggle) {
            darkToggle.addEventListener('click', function () {
                const html = document.documentElement;
                const wasDark = html.classList.contains('dark');
                html.classList.toggle('dark', !wasDark);
                try { localStorage.setItem('shopping_dark', wasDark ? '0' : '1'); } catch (_) {}
            });
        }

        // Initialize on load
        updateProgress();
    </script>
</body>
</html>
