<?php

namespace App\Http\Resources\Sports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StadiumResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'location' => $this->location,
            'cover_img' => $this->cover_img,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'stadium_details' => $this->stadiumDetails ?? null,
            'stadium_highlights' => $this->stadiumHighlights ?? [],
            'stadium_images' => $this->images ?? []
        ];
    }
}
