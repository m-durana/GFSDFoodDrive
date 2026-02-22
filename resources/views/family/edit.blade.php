<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Family: {{ $family->family_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <p class="font-medium">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('family.update', $family) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Family Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="family_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Family Name <span class="text-red-500">*</span></label>
                                <input type="text" name="family_name" id="family_name" value="{{ old('family_name', $family->family_name) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address <span class="text-red-500">*</span></label>
                                <input type="text" name="address" id="address" value="{{ old('address', $family->address) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Phone <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone1" id="phone1" value="{{ old('phone1', $family->phone1) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secondary Phone</label>
                                <input type="tel" name="phone2" id="phone2" value="{{ old('phone2', $family->phone2) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $family->email) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="preferred_language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Language</label>
                                <select name="preferred_language" id="preferred_language"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="English" {{ old('preferred_language', $family->preferred_language) === 'English' ? 'selected' : '' }}>English</option>
                                    <option value="Spanish" {{ old('preferred_language', $family->preferred_language) === 'Spanish' ? 'selected' : '' }}>Spanish</option>
                                    <option value="Other" {{ old('preferred_language', $family->preferred_language) === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Household Members -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Household Members</h3>

                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adults (18+)</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label for="female_adults" class="block text-sm text-gray-600 dark:text-gray-400">Female Adults</label>
                                <input type="number" name="female_adults" id="female_adults" value="{{ old('female_adults', $family->female_adults) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="male_adults" class="block text-sm text-gray-600 dark:text-gray-400">Male Adults</label>
                                <input type="number" name="male_adults" id="male_adults" value="{{ old('male_adults', $family->male_adults) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                        </div>

                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Children (by age group)</h4>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                            <div>
                                <label for="infants" class="block text-sm text-gray-600 dark:text-gray-400">Infants (0-2)</label>
                                <input type="number" name="infants" id="infants" value="{{ old('infants', $family->infants) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="young_children" class="block text-sm text-gray-600 dark:text-gray-400">Young (3-7)</label>
                                <input type="number" name="young_children" id="young_children" value="{{ old('young_children', $family->young_children) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="children_count" class="block text-sm text-gray-600 dark:text-gray-400">Children (8-12)</label>
                                <input type="number" name="children_count" id="children_count" value="{{ old('children_count', $family->children_count) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="tweens" class="block text-sm text-gray-600 dark:text-gray-400">Tweens (13-14)</label>
                                <input type="number" name="tweens" id="tweens" value="{{ old('tweens', $family->tweens) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="teenagers" class="block text-sm text-gray-600 dark:text-gray-400">Teenagers (15-17)</label>
                                <input type="number" name="teenagers" id="teenagers" value="{{ old('teenagers', $family->teenagers) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-sm text-gray-700 dark:text-gray-300">
                            Total Adults: <span id="total-adults" class="font-medium">0</span> |
                            Total Children: <span id="total-children" class="font-medium">0</span> |
                            Total Family Members: <span id="total-members" class="font-bold">0</span>
                        </div>
                    </div>
                </div>

                <!-- School & Pets -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">School & Pets</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_crhs_children" value="1" {{ old('has_crhs_children', $family->has_crhs_children) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has children at Crossroads High School</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_gfhs_children" value="1" {{ old('has_gfhs_children', $family->has_gfhs_children) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has children at Granite Falls High School</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="needs_baby_supplies" value="1" {{ old('needs_baby_supplies', $family->needs_baby_supplies) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Family needs baby supplies / baby food</span>
                                </label>
                            </div>
                            <div>
                                <label for="pet_information" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pet Information / Allergies</label>
                                <textarea name="pet_information" id="pet_information" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('pet_information', $family->pet_information) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Preferences -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Delivery Preferences</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="delivery_preference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preference</label>
                                <select name="delivery_preference" id="delivery_preference"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Delivery" {{ old('delivery_preference', $family->delivery_preference) === 'Delivery' ? 'selected' : '' }}>Delivery</option>
                                    <option value="Pickup" {{ old('delivery_preference', $family->delivery_preference) === 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                </select>
                            </div>
                            <div>
                                <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date</label>
                                <select name="delivery_date" id="delivery_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    <option value="December 18th" {{ old('delivery_date', $family->delivery_date) === 'December 18th' ? 'selected' : '' }}>December 18th</option>
                                    <option value="December 19th" {{ old('delivery_date', $family->delivery_date) === 'December 19th' ? 'selected' : '' }}>December 19th</option>
                                </select>
                            </div>
                            <div>
                                <label for="delivery_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Time</label>
                                <select name="delivery_time" id="delivery_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    @foreach(['8 am', '9 am', '10 am', '11 am', '12 pm', '1 pm', '2 pm', '3 pm', '4 pm', '5 pm'] as $time)
                                        <option value="{{ $time }}" {{ old('delivery_time', $family->delivery_time) === $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="delivery_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">If family cannot have items delivered, why?</label>
                            <textarea name="delivery_reason" id="delivery_reason" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('delivery_reason', $family->delivery_reason) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="need_for_help" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Needing Help</label>
                                <textarea name="need_for_help" id="need_for_help" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('need_for_help', $family->need_for_help) }}</textarea>
                            </div>
                            <div>
                                <label for="severe_need" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Severe Need Description</label>
                                <textarea name="severe_need" id="severe_need" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('severe_need', $family->severe_need) }}</textarea>
                            </div>
                            <div>
                                <label for="other_questions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Questions / Comments</label>
                                <textarea name="other_questions" id="other_questions" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('other_questions', $family->other_questions) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('family.show', $family) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Family
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Update Family
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateTotals() {
            const femaleAdults = parseInt(document.getElementById('female_adults').value) || 0;
            const maleAdults = parseInt(document.getElementById('male_adults').value) || 0;
            const infants = parseInt(document.getElementById('infants').value) || 0;
            const youngChildren = parseInt(document.getElementById('young_children').value) || 0;
            const children = parseInt(document.getElementById('children_count').value) || 0;
            const tweens = parseInt(document.getElementById('tweens').value) || 0;
            const teenagers = parseInt(document.getElementById('teenagers').value) || 0;

            const totalAdults = femaleAdults + maleAdults;
            const totalChildren = infants + youngChildren + children + tweens + teenagers;
            const totalMembers = totalAdults + totalChildren;

            document.getElementById('total-adults').textContent = totalAdults;
            document.getElementById('total-children').textContent = totalChildren;
            document.getElementById('total-members').textContent = totalMembers;
        }

        document.querySelectorAll('.member-count').forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        // Initialize totals on page load
        updateTotals();
    </script>
</x-app-layout>
