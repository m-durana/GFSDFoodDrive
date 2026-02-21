<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <span class="text-xl font-bold text-red-700">North Pole</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <?php if(auth()->user()->permission >= 7): ?>
                        <a href="<?php echo e(route('family.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('family.*') ? 'border-red-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Families
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->isSanta()): ?>
                        <a href="<?php echo e(route('santa.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('santa.*') ? 'border-red-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Santa
                        </a>
                        <a href="<?php echo e(route('delivery.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('delivery.*') ? 'border-red-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Delivery Day
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->isCoordinator() || auth()->user()->isSanta()): ?>
                        <a href="<?php echo e(route('coordinator.index')); ?>" class="inline-flex items-center px-1 pt-1 border-b-2 <?php echo e(request()->routeIs('coordinator.*') ? 'border-red-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> text-sm font-medium leading-5 transition duration-150 ease-in-out">
                            Coordinator
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Info & Logout -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <span class="text-sm text-gray-500 mr-4">Hello, <?php echo e(auth()->user()->first_name); ?></span>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Log Out
                    </button>
                </form>
            </div>

            <!-- Mobile hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition duration-150 ease-in-out">
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
            <a href="<?php echo e(route('family.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50">Families</a>
            <?php if(auth()->user()->isSanta()): ?>
                <a href="<?php echo e(route('santa.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50">Santa</a>
                <a href="<?php echo e(route('delivery.index')); ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50">Delivery Day</a>
            <?php endif; ?>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800"><?php echo e(auth()->user()->first_name); ?> <?php echo e(auth()->user()->last_name); ?></div>
                <div class="font-medium text-sm text-gray-500"><?php echo e(auth()->user()->username); ?></div>
            </div>
            <div class="mt-3 space-y-1">
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="block w-full text-start pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>