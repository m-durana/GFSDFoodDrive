<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Admin Settings
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('santa.updateSettings')); ?>">
                <?php echo csrf_field(); ?>

                <!-- Self-Registration -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Self-Service Registration</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="self_registration_enabled" value="1" <?php echo e($selfRegistration ? 'checked' : ''); ?>

                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Allow families to self-register</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    When enabled, families can submit their own information via <code><?php echo e(url('/register-family')); ?></code>
                                </p>
                            </div>

                            <?php if($selfRegistration): ?>
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        Self-registration is <strong>enabled</strong>. Share this link with families:
                                    </p>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <input type="text" readonly value="<?php echo e(url('/register-family')); ?>" id="registration-link"
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm bg-gray-50 dark:bg-gray-800">
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('registration-link').value).then(() => this.textContent = 'Copied!')"
                                            class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Self-registration is <strong>disabled</strong>. The registration link will show a 403 error.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Season -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Season</h3>
                        <div>
                            <label for="season_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Season Year</label>
                            <input type="number" name="season_year" id="season_year" value="<?php echo e($seasonYear); ?>" min="2020" max="2099"
                                class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Paper Size -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">PDF Paper Size</h3>
                        <div>
                            <label for="paper_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paper Size for Gift Tags, Family Summaries, and Delivery Sheets</label>
                            <select name="paper_size" id="paper_size"
                                class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                <option value="letter" <?php echo e(\App\Models\Setting::get('paper_size', 'letter') === 'letter' ? 'selected' : ''); ?>>US Letter (8.5 x 11)</option>
                                <option value="a4" <?php echo e(\App\Models\Setting::get('paper_size', 'letter') === 'a4' ? 'selected' : ''); ?>>A4 (210 x 297mm)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Google OAuth -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Google Sign-In (OAuth)</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Enable "Sign in with Google" on the login page. Users must have a matching email in their account.
                            Get credentials from <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a>.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label for="google_client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Google Client ID</label>
                                <input type="text" name="google_client_id" id="google_client_id"
                                    value="<?php echo e(\App\Models\Setting::get('google_client_id', '')); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="xxxx.apps.googleusercontent.com">
                            </div>
                            <div>
                                <label for="google_client_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Google Client Secret</label>
                                <input type="password" name="google_client_secret" id="google_client_secret"
                                    value="<?php echo e(\App\Models\Setting::get('google_client_secret', '')); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Client secret">
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <strong>Callback URL:</strong> <code><?php echo e(url('/auth/google/callback')); ?></code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geocode -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Address Geocoding</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Geocode family addresses for the live delivery map. Uses free OpenStreetMap Nominatim service (rate limited to 1 req/sec).
                        </p>
                        <form method="POST" action="<?php echo e(route('santa.geocodeFamilies')); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition"
                                    onclick="this.textContent='Geocoding...'; this.disabled=true; this.form.submit();">
                                Geocode Missing Addresses
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Save -->
                <div class="flex items-center justify-between mt-6">
                    <a href="<?php echo e(route('santa.index')); ?>" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Dashboard
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/santa/settings.blade.php ENDPATH**/ ?>