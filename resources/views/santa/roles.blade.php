<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Roles
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h2 class="text-lg font-semibold text-base-content">Roles</h2>
                <p class="text-sm text-base-content/60 mt-1">
                    Add custom roles or rename / delete existing custom ones. Built-in roles are protected
                    because route guards and controllers reference them by name. Newly created roles default
                    to Advisor permission tier (7) when assigned; raise them via the
                    <a href="{{ route('santa.settings') }}#sudoer-roles" class="link link-primary">sudoer roles</a> list
                    or the
                    <a href="{{ route('santa.settings') }}#coordinator-section-map" class="link link-primary">section permission map</a>.
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Existing roles --}}
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h3 class="text-md font-semibold text-base-content mb-3">Existing roles</h3>
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-base-content/60 border-b border-base-300">
                        <tr>
                            <th class="text-left py-2">Name</th>
                            <th class="text-left py-2">Users</th>
                            <th class="text-left py-2">Sudoer?</th>
                            <th class="text-right py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            @php $isProtected = in_array($role->name, $protectedRoles, true); @endphp
                            <tr class="border-b border-base-200">
                                <td class="py-2 font-mono">{{ $role->name }}</td>
                                <td class="py-2">{{ $role->users_count }}</td>
                                <td class="py-2">
                                    @if(in_array($role->name, $sudoerRoles, true))
                                        <span class="badge badge-warning badge-sm">sudoer</span>
                                    @else
                                        <span class="text-base-content/40">—</span>
                                    @endif
                                </td>
                                <td class="py-2">
                                    <div class="flex justify-end gap-2">
                                        @if($isProtected)
                                            <span class="text-xs text-base-content/40">built-in</span>
                                        @else
                                            <form method="POST" action="{{ route('santa.roles.update', $role) }}" class="flex gap-1 items-center"
                                                  onsubmit="return confirm('Rename role {{ $role->name }}?');">
                                                @csrf @method('PUT')
                                                <input type="text" name="name" value="{{ $role->name }}"
                                                    class="input input-bordered input-xs w-32 font-mono" required>
                                                <button type="submit" class="btn btn-xs btn-outline">Rename</button>
                                            </form>
                                            <form method="POST" action="{{ route('santa.roles.destroy', $role) }}"
                                                  onsubmit="return confirm('Delete role {{ $role->name }}? This cannot be undone.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-error btn-outline">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Add new role --}}
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h3 class="text-md font-semibold text-base-content mb-3">Add a new role</h3>
                <form method="POST" action="{{ route('santa.roles.store') }}" class="flex gap-2 items-center">
                    @csrf
                    <input type="text" name="name" placeholder="e.g. donor_liaison"
                        class="input input-bordered input-sm w-full max-w-xs font-mono"
                        pattern="[a-z0-9_]{2,64}"
                        title="Lowercase letters, digits, underscores; 2–64 characters"
                        required>
                    <button type="submit" class="btn btn-sm btn-primary">Create role</button>
                </form>
                <p class="text-xs text-base-content/60 mt-2">
                    Lowercase letters, digits, underscores. Once created, you can promote it to sudoer or
                    assign it sections from the Settings page.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
