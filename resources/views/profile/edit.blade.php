<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">Profile Settings</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
            @endif

            @if(session('error'))
                <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body space-y-6">

                        {{-- Avatar Section --}}
                        <div>
                            <h3 class="text-lg font-medium mb-4">Avatar</h3>
                            <div class="flex items-center gap-6">
                                <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Avatar"
                                    class="w-20 h-20 rounded-full object-cover border-2 border-base-300">
                                <div class="space-y-2">
                                    @if($user->avatar_restricted)
                                        <p class="text-sm text-base-content/60">
                                            Your avatar has been locked by an administrator.
                                        </p>
                                    @else
                                        <div>
                                            <label class="btn btn-sm btn-ghost cursor-pointer">
                                                Upload Photo
                                                <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden"
                                                    onchange="document.getElementById('avatar-action').value='upload'; previewAvatar(this)">
                                            </label>
                                        </div>
                                        <button type="button" onclick="document.getElementById('avatar-action').value='randomize'; document.getElementById('avatar-seed').value=Math.random().toString(36).substring(2,10); this.form.submit()"
                                            class="link link-info text-sm">
                                            Randomize Avatar
                                        </button>
                                        @if($user->avatar_path)
                                            <button type="button" onclick="document.getElementById('avatar-action').value='remove'; this.form.submit()"
                                                class="block link link-error text-sm">
                                                Remove Photo
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="avatar_action" id="avatar-action" value="">
                            <input type="hidden" name="avatar_seed" id="avatar-seed" value="">
                            @error('avatar')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                            @error('avatar_action')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                            @error('avatar_seed')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Website Visibility --}}
                        <div class="border-t border-base-300 pt-6">
                            <h3 class="text-lg font-medium mb-2">Website Visibility</h3>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="show_on_website" value="0">
                                <input type="checkbox" name="show_on_website" value="1"
                                    {{ $user->show_on_website ? 'checked' : '' }}
                                    class="checkbox checkbox-primary checkbox-sm">
                                <span class="text-sm">
                                    Show me on the public website's Coordinator Team section
                                </span>
                            </label>
                            <p class="mt-1 text-xs text-base-content/60 ml-8">
                                When enabled, your name, position, and avatar appear on the public homepage.
                            </p>
                            @error('show_on_website')
                                <p class="mt-1 text-sm text-error ml-8">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Account Info (read-only) --}}
                        <div class="border-t border-base-300 pt-6">
                            <h3 class="text-lg font-medium mb-4">Account Info</h3>
                            <dl class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-base-content/60">Name</dt>
                                    <dd>{{ $user->first_name }} {{ $user->last_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-base-content/60">Username</dt>
                                    <dd>{{ $user->username }}</dd>
                                </div>
                                <div>
                                    <dt class="text-base-content/60">Role</dt>
                                    <dd>
                                        @if($user->isSanta()) Santa
                                        @elseif($user->isCoordinator()) Coordinator
                                        @elseif($user->isAdvisor()) Advisor
                                        @else Family
                                        @endif
                                    </dd>
                                </div>
                                @if($user->position)
                                    <div>
                                        <dt class="text-base-content/60">Position</dt>
                                        <dd>{{ $user->position }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-base-300">
                            <x-primary-button type="submit">Save Changes</x-primary-button>
                        </div>
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
