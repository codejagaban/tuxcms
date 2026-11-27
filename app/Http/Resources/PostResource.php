<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_image' => $this->cover_image,
            'cover_image_alt' => $this->cover_image_alt,
            'post_category_id' => $this->post_category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'author' => $this->whenLoaded('author', fn () => $this->author?->name),
            'status' => $this->status,
            'is_scheduled' => $this->isScheduled(),
            'published_at' => $this->published_at?->toIso8601String(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'path' => $this->path(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
