<?php

namespace App\Http\Requests\Maintenance;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $assetType = $this->input('asset_type');
        if (empty($assetType)) {
            if (!empty($this->input('industrial_machine_id'))) {
                $assetType = 'industrial';
            } elseif (!empty($this->input('technical_asset_id'))) {
                $assetType = 'technical';
            } elseif (!empty($this->input('logistic_asset_id'))) {
                $assetType = 'logistic';
            } else {
                $assetType = 'other';
            }
        }

        $user = $this->user();
        $sector = $this->input('sector') ?: $user?->sector;
        if ($user && !$user->isRole('super_admin') && !empty($user->sector)) {
            $sector = $user->sector;
        }

        $this->merge([
            'asset_type' => $assetType,
            'sector' => $sector,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'asset_type' => ['required', Rule::in(GmaoOptions::ASSET_TYPES)],
            'asset_reference' => ['nullable', 'string', 'max:180'],
            'industrial_machine_id' => ['nullable', 'exists:industrial_machines,id'],
            'technical_asset_id' => ['nullable', 'exists:technical_assets,id'],
            'logistic_asset_id' => ['nullable', 'exists:logistic_assets,id'],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'maintenance_domain' => ['required', Rule::in(GmaoOptions::MAINTENANCE_DOMAINS)],
            'failure_mode' => ['required', 'string', Rule::in(GmaoOptions::allFailureModes())],
            'issue_category' => ['required', Rule::in(GmaoOptions::ISSUE_CATEGORIES)],
            'severity' => ['required', Rule::in(GmaoOptions::PRIORITIES)],
            'status' => ['required', Rule::in(GmaoOptions::MAINTENANCE_STATUSES)],
            'location' => ['nullable', 'string', 'max:255'],
            'occurrence_at' => ['nullable', 'date'],
            'downtime_minutes' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'min:10'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'technician')->where('is_active', true))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $domain = $this->input('maintenance_domain');
            $mode = $this->input('failure_mode');
            $allowed = GmaoOptions::FAILURE_MODES[$domain] ?? [];
            $assetType = $this->input('asset_type');

            if ($domain && $mode && !in_array($mode, $allowed, true)) {
                $validator->errors()->add('failure_mode', __('validation.in'));
            }

            if ($assetType === 'industrial' && empty($this->input('industrial_machine_id')) && empty($this->input('asset_reference'))) {
                $validator->errors()->add('industrial_machine_id', __('validation.required'));
            }
            if ($assetType === 'technical' && empty($this->input('technical_asset_id')) && empty($this->input('asset_reference'))) {
                $validator->errors()->add('technical_asset_id', __('validation.required'));
            }
            if ($assetType === 'logistic' && empty($this->input('logistic_asset_id')) && empty($this->input('asset_reference'))) {
                $validator->errors()->add('logistic_asset_id', __('validation.required'));
            }

            if (!empty($this->input('assigned_to'))) {
                $assigneeSector = \App\Models\User::whereKey($this->input('assigned_to'))->value('sector');
                $requestSector = $this->input('sector');
                if (!empty($requestSector) && !empty($assigneeSector) && $requestSector !== $assigneeSector) {
                    $validator->errors()->add('assigned_to', __('validation.in'));
                }
            }
        });
    }
}
