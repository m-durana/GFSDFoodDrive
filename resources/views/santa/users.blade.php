<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Manage Users
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success/Error messages -->
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('created_credentials'))
                <div id="credentials-banner" class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-200 px-4 py-3 rounded">
                    <p class="font-medium mb-2">New user credentials (copy before navigating away):</p>
                    <div class="flex items-center space-x-3">
                        <code id="credentials-text" class="bg-blue-100 dark:bg-blue-800 px-3 py-1 rounded text-sm font-mono">{{ session('created_credentials') }}</code>
                        <button onclick="copyCredentials()" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium transition">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" /></svg>
                            Copy
                        </button>
                        <span id="copy-confirm" class="text-xs text-green-600 dark:text-green-400 hidden">Copied!</span>
                    </div>
                </div>
            @endif

            <!-- Pending Access Requests -->
            @if($accessRequests->count() > 0)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-amber-800 dark:text-amber-200 mb-4">
                            <svg class="inline h-5 w-5 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                            Pending Access Requests ({{ $accessRequests->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($accessRequests as $req)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 gap-3">
                                    <div class="flex items-center gap-3">
                                        @if($req->avatar)
                                            <img src="{{ $req->avatar }}" alt="" class="w-10 h-10 rounded-full">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 text-sm font-bold">{{ strtoupper(substr($req->name, 0, 1)) }}</div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $req->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $req->email }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                Requested: <span class="font-medium">{{ $req->roleLabel() }}</span>
                                                @if($req->school_source) &middot; {{ $req->school_source }} @endif
                                                &middot; {{ $req->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <form method="POST" action="{{ route('santa.approveAccessRequest', $req) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="role" value="{{ $req->requested_role }}">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition" onclick="return confirm('Approve {{ $req->name }}?')">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('santa.denyAccessRequest', $req) }}" class="inline" onsubmit="var r=prompt('Reason (optional):');if(r!==null){this.querySelector('[name=deny_reason]').value=r;return true;}return false;">
                                            @csrf
                                            <input type="hidden" name="deny_reason" value="">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-500 text-xs font-medium transition">Deny</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Add New User Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add New User</h3>
                    <form method="POST" action="{{ route('santa.storeUser') }}" class="space-y-4" x-data="{ newRole: '{{ old('role', 'advisor') }}' }">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                                <input type="text" name="username" value="{{ old('username') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select name="role" x-model="newRole" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="advisor">Advisor</option>
                                    <option value="coordinator">Coordinator</option>
                                    <option value="santa">Santa</option>
                                </select>
                            </div>
                            <div x-show="newRole === 'advisor' || newRole === 'coordinator'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                                <select name="school_source" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="">-- None --</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school }}">{{ $school }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="newRole === 'coordinator' || newRole === 'santa'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Position</label>
                                <select name="position" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="">-- None --</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}">{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="password" id="new-password" required class="block w-full rounded-l-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    <button type="button" onclick="generatePassword()" class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 text-xs" title="Generate">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">Add User</button>
                    </form>
                </div>
            </div>

            <!-- Existing Users (Single Bulk Form) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Existing Users ({{ $users->count() }})</h3>

                    <form method="POST" action="{{ route('santa.bulkUpdateUsers') }}" id="bulk-form">
                        @csrf
                        <div class="overflow-x-auto" x-data="sortTable()">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avatar</th>
                                        <x-sort-th key="username" class="px-3 py-3">Username</x-sort-th>
                                        <x-sort-th key="first_name" class="px-3 py-3">First Name</x-sort-th>
                                        <x-sort-th key="last_name" class="px-3 py-3">Last Name</x-sort-th>
                                        <x-sort-th key="role" class="px-3 py-3">Role</x-sort-th>
                                        <x-sort-th key="position" class="px-3 py-3">Position</x-sort-th>
                                        <x-sort-th key="school" class="px-3 py-3">School</x-sort-th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">New Password</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Flags</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-data>
                                    @foreach($users as $u)
                                        @php $isCurrentUser = $u->id === auth()->id(); @endphp
                                        <tr class="{{ $u->permission === 0 ? 'bg-gray-100 dark:bg-gray-900/50 opacity-60' : '' }}" x-data="{ role: '{{ match($u->permission) { 0 => 'inactive', 7 => 'advisor', 8 => 'coordinator', 9 => 'santa', default => 'advisor' } }}' }">
                                            <!-- Avatar -->
                                            <td class="px-3 py-2" x-data="{ avatarUrl: '{{ $u->avatar_url }}' }">
                                                <div class="flex items-center gap-1">
                                                    <img :src="avatarUrl" alt="" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                                                    <button type="button" @click="fetch('{{ route('santa.randomizeUserAvatar', $u) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(r => r.json()).then(d => avatarUrl = d.avatar_url)" class="text-gray-400 hover:text-blue-500 transition" title="Randomize avatar">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <!-- Username (read-only) -->
                                            <td class="px-3 py-2" data-sort-value="{{ $u->username }}">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 truncate block max-w-[120px]" title="{{ $u->username }}">{{ $u->username }}</span>
                                            </td>
                                            <!-- First Name -->
                                            <td class="px-3 py-2" data-sort-value="{{ $u->first_name }}">
                                                <input type="text" name="users[{{ $u->id }}][first_name]" value="{{ $u->first_name }}"
                                                    class="w-full min-w-[80px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                            </td>
                                            <!-- Last Name -->
                                            <td class="px-3 py-2" data-sort-value="{{ $u->last_name }}">
                                                <input type="text" name="users[{{ $u->id }}][last_name]" value="{{ $u->last_name }}"
                                                    class="w-full min-w-[80px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                            </td>
                                            <!-- Role -->
                                            <td class="px-3 py-2" data-sort-value="{{ match($u->permission) { 0 => 'inactive', 7 => 'advisor', 8 => 'coordinator', 9 => 'santa', default => 'advisor' } }}">
                                                <select name="users[{{ $u->id }}][role]" x-model="role"
                                                    class="w-full min-w-[100px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    <option value="inactive" {{ $u->permission === 0 ? 'selected' : '' }}>Inactive</option>
                                                    <option value="advisor" {{ $u->permission === 7 ? 'selected' : '' }}>Advisor</option>
                                                    <option value="coordinator" {{ $u->permission === 8 ? 'selected' : '' }}>Coordinator</option>
                                                    <option value="santa" {{ $u->permission === 9 ? 'selected' : '' }}>Santa</option>
                                                </select>
                                            </td>
                                            <!-- Position -->
                                            <td class="px-3 py-2" data-sort-value="{{ $u->position ?? '' }}">
                                                <select name="users[{{ $u->id }}][position]" :disabled="role === 'advisor' || role === 'inactive'"
                                                    class="w-full min-w-[100px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                                                    <option value="">--</option>
                                                    @foreach($positions as $pos)
                                                        <option value="{{ $pos }}" {{ $u->position === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <!-- School -->
                                            <td class="px-3 py-2" data-sort-value="{{ $u->school_source ?? '' }}">
                                                <select name="users[{{ $u->id }}][school_source]" :disabled="role === 'santa' || role === 'inactive'"
                                                    class="w-full min-w-[100px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                                                    <option value="">--</option>
                                                    @foreach($schools as $school)
                                                        <option value="{{ $school }}" {{ $u->school_source === $school ? 'selected' : '' }}>{{ $school }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <!-- New Password -->
                                            <td class="px-3 py-2" x-data="{ generatedPw: '' }">
                                                <input type="hidden" name="users[{{ $u->id }}][password]" :value="generatedPw">
                                                <div class="flex items-center gap-1">
                                                    <span x-show="generatedPw" class="text-xs text-green-600 dark:text-green-400 font-mono truncate max-w-[80px]" x-text="generatedPw" title="Password set"></span>
                                                    <span x-show="!generatedPw" class="text-xs text-gray-400">Unchanged</span>
                                                    <button type="button" @click="generatedPw = Array.from(crypto.getRandomValues(new Uint8Array(9)), b => 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'.charAt(b % 56)).join(''); navigator.clipboard.writeText(generatedPw); $dispatch('show-toast', { message: 'Password copied: ' + generatedPw, type: 'success' })" class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded text-xs hover:bg-gray-200 dark:hover:bg-gray-500 transition whitespace-nowrap" title="Generate random password and copy">
                                                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                                        Generate
                                                    </button>
                                                    <button type="button" x-show="generatedPw" @click="generatedPw = ''" class="text-gray-400 hover:text-red-500 transition" title="Clear password">
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <!-- Flags -->
                                            <td class="px-3 py-2">
                                                <div class="flex flex-col gap-1 text-xs">
                                                    <label class="inline-flex items-center gap-1 whitespace-nowrap" :class="role !== 'coordinator' && role !== 'santa' ? 'opacity-40' : ''" title="Force show on homepage (overrides user preference)">
                                                        <input type="checkbox" name="users[{{ $u->id }}][force_show_on_website]" value="1" {{ $u->force_show_on_website ? 'checked' : '' }}
                                                            :disabled="role !== 'coordinator' && role !== 'santa'"
                                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 dark:text-red-400 shadow-sm disabled:cursor-not-allowed">
                                                        <span class="text-gray-700 dark:text-gray-300">Homepage</span>
                                                    </label>
                                                    <label class="inline-flex items-center gap-1 whitespace-nowrap" :class="role !== 'coordinator' && role !== 'santa' ? 'opacity-40' : ''" title="Restrict user from changing their avatar">
                                                        <input type="checkbox" name="users[{{ $u->id }}][avatar_restricted]" value="1" {{ $u->avatar_restricted ? 'checked' : '' }}
                                                            :disabled="role !== 'coordinator' && role !== 'santa'"
                                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 dark:text-red-400 shadow-sm disabled:cursor-not-allowed">
                                                        <span class="text-gray-700 dark:text-gray-300">Lock avatar</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <!-- Actions -->
                                            <td class="px-3 py-2">
                                                @unless($isCurrentUser)
                                                    <form method="POST" action="{{ route('santa.deleteUser', $u) }}" class="inline" onsubmit="return confirm('Delete user {{ $u->username }}? This cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition" title="Delete user">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                                        </button>
                                                    </form>
                                                @endunless
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Sticky Update All button -->
                        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 pt-4 mt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition shadow-sm">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg>
                                Update All Users
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Toast notification -->
    <div x-data="{ toasts: [] }" @show-toast.window="toasts.push($event.detail); setTimeout(() => toasts.shift(), 3000)"
        class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="(toast, i) in toasts" :key="i">
            <div x-transition class="px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white bg-green-600 max-w-sm">
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <script>
        function generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('new-password').value = password;
        }

        function copyCredentials() {
            const text = document.getElementById('credentials-text').textContent;
            navigator.clipboard.writeText(text).then(() => {
                document.getElementById('copy-confirm').classList.remove('hidden');
                setTimeout(() => document.getElementById('copy-confirm').classList.add('hidden'), 2000);
            });
        }
    </script>
</x-app-layout>
