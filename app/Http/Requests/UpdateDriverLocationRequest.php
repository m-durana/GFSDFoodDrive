<?php

namespace App\Http\Requests;

use App\Models\DeliveryRoute;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public token-authenticated endpoint: require the session-verified
        // driver PIN before allowing the location update. Mirrors the
        // pre-FormRequest behavior where abortUnlessDriverRouteVerified()
        // ran before validation (so unverified callers see 403, not 422).
        $token = $this->route('token');
        if (! $token) {
            return false;
        }
        $route = DeliveryRoute::where('access_token', $token)->first();
        if (! $route) {
            return false;
        }
        return $this->session()->get('driver_route_verified:'.$route->id) === true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
