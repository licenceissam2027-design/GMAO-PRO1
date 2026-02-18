<?php

namespace App\Http\Requests\Maintenance;

use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\TechnicalAsset;
use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePreventivePlanRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'asset_type' => ['required', Rule::in(GmaoOptions::ASSET_TYPES)],
            'industrial_machine_id' => ['nullable', 'exists:industrial_machines,id'],
            'technical_asset_id' => ['nullable', 'exists:technical_assets,id'],
            'logistic_asset_id' => ['nullable', 'exists:logistic_assets,id'],
            'asset_reference' => ['nullable', 'string', 'max:255'],
            'maintenance_domain' => ['required', Rule::in(GmaoOptions::MAINTENANCE_DOMAINS)],
            'failure_mode' => ['required', 'string', Rule::in(GmaoOptions::allFailureModes())],
            'frequency' => ['required', Rule::in(GmaoOptions::PLAN_FREQUENCIES)],
            'interval_value' => ['required', 'integer', 'min:1', 'max:365'],
            'trigger_mode' => ['required', Rule::in(['calendar', 'meter', 'both'])],
            'meter_threshold' => ['nullable', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'skill_level' => ['required', Rule::in(['operator', 'technician', 'senior_technician', 'specialist'])],
            'requires_shutdown' => ['nullable', 'boolean'],
            'next_due_date' => ['required', 'date'],
            'responsible_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['super_admin', 'manager', 'technician'])->where('is_active', true))],
            'checklist' => ['required', 'string', 'min:10'],
            'procedure_steps' => ['required', 'string', 'min:10'],
            'safety_notes' => ['nullable', 'string'],
            'spare_parts_list' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $assetType = $this->input('asset_type');
            $sector = $this->input('sector');
            $domain = $this->input('maintenance_domain');
            $mode = $this->input('failure_mode');
            $triggerMode = $this->input('trigger_mode');

            if ($assetType === 'industrial' && empty($this->input('industrial_machine_id')) && empty($this->input('asset_reference'))) {
                $validator->errors()->add('industrial_machine_id', __('validation.required'));
            }
            if ($assetType === 'technical' && empty($this->input('technical_asset_id')) && empty($this->input('asset_reference'))) {
                $validator->errors()->add('technical_asset_id', __('validation.required'));
            }
            if ($assetType === 'logistic' && empty($this->input('logistic_asset_id')) && empty($this->input('asset_reference'))) {
                $validator->errors()->add('logistic_asset_id', __('validation.required'));
            }

            if (!empty($sector)) {
                if ($assetType === 'industrial' && !empty($this->input('industrial_machine_id'))) {
                    $assetSector = IndustrialMachine::whereKey($this->input('industrial_machine_id'))->value('sector');
                    if (!empty($assetSector) && $assetSector !== $sector) {
                        $validator->errors()->add('industrial_machine_id', __('validation.in'));
                    }
                }
                if ($assetType === 'technical' && !empty($this->input('technical_asset_id'))) {
                    $assetSector = TechnicalAsset::whereKey($this->input('technical_asset_id'))->value('sector');
                    if (!empty($assetSector) && $assetSector !== $sector) {
                        $validator->errors()->add('technical_asset_id', __('validation.in'));
                    }
                }
                if ($assetType === 'logistic' && !empty($this->input('logistic_asset_id'))) {
                    $assetSector = LogisticAsset::whereKey($this->input('logistic_asset_id'))->value('sector');
                    if (!empty($assetSector) && $assetSector !== $sector) {
                        $validator->errors()->add('logistic_asset_id', __('validation.in'));
                    }
                }
            }

            if ($domain && $mode) {
                $allowed = GmaoOptions::FAILURE_MODES[$domain] ?? [];
                if (!in_array($mode, $allowed, true)) {
                    $validator->errors()->add('failure_mode', __('validation.in'));
                }
            }

            if (in_array($triggerMode, ['meter', 'both'], true) && empty($this->input('meter_threshold'))) {
                $validator->errors()->add('meter_threshold', __('validation.required'));
            }
        });
    }
}
