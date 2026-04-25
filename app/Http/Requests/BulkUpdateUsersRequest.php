<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class BulkUpdateUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'users' => ['required', 'array'],
            'users.*.first_name' => ['required', 'string', 'max:255'],
            'users.*.last_name' => ['required', 'string', 'max:255'],
            'users.*.role' => ['required', 'string', 'in:family,coordinator,santa,inactive'],
            'users.*.school_source' => ['nullable', 'string', 'max:255'],
            'users.*.position' => ['nullable', 'string', 'max:255'],
            'users.*.password' => ['nullable', 'string', Password::min(8)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $actorId = $this->user()?->id;
            $users = $this->input('users', []);

            // Self-protection: actor cannot demote or deactivate their own Santa row.
            if ($actorId && isset($users[$actorId])) {
                $selfRole = $users[$actorId]['role'] ?? null;
                if ($selfRole !== null && $selfRole !== 'santa') {
                    $v->errors()->add(
                        "users.{$actorId}.role",
                        'You cannot demote or deactivate your own account.'
                    );
                }
            }

            // Must-keep-one-Santa invariant: simulate the payload and ensure at least
            // one active Santa (permission = 9) remains.
            $currentSantaIds = User::where('permission', 9)->pluck('id')->all();
            $remaining = [];
            foreach ($currentSantaIds as $id) {
                if (isset($users[$id])) {
                    $newRole = $users[$id]['role'] ?? null;
                    if ($newRole === 'santa') {
                        $remaining[] = $id;
                    }
                    // else: this Santa is being demoted/deactivated — drop.
                } else {
                    // Not in payload → unchanged → still Santa.
                    $remaining[] = $id;
                }
            }
            // Also count non-Santa rows being promoted to Santa in the payload.
            foreach ($users as $id => $data) {
                if (($data['role'] ?? null) === 'santa' && !in_array($id, $currentSantaIds, true)) {
                    $remaining[] = $id;
                }
            }

            if ($currentSantaIds && count($remaining) === 0) {
                $v->errors()->add(
                    'users',
                    'At least one Santa account must remain after this update.'
                );
            }
        });
    }
}
