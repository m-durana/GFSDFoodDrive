<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DismissDuplicateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));

        return [
            'family_a_id' => [
                'required',
                Rule::exists('families', 'id')->where(fn($q) => $q->where('season_year', $seasonYear)),
            ],
            'family_b_id' => [
                'required',
                Rule::exists('families', 'id')->where(fn($q) => $q->where('season_year', $seasonYear)),
            ],
        ];
    }
}
