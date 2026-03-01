<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile Settings</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">

                    {{-- Avatar Section --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Avatar</h3>
                        <div class="flex items-center gap-6">
                            <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Avatar"
                                class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600">
                            <div class="space-y-2">
                                <div>
                                    <label class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-md cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                        Upload Photo
                                        <input type="file" name="avatar" accept="image/*" class="hidden"
                                            onchange="document.getElementById('avatar-action').value='upload'; previewAvatar(this)">
                                    </label>
                                </div>
                                <button type="button" onclick="document.getElementById('avatar-action').value='randomize'; document.getElementById('avatar-seed').value=Math.random().toString(36).substring(2,10); this.form.submit()"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    Randomize Avatar
                                </button>
                                @if($user->avatar_path)
                                    <button type="button" onclick="document.getElementById('avatar-action').value='remove'; this.form.submit()"
                                        class="block text-sm text-red-600 dark:text-red-400 hover:underline">
                                        Remove Photo
                                    </button>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="avatar_action" id="avatar-action" value="">
                        <input type="hidden" name="avatar_seed" id="avatar-seed" value="">
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Website Visibility --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Website Visibility</h3>
                        <label class="flex items-center gap-3">
                            <input type="hidden" name="show_on_website" value="0">
                            <input type="checkbox" name="show_on_website" value="1"
                                {{ $user->show_on_website ? 'checked' : '' }}
                                class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Show me on the public website's Coordinator Team section
                            </span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 ml-8">
                            When enabled, your name, position, and avatar appear on the public homepage.
                        </p>
                    </div>

                    {{-- Account Info (read-only) --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Info</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Name</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $user->first_name }} {{ $user->last_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Username</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $user->username }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Role</dt>
                                <dd class="text-gray-900 dark:text-gray-100">
                                    @if($user->isSanta()) Santa
                                    @elseif($user->isCoordinator()) Coordinator
                                    @elseif($user->isAdvisor()) Advisor
                                    @else Family
                                    @endif
                                </dd>
                            </div>
                            @if($user->position)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Position</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $user->position }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>
