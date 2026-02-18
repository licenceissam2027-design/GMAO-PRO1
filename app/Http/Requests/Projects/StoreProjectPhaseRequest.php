<?php

namespace App\Http\Requests\Projects;

use App\Models\Project;
use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class StoreProjectPhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'execution_mode' => ['required', Rule::in(GmaoOptions::PROJECT_PHASE_MODES)],
            'phase_order' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(GmaoOptions::PROJECT_PHASE_STATUSES)],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'responsible_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['super_admin', 'manager', 'technician'])->where('is_active', true))],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date', 'after_or_equal:actual_start_date'],
            'depends_on_phase_id' => ['nullable'],
        ];

        if (Schema::hasTable('project_phases')) {
            $rules['depends_on_phase_id'][] = Rule::exists('project_phases', 'id')->where(fn ($q) => $q->where('project_id', $project?->id));
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!Schema::hasTable('project_phases')) {
                return;
            }

            $dependsOnId = $this->input('depends_on_phase_id');
            $targetStatus = $this->input('status');

            if (empty($dependsOnId) || !in_array($targetStatus, ['in_progress', 'completed'], true)) {
                return;
            }

            $dependency = \App\Models\ProjectPhase::query()->find($dependsOnId);
            if ($dependency && $dependency->status !== 'completed') {
                $validator->errors()->add('depends_on_phase_id', __('validation.in'));
            }
        });
    }
}
