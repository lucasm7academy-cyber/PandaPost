<?php

declare(strict_types=1);

namespace App\Http\Requests\App\Asset;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            // Capped so a runaway selection can't unlink thousands of files in
            // one request; the UI selects at most one loaded page at a time.
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['required', 'uuid', 'distinct'],
        ];
    }
}
