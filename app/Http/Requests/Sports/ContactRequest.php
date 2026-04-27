<?php

namespace App\Http\Requests\Sports;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'name' => 'required|max:255',
            'designation' => 'required|max:255',
            'department' => 'required|max:255',
            'address' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'phone_1' => 'nullable|max:255',
            'phone_2' => 'nullable|max:255',
            'fax' => 'nullable|max:255',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Name',
            'designation' => 'Designation',
            'department' => 'Department',
            'address' => 'Address',
            'email' => 'Email',
            'phone_1' => 'Phone 1',
            'phone_2' => 'Phone 2',
            'fax' => 'Fax',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':attribute is required',
            '*.max' => ':attribute may not be more than :max characters',
            'email.email' => 'Invalid email',
            '*.numeric' => ':attribute must be a number',
            '*.max' => ':attribute can not be more than 10'
        ];
    }
}
