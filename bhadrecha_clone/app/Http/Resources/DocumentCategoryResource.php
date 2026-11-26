<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent?->name,
            'description' => $this->description,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'documents_count' => $this->documents_count ?? $this->documents()->count(),
        ];
    }
}
