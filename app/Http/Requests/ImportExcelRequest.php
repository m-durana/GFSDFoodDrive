<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'], // 50MB for Access DBs
            'type' => ['required_without:access_table', 'in:family,child'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
        ];
    }
}
