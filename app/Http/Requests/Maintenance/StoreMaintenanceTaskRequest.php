<?php

namespace App\Http\Requests\Maintenance;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceTaskRequest extends FormRequest
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
        $sector = $this->input('sector');
        $requestRule = Rule::exists('maintenance_requests', 'id');
        if (!empty($sector)) {
            $requestRule = $requestRule->where('sector', $sector);
        }

        $technicianRule = Rule::exists('users', 'id')->where(function ($q) use ($sector): void {
            $q->where('role', 'technician')->where('is_active', true);
            if (!empty($sector)) {
                $q->where('sector', $sector);
            }
        });

        return [
            'title' => ['required', 'string', 'max:255'],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'type' => ['required', Rule::in(GmaoOptions::TASK_TYPES)],
            'status' => ['required', Rule::in(GmaoOptions::MAINTENANCE_STATUSES)],
            'maintenance_request_id' => ['nullable', $requestRule],
            'technician_id' => ['nullable', $technicianRule],
            'scheduled_for' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
