<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $family->family_name }}
                @if($family->family_number)
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">#{{ $family->family_number }}</span>
                @endif
            </h2>
            <div class="flex items-center space-x-2">
                <form method="POST" action="{{ route('family.toggleDone', $family) }}" class="inline">
                    @csrf
                    @if($family->family_done)
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                            Complete &check;
                        </button>
                    @else
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white rounded-md hover:bg-yellow-500 text-xs font-medium transition">
                            Mark Done
                        </button>
                    @endif
                </form>
                <a href="{{ route('family.edit', $family) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Edit Family
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Family Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contact Info -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Contact Information</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Address:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->address }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Phone:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->phone1 }}</dd></div>
                        @if($family->phone2)<div><dt class="text-gray-500 dark:text-gray-400 inline">Alt Phone:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->phone2 }}</dd></div>@endif
                        @if($family->email)<div><dt class="text-gray-500 dark:text-gray-400 inline">Email:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->email }}</dd></div>@endif
                        @if($family->preferred_language)<div><dt class="text-gray-500 dark:text-gray-400 inline">Language:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->preferred_language }}</dd></div>@endif
                    </dl>
                </div>

                <!-- Demographics -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Demographics</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Adults:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->number_of_adults }} ({{ $family->female_adults }}F, {{ $family->male_adults }}M)</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Children:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->number_of_children }}</dd></div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 ml-4">
                            Infants: {{ $family->infants }} | Young (3-7): {{ $family->young_children }} | Children (8-12): {{ $family->children_count }} | Tweens: {{ $family->tweens }} | Teens: {{ $family->teenagers }}
                        </div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Total Members:</dt> <dd class="inline font-medium text-gray-900 dark:text-gray-100">{{ $family->number_of_family_members }}</dd></div>
                        @if($family->needs_baby_supplies)<div class="text-yellow-600 dark:text-yellow-400 font-medium">Needs baby supplies</div>@endif
                    </dl>
                </div>

                <!-- Delivery -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Delivery</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Preference:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->delivery_preference ?? 'Not set' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Date:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->delivery_date ?? 'Not set' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400 inline">Time:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->delivery_time ?? 'Not set' }}</dd></div>
                        @if($family->delivery_team)<div><dt class="text-gray-500 dark:text-gray-400 inline">Team:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->delivery_team }}</dd></div>@endif
                        @if($family->delivery_status)<div><dt class="text-gray-500 dark:text-gray-400 inline">Status:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->delivery_status->label() }}</dd></div>@endif
                        @if($family->delivery_reason)<div><dt class="text-gray-500 dark:text-gray-400 inline">Can't deliver because:</dt> <dd class="inline text-gray-900 dark:text-gray-100">{{ $family->delivery_reason }}</dd></div>@endif
                    </dl>
                </div>

                <!-- Needs -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Needs & Notes</h3>
                    <dl class="space-y-2 text-sm">
                        @if($family->need_for_help)<div><dt class="text-gray-500 dark:text-gray-400">Reason for help:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ $family->need_for_help }}</dd></div>@endif
                        @if($family->severe_need)<div><dt class="text-red-500 dark:text-red-400 font-medium">Severe need:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ $family->severe_need }}</dd></div>@endif
                        @if($family->pet_information)<div><dt class="text-gray-500 dark:text-gray-400">Pets (for pet food):</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ $family->pet_information }}</dd></div>@endif
                        @if($family->other_questions)<div><dt class="text-gray-500 dark:text-gray-400">Other:</dt> <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ $family->other_questions }}</dd></div>@endif
                        @if($family->family_done)
                            <div class="mt-2 inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded text-xs font-medium">Family Complete</div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Children -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Children ({{ $family->children->count() }})</h3>

                    @if($family->children->count() > 0)
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
                                    @foreach($family->children as $child)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->gender }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->age }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->school ?? '-' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[150px] truncate" title="{{ $child->all_sizes }}">{{ $child->all_sizes ?? '-' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 max-w-[150px] truncate" title="{{ $child->toy_ideas }}">{{ $child->toy_ideas ?? '-' }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                @php $level = $child->gift_level ?? \App\Enums\GiftLevel::None; @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $level->color() === 'red' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : '' }}
                                                    {{ $level->color() === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                                                    {{ $level->color() === 'green' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : '' }}
                                                ">{{ $level->label() }}</span>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $child->adopter_name ?? '-' }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                @if($child->mail_merged)
                                                    <span class="text-green-600 dark:text-green-400" title="Printed">Printed</span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                                <button type="button" onclick="toggleEditChild({{ $child->id }})" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Edit</button>
                                                <form method="POST" action="{{ route('family.destroyChild', [$family, $child]) }}" class="inline" onsubmit="return confirm('Remove this child?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs ml-2">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <!-- Inline edit row (hidden by default) -->
                                        <tr id="edit-child-{{ $child->id }}" class="hidden bg-gray-50 dark:bg-gray-700/50">
                                            <td colspan="9" class="px-3 py-3">
                                                <form method="POST" action="{{ route('family.updateChild', [$family, $child]) }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Gender</label>
                                                        <select name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            <option value="Male" {{ $child->gender === 'Male' ? 'selected' : '' }}>Male</option>
                                                            <option value="Female" {{ $child->gender === 'Female' ? 'selected' : '' }}>Female</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Age</label>
                                                        <input type="text" name="age" value="{{ $child->age }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">School</label>
                                                        <input type="text" name="school" value="{{ $child->school }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">All Sizes</label>
                                                        <input type="text" name="all_sizes" value="{{ $child->all_sizes }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Clothing Options</label>
                                                        <input type="text" name="clothing_options" value="{{ $child->clothing_options }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Clothing Styles</label>
                                                        <input type="text" name="clothing_styles" value="{{ $child->clothing_styles }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Toy Ideas</label>
                                                        <input type="text" name="toy_ideas" value="{{ $child->toy_ideas }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Gift Level</label>
                                                        <select name="gift_level" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            @foreach(\App\Enums\GiftLevel::cases() as $gl)
                                                                <option value="{{ $gl->value }}" {{ ($child->gift_level ?? \App\Enums\GiftLevel::None) === $gl ? 'selected' : '' }}>{{ $gl->label() }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Gifts Received</label>
                                                        <input type="text" name="gifts_received" value="{{ $child->gifts_received }}" placeholder="List gifts received" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Adopter Name</label>
                                                        <input type="text" name="adopter_name" value="{{ $child->adopter_name }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Adopter Contact</label>
                                                        <input type="text" name="adopter_contact_info" value="{{ $child->adopter_contact_info }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 dark:text-gray-400">Where is Tag?</label>
                                                        <input type="text" name="where_is_tag" value="{{ $child->where_is_tag }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    </div>
                                                    <div class="flex items-end">
                                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">Save</button>
                                                        <button type="button" onclick="toggleEditChild({{ $child->id }})" class="ml-2 px-3 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xs">Cancel</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No children added yet.</p>
                    @endif

                    <!-- Add Child Form -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Add a Child</h4>
                        <form method="POST" action="{{ route('family.storeChild', $family) }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @csrf
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
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Clothes Size</label>
                                <input type="text" name="clothes_size" placeholder="e.g. M (8-10), 4T" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Toy Ideas</label>
                                <input type="text" name="toy_ideas" placeholder="Legos, art supplies" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">Gift Preferences</label>
                                <input type="text" name="gift_preferences" placeholder="e.g. Practical gifts, toys preferred" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
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
                <a href="{{ route('family.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
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
</x-app-layout>
