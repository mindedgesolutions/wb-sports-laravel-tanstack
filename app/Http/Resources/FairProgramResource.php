<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class FairProgramResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'occurance' => $this->occurance,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'uuid' => $this->uuid,
            'added_by' => $this->added_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organisation' => $this->organisation,
            'cover_image' => $this->cover_image,
            'images' => $this->images,
        ];
    }
}
