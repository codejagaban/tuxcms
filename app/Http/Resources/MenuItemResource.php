<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target,
            'order' => $this->order,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'linkable_id' => $this->linkable_id,
            'linkable_type' => $this->linkable_type,
            'children' => self::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
