<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroceryItemRequest extends FormRequest
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
            'qty_1' => ['required', 'integer', 'min:0'],
            'qty_2' => ['required', 'integer', 'min:0'],
            'qty_3' => ['required', 'integer', 'min:0'],
            'qty_4' => ['required', 'integer', 'min:0'],
            'qty_5' => ['required', 'integer', 'min:0'],
            'qty_6' => ['required', 'integer', 'min:0'],
            'qty_7' => ['required', 'integer', 'min:0'],
            'qty_8' => ['required', 'integer', 'min:0'],
        ];
    }
}
