<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'og_title' => $this->og_title,
            'og_description' => $this->og_description,
            'og_image' => $this->og_image,
            'og_type' => $this->og_type,
            'twitter_card' => $this->twitter_card,
            'twitter_title' => $this->twitter_title,
            'twitter_description' => $this->twitter_description,
            'twitter_image' => $this->twitter_image,
            'canonical_url' => $this->canonical_url,
            'robots' => $this->robots,
            'schema_markup' => $this->schema_markup,
            'custom_head' => $this->custom_head,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
