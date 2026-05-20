<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrintPackingBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'list_ids' => 'required|array',
            'list_ids.*' => 'exists:packing_lists,id',
        ];
    }
}
