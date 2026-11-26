<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'document_number' => $this->document_number,
            'company_id' => $this->company_id,
            'company_name' => $this->company?->name,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'folder_id' => $this->folder_id,
            'folder_name' => $this->folder?->name,
            'description' => $this->description,
            'tags' => $this->tags,
            'version' => $this->version,
            'original_file_name' => $this->original_file_name,
            'file_extension' => $this->file_extension,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'uploaded_by' => $this->uploader?->full_name,
            'department' => $this->department,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'status' => $this->status,
            'remarks' => $this->remarks,
            'downloads_count' => $this->downloads_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
