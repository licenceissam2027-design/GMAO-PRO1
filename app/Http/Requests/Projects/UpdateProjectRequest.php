<?php

namespace App\Http\Requests\Projects;

use App\Models\Project;
use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('projects', 'code')->ignore($project?->id)],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['super_admin', 'manager'])->where('is_active', true))],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'priority' => ['required', Rule::in(GmaoOptions::PRIORITIES)],
            'status' => ['required', Rule::in(GmaoOptions::PROJECT_STATUSES)],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
