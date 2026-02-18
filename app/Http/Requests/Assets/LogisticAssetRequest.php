<?php

namespace App\Http\Requests\Assets;

use App\Support\GmaoOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogisticAssetRequest extends FormRequest
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
        $assetId = $this->route('logisticAsset')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:120', Rule::unique('logistic_assets', 'code')->ignore($assetId)],
            'sector' => ['nullable', Rule::in(GmaoOptions::SECTORS)],
            'type' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:available,in_use,maintenance,out_of_service'],
            'location' => ['nullable', 'string', 'max:120'],
            'next_inspection_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
