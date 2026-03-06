<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo - links to role-appropriate dashboard -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" data-tour="nav-home" class="text-xl font-bold text-red-700 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 transition">
                        North Pole
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->user()->isFamily() || auth()->user()->isSanta())
                        <a href="{{ route('family.index') }}" data-tour="nav-families" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('family.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Families
                        </a>
                    @endif

                    @if(auth()->user()->isSanta())
                        <a href="{{ route('santa.index') }}" data-tour="nav-santa" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('santa.*') && !request()->routeIs('santa.commandCenter*') && !request()->routeIs('santa.shoppingDay') && !request()->routeIs('santa.analytics*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Santa
                        </a>
                    @endif

                    @if(auth()->user()->isCoordinator() || auth()->user()->isSanta())
                        <a href="{{ route('coordinator.index') }}" data-tour="nav-coordinator" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('coordinator.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Print Tags
                        </a>
                        <a href="{{ route('warehouse.index') }}" data-tour="nav-warehouse" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('warehouse.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Warehouse
                        </a>
                        @if(\App\Models\Setting::get('packing_system_enabled', '1') === '1')
                        @php $packingQaCount = \App\Models\PackingList::where('status', 'complete')->count(); @endphp
                        <a href="{{ route('packing.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('packing.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Packing
                            @if($packingQaCount > 0)
                                <span class="packing-qa-badge ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full">{{ $packingQaCount }}</span>
                            @endif
                        </a>
                        @endif
                        <a href="{{ route('santa.commandCenter') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('santa.commandCenter*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Command Center
                        </a>

                        <a href="{{ route('santa.analytics') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('santa.analytics*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Analytics
                        </a>

                        {{-- Event Day dropdown: Delivery Day + Shopping Day --}}
                        <div x-data="{ eventMenu: false }" class="relative inline-flex items-center">
                            <button @click="eventMenu = !eventMenu" @click.away="eventMenu = false"
                                class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('delivery.*') || request()->routeIs('santa.shoppingDay') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }} text-sm font-medium leading-5 transition duration-150 ease-in-out h-full">
                                Event Day
                                <svg class="ml-1 w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div x-show="eventMenu" x-transition class="absolute top-full left-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                <a href="{{ route('delivery.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('delivery.*') ? 'font-semibold' : '' }}">
                                    Delivery Day
                                </a>
                                <a href="{{ route('santa.shoppingDay') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('santa.shoppingDay') ? 'font-semibold' : '' }}">
                                    Shopping Day
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right side: dark mode toggle, user info, logout -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Dark mode toggle -->
                <button onclick="toggleDarkMode()" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-md transition" title="Toggle dark mode">
                    <!-- Sun icon (shown in dark mode) -->
                    <svg class="hidden dark:block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg class="block dark:hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <a href="{{ route('help.index') }}" data-tour="nav-help" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" title="Help">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>
                </a>
                {{-- User dropdown --}}
                <div x-data="{ userMenu: false }" class="relative">
                    <button @click="userMenu = !userMenu" @click.away="userMenu = false"
                        class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                        <span>{{ auth()->user()->first_name }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="userMenu" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            Profile Settings
                        </a>
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button onclick="toggleDarkMode()" class="p-2 mr-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition" title="Toggle dark mode">
                    <svg class="hidden dark:block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                    <svg class="block dark:hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                </button>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('family.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Families</a>
            @if(auth()->user()->isSanta())
                <a href="{{ route('santa.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Santa</a>
            @endif
            @if(auth()->user()->isCoordinator() || auth()->user()->isSanta())
                <a href="{{ route('coordinator.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Print Tags</a>
                <a href="{{ route('warehouse.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Warehouse</a>
                @if(\App\Models\Setting::get('packing_system_enabled', '1') === '1')
                <a href="{{ route('packing.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Packing
                    @if(($packingQaCount ?? 0) > 0)
                        <span class="packing-qa-badge ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full">{{ $packingQaCount }}</span>
                    @endif
                </a>
                @endif
                <a href="{{ route('santa.commandCenter') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Command Center</a>
                <a href="{{ route('santa.analytics') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Analytics</a>
                <div class="pl-3 pr-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Event Day</div>
                <a href="{{ route('delivery.index') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Delivery Day</a>
                <a href="{{ route('santa.shoppingDay') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Shopping Day</a>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->username }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Profile Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-start pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
