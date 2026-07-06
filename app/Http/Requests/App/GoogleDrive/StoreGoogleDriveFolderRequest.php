<?php

declare(strict_types=1);

namespace App\Http\Requests\App\GoogleDrive;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoogleDriveFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_name' => ['required', 'string', 'max:255'],
            'folder_link' => ['required', 'string', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'folder_name.required' => 'Nome da pasta é obrigatório',
            'folder_link.required' => 'Link da pasta é obrigatório',
            'folder_link.url' => 'Link inválido',
        ];
    }
}
