<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportAllAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'path' => ['required', 'string'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
        ];
    }
}
