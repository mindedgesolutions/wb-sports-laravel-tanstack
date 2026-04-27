<?php

namespace App\Http\Requests\Sports;

use App\Models\SpStadium;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StadiumRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (SpStadium::where('slug', $slug)
                    ->where('id', '!=', $this->id)
                    ->exists()
                ) {
                    $fail('Stadium exists');
                }
            }],
            'location' => ['required', 'max:255'],
            'address' => ['nullable', 'max:255'],
            'coverImg' => [
                Rule::requiredIf(!$this->id),
                'file',
                'max:2048',
            ],
            'newGalleryImg' => ['nullable', 'array'],
            'newGalleryImg.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }
}
