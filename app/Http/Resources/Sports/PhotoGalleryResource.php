<?php

namespace App\Http\Resources\Sports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoGalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'event_date' => $this->event_date ? $this->event_date : null,
            'cover_img' => $this->cover_img ? $this->cover_img : null,
            'photos_count' => $this->photos_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'photos' => $this->photos->flatten()
        ];
    }
}
