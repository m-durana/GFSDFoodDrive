<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PackingSystemEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::get('packing_system_enabled', '1') !== '1') {
            abort(404);
        }

        return $next($request);
    }
}
