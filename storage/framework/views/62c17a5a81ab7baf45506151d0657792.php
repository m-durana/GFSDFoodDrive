<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo - links to role-appropriate dashboard -->
                <div class="shrink-0 flex items-center">
                    <a href="<?php echo e(route('home')); ?>" class="text-xl font-bold text-red-700 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 transition">
                        North Pole
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <?php if(auth()->user()->isFamily() || auth()->user()->isSanta()): ?>
                        <a href="<?php echo e(route('family.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('family.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Families
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->isSanta()): ?>
                        <a href="<?php echo e(route('santa.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('santa.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Santa
                        </a>
                        <a href="<?php echo e(route('delivery.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('delivery.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Delivery Day
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->isCoordinator() || auth()->user()->isSanta()): ?>
                        <a href="<?php echo e(route('coordinator.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('coordinator.*') ? 'border-red-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Coordinator
                        </a>
                    <?php endif; ?>
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

                <span class="text-sm text-gray-500 dark:text-gray-400">Hello, <?php echo e(auth()->user()->first_name); ?></span>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 underline">
                        Log Out
                    </button>
                </form>
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
            <a href="<?php echo e(route('family.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Families</a>
            <?php if(auth()->user()->isSanta()): ?>
                <a href="<?php echo e(route('santa.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Santa</a>
                <a href="<?php echo e(route('delivery.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Delivery Day</a>
            <?php endif; ?>
            <?php if(auth()->user()->isCoordinator() || auth()->user()->isSanta()): ?>
                <a href="<?php echo e(route('coordinator.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Coordinator</a>
            <?php endif; ?>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200"><?php echo e(auth()->user()->first_name); ?> <?php echo e(auth()->user()->last_name); ?></div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400"><?php echo e(auth()->user()->username); ?></div>
            </div>
            <div class="mt-3 space-y-1">
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="block w-full text-start pl-3 pr-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>