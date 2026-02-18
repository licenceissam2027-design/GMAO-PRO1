<?php

namespace App\Http\Requests\Projects;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user && !$user->isRole('super_admin') && !empty($user->sector)) {
            $this->merge(['sector' => $user->sector]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', 'unique:projects,code'],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['super_admin', 'manager'])->where('is_active', true))],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'priority' => ['required', Rule::in(GmaoOptions::PRIORITIES)],
            'status' => ['required', Rule::in(GmaoOptions::PROJECT_STATUSES)],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'phases' => ['nullable', 'array'],
            'phases.*.title' => ['nullable', 'string', 'max:255'],
            'phases.*.description' => ['nullable', 'string'],
            'phases.*.execution_mode' => ['nullable', Rule::in(GmaoOptions::PROJECT_PHASE_MODES)],
            'phases.*.phase_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'phases.*.status' => ['nullable', Rule::in(GmaoOptions::PROJECT_PHASE_STATUSES)],
            'phases.*.progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'phases.*.responsible_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['super_admin', 'manager', 'technician'])->where('is_active', true))],
            'phases.*.planned_start_date' => ['nullable', 'date'],
            'phases.*.planned_end_date' => ['nullable', 'date'],
        ];
    }
}
