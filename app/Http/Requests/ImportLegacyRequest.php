<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportLegacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'legacy_path' => 'required|string',
            'season_year' => 'required|integer|min:2000|max:2099',
        ];
    }
}
