<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Manage Users
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success message with copy-to-clipboard -->
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
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
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-amber-800 dark:text-amber-200">
                                <svg class="inline h-5 w-5 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                Pending Access Requests ({{ $accessRequests->count() }})
                            </h3>
                        </div>

                        <div class="space-y-3">
                            @foreach($accessRequests as $req)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 gap-3">
                                    <div class="flex items-center gap-3">
                                        @if($req->avatar)
                                            <img src="{{ $req->avatar }}" alt="" class="w-10 h-10 rounded-full">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 text-sm font-bold">
                                                {{ strtoupper(substr($req->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $req->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $req->email }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                Requested: <span class="font-medium">{{ $req->roleLabel() }}</span>
                                                @if($req->school_source) &middot; {{ $req->school_source }} @endif
                                                @if($req->position) &middot; {{ $req->position }} @endif
                                                &middot; {{ $req->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <!-- Approve -->
                                        <form method="POST" action="{{ route('santa.approveAccessRequest', $req) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="role" value="{{ $req->requested_role }}">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition"
                                                    onclick="return confirm('Approve {{ $req->name }} as {{ $req->roleLabel() }}?')">
                                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                Approve
                                            </button>
                                        </form>

                                        <!-- Approve as different role -->
                                        <form method="POST" action="{{ route('santa.approveAccessRequest', $req) }}" class="inline" x-data="{ open: false }">
                                            @csrf
                                            <div class="relative">
                                                <button type="button" @click="open = !open" class="inline-flex items-center px-2 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-700 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 z-10">
                                                    @foreach(['family' => 'Family', 'coordinator' => 'Coordinator'] as $roleKey => $roleLabel)
                                                        @if($roleKey !== $req->requested_role)
                                                            <button type="submit" name="role" value="{{ $roleKey }}" class="block w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600"
                                                                    onclick="return confirm('Approve as {{ $roleLabel }}?')">
                                                                Approve as {{ $roleLabel }}
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </form>

                                        <!-- Deny -->
                                        <form method="POST" action="{{ route('santa.denyAccessRequest', $req) }}" class="inline"
                                              onsubmit="var reason = prompt('Reason for denial (optional):'); if (reason !== null) { this.querySelector('[name=deny_reason]').value = reason; return true; } return false;">
                                            @csrf
                                            <input type="hidden" name="deny_reason" value="">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-500 text-xs font-medium transition">
                                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                Deny
                                            </button>
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

                    <form method="POST" action="{{ route('santa.storeUser') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                                <input type="text" name="username" id="username" value="{{ old('username') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                @error('username')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select name="role" id="role" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="family" {{ old('role') === 'family' ? 'selected' : '' }}>Family Entry</option>
                                    <option value="coordinator" {{ old('role') === 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                                    <option value="santa" {{ old('role') === 'santa' ? 'selected' : '' }}>Santa</option>
                                </select>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="school_source" class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                                <select name="school_source" id="school_source"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- None --</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school }}" {{ old('school_source') === $school ? 'selected' : '' }}>{{ $school }}</option>
                                    @endforeach
                                    <option value="Other" {{ old('school_source') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('school_source')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="js-position-wrapper" style="display:none;">
                                <label for="position" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Position</label>
                                <select name="position" id="position"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- None --</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}" {{ old('position') === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="password" id="password" required
                                        class="block w-full rounded-l-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <button type="button" onclick="generatePassword()"
                                        class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 text-xs font-medium transition" title="Generate random password">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Add User
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Users -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Existing Users</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Username</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">First Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">School</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">New Password</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($users as $u)
                                    <tr>
                                        <form method="POST" action="{{ route('santa.updateUser', $u) }}">
                                            @csrf
                                            @method('PUT')
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $u->username }}</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="text" name="first_name" value="{{ $u->first_name }}"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="text" name="last_name" value="{{ $u->last_name }}"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <select name="role"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                                    <option value="inactive" {{ $u->permission === 0 ? 'selected' : '' }}>Inactive</option>
                                                    <option value="family" {{ $u->permission === 7 ? 'selected' : '' }}>Family Entry</option>
                                                    <option value="coordinator" {{ $u->permission === 8 ? 'selected' : '' }}>Coordinator</option>
                                                    <option value="santa" {{ $u->permission === 9 ? 'selected' : '' }}>Santa</option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap js-row-position" @if(!in_array($u->permission, [8, 9])) style="display:none;" @endif>
                                                <select name="position"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                                    <option value="">-- None --</option>
                                                    @foreach($positions as $pos)
                                                        <option value="{{ $pos }}" {{ $u->position === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <select name="school_source"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                                    <option value="">-- None --</option>
                                                    @foreach($schools as $school)
                                                        <option value="{{ $school }}" {{ $u->school_source === $school ? 'selected' : '' }}>{{ $school }}</option>
                                                    @endforeach
                                                    <option value="Other" {{ $u->school_source === 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="password" name="password" placeholder="Leave blank to keep"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                                                    Update
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
        }

        function copyCredentials() {
            const text = document.getElementById('credentials-text').textContent;
            navigator.clipboard.writeText(text).then(() => {
                document.getElementById('copy-confirm').classList.remove('hidden');
                setTimeout(() => document.getElementById('copy-confirm').classList.add('hidden'), 2000);
            });
        }

        // Show/hide position field based on role selection
        var newRoleSelect = document.getElementById('role');
        if (newRoleSelect) {
            newRoleSelect.addEventListener('change', function() {
                var show = this.value === 'coordinator' || this.value === 'santa';
                document.querySelectorAll('.js-position-wrapper').forEach(function(el) {
                    el.style.display = show ? '' : 'none';
                });
            });
            // Trigger on load
            newRoleSelect.dispatchEvent(new Event('change'));
        }

        // For inline edit rows: show/hide position cell when role changes
        document.querySelectorAll('select[name="role"]').forEach(function(sel) {
            if (sel.id === 'role') return; // skip the create form one
            sel.addEventListener('change', function() {
                var show = this.value === 'coordinator' || this.value === 'santa';
                var row = this.closest('tr');
                if (row) {
                    var posCell = row.querySelector('.js-row-position');
                    if (posCell) posCell.style.display = show ? '' : 'none';
                }
            });
        });
    </script>
</x-app-layout>
