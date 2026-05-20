<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('Shopping') }} — {{ $assignment->getDisplayName() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @supports (padding: env(safe-area-inset-bottom)) {
            .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        }
        /* REL-43: prevent iOS focus-zoom on form controls. */
        @media (max-width:767px){input:not([type=checkbox]):not([type=radio]):not([type=range]),select,textarea{font-size:16px!important}}
    </style>
</head>
<body class="bg-gray-100 min-h-screen pb-safe">
    <div class="max-w-lg mx-auto px-4 py-4" id="app">
        <!-- Name prompt (shown when no ninja name set) -->
        <div id="name-prompt" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
                <h2 class="text-lg font-bold text-gray-900 mb-2">{{ __("What's your name?") }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ __('This will show others who checked off items.') }}</p>
                <input type="text" id="ninja-name-input" placeholder="{{ __('Your name') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-primary focus:border-primary mb-4"
                       autofocus>
                <button id="save-name-btn" class="w-full bg-primary text-white rounded-lg py-3 font-semibold text-base hover:opacity-90 transition">
                    {{ __('Start Shopping') }}
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="bg-primary text-white rounded-xl p-4 mb-4 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold" id="display-name">{{ $assignment->getDisplayName() }}</h1>
                    <p class="text-primary-content/80 text-sm" id="description">{{ $assignment->getDescription() }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold" id="progress-count">0</div>
                    <div class="text-primary-content/80 text-xs">{!! __('of :n items', ['n' => '<span id="total-count">0</span>']) !!}</div>
                </div>
            </div>
            <div class="mt-3 bg-primary/30 rounded-full h-2">
                <div class="bg-white rounded-full h-2 transition-all duration-300" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>

        @if($assignment->notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 text-sm text-yellow-800">
                {{ $assignment->notes }}
            </div>
        @endif

        <!-- Sync status -->
        <div class="flex items-center justify-between mb-3 px-1">
            <div class="flex items-center space-x-2">
                <div id="sync-dot" class="w-2 h-2 rounded-full bg-green-500"></div>
                <span id="sync-text" class="text-xs text-gray-400">{{ __('Live') }}</span>
            </div>
            <div class="flex items-center gap-2">
                @php $other = app()->getLocale() === 'es' ? 'en' : 'es'; @endphp
                <a href="?{{ http_build_query(array_merge(request()->query(), ['lang' => $other])) }}" class="text-xs text-gray-400 underline">
                    {{ $other === 'es' ? 'ES' : 'EN' }}
                </a>
                <span id="your-name-display" class="text-xs text-gray-400"></span>
            </div>
        </div>

        <!-- Shopping list (rendered by JS) -->
        <div id="shopping-list"></div>

        <!-- Reset button -->
        <div class="mt-6 mb-8 text-center">
            <button onclick="resetChecklist()" class="text-sm text-gray-400 hover:text-primary transition">
                {{ __('Reset All My Checks') }}
            </button>
        </div>
    </div>

    <script>
        const TOKEN = '{{ $assignment->token }}';
        const API_BASE = '/api/shopping/' + TOKEN;
        const STORAGE_NAME_KEY = 'ninja_name_' + TOKEN;
        const I18N = {!! json_encode([
            'canned' => __('Canned Goods'),
            'dry' => __('Dry Goods'),
            'personal' => __('Personal Care'),
            'condiment' => __('Condiments & Extras'),
            'shopping_as' => __('Shopping as:'),
            'no_items' => __('No items in this assignment.'),
            'items' => __('items'),
            'live' => __('Live'),
            'syncing' => __('Syncing...'),
            'offline' => __('Offline — retrying'),
            'reset_confirm' => __("Reset all items YOU checked? Others' checks will remain."),
        ]) !!};

        const categoryLabels = {
            canned: I18N.canned,
            dry: I18N.dry,
            personal: I18N.personal,
            condiment: I18N.condiment
        };
        const categoryColors = {
            canned: 'bg-orange-100 text-orange-800',
            dry: 'bg-amber-100 text-amber-800',
            personal: 'bg-blue-100 text-blue-800',
            condiment: 'bg-green-100 text-green-800'
        };

        let items = [];
        let checks = {};
        let totalItems = 0;
        let ninjaName = localStorage.getItem(STORAGE_NAME_KEY) || '';
        let pollInterval = null;

        // Name prompt
        function checkName() {
            if (!ninjaName) {
                document.getElementById('name-prompt').classList.remove('hidden');
                document.getElementById('ninja-name-input').focus();
            } else {
                document.getElementById('your-name-display').textContent = I18N.shopping_as + ' ' + ninjaName;
                startPolling();
            }
        }

        document.getElementById('save-name-btn').addEventListener('click', function() {
            const input = document.getElementById('ninja-name-input').value.trim();
            if (!input) return;
            ninjaName = input;
            localStorage.setItem(STORAGE_NAME_KEY, ninjaName);
            document.getElementById('name-prompt').classList.add('hidden');
            document.getElementById('your-name-display').textContent = I18N.shopping_as + ' ' + ninjaName;
            startPolling();
        });

        document.getElementById('ninja-name-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') document.getElementById('save-name-btn').click();
        });

        // Fetch data from API
        async function fetchData() {
            try {
                const res = await fetch(API_BASE);
                if (!res.ok) throw new Error('Failed to fetch');
                const data = await res.json();
                items = data.items;
                checks = data.checks;
                totalItems = data.total_items;
                document.getElementById('total-count').textContent = totalItems;
                renderList();
                updateProgress();
                setSyncStatus('ok');
            } catch (e) {
                setSyncStatus('error');
            }
        }

        // Toggle check via API
        async function toggleItem(itemKey) {
            setSyncStatus('syncing');
            try {
                const res = await fetch(API_BASE + '/check', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ item_key: itemKey, ninja_name: ninjaName })
                });
                if (!res.ok) throw new Error('Failed to toggle');
                const data = await res.json();
                checks = data.checks;
                renderList();
                updateProgress();
                setSyncStatus('ok');
            } catch (e) {
                setSyncStatus('error');
                // Retry fetch to resync
                setTimeout(fetchData, 1000);
            }
        }

        // Render the shopping list
        function renderList() {
            const container = document.getElementById('shopping-list');
            if (items.length === 0) {
                container.innerHTML = '<div class="bg-white rounded-xl p-6 text-center text-gray-500 shadow-xs">' + I18N.no_items + '</div>';
                return;
            }

            // Group by category
            const grouped = {};
            items.forEach(item => {
                if (!grouped[item.category]) grouped[item.category] = [];
                grouped[item.category].push(item);
            });

            let html = '';
            for (const [cat, catItems] of Object.entries(grouped)) {
                const catTotal = catItems.reduce((sum, i) => sum + i.quantity, 0);
                const label = categoryLabels[cat] || cat.charAt(0).toUpperCase() + cat.slice(1);
                const colorClass = categoryColors[cat] || 'bg-gray-100 text-gray-800';

                html += '<div class="mb-4">';
                html += '<h2 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-2 px-1">' +
                         label + ' <span class="text-xs font-normal">(' + catTotal + ' ' + I18N.items + ')</span></h2>';
                html += '<div class="bg-white rounded-xl shadow-xs overflow-hidden divide-y divide-gray-100">';

                catItems.forEach(item => {
                    const check = checks[item.key];
                    const isChecked = !!check;
                    const circleClass = isChecked ? 'bg-green-500 border-green-500' : 'border-gray-300';
                    const iconHidden = isChecked ? '' : 'hidden';
                    const nameClass = isChecked ? 'line-through text-gray-400' : 'text-gray-900';
                    const checkedInfo = isChecked ? '<span class="text-xs text-gray-400 ml-1">' + check.checked_by + '</span>' : '';

                    html += '<div class="shopping-item flex items-center px-4 py-3 cursor-pointer active:bg-gray-50 transition" data-key="' + escHtml(item.key) + '">' +
                        '<div class="w-6 h-6 rounded-full border-2 ' + circleClass + ' flex items-center justify-center mr-3 shrink-0">' +
                            '<svg class="w-4 h-4 text-white ' + iconHidden + '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>' +
                            '</svg>' +
                        '</div>' +
                        '<div class="flex-1 min-w-0">' +
                            '<span class="text-sm font-medium ' + nameClass + '">' + escHtml(item.key) + '</span>' +
                            checkedInfo +
                        '</div>' +
                        '<span class="inline-flex items-center justify-center min-w-8 h-7 px-2 rounded-full text-sm font-bold ' + colorClass + '">' +
                            item.quantity +
                        '</span>' +
                    '</div>';
                });

                html += '</div></div>';
            }

            container.innerHTML = html;
        }

        function escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Update progress bar
        function updateProgress() {
            let collected = 0;
            items.forEach(item => {
                if (checks[item.key]) collected += item.quantity;
            });
            document.getElementById('progress-count').textContent = collected;
            const pct = totalItems > 0 ? (collected / totalItems * 100) : 0;
            document.getElementById('progress-bar').style.width = pct + '%';
        }

        // Sync status indicator
        function setSyncStatus(status) {
            const dot = document.getElementById('sync-dot');
            const text = document.getElementById('sync-text');
            if (status === 'ok') {
                dot.className = 'w-2 h-2 rounded-full bg-green-500';
                text.textContent = I18N.live;
            } else if (status === 'syncing') {
                dot.className = 'w-2 h-2 rounded-full bg-yellow-500 animate-pulse';
                text.textContent = I18N.syncing;
            } else {
                dot.className = 'w-2 h-2 rounded-full bg-primary';
                text.textContent = I18N.offline;
            }
        }

        // Reset all MY checks
        async function resetChecklist() {
            if (!confirm(I18N.reset_confirm)) return;
            // Find items checked by me
            const myItems = Object.entries(checks)
                .filter(([key, val]) => val.checked_by === ninjaName)
                .map(([key]) => key);

            for (const key of myItems) {
                await toggleItem(key);
            }
        }

        // Click handler
        document.addEventListener('click', function(e) {
            const item = e.target.closest('.shopping-item');
            if (!item) return;
            toggleItem(item.dataset.key);
        });

        // Polling
        function startPolling() {
            fetchData();
            pollInterval = setInterval(fetchData, 5000);
        }

        // Visibility API — pause polling when tab hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(pollInterval);
            } else {
                fetchData();
                pollInterval = setInterval(fetchData, 5000);
            }
        });

        checkName();
    </script>
</body>
</html>
