<x-public-layout>
    <div class="min-h-screen bg-base-200 py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="text-center mb-8">
                <div class="flex justify-end mb-2">
                    @php $other = app()->getLocale() === 'es' ? 'en' : 'es'; @endphp
                    <a href="?{{ http_build_query(array_merge(request()->query(), ['lang' => $other])) }}" class="text-base-content/60 hover:text-base-content text-xs underline">
                        {{ $other === 'es' ? 'Español' : 'English' }}
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-base-content">{{ __('GFSD Food Drive') }} — {{ __('Family Registration') }}</h1>
                <p class="text-sm text-base-content/60 mt-2">{{ __('Please fill out the form below to register your family for the food drive.') }}</p>
            </div>

            @if($errors->any())
                <div class="bg-primary/5 dark:bg-primary/20 border border-primary/30 dark:border-primary text-primary dark:text-primary-content/80 px-4 py-3 rounded-sm">
                    <p class="font-medium">{{ __('Please fix the following errors:') }}</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('self-service.store') }}" class="space-y-6">
                @csrf

                <!-- Basic Information -->
                <div class="bg-base-100 overflow-hidden shadow-xs sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-base-content mb-4">{{ __('Family Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="family_name" class="block text-sm font-medium text-base-content/80">{{ __('Family Name') }} <span class="text-primary">*</span></label>
                                <input type="text" name="family_name" id="family_name" value="{{ old('family_name') }}" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-base-content/80">{{ __('Address') }} <span class="text-primary">*</span></label>
                                <input type="text" name="address" id="address" value="{{ old('address') }}" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="phone1" class="block text-sm font-medium text-base-content/80">{{ __('Primary Phone') }} <span class="text-primary">*</span></label>
                                <input type="tel" name="phone1" id="phone1" value="{{ old('phone1') }}" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="phone2" class="block text-sm font-medium text-base-content/80">{{ __('Secondary Phone') }}</label>
                                <input type="tel" name="phone2" id="phone2" value="{{ old('phone2') }}"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-base-content/80">{{ __('Email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="preferred_language" class="block text-sm font-medium text-base-content/80">{{ __('Preferred Language') }}</label>
                                <select name="preferred_language" id="preferred_language"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                                    <option value="English" {{ old('preferred_language', 'English') === 'English' ? 'selected' : '' }}>{{ __('English') }}</option>
                                    <option value="Spanish" {{ old('preferred_language') === 'Spanish' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                    <option value="Other" {{ old('preferred_language') === 'Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Household Members -->
                <div class="bg-base-100 overflow-hidden shadow-xs sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-base-content mb-4">{{ __('Household Members') }}</h3>

                        <h4 class="text-sm font-medium text-base-content/80 mb-2">{{ __('Adults (18+)') }}</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label for="female_adults" class="block text-sm text-base-content/70">{{ __('Female Adults') }}</label>
                                <input type="number" name="female_adults" id="female_adults" value="{{ old('female_adults', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="male_adults" class="block text-sm text-base-content/70">{{ __('Male Adults') }}</label>
                                <input type="number" name="male_adults" id="male_adults" value="{{ old('male_adults', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="other_adults" class="block text-sm text-base-content/70">{{ __('Other Adults') }}</label>
                                <input type="number" name="other_adults" id="other_adults" value="{{ old('other_adults', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                        </div>

                        <h4 class="text-sm font-medium text-base-content/80 mb-2">{{ __('Children (by age group)') }}</h4>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                            <div>
                                <label for="infants" class="block text-sm text-base-content/70">{{ __('Infants (0-2)') }}</label>
                                <input type="number" name="infants" id="infants" value="{{ old('infants', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="young_children" class="block text-sm text-base-content/70">{{ __('Young (3-7)') }}</label>
                                <input type="number" name="young_children" id="young_children" value="{{ old('young_children', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="children_count" class="block text-sm text-base-content/70">{{ __('Children (8-12)') }}</label>
                                <input type="number" name="children_count" id="children_count" value="{{ old('children_count', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="tweens" class="block text-sm text-base-content/70">{{ __('Tweens (13-14)') }}</label>
                                <input type="number" name="tweens" id="tweens" value="{{ old('tweens', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="teenagers" class="block text-sm text-base-content/70">{{ __('Teenagers (15-17)') }}</label>
                                <input type="number" name="teenagers" id="teenagers" value="{{ old('teenagers', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm member-count">
                            </div>
                        </div>

                        <div class="bg-base-200 rounded-lg p-3 text-sm text-base-content/80">
                            {{ __('Total Adults:') }} <span id="total-adults" class="font-medium">0</span> |
                            {{ __('Total Children:') }} <span id="total-children" class="font-medium">0</span> |
                            {{ __('Total Family Members:') }} <span id="total-members" class="font-bold">0</span>
                        </div>
                    </div>
                </div>

                <!-- School & Pets -->
                <div class="bg-base-100 overflow-hidden shadow-xs sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-base-content mb-4">{{ __('School & Pets') }}</h3>
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center gap-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_crhs_children" value="1" {{ old('has_crhs_children') ? 'checked' : '' }}
                                        class="rounded-sm border-base-300 text-primary shadow-xs focus:ring-primary">
                                    <span class="ml-2 text-sm text-base-content/80">{{ __('Has children at Crossroads High School') }}</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_gfhs_children" value="1" {{ old('has_gfhs_children') ? 'checked' : '' }}
                                        class="rounded-sm border-base-300 text-primary shadow-xs focus:ring-primary">
                                    <span class="ml-2 text-sm text-base-content/80">{{ __('Has children at Granite Falls High School') }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="needs_baby_supplies" value="1" {{ old('needs_baby_supplies') ? 'checked' : '' }}
                                        class="rounded-sm border-base-300 text-primary shadow-xs focus:ring-primary">
                                    <span class="ml-2 text-sm text-base-content/80">{{ __('Family needs baby supplies / baby food') }}</span>
                                </label>
                            </div>
                            <div>
                                <label for="pet_information" class="block text-sm font-medium text-base-content/80">{{ __('Pet Information / Allergies') }}</label>
                                <textarea name="pet_information" id="pet_information" rows="2"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">{{ old('pet_information') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Preferences -->
                <div class="bg-base-100 overflow-hidden shadow-xs sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-base-content mb-4">{{ __('Delivery Preferences') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="delivery_preference" class="block text-sm font-medium text-base-content/80">{{ __('Preference') }}</label>
                                <select name="delivery_preference" id="delivery_preference"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    <option value="Delivery" {{ old('delivery_preference') === 'Delivery' ? 'selected' : '' }}>{{ __('Delivery') }}</option>
                                    <option value="Pickup" {{ old('delivery_preference') === 'Pickup' ? 'selected' : '' }}>{{ __('Pickup') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="delivery_date" class="block text-sm font-medium text-base-content/80">{{ __('Delivery Date') }}</label>
                                <select name="delivery_date" id="delivery_date"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    @foreach(array_filter(array_map('trim', explode(',', \App\Models\Setting::get('delivery_dates', 'December 18th,December 19th')))) as $date)
                                        <option value="{{ $date }}" {{ old('delivery_date') === $date ? 'selected' : '' }}>{{ $date }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="delivery_time" class="block text-sm font-medium text-base-content/80">{{ __('Delivery Time') }}</label>
                                <select name="delivery_time" id="delivery_time"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    @foreach(['8 am', '9 am', '10 am', '11 am', '12 pm', '1 pm', '2 pm', '3 pm', '4 pm', '5 pm'] as $time)
                                        <option value="{{ $time }}" {{ old('delivery_time') === $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="delivery_reason" class="block text-sm font-medium text-base-content/80">{{ __('If family cannot have items delivered, why?') }}</label>
                            <textarea name="delivery_reason" id="delivery_reason" rows="2"
                                class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">{{ old('delivery_reason') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-base-100 overflow-hidden shadow-xs sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-base-content mb-4">{{ __('Additional Information') }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="need_for_help" class="block text-sm font-medium text-base-content/80">{{ __('Reason for Needing Help') }}</label>
                                <textarea name="need_for_help" id="need_for_help" rows="3"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">{{ old('need_for_help') }}</textarea>
                            </div>
                            <div>
                                <label for="severe_need" class="block text-sm font-medium text-base-content/80">{{ __('Severe Need Description') }}</label>
                                <textarea name="severe_need" id="severe_need" rows="3"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">{{ old('severe_need') }}</textarea>
                            </div>
                            <div>
                                <label for="other_questions" class="block text-sm font-medium text-base-content/80">{{ __('Other Questions / Comments') }}</label>
                                <textarea name="other_questions" id="other_questions" rows="3"
                                    class="mt-1 block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs focus:border-primary focus:ring-primary sm:text-sm">{{ old('other_questions') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                        {{ __('Submit Family Registration') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateTotals() {
            const femaleAdults = parseInt(document.getElementById('female_adults').value) || 0;
            const maleAdults = parseInt(document.getElementById('male_adults').value) || 0;
            const otherAdults = parseInt(document.getElementById('other_adults').value) || 0;
            const infants = parseInt(document.getElementById('infants').value) || 0;
            const youngChildren = parseInt(document.getElementById('young_children').value) || 0;
            const children = parseInt(document.getElementById('children_count').value) || 0;
            const tweens = parseInt(document.getElementById('tweens').value) || 0;
            const teenagers = parseInt(document.getElementById('teenagers').value) || 0;

            const totalAdults = femaleAdults + maleAdults + otherAdults;
            const totalChildren = infants + youngChildren + children + tweens + teenagers;
            const totalMembers = totalAdults + totalChildren;

            document.getElementById('total-adults').textContent = totalAdults;
            document.getElementById('total-children').textContent = totalChildren;
            document.getElementById('total-members').textContent = totalMembers;
        }

        document.querySelectorAll('.member-count').forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        updateTotals();

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('focus', function() { this.select(); });
        });
    </script>
</x-public-layout>
