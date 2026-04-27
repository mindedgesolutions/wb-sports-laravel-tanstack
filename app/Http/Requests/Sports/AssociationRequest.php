<?php

namespace App\Http\Requests\Sports;

use App\Models\SpAssociation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class AssociationRequest extends FormRequest
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
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpAssociation::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        return $query->where('id', '!=', $this->id);
                    })
                    ->exists()
                ) {
                    $fail('Association exists');
                }
            }],
            'address' => ['nullable', 'max:255'],
            'website' => ['nullable', 'url'],
            'phone_1' => ['nullable', 'max:20'],
            'phone_2' => ['nullable', 'max:20'],
            'fax' => ['nullable', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'newImg' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Association name',
            'address' => 'Address',
            'website' => 'Website',
            'phone_1' => 'Phone 1',
            'phone_2' => 'Phone 2',
            'email' => 'Email',
            'newImg' => 'Logo',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters',
            '*.url' => 'Invalid URL',
            '*.email' => 'Invalid email',
            '*.image' => ':Attribute must be an image file',
            '*.mimes' => ':Attribute must be a file of type: :values',
            'newImg.mimes' => ':Attribute must be a file of type: :values',
            'newImg.max' => ':Attribute must not exceed :max kilobytes',
        ];
    }
}
