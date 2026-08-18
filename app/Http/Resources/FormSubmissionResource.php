<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'data' => $this->data,
            'is_read' => $this->is_read,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
