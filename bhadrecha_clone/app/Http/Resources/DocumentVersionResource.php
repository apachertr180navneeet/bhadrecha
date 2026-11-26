<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'version_number' => $this->version_number,
            'original_file_name' => $this->original_file_name,
            'file_extension' => $this->file_extension,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'changelog' => $this->changelog,
            'uploaded_by' => $this->uploader?->full_name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
