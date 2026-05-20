<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        // REL-13: Santa (permission 9) + System Coordinator can view the queue dashboard.
        Gate::define('viewHorizon', function ($user = null) {
            if (!$user) return false;
            if (method_exists($user, 'isSanta') && $user->isSanta()) return true;
            if (method_exists($user, 'hasCoordinatorSection') && $user->hasCoordinatorSection('system')) return true;
            return false;
        });
    }
}
