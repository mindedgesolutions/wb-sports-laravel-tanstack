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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255|min:3',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user()->id,
            'mobile' => [
                'nullable',
                'numeric',
                'digits:10',
                Rule::unique('user_details', 'mobile')->where(function ($query) {
                    $query->where('user_id', '!=', $this->user()->id);
                })
            ],
            'profileImg' => 'nullable|image|file|mimes:jpeg,png,jpg,webp|max:100',
        ];
    }

    public function attributes()
    {
        return [
            'profileImg' => 'profile image',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed 255 characters',
            '*.min' => ':Attribute must be at least 3 characters',
            'email.email' => 'Invalid email',
            'email.unique' => 'Email already exists',
            'mobile.numeric' => 'Mobile number must be numeric',
            'mobile.digits' => 'Mobile number must be 10 digits',
            'mobile.unique' => 'Mobile number already exists',
            'profileImg.image' => 'Profile image must be an image',
            'profileImg.mimes' => 'Invalid file type. Allowed: jpeg, png, jpg, webp',
            'profileImg.max' => 'File size must be less than 100 KB',
        ];
    }
}
