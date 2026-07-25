<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'category_id' => 'required|exists:document_categories,id',
            'folder_id' => 'nullable|exists:document_folders,id',
            'document_file' => 'required|file|max:51200', // 50MB default max
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'department' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:active,archived,expired,draft',
            'remarks' => 'nullable|string',
        ];
    }
}
