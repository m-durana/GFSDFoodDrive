<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Potential Duplicate Families
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(count($pairs) === 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No potential duplicates found.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">All families appear to be unique entries.</p>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Found <strong>{{ count($pairs) }}</strong> potential duplicate {{ Str::plural('pair', count($pairs)) }}.
                        Review each pair and take action.
                    </p>
                </div>

                @foreach($pairs as $pair)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-700">
                            <span class="text-sm font-medium text-yellow-700 dark:text-yellow-300">
                                Match Score: {{ $pair['score'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">
                            @foreach(['family_a', 'family_b'] as $key)
                                @php $family = $pair[$key]; @endphp
                                <div class="p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $family->family_name }}
                                            @if($family->family_number)
                                                <span class="text-sm font-normal text-gray-500">#{{ $family->family_number }}</span>
                                            @endif
                                        </h3>
                                        <a href="{{ route('family.show', $family) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                    </div>

                                    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                        @if($family->address)
                                            <div><span class="font-medium">Address:</span> {{ $family->address }}</div>
                                        @endif
                                        @if($family->phone1)
                                            <div><span class="font-medium">Phone:</span> {{ $family->phone1 }}</div>
                                        @endif
                                        @if($family->phone2)
                                            <div><span class="font-medium">Alt Phone:</span> {{ $family->phone2 }}</div>
                                        @endif
                                        @if($family->email)
                                            <div><span class="font-medium">Email:</span> {{ $family->email }}</div>
                                        @endif
                                        <div><span class="font-medium">Members:</span> {{ $family->number_of_family_members }} ({{ $family->children->count() }} children)</div>
                                    </div>

                                    @if($family->children->count() > 0)
                                        <div class="mt-2">
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Children:</span>
                                            <ul class="mt-1 space-y-0.5">
                                                @foreach($family->children as $child)
                                                    <li class="text-xs text-gray-600 dark:text-gray-300">
                                                        {{ $child->gender }}, age {{ $child->age }}{{ $child->school ? ' — '.$child->school : '' }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Actions -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 flex flex-wrap items-center gap-3 border-t border-gray-200 dark:border-gray-700">
                            <form method="POST" action="{{ route('santa.dismissDuplicate') }}">
                                @csrf
                                <input type="hidden" name="family_a_id" value="{{ $pair['family_a']->id }}">
                                <input type="hidden" name="family_b_id" value="{{ $pair['family_b']->id }}">
                                <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium transition">
                                    Not Duplicates
                                </button>
                            </form>

                            <form method="POST" action="{{ route('santa.mergeFamilies') }}" onsubmit="return confirm('Keep {{ $pair['family_a']->family_name }} and delete {{ $pair['family_b']->family_name }}? Children will be transferred.')">
                                @csrf
                                <input type="hidden" name="keep_id" value="{{ $pair['family_a']->id }}">
                                <input type="hidden" name="merge_id" value="{{ $pair['family_b']->id }}">
                                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Keep Left
                                </button>
                            </form>

                            <form method="POST" action="{{ route('santa.mergeFamilies') }}" onsubmit="return confirm('Keep {{ $pair['family_b']->family_name }} and delete {{ $pair['family_a']->family_name }}? Children will be transferred.')">
                                @csrf
                                <input type="hidden" name="keep_id" value="{{ $pair['family_b']->id }}">
                                <input type="hidden" name="merge_id" value="{{ $pair['family_a']->id }}">
                                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                    Keep Right
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
