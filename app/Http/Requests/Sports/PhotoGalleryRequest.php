<?php

namespace App\Http\Requests\Sports;

use App\Models\SpPhotoGallery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class PhotoGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->eventDate && $this->eventDate !== '') {
            $this->merge([
                'eventDate' => Date::createFromFormat('d/m/Y', $this->eventDate)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpPhotoGallery::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        $query->where('id', '!=', $this->id);
                    })
                    ->exists()
                ) {
                    $fail('Photo gallery exists.');
                }
            }],
            'eventDate' => 'required|date|before_or_equal:today',
            'coverImg' => 'nullable|array',
            'coverImg.*' => 'nullable|mimes:png,jpg,jpeg,webp|max:200',
        ];
    }
}
