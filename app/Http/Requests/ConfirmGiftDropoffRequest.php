<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmGiftDropoffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isCoordinator() || $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'gifts_received' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
