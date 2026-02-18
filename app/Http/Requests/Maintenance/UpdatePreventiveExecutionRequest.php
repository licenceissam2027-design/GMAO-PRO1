<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreventiveExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'execution_status' => ['required', Rule::in(['in_progress', 'completed', 'stopped'])],
            'did_lubrication' => ['nullable', 'boolean'],
            'did_measurement' => ['nullable', 'boolean'],
            'did_inspection' => ['nullable', 'boolean'],
            'did_replacement' => ['nullable', 'boolean'],
            'did_cleaning' => ['nullable', 'boolean'],
            'anomaly_detected' => ['nullable', 'boolean'],
            'create_request_on_anomaly' => ['nullable', 'boolean'],
            'measurement_reading' => ['nullable', 'string', 'max:120'],
            'inspection_location' => ['nullable', 'string', 'max:180'],
            'execution_note' => ['nullable', 'string'],
            'anomaly_note' => ['nullable', 'string'],
            'actual_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'execution_checks' => ['nullable', 'array'],
            'execution_checks.*.label' => ['required_with:execution_checks', 'string', 'max:255'],
            'execution_checks.*.done' => ['nullable', 'boolean'],
            'execution_checks.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $didAnyAction = collect([
                (bool) $this->boolean('did_lubrication'),
                (bool) $this->boolean('did_measurement'),
                (bool) $this->boolean('did_inspection'),
                (bool) $this->boolean('did_replacement'),
                (bool) $this->boolean('did_cleaning'),
            ])->contains(true);
            $didAnyChecklistItem = collect($this->input('execution_checks', []))
                ->contains(fn ($item): bool => (bool) data_get($item, 'done', false));

            $status = $this->input('execution_status');
            $hasAnomaly = $this->boolean('anomaly_detected');

            if ($status === 'completed' && !$didAnyAction && !$didAnyChecklistItem && !$hasAnomaly) {
                $validator->errors()->add('execution_status', __('validation.required'));
            }

            if ($hasAnomaly && empty(trim((string) $this->input('anomaly_note')))) {
                $validator->errors()->add('anomaly_note', __('validation.required'));
            }
        });
    }
}
