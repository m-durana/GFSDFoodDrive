<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add New Family
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Step Indicator --}}
            <div class="flex items-center justify-center gap-2 text-sm" id="step-indicator">
                <button type="button" onclick="goToStep(1)" class="step-dot active px-4 py-2 rounded-full bg-red-700 text-white font-medium transition">1. Family Info</button>
                <div class="w-8 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                <button type="button" onclick="goToStep(2)" class="step-dot px-4 py-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-medium transition">2. Children</button>
                <div class="w-8 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                <button type="button" onclick="goToStep(3)" class="step-dot px-4 py-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-medium transition">3. Review</button>
            </div>

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

            <form method="POST" action="{{ route('family.store') }}" class="space-y-6" id="family-form">
                @csrf

                {{-- ══════════ STEP 1: Family Info ══════════ --}}
                <div id="step-1" class="wizard-step space-y-6">

                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Family Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="family_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Family Name <span class="text-red-500">*</span></label>
                                <input type="text" name="family_name" id="family_name" value="{{ old('family_name') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address <span class="text-red-500">*</span></label>
                                <input type="text" name="address" id="address" value="{{ old('address') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Phone <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone1" id="phone1" value="{{ old('phone1') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secondary Phone</label>
                                <input type="tel" name="phone2" id="phone2" value="{{ old('phone2') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="preferred_language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Language</label>
                                <select name="preferred_language" id="preferred_language"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="English" {{ old('preferred_language', 'English') === 'English' ? 'selected' : '' }}>English</option>
                                    <option value="Spanish" {{ old('preferred_language') === 'Spanish' ? 'selected' : '' }}>Spanish</option>
                                    <option value="Other" {{ !in_array(old('preferred_language', 'English'), ['English', 'Spanish']) ? 'selected' : '' }}>Other</option>
                                </select>
                                <input type="text" name="preferred_language_other" id="preferred_language_other"
                                    value="{{ old('preferred_language_other') }}" placeholder="Please specify language"
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm {{ in_array(old('preferred_language', 'English'), ['English', 'Spanish']) ? 'hidden' : '' }}"
                                    id="preferred_language_other_input">
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
                                <input type="number" name="female_adults" id="female_adults" value="{{ old('female_adults', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="male_adults" class="block text-sm text-gray-600 dark:text-gray-400">Male Adults</label>
                                <input type="number" name="male_adults" id="male_adults" value="{{ old('male_adults', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="other_adults" class="block text-sm text-gray-600 dark:text-gray-400">Other Adults</label>
                                <input type="number" name="other_adults" id="other_adults" value="{{ old('other_adults', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                        </div>

                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Children (by age group)</h4>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                            <div>
                                <label for="infants" class="block text-sm text-gray-600 dark:text-gray-400">Infants (0-2)</label>
                                <input type="number" name="infants" id="infants" value="{{ old('infants', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="young_children" class="block text-sm text-gray-600 dark:text-gray-400">Young (3-7)</label>
                                <input type="number" name="young_children" id="young_children" value="{{ old('young_children', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="children_count" class="block text-sm text-gray-600 dark:text-gray-400">Children (8-12)</label>
                                <input type="number" name="children_count" id="children_count" value="{{ old('children_count', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="tweens" class="block text-sm text-gray-600 dark:text-gray-400">Tweens (13-14)</label>
                                <input type="number" name="tweens" id="tweens" value="{{ old('tweens', 0) }}" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="teenagers" class="block text-sm text-gray-600 dark:text-gray-400">Teenagers (15-17)</label>
                                <input type="number" name="teenagers" id="teenagers" value="{{ old('teenagers', 0) }}" min="0" required
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

                <!-- School & Needs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">School & Needs</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_crhs_children" value="1" {{ old('has_crhs_children') ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has children at Crossroads High School</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_gfhs_children" value="1" {{ old('has_gfhs_children') ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has children at Granite Falls High School</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="needs_baby_supplies" value="1" {{ old('needs_baby_supplies') ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Family needs baby supplies / baby food</span>
                                </label>
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
                                    <option value="Delivery" {{ old('delivery_preference') === 'Delivery' ? 'selected' : '' }}>Delivery</option>
                                    <option value="Pickup" {{ old('delivery_preference') === 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                </select>
                            </div>
                            <div>
                                <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date</label>
                                <select name="delivery_date" id="delivery_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    @foreach(array_filter(array_map('trim', explode(',', \App\Models\Setting::get('delivery_dates', 'December 18th,December 19th')))) as $date)
                                        <option value="{{ $date }}" {{ old('delivery_date') === $date ? 'selected' : '' }}>{{ $date }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="delivery_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Time</label>
                                <select name="delivery_time" id="delivery_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    @foreach(['8 am', '9 am', '10 am', '11 am', '12 pm', '1 pm', '2 pm', '3 pm', '4 pm', '5 pm'] as $time)
                                        <option value="{{ $time }}" {{ old('delivery_time') === $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="delivery_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">If family can't have items picked up, why?</label>
                            <textarea name="delivery_reason" id="delivery_reason" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('delivery_reason') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="pet_information" class="block text-sm font-medium text-gray-700 dark:text-gray-300">What pets does family have?</label>
                                <textarea name="pet_information" id="pet_information" rows="2" placeholder="e.g. 2 dogs, 1 cat (for pet food)"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('pet_information') }}</textarea>
                            </div>
                            <div>
                                <label for="need_for_help" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Needing Help</label>
                                <textarea name="need_for_help" id="need_for_help" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('need_for_help') }}</textarea>
                            </div>
                            @if(auth()->user()->isSanta() || auth()->user()->permission >= 7)
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_severe_need" value="1" {{ old('is_severe_need') ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500"
                                        id="is_severe_need_checkbox">
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Severe Need</span>
                                </label>
                                <textarea name="severe_need_notes" id="severe_need_notes" rows="2" placeholder="Optional: describe the severe need"
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm {{ old('is_severe_need') ? '' : 'hidden' }}">{{ old('severe_need_notes') }}</textarea>
                            </div>
                            @endif
                            <div>
                                <label for="other_questions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Questions / Comments</label>
                                <textarea name="other_questions" id="other_questions" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('other_questions') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1 Navigation -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('family.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Dashboard
                    </a>
                    <button type="button" onclick="goToStep(2)" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Next: Add Children &rarr;
                    </button>
                </div>

                </div> {{-- end step-1 --}}

                {{-- ══════════ STEP 2: Children ══════════ --}}
                <div id="step-2" class="wizard-step space-y-6 hidden">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Children Details</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Total children: <span id="wizard-child-count" class="font-bold">0</span>
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Fill in details for each child. These details are used for gift tags and Adopt-a-Tag.
                            You can also add children later from the family page.
                        </p>

                        <div id="children-container" class="space-y-4">
                            {{-- Dynamically generated child forms appear here --}}
                        </div>

                        <p id="no-children-msg" class="text-center text-gray-400 dark:text-gray-500 py-8 text-sm">
                            Go back to Step 1 and enter children counts to generate entry forms here.
                        </p>
                    </div>
                </div>

                <!-- Step 2 Navigation -->
                <div class="flex items-center justify-between">
                    <button type="button" onclick="goToStep(1)" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Family Info
                    </button>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium transition">
                            Skip &amp; Save Without Children
                        </button>
                        <button type="button" onclick="goToStep(3)" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Review &amp; Submit &rarr;
                        </button>
                    </div>
                </div>

                </div> {{-- end step-2 --}}

                {{-- ══════════ STEP 3: Review ══════════ --}}
                <div id="step-3" class="wizard-step space-y-6 hidden">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Review &amp; Submit</h3>
                        <div id="review-summary" class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            {{-- Populated by JS --}}
                        </div>
                    </div>
                </div>

                <!-- Step 3 Navigation -->
                <div class="flex items-center justify-between">
                    <button type="button" onclick="goToStep(2)" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Children
                    </button>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-green-700 text-white rounded-md hover:bg-green-600 text-sm font-bold transition">
                        Save Family &amp; Children
                    </button>
                </div>

                </div> {{-- end step-3 --}}

            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;

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

        // Language "Other" toggle
        document.getElementById('preferred_language').addEventListener('change', function() {
            const otherInput = document.getElementById('preferred_language_other');
            if (this.value === 'Other') {
                otherInput.classList.remove('hidden');
                otherInput.focus();
            } else {
                otherInput.classList.add('hidden');
                otherInput.value = '';
            }
        });

        // Number inputs: select all on focus
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('focus', function() { this.select(); });
        });

        // Severe need checkbox toggle
        const severeCheckbox = document.getElementById('is_severe_need_checkbox');
        if (severeCheckbox) {
            severeCheckbox.addEventListener('change', function() {
                const notes = document.getElementById('severe_need_notes');
                notes.classList.toggle('hidden', !this.checked);
            });
        }

        // ── Wizard Step Navigation ──
        function goToStep(step) {
            if (step === 2) generateChildForms();
            if (step === 3) buildReview();
            currentStep = step;
            document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('hidden'));
            document.getElementById('step-' + step).classList.remove('hidden');
            // Update step dots
            document.querySelectorAll('.step-dot').forEach((dot, i) => {
                if (i + 1 <= step) {
                    dot.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-500', 'dark:text-gray-400');
                    dot.classList.add('bg-red-700', 'text-white');
                } else {
                    dot.classList.remove('bg-red-700', 'text-white');
                    dot.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-500', 'dark:text-gray-400');
                }
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // ── Dynamic Child Form Generation ──
        function getTotalChildren() {
            return (parseInt(document.getElementById('infants').value) || 0)
                + (parseInt(document.getElementById('young_children').value) || 0)
                + (parseInt(document.getElementById('children_count').value) || 0)
                + (parseInt(document.getElementById('tweens').value) || 0)
                + (parseInt(document.getElementById('teenagers').value) || 0);
        }

        function generateChildForms() {
            const count = getTotalChildren();
            const container = document.getElementById('children-container');
            const msg = document.getElementById('no-children-msg');
            document.getElementById('wizard-child-count').textContent = count;

            if (count === 0) {
                container.innerHTML = '';
                msg.classList.remove('hidden');
                return;
            }
            msg.classList.add('hidden');

            // Preserve existing forms if count matches
            const existing = container.querySelectorAll('.child-form');
            if (existing.length === count) return;

            // Build age labels from counts
            const groups = [
                { id: 'infants', label: 'Infant (0-2)' },
                { id: 'young_children', label: 'Young Child (3-7)' },
                { id: 'children_count', label: 'Child (8-12)' },
                { id: 'tweens', label: 'Tween (13-14)' },
                { id: 'teenagers', label: 'Teen (15-17)' },
            ];
            let childLabels = [];
            groups.forEach(g => {
                const n = parseInt(document.getElementById(g.id).value) || 0;
                for (let i = 0; i < n; i++) childLabels.push(g.label);
            });

            container.innerHTML = '';
            childLabels.forEach((label, i) => {
                const div = document.createElement('div');
                div.className = 'child-form bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600';
                const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm';
                div.innerHTML = `
                    <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-3">Child ${i + 1} <span class="text-sm font-normal text-gray-500">(${label})</span></h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Gender</label>
                            <select name="children[${i}][gender]" class="${inputClass}">
                                <option value="">-- Select --</option>
                                <option value="Boy">Boy</option>
                                <option value="Girl">Girl</option>
                                <option value="Non-binary">Non-binary</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Age</label>
                            <input type="text" name="children[${i}][age]" placeholder="e.g. 5" class="${inputClass}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">School</label>
                            <input type="text" name="children[${i}][school]" placeholder="School name" class="${inputClass}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Clothing Options</label>
                            <input type="text" name="children[${i}][clothing_options]" placeholder="e.g. pants, shirts" class="${inputClass}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Clothing Styles</label>
                            <input type="text" name="children[${i}][clothing_styles]" placeholder="e.g. sporty, casual" class="${inputClass}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Sizes</label>
                            <input type="text" name="children[${i}][all_sizes]" placeholder="e.g. Youth M, Size 5" class="${inputClass}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Toy Ideas</label>
                            <input type="text" name="children[${i}][toy_ideas]" placeholder="e.g. LEGO, art supplies" class="${inputClass}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400">Gift Preferences</label>
                            <input type="text" name="children[${i}][gift_preferences]" placeholder="e.g. books, sports" class="${inputClass}">
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        // ── Review Summary ──
        function buildReview() {
            const summary = document.getElementById('review-summary');
            const val = id => document.getElementById(id)?.value || '';
            const totalChildren = getTotalChildren();
            const childForms = document.querySelectorAll('.child-form');
            let childrenHtml = '';
            childForms.forEach((form, i) => {
                const gender = form.querySelector(`[name="children[${i}][gender]"]`)?.value || 'Not set';
                const age = form.querySelector(`[name="children[${i}][age]"]`)?.value || '?';
                childrenHtml += `<li>Child ${i + 1}: ${gender}, age ${age}</li>`;
            });

            summary.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">Family</p>
                        <p><strong>${val('family_name')}</strong></p>
                        <p class="text-gray-500">${val('address')}</p>
                        <p class="text-gray-500">${val('phone1')}${val('phone2') ? ' / ' + val('phone2') : ''}</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">Household</p>
                        <p>Adults: ${document.getElementById('total-adults').textContent}</p>
                        <p>Children: ${document.getElementById('total-children').textContent}</p>
                        <p>Total: ${document.getElementById('total-members').textContent}</p>
                    </div>
                </div>
                ${totalChildren > 0 ? `
                <div class="mt-3">
                    <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">Children (${totalChildren})</p>
                    <ul class="list-disc list-inside text-gray-600 dark:text-gray-400">${childrenHtml || '<li>No details entered</li>'}</ul>
                </div>` : '<p class="mt-3 text-gray-400">No children to add.</p>'}
                <p class="mt-3 text-xs text-gray-400">You can always edit children later from the family detail page.</p>
            `;
        }
    </script>
</x-app-layout>
