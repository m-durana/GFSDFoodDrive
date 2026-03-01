<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gift Tracking Overview
            </h2>
            <a href="{{ route('warehouse.gift-bank') }}" class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white rounded-md hover:bg-purple-500 text-xs font-medium transition">
                Gift Bank
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $counts['total'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Children</div>
                </div>
                <a href="{{ route('santa.gifts', ['level' => 0]) }}" class="bg-red-50 dark:bg-red-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-red-300 transition {{ request('level') === '0' ? 'ring-2 ring-red-500' : '' }}">
                    <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $counts['no_gifts'] }}</div>
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">No Gifts</div>
                </a>
                <a href="{{ route('santa.gifts', ['level' => 1]) }}" class="bg-yellow-50 dark:bg-yellow-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-yellow-300 transition {{ in_array(request('level'), ['1', '2']) ? 'ring-2 ring-yellow-500' : '' }}">
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $counts['partial'] }}</div>
                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">In Progress</div>
                </a>
                <a href="{{ route('santa.gifts', ['level' => 3]) }}" class="bg-green-50 dark:bg-green-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-green-300 transition {{ request('level') === '3' ? 'ring-2 ring-green-500' : '' }}">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $counts['complete'] }}</div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">Fully Gifted</div>
                </a>
                <a href="{{ route('santa.gifts', ['merged' => '0']) }}" class="bg-blue-50 dark:bg-blue-900/20 shadow-sm sm:rounded-lg p-4 text-center hover:ring-2 ring-blue-300 transition {{ request('merged') === '0' ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $counts['unmerged'] }}</div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">Unprinted Tags</div>
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('santa.gifts') }}" class="flex flex-wrap items-center gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Gift Level</label>
                        <select name="level" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All Levels</option>
                            @foreach(\App\Enums\GiftLevel::cases() as $gl)
                                <option value="{{ $gl->value }}" {{ request('level') == (string)$gl->value ? 'selected' : '' }}>{{ $gl->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Tag Status</label>
                        <select name="merged" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="0" {{ request('merged') === '0' ? 'selected' : '' }}>Unprinted</option>
                            <option value="1" {{ request('merged') === '1' ? 'selected' : '' }}>Printed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Adopted</label>
                        <select name="adopted" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="0" {{ request('adopted') === '0' ? 'selected' : '' }}>Not Adopted</option>
                            <option value="1" {{ request('adopted') === '1' ? 'selected' : '' }}>Adopted</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Filter
                        </button>
                        <a href="{{ route('santa.gifts') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium transition">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Children Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Children ({{ $children->count() }} {{ request()->hasAny(['level', 'merged', 'adopted']) ? 'filtered' : 'total' }})
                    </h3>

                    @if($children->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family #</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gender</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Age</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">School</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gift Level</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gifts Received</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopter</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tag</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Where</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($children as $child)
                                        <tr class="{{ $child->family->family_done ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}">
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $child->family->family_number ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 text-sm">
                                                <a href="{{ route('family.show', $child->family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    {{ $child->family->family_name }}
                                                </a>
                                                @if($child->family->family_done)
                                                    <span class="ml-1 text-green-600 dark:text-green-400 text-xs">&check;</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->gender }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->age }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->school ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                @php $level = $child->gift_level ?? \App\Enums\GiftLevel::None; @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $level->color() === 'red' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : '' }}
                                                    {{ $level->color() === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                                                    {{ $level->color() === 'green' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : '' }}
                                                ">{{ $level->label() }}</span>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[200px]" x-data="{ expanded: false }">
                                                @php $computedGifts = $child->getComputedGiftsReceived(); @endphp
                                                @if($computedGifts)
                                                    <span x-show="!expanded" class="truncate block cursor-pointer" @click="expanded = true" title="Click to expand">{{ $computedGifts }}</span>
                                                    <span x-show="expanded" x-cloak class="cursor-pointer whitespace-normal" @click="expanded = false">{{ $computedGifts }}</span>
                                                    <a href="{{ route('warehouse.child.gifts', $child) }}" class="text-xs text-blue-500 dark:text-blue-400 hover:underline block mt-0.5">view details &rarr;</a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                @if($child->adopter_name)
                                                    <span title="{{ $child->adopter_email ?: $child->adopter_phone }}">{{ $child->adopter_name }}</span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm">
                                                @if($child->mail_merged)
                                                    <span class="text-green-600 dark:text-green-400">Printed</span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">Pending</span>
                                                @endif
                                                @if($child->adoption_token)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ml-1">Online</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->where_is_tag ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No children match the selected filters.</p>
                    @endif
                </div>
            </div>

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
