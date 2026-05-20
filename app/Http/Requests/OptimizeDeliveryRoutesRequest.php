<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OptimizeDeliveryRoutesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));

        return [
            'route_ids' => ['required', 'array', 'min:1'],
            'route_ids.*' => [
                Rule::exists('delivery_routes', 'id')->where(fn($q) => $q->where('season_year', $seasonYear)),
            ],
            'start_lat' => ['required', 'numeric'],
            'start_lng' => ['required', 'numeric'],
        ];
    }
}
