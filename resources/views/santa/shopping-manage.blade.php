<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-base-content leading-tight">
                Manage Grocery Items
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('santa.exportGroceryFormula') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                    Export Formula CSV
                </a>
                <a href="{{ route('santa.shopping') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Back to Shopping Hub
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-primary/5 dark:bg-primary/20 border border-primary/30 dark:border-primary text-primary dark:text-primary px-4 py-3 rounded-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Import from CSV -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-base-content mb-2">Import Formula from CSV</h3>
                <p class="text-sm text-base-content/60 mb-4">
                    Upload a shopping list CSV (like the ones exported from Access) to update all item quantities.
                    The importer reads all families, groups by family size, and calculates the median quantity per item per size bracket (1-8 members).
                    Existing items are updated; new items are created.
                </p>
                <form method="POST" action="{{ route('santa.importGroceryItems') }}" enctype="multipart/form-data" class="flex items-end space-x-3">
                    @csrf
                    <div class="flex-1">
                        <input type="file" name="csv_file" accept=".csv,.txt" required
                               class="w-full text-sm text-base-content/60 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/5 dark:file:bg-primary/20 file:text-primary dark:file:text-primary-content/80 hover:file:bg-primary/10 dark:hover:file:bg-primary/30">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                        Import
                    </button>
                </form>
            </div>

            <!-- Add new item -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-base-content mb-4">Add New Item</h3>
                <form method="POST" action="{{ route('santa.storeGroceryItem') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Category</label>
                        <select name="category" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="canned">Canned</option>
                            <option value="dry">Dry</option>
                            <option value="personal">Personal</option>
                            <option value="condiment">Condiment</option>
                        </select>
                    </div>
                    @for($s = 1; $s <= 8; $s++)
                        <div>
                            <label class="block text-xs font-medium text-base-content/60 mb-1">Size {{ $s }}</label>
                            <input type="number" name="qty_{{ $s }}" value="0" min="0"
                                   class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                        </div>
                    @endfor
                    <div>
                        <button type="submit" class="w-full px-3 py-2 bg-primary text-white rounded-md hover:opacity-90 text-xs font-medium transition">
                            Add
                        </button>
                    </div>
                </form>
            </div>

            <!-- Items table -->
            @php
                $categories = ['canned' => 'Canned Goods', 'dry' => 'Dry Goods', 'personal' => 'Personal Care', 'condiment' => 'Condiments & Other'];
            @endphp

            @foreach($categories as $catKey => $catLabel)
                @php $catItems = $groceryItems->where('category', $catKey); @endphp
                @if($catItems->count() > 0)
                    <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-base-content mb-4">
                            {{ $catLabel }}
                            <span class="text-sm font-normal text-base-content/60">({{ $catItems->count() }} items)</span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-base-300 text-sm">
                                <thead class="bg-base-200">
                                    <tr>
                                        <th class="px-2 py-1.5 text-left text-xs font-medium text-base-content/60 uppercase">Item</th>
                                        @for($s = 1; $s <= 8; $s++)
                                            <th class="px-2 py-1.5 text-center text-xs font-medium text-base-content/60 uppercase w-14">{{ $s }}</th>
                                        @endfor
                                        <th class="px-2 py-1.5 text-center text-xs font-medium text-base-content/60 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-300">
                                    @foreach($catItems as $item)
                                        <tr>
                                            <form method="POST" action="{{ route('santa.updateGroceryItem', $item) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="category" value="{{ $item->category }}">
                                                <td class="px-2 py-1">
                                                    <input type="text" name="name" value="{{ $item->name }}"
                                                           class="w-full rounded-sm border-base-300 dark:bg-gray-700 dark:text-gray-100 text-xs px-1 py-0.5">
                                                </td>
                                                @for($s = 1; $s <= 8; $s++)
                                                    <td class="px-1 py-1 text-center">
                                                        <input type="number" name="qty_{{ $s }}" value="{{ $item->{'qty_'.$s} }}" min="0"
                                                               class="w-12 rounded-sm border-base-300 dark:bg-gray-700 dark:text-gray-100 text-xs text-center px-0.5 py-0.5">
                                                    </td>
                                                @endfor
                                                <td class="px-2 py-1 text-center whitespace-nowrap">
                                                    <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline text-xs mr-2">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('santa.destroyGroceryItem', $item) }}" class="inline" onsubmit="return confirm('Delete {{ $item->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-primary dark:text-primary hover:underline text-xs">Del</button>
                                            </form>
                                                </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-base-content/70 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
