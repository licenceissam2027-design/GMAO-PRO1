<?php

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeamUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $target = $this->route('user');
        $targetId = $target instanceof User ? $target->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetId)],
            'password' => ['nullable', Password::min(8)],
            'role' => ['required', 'in:super_admin,manager,technician,employee'],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
