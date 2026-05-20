<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'section' => \App\Http\Middleware\CoordinatorSection::class,
            // REL-06: public / token-bearer locale resolver (en, es).
            'public-locale' => \App\Http\Middleware\SetPublicLocale::class,
        ]);
        // Audit every state-changing request by an authenticated user.
        // Sudoer actions are tagged separately. See LogMutatingActivity.
        $middleware->appendToGroup('web', \App\Http\Middleware\LogMutatingActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // REL-11 Sentry — DSN in .env (SENTRY_LARAVEL_DSN).
        $exceptions->reportable(function (\Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    })->create();
