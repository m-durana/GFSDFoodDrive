<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveAccessRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            // Whitelist prevents crafted POST of role=santa (or anything else)
            // from minting elevated accounts through the approval flow.
            'role' => ['nullable', 'string', 'in:family,coordinator,santa'],
        ];
    }
}
