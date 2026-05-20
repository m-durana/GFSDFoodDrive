<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroceryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:canned,dry,personal,condiment'],
            'qty_1' => ['nullable', 'integer', 'min:0'],
            'qty_2' => ['nullable', 'integer', 'min:0'],
            'qty_3' => ['nullable', 'integer', 'min:0'],
            'qty_4' => ['nullable', 'integer', 'min:0'],
            'qty_5' => ['nullable', 'integer', 'min:0'],
            'qty_6' => ['nullable', 'integer', 'min:0'],
            'qty_7' => ['nullable', 'integer', 'min:0'],
            'qty_8' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
