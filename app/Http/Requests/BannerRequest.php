<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', Rule::requiredIf(!$this->id)],
            'title' => ['nullable', 'max:255'],
            'newImg' => [
                Rule::requiredIf(!$this->id),
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:10240',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'page' => 'Page',
            'title' => 'Page title',
            'newImg' => 'Banner',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            'title.max' => 'Page title must not exceed 255 characters',
            'newImg.image' => 'Banner must be an image',
            'newImg.mimes' => 'Invalid file type. Allowed: jpeg, png, jpg, webp',
            'newImg.max' => 'File size must be less than 1 MB',
        ];
    }
}
