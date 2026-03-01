<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Reports & Statistics
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Top-level stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalFamilies }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalChildren }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Children</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $familiesDone }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Families Complete</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $assignedFamilies }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Numbers Assigned</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Gift Level Breakdown -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Gift Levels</h3>
                    <div class="space-y-3">
                        @php
                            $giftTotal = max(array_sum($giftLevels), 1);
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-red-600 dark:text-red-400 font-medium">No Gifts</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $giftLevels['none'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-red-500 h-2.5 rounded-full" style="width: {{ ($giftLevels['none'] / $giftTotal) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-yellow-600 dark:text-yellow-400 font-medium">Partial</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $giftLevels['partial'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ ($giftLevels['partial'] / $giftTotal) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-yellow-600 dark:text-yellow-400 font-medium">Moderate</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $giftLevels['moderate'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ ($giftLevels['moderate'] / $giftTotal) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-green-600 dark:text-green-400 font-medium">Fully Gifted</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $giftLevels['full'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ ($giftLevels['full'] / $giftTotal) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    @if($totalChildren > 0)
                        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ round(($giftLevels['full'] / $totalChildren) * 100) }}% fully gifted
                        </div>
                    @endif
                </div>

                <!-- Delivery Status -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Delivery Status</h3>
                    <div class="space-y-3">
                        @php
                            $deliveryTotal = max(array_sum($deliveryStats), 1);
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Pending</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $deliveryStats['pending'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-gray-400 h-2.5 rounded-full" style="width: {{ ($deliveryStats['pending'] / $deliveryTotal) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-blue-600 dark:text-blue-400 font-medium">In Transit</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $deliveryStats['in_transit'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ ($deliveryStats['in_transit'] / $deliveryTotal) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-green-600 dark:text-green-400 font-medium">Delivered</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $deliveryStats['delivered'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ ($deliveryStats['delivered'] / $deliveryTotal) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    @if($totalFamilies > 0)
                        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ round(($deliveryStats['delivered'] / $totalFamilies) * 100) }}% delivery complete
                        </div>
                    @endif
                </div>

                <!-- Children by Age Group -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Children by Age Group</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Infants (0-2)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $ageGroups['infants'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Young Children (3-7)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $ageGroups['young_children'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Children (8-12)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $ageGroups['children'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Tweens (13-14)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $ageGroups['tweens'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 dark:text-gray-300">Teenagers (15-17)</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $ageGroups['teenagers'] }}</td>
                            </tr>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">Total</td>
                                <td class="py-2 text-right font-bold text-gray-900 dark:text-gray-100">{{ array_sum($ageGroups) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Children by School -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Children by School</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($childrenBySchool as $row)
                                <tr>
                                    <td class="py-2 text-gray-600 dark:text-gray-300">{{ $row->school }}</td>
                                    <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $row->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tag & Adopter Stats -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tags & Adopters</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $tagStats['merged'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tags Printed</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $tagStats['unmerged'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Unprinted</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $tagStats['adopted'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Adopted</div>
                        </div>
                    </div>
                </div>

                <!-- Special Needs -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Special Needs</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $needsStats['baby_supplies'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Need Baby Supplies</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $needsStats['severe_need'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Severe Need</div>
                        </div>
                    </div>

                    @if($languages->count() > 0)
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-6 mb-2">Language Breakdown</h4>
                        <div class="space-y-1">
                            @foreach($languages as $lang)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">{{ $lang->preferred_language }}</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $lang->total }}</span>
                                </div>
                            @endforeach
                        </div>
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
