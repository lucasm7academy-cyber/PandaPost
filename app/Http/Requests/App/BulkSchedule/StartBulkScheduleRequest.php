<?php

declare(strict_types=1);

namespace App\Http\Requests\App\BulkSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StartBulkScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1', 'max:100'],
            'media_ids.*' => ['uuid', 'exists:medias,id'],

            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*.social_account_id' => ['required', 'uuid', 'exists:social_accounts,id'],
            'platforms.*.content_type' => ['nullable', 'string'],

            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['date_format:Y-m-d'],

            'times' => ['required', 'array', 'min:1'],
            'times.*' => ['regex:/^([01]\d|2[0-3]):[0-5]\d$/'],

            'timezone' => ['required', 'string', 'timezone'],

            'prompt' => ['required', 'string', 'min:3', 'max:2000'],
            
            'signature_id' => ['nullable', 'uuid', 'exists:workspace_signatures,id'],
        ];
    }
}
