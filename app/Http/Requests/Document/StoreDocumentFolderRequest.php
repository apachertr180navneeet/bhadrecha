<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'category_id' => 'nullable|exists:document_categories,id',
            'parent_id' => 'nullable|exists:document_folders,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }
}
