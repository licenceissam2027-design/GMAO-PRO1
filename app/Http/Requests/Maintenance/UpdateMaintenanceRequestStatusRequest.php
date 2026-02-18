<?php

namespace App\Http\Requests\Maintenance;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(GmaoOptions::MAINTENANCE_STATUSES)],
        ];
    }
}
