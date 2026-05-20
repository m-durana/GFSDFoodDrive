<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanPackingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is permission:coordinator,santa gated.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'barcode' => 'required|string',
        ];
    }
}
