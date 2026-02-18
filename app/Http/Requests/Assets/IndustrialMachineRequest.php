<?php

namespace App\Http\Requests\Assets;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndustrialMachineRequest extends FormRequest
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
        $machineId = $this->route('industrialMachine')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:120', Rule::unique('industrial_machines', 'code')->ignore($machineId)],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:running,stopped,maintenance'],
            'criticality' => ['required', 'in:low,medium,high'],
        ];
    }
}
