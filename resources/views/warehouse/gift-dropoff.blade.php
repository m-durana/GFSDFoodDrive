<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Gift Drop-Off
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Child Info -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-base-content">
                                Family #{{ $child->family->family_number ?? 'N/A' }}
                            </h3>
                            <p class="text-sm text-base-content/60">{{ $child->family->family_name }}</p>
                        </div>
                        @if($child->gift_dropped_off)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Already Dropped Off
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-base-content/60">Gender:</span>
                            <span class="ml-1 text-base-content">{{ $child->gender ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60">Age:</span>
                            <span class="ml-1 text-base-content">{{ $child->age ?? 'N/A' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-base-content/60">Gift Preferences:</span>
                            <span class="ml-1 text-base-content">{{ $child->gift_preferences ?? 'None specified' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-base-content/60">Toy Ideas:</span>
                            <span class="ml-1 text-base-content">{{ $child->toy_ideas ?? 'None specified' }}</span>
                        </div>
                        @if($child->gifts_received)
                            <div class="col-span-2">
                                <span class="text-base-content/60">Recorded Gifts:</span>
                                <span class="ml-1 text-base-content">{{ $child->gifts_received }}</span>
                            </div>
                        @endif
                        @if($child->adopter_name)
                            <div class="col-span-2">
                                <span class="text-base-content/60">Adopter:</span>
                                <span class="ml-1 text-base-content">{{ $child->adopter_name }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="text-base-content/60">Gift Level:</span>
                            <span class="ml-1 font-medium {{ $child->gift_level?->color() === 'green' ? 'text-green-600 dark:text-green-400' : ($child->gift_level?->color() === 'red' ? 'text-primary dark:text-primary' : 'text-yellow-600 dark:text-yellow-400') }}">
                                {{ $child->gift_level?->label() ?? 'No Gifts' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Button -->
            @unless($child->gift_dropped_off)
                <form method="POST" action="{{ route('warehouse.gift.dropoff.confirm', $child) }}" id="dropoff-form">
                    @csrf
                    <div class="bg-base-100 shadow-xs sm:rounded-lg">
                        <div class="p-6 space-y-2">
                            <label for="gifts_received" class="block text-sm font-medium text-base-content/80">
                                Gifts Received (optional)
                            </label>
                            <textarea name="gifts_received" id="gifts_received" rows="3"
                                class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-green-500 focus:ring-green-500 text-sm"
                                placeholder="e.g. Coat, shoes size 4, LEGO set">{{ old('gifts_received', $child->gifts_received) }}</textarea>
                            <p class="text-xs text-base-content/60">This is saved to the child's record so you know exactly what was dropped off.</p>
                        </div>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-500 text-lg font-medium transition shadow-xs">
                        Accept Gift Drop-Off
                    </button>
                </form>
            @endunless

            <!-- Back link -->
            <div class="text-center">
                <a href="{{ route('warehouse.index') }}" class="text-sm text-base-content/60 hover:text-gray-700 dark:hover:text-gray-300">
                    &larr; Back to Warehouse
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
