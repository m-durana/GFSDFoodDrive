<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddFamiliesToRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));

        return [
            'family_ids' => ['required', 'array', 'min:1'],
            'family_ids.*' => [
                Rule::exists('families', 'id')->where(fn($q) => $q->where('season_year', $seasonYear)),
            ],
        ];
    }
}
