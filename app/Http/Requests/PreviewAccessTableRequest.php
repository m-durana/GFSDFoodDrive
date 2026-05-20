<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewAccessTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'path' => ['required', 'string'],
            'table' => ['required', 'string'],
            'type' => ['required', 'in:family,child'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
        ];
    }
}
