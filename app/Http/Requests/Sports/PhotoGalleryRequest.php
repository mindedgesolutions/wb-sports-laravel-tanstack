<?php

namespace App\Http\Requests\Sports;

use App\Models\SpPhotoGallery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PhotoGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'description' => ['nullable'],
            'eventDate' => ['nullable', 'date', 'before_or_equal:today'],
            'coverImg' => [
                Rule::requiredIf(!$this->id),
                'file',
                'max:2048',
            ],
        ];
    }
}
