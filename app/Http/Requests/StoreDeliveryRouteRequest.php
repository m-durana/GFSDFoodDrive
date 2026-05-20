<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'driver_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('permission', '>=', 8)),
            ],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'family_ids' => ['nullable', 'array'],
            'family_ids.*' => [
                Rule::exists('families', 'id')->where(fn($q) => $q->where('season_year', $seasonYear)),
            ],
        ];
    }
}
