<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Assignment — {{ $assignment->user->first_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-4">
        <!-- Header -->
        <div class="bg-red-700 text-white rounded-xl p-4 mb-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">{{ $assignment->user->first_name }} {{ $assignment->user->last_name }}</h1>
                    <p class="text-red-200 text-sm">{{ $assignment->getDescription() }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold" id="progress-count">0</div>
                    <div class="text-red-200 text-xs">of {{ $totalItems }} items</div>
                </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-3 bg-red-900 rounded-full h-2">
                <div class="bg-white rounded-full h-2 transition-all duration-300" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>

        @if($assignment->notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 text-sm text-yellow-800">
                {{ $assignment->notes }}
            </div>
        @endif

        @if(count($shoppingList) === 0)
            <div class="bg-white rounded-xl p-6 text-center text-gray-500 shadow-sm">
                No items in this assignment.
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
                    'canned' => 'bg-orange-100 text-orange-800 border-orange-200',
                    'dry' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'personal' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'condiment' => 'bg-green-100 text-green-800 border-green-200',
                ];
            @endphp

            @foreach($shoppingList as $category => $items)
                <div class="mb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-2 px-1">
                        {{ $categoryLabels[$category] ?? ucfirst($category) }}
                        <span class="text-xs font-normal">({{ array_sum($items) }} items)</span>
                    </h2>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden divide-y divide-gray-100">
                        @foreach($items as $itemName => $qty)
                            <label class="shopping-item flex items-center px-4 py-3 cursor-pointer active:bg-gray-50 transition"
                                   data-key="{{ md5('assignment_' . $assignment->id . '_' . $itemName) }}"
                                   data-qty="{{ $qty }}">
                                <input type="checkbox" class="item-checkbox sr-only">
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3 flex-shrink-0 check-circle transition-colors">
                                    <svg class="w-4 h-4 text-white hidden check-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="flex-1 text-sm font-medium text-gray-900 item-name">{{ $itemName }}</span>
                                <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full text-sm font-bold {{ $categoryColors[$category] ?? 'bg-gray-100 text-gray-800' }}">
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
            <button onclick="resetChecklist()" class="text-sm text-gray-400 hover:text-red-600 transition">
                Reset Checklist
            </button>
        </div>
    </div>

    <script>
        const STORAGE_KEY = 'assignment_{{ $assignment->id }}';

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
                const circle = item.querySelector('.check-circle');
                const icon = item.querySelector('.check-icon');
                const name = item.querySelector('.item-name');

                if (isChecked) {
                    circle.classList.add('bg-green-500', 'border-green-500');
                    circle.classList.remove('border-gray-300');
                    icon.classList.remove('hidden');
                    name.classList.add('line-through', 'text-gray-400');
                    name.classList.remove('text-gray-900');
                    collected += qty;
                } else {
                    circle.classList.remove('bg-green-500', 'border-green-500');
                    circle.classList.add('border-gray-300');
                    icon.classList.add('hidden');
                    name.classList.remove('line-through', 'text-gray-400');
                    name.classList.add('text-gray-900');
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

        updateProgress();
    </script>
</body>
</html>
