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
            'pageTitle' => ['nullable', 'max:255'],
            'banner' => ['nullable', 'array', Rule::requiredIf(!$this->id)],
            'banner.*' => 'image|file|mimes:jpeg,png,jpg,webp|max:200',
        ];
    }

    public function attributes()
    {
        return [
            'page' => 'Page',
            'pageTitle' => 'Page title',
            'banner' => 'Banner',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            'pageTitle.max' => 'Page title must not exceed 255 characters',
            'banner.image' => 'Banner must be an image',
            'banner.mimes' => 'Invalid file type. Allowed: jpeg, png, jpg, webp',
            'banner.max' => 'File size must be less than 500 KB',
        ];
    }
}
