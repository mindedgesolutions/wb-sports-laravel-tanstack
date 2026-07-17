<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
            'name' => 'required|max:255|min:3',
            'mobile' => [
                'nullable',
                'numeric',
                'digits:10',
                Rule::unique('user_details', 'mobile')->where(function ($query) {
                    $query->where('user_id', '!=', $this->user()->id);
                })
            ],
            'password' => ['nullable', 'min:8', 'max:16', 'confirmed'],
            'newImg' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    public function attributes()
    {
        return [
            'newImg' => 'profile image',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed 255 characters',
            '*.min' => ':Attribute must be at least 3 characters',
            'mobile.numeric' => 'Mobile number must be numeric',
            'mobile.digits' => 'Mobile number must be 10 digits',
            'mobile.unique' => 'Mobile number already exists',
            'newImg.image' => 'Profile image must be an image',
            'newImg.mimes' => 'Invalid file type. Allowed: jpeg, png, jpg, webp',
            'newImg.max' => 'File size must be less than 100 KB',
        ];
    }
}
