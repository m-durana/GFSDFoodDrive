<?php if (isset($component)) { $__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.guest-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">GFSD Food Drive — Family Registration</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Please fill out the form below to register your family for the food drive.</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <p class="font-medium">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('self-service.store')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>

                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Family Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="family_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Family Name <span class="text-red-500">*</span></label>
                                <input type="text" name="family_name" id="family_name" value="<?php echo e(old('family_name')); ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address <span class="text-red-500">*</span></label>
                                <input type="text" name="address" id="address" value="<?php echo e(old('address')); ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Phone <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone1" id="phone1" value="<?php echo e(old('phone1')); ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secondary Phone</label>
                                <input type="tel" name="phone2" id="phone2" value="<?php echo e(old('phone2')); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="preferred_language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Language</label>
                                <select name="preferred_language" id="preferred_language"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="English" <?php echo e(old('preferred_language', 'English') === 'English' ? 'selected' : ''); ?>>English</option>
                                    <option value="Spanish" <?php echo e(old('preferred_language') === 'Spanish' ? 'selected' : ''); ?>>Spanish</option>
                                    <option value="Other" <?php echo e(old('preferred_language') === 'Other' ? 'selected' : ''); ?>>Other</option>
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
                                <input type="number" name="female_adults" id="female_adults" value="<?php echo e(old('female_adults', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="male_adults" class="block text-sm text-gray-600 dark:text-gray-400">Male Adults</label>
                                <input type="number" name="male_adults" id="male_adults" value="<?php echo e(old('male_adults', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="other_adults" class="block text-sm text-gray-600 dark:text-gray-400">Other Adults</label>
                                <input type="number" name="other_adults" id="other_adults" value="<?php echo e(old('other_adults', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                        </div>

                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Children (by age group)</h4>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                            <div>
                                <label for="infants" class="block text-sm text-gray-600 dark:text-gray-400">Infants (0-2)</label>
                                <input type="number" name="infants" id="infants" value="<?php echo e(old('infants', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="young_children" class="block text-sm text-gray-600 dark:text-gray-400">Young (3-7)</label>
                                <input type="number" name="young_children" id="young_children" value="<?php echo e(old('young_children', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="children_count" class="block text-sm text-gray-600 dark:text-gray-400">Children (8-12)</label>
                                <input type="number" name="children_count" id="children_count" value="<?php echo e(old('children_count', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="tweens" class="block text-sm text-gray-600 dark:text-gray-400">Tweens (13-14)</label>
                                <input type="number" name="tweens" id="tweens" value="<?php echo e(old('tweens', 0)); ?>" min="0" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm member-count">
                            </div>
                            <div>
                                <label for="teenagers" class="block text-sm text-gray-600 dark:text-gray-400">Teenagers (15-17)</label>
                                <input type="number" name="teenagers" id="teenagers" value="<?php echo e(old('teenagers', 0)); ?>" min="0" required
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
                            <div class="flex flex-wrap items-center gap-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_crhs_children" value="1" <?php echo e(old('has_crhs_children') ? 'checked' : ''); ?>

                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has children at Crossroads High School</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="has_gfhs_children" value="1" <?php echo e(old('has_gfhs_children') ? 'checked' : ''); ?>

                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has children at Granite Falls High School</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="needs_baby_supplies" value="1" <?php echo e(old('needs_baby_supplies') ? 'checked' : ''); ?>

                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Family needs baby supplies / baby food</span>
                                </label>
                            </div>
                            <div>
                                <label for="pet_information" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pet Information / Allergies</label>
                                <textarea name="pet_information" id="pet_information" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"><?php echo e(old('pet_information')); ?></textarea>
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
                                    <option value="Delivery" <?php echo e(old('delivery_preference') === 'Delivery' ? 'selected' : ''); ?>>Delivery</option>
                                    <option value="Pickup" <?php echo e(old('delivery_preference') === 'Pickup' ? 'selected' : ''); ?>>Pickup</option>
                                </select>
                            </div>
                            <div>
                                <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date</label>
                                <select name="delivery_date" id="delivery_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    <?php $__currentLoopData = array_filter(array_map('trim', explode(',', \App\Models\Setting::get('delivery_dates', 'December 18th,December 19th')))); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($date); ?>" <?php echo e(old('delivery_date') === $date ? 'selected' : ''); ?>><?php echo e($date); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label for="delivery_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Time</label>
                                <select name="delivery_time" id="delivery_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Select --</option>
                                    <?php $__currentLoopData = ['8 am', '9 am', '10 am', '11 am', '12 pm', '1 pm', '2 pm', '3 pm', '4 pm', '5 pm']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($time); ?>" <?php echo e(old('delivery_time') === $time ? 'selected' : ''); ?>><?php echo e($time); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="delivery_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">If family cannot have items delivered, why?</label>
                            <textarea name="delivery_reason" id="delivery_reason" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"><?php echo e(old('delivery_reason')); ?></textarea>
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
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"><?php echo e(old('need_for_help')); ?></textarea>
                            </div>
                            <div>
                                <label for="severe_need" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Severe Need Description</label>
                                <textarea name="severe_need" id="severe_need" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"><?php echo e(old('severe_need')); ?></textarea>
                            </div>
                            <div>
                                <label for="other_questions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Questions / Comments</label>
                                <textarea name="other_questions" id="other_questions" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"><?php echo e(old('other_questions')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Submit Family Registration
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
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a)): ?>
<?php $attributes = $__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a; ?>
<?php unset($__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a)): ?>
<?php $component = $__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a; ?>
<?php unset($__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/self-service/create.blade.php ENDPATH**/ ?>