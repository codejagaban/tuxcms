<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'template' => $this->template,
            'status' => $this->status,
            'order' => $this->order,
            'is_homepage' => $this->is_homepage,
            'custom_fields' => $this->custom_fields,
            'full_path' => $this->getFullPath(),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relationships
            'author' => $this->whenLoaded('author', fn() => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
            'parent' => $this->whenLoaded('parent', fn() => [
                'id' => $this->parent->id,
                'title' => $this->parent->title,
                'slug' => $this->parent->slug,
            ]),
            'children' => PageResource::collection($this->whenLoaded('children')),
            'sections' => PageSectionResource::collection($this->whenLoaded('sections')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'featured_image' => $this->when(
                $this->relationLoaded('media'),
                fn() => $this->getFirstMediaUrl('featured_image') ?: null
            ),
            'gallery' => $this->when(
                $this->relationLoaded('media'),
                fn() => $this->getMedia('gallery')->map(fn($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ])
            ),
        ];
    }
}
