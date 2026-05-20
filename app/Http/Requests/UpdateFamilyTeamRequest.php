<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFamilyTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));

        return [
            'delivery_team' => ['nullable', 'string', 'max:255'],
            'delivery_team_id' => [
                'nullable',
                Rule::exists('delivery_teams', 'id')->where(fn($q) => $q->where('season_year', $seasonYear)),
            ],
        ];
    }
}
