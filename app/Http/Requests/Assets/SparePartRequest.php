<?php

namespace App\Http\Requests\Assets;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SparePartRequest extends FormRequest
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
        $partId = $this->route('sparePart')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:120', Rule::unique('spare_parts', 'sku')->ignore($partId)],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'category' => ['nullable', 'string', 'max:120'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:120'],
            'shelf_location' => ['nullable', 'string', 'max:120'],
        ];
    }
}
