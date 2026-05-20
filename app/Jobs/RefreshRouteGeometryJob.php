<?php

namespace App\Jobs;

use App\Models\DeliveryRoute;
use App\Services\RoutePlanningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Refreshes the polyline geometry of a delivery route by calling
 * OpenRouteService off the request thread. The geometry is only used by
 * the map view; users don't see it until they open the route page, so
 * a small async lag is fine and the saved request-thread latency is large.
 */
class RefreshRouteGeometryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function __construct(public int $routeId)
    {
    }

    public function handle(RoutePlanningService $routePlanning): void
    {
        $route = DeliveryRoute::find($this->routeId);
        if (! $route) {
            return;
        }
        $routePlanning->refreshRouteGeometry($route);
    }
}
