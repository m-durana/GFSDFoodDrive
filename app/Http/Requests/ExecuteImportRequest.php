<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'path' => ['required', 'string'],
            'type' => ['required', 'in:family,child'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
            'access_table' => ['nullable', 'string'],
            'background' => ['nullable', 'boolean'],
        ];
    }
}
