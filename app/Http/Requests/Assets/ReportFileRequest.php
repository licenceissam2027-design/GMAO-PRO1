<?php

namespace App\Http\Requests\Assets;

use App\Models\ReportFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:daily,weekly,monthly,yearly,custom'],
            'format' => ['required', 'in:excel,word,pdf'],
            'report_date' => ['required', 'date'],
            'context_type' => ['required', Rule::in(array_keys(ReportFile::CONTEXT_TYPES))],
            'context_id' => ['required', 'integer', 'min:1'],
            'report_file' => ['nullable', 'file', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = (string) $this->input('context_type');
            $id = (int) $this->input('context_id');
            $class = ReportFile::contextClass($type);

            if ($class === null || $id <= 0) {
                $validator->errors()->add('context_type', __('validation.in'));
                return;
            }

            $record = $class::query()->find($id);
            if ($record === null) {
                $validator->errors()->add('context_id', __('validation.exists', ['attribute' => 'context_id']));
                return;
            }

            $user = $this->user();
            if ($user && !$user->isRole('super_admin') && !empty($user->sector)) {
                $recordSector = $record->sector ?? null;
                if (!empty($recordSector) && $recordSector !== $user->sector) {
                    $validator->errors()->add('context_id', __('validation.in'));
                }
            }
        });
    }
}
