<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'fields' => $this->fields,
            'success_message' => $this->success_message,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // Only show admin fields to authenticated users
            'notification_email' => $this->when($request->user() !== null, $this->notification_email),
            'submissions_count' => $this->when(
                $request->user() !== null && $this->submissions_count !== null,
                $this->submissions_count
            ),
        ];
    }
}
