<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <?php echo e($family->family_name); ?>

                <?php if($family->family_number): ?>
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">#<?php echo e($family->family_number); ?></span>
                <?php endif; ?>
            </h2>
            <div class="flex items-center space-x-2">
                <form method="POST" action="<?php echo e(route('family.toggleDone', $family)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <?php if($family->family_done): ?>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                            Complete &check;
                        </button>
                    <?php else: ?>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white rounded-md hover:bg-yellow-500 text-xs font-medium transition">
                            Mark Done
                        </button>
                    <?php endif; ?>
                </form>
                <a href="<?php echo e(route('family.edit', $family)); ?>" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Edit Family
                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Family Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contact Info -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Contact Information</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Address:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->address); ?></dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Phone:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->phone1); ?></dd></div>
                        <?php if($family->phone2): ?><div><dt class="text-gray-500 dark:text-gray-400 inline">Alt Phone:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->phone2); ?></dd></div><?php endif; ?>
                        <?php if($family->email): ?><div><dt class="text-gray-500 dark:text-gray-400 inline">Email:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->email); ?></dd></div><?php endif; ?>
                        <?php if($family->preferred_language): ?><div><dt class="text-gray-500 dark:text-gray-400 inline">Language:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->preferred_language); ?></dd></div><?php endif; ?>
                    </dl>
                </div>

                <!-- Demographics -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Demographics</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Adults:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->number_of_adults); ?> (<?php echo e($family->female_adults); ?>F, <?php echo e($family->male_adults); ?>M)</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Children:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->number_of_children); ?></dd></div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 ml-4">
                            Infants: <?php echo e($family->infants); ?> | Young (3-7): <?php echo e($family->young_children); ?> | Children (8-12): <?php echo e($family->children_count); ?> | Tweens: <?php echo e($family->tweens); ?> | Teens: <?php echo e($family->teenagers); ?>

                        </div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Total Members:</dt> <dd class="inline font-medium text-gray-900 dark:text-gray-100"><?php echo e($family->number_of_family_members); ?></dd></div>
                        <?php if($family->needs_baby_supplies): ?><div class="text-yellow-600 dark:text-yellow-400 font-medium">Needs baby supplies</div><?php endif; ?>
                    </dl>
                </div>

                <!-- Delivery -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Delivery</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Preference:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_preference ?? 'Not set'); ?></dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Date:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_date ?? 'Not set'); ?></dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Time:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_time ?? 'Not set'); ?></dd></div>
                        <?php if($family->delivery_team): ?><div><dt class="text-gray-500 dark:text-gray-400 inline">Team:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_team); ?></dd></div><?php endif; ?>
                        <?php if($family->delivery_status): ?><div><dt class="text-gray-500 dark:text-gray-400 inline">Status:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_status->label()); ?></dd></div><?php endif; ?>
                        <?php if($family->delivery_reason): ?><div><dt class="text-gray-500 dark:text-gray-400 inline">Can't deliver because:</dt> <dd class="inline text-gray-900 dark:text-gray-100"><?php echo e($family->delivery_reason); ?></dd></div><?php endif; ?>
                    </dl>
                </div>

                <!-- Needs -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Needs & Notes</h3>
                    <dl class="space-y-2 text-sm">
                        <?php if($family->need_for_help): ?><div><dt class="text-gray-500 dark:text-gray-400">Reason for help:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1"><?php echo e($family->need_for_help); ?></dd></div><?php endif; ?>
                        <?php if($family->severe_need): ?><div><dt class="text-red-500 dark:text-red-400 font-medium">Severe need:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1"><?php echo e($family->severe_need); ?></dd></div><?php endif; ?>
                        <?php if($family->pet_information): ?><div><dt class="text-gray-500 dark:text-gray-400">Pets:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1"><?php echo e($family->pet_information); ?></dd></div><?php endif; ?>
                        <?php if($family->other_questions): ?><div><dt class="text-gray-500 dark:text-gray-400">Other:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1"><?php echo e($family->other_questions); ?></dd></div><?php endif; ?>
                        <?php if($family->family_done): ?>
                            <div class="mt-2 inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded text-xs font-medium">Family Complete</div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Children -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Children (<?php echo e($family->children->count()); ?>)</h3>

                    <?php if($family->children->count() > 0): ?>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gender</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Age</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">School</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sizes</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Toy Ideas</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gift Level</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopter</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tag</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php $__currentLoopData = $family->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->gender); ?></td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->age); ?></td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->school ?? '-'); ?></td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[150px] truncate" title="<?php echo e($child->all_sizes); ?>"><?php echo e($child->all_sizes ?? '-'); ?></td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[150px] truncate" title="<?php echo e($child->toy_ideas); ?>"><?php echo e($child->toy_ideas ?? '-'); ?></td>
                                            <td class="px-3 py-2 text-sm">
                                                <?php $level = $child->gift_level ?? \App\Enums\GiftLevel::None; ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    <?php echo e($level->color() === 'red' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : ''); ?>

                                                    <?php echo e($level->color() === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : ''); ?>

                                                    <?php echo e($level->color() === 'green' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : ''); ?>

                                                "><?php echo e($level->label()); ?></span>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100"><?php echo e($child->adopter_name ?? '-'); ?></td>
                                            <td class="px-3 py-2 text-sm">
                                                <?php if($child->mail_merged): ?>
                                                    <span class="text-green-600 dark:text-green-400" title="Printed">Printed</span>
                                                <?php else: ?>
                                                    <span class="text-gray-400 dark:text-gray-500">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                                <button type="button" onclick="toggleEditChild(<?php echo e($child->id); ?>)" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Edit</button>
                                                <form method="POST" action="<?php echo e(route('family.destroyChild', [$family, $child])); ?>" class="inline" onsubmit="return confirm('Remove this child?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs ml-2">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <!-- Inline edit row (hidden by default) -->
                                        <tr id="edit-child-<?php echo e($child->id); ?>" class="hidden bg-gray-50 dark:bg-gray-700/50">
                                            <td colspan="9" class="px-3 py-3">
                                                <form method="POST" action="<?php echo e(route('family.updateChild', [$family, $child])); ?>" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Gender</label>
                                                        <select name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            <option value="Male" <?php echo e($child->gender === 'Male' ? 'selected' : ''); ?>>Male</option>
                                                            <option value="Female" <?php echo e($child->gender === 'Female' ? 'selected' : ''); ?>>Female</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Age</label>
                                                        <input type="text" name="age" value="<?php echo e($child->age); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">School</label>
                                                        <input type="text" name="school" value="<?php echo e($child->school); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">All Sizes</label>
                                                        <input type="text" name="all_sizes" value="<?php echo e($child->all_sizes); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Clothing Options</label>
                                                        <input type="text" name="clothing_options" value="<?php echo e($child->clothing_options); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Clothing Styles</label>
                                                        <input type="text" name="clothing_styles" value="<?php echo e($child->clothing_styles); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Toy Ideas</label>
                                                        <input type="text" name="toy_ideas" value="<?php echo e($child->toy_ideas); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Gift Level</label>
                                                        <select name="gift_level" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            <?php $__currentLoopData = \App\Enums\GiftLevel::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($gl->value); ?>" <?php echo e(($child->gift_level ?? \App\Enums\GiftLevel::None) === $gl ? 'selected' : ''); ?>><?php echo e($gl->label()); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Gifts Received</label>
                                                        <input type="text" name="gifts_received" value="<?php echo e($child->gifts_received); ?>" placeholder="List gifts received" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Adopter Name</label>
                                                        <input type="text" name="adopter_name" value="<?php echo e($child->adopter_name); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Adopter Contact</label>
                                                        <input type="text" name="adopter_contact_info" value="<?php echo e($child->adopter_contact_info); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Where is Tag?</label>
                                                        <input type="text" name="where_is_tag" value="<?php echo e($child->where_is_tag); ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div class="flex items-end">
                                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">Save</button>
                                                        <button type="button" onclick="toggleEditChild(<?php echo e($child->id); ?>)" class="ml-2 px-3 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xs">Cancel</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No children added yet.</p>
                    <?php endif; ?>

                    <!-- Add Child Form -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Add a Child</h4>
                        <form method="POST" action="<?php echo e(route('family.storeChild', $family)); ?>" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Gender <span class="text-red-500">*</span></label>
                                <select name="gender" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Age <span class="text-red-500">*</span></label>
                                <input type="text" name="age" required placeholder="e.g. 5, 12, 16" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">School</label>
                                <input type="text" name="school" placeholder="e.g. Mountain Way" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">All Sizes</label>
                                <input type="text" name="all_sizes" placeholder="Shirt M, Pants 10, Shoe 5" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Clothing Options</label>
                                <input type="text" name="clothing_options" placeholder="Shirts, pants, shoes" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Clothing Styles</label>
                                <input type="text" name="clothing_styles" placeholder="Sporty, casual" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Toy Ideas</label>
                                <input type="text" name="toy_ideas" placeholder="Legos, art supplies" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Add Child
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div>
                <a href="<?php echo e(route('family.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleEditChild(childId) {
            const row = document.getElementById('edit-child-' + childId);
            row.classList.toggle('hidden');
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/family/show.blade.php ENDPATH**/ ?>