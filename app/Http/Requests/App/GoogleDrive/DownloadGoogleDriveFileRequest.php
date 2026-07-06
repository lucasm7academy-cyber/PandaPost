<?php

declare(strict_types=1);

namespace App\Http\Requests\App\GoogleDrive;

use Illuminate\Foundation\Http\FormRequest;

class DownloadGoogleDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_id' => ['required', 'string', 'max:255'],
            'file_name' => ['required', 'string', 'max:512'],
            'mime_type' => ['required', 'string', 'max:255'],
        ];
    }
}
