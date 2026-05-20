<?php

namespace App\Notifications;

use App\Models\DeliveryRoute;
use App\Services\SmsService;

/**
 * REL-05: notify a driver their route is ready to start, with the
 * driver-view URL + PIN. Triggered when Santa finalises a route assignment.
 */
class DriverRouteReady
{
    public static function send(DeliveryRoute $route): bool
    {
        $phone = $route->driver_phone;
        if (empty($phone)) return false;

        $name = $route->driver_name ?? 'Driver';
        $url = route('delivery.driverView', $route->access_token);
        $pin = $route->driver_pin ? " (PIN: {$route->driver_pin})" : '';

        $msg = "Hi {$name}! Your GFSD delivery route is ready. Open: {$url}{$pin}. Stops: {$route->families()->count()}. Drive safe!";

        SmsService::dispatch($phone, $msg);
        $route->forceFill(['driver_notified_at' => now()])->saveQuietly();
        return true;
    }
}
