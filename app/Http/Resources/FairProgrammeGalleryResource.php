<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FairProgrammeGalleryResource extends JsonResource
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
            'program_id' => $this->program_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'programme_date' => $this->programme_date,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'organisation' => $this->organisation,
            'show_in_gallery' => $this->show_in_gallery,
            'added_by' => $this->added_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'images' => $this->images,
        ];
    }
}
