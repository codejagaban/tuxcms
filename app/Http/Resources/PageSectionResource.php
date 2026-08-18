<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'data' => $this->data,
            'order' => $this->order,
            'is_visible' => $this->is_visible,
            'background_image' => $this->when(
                $this->relationLoaded('media'),
                fn() => $this->getFirstMediaUrl('section_background') ?: null
            ),
            'images' => $this->when(
                $this->relationLoaded('media'),
                fn() => $this->getMedia('section_images')->map(fn($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->name,
                ])
            ),
        ];
    }
}
