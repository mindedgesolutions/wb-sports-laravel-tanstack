<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DistrictBlockOfficeRequest extends FormRequest
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
            'district' => 'required|exists:districts,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'landline' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
            'mobile_1' => 'nullable|numeric|digits:10|regex:/^[0-9]{10}$/',
            'mobile_2' => 'nullable|numeric|digits:10|regex:/^[0-9]{10}$/',
            'officerName' => 'nullable|string|max:255',
            'officerDesignation' => 'required|string|max:255',
            'officerMobile' => 'nullable|numeric|digits:10|regex:/^[0-9]{10}$/',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'name of the office',
            'landline' => 'landline no.',
            'mobile_1' => 'mobile no. 1',
            'mobile_2' => 'mobile no. 2',
            'officerName' => 'name of the officer',
            'officerDesignation' => 'designation of the officer',
            'officerMobile' => 'mobile no. of the officer',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required.',
            '*.numeric' => ':Attribute must be a number.',
            '*.string' => ':Attribute must be a string.',
            '*.max' => ':Attribute must not exceed :max characters.',
            '*.email' => 'Invalid email',
            '*.exists' => ':Attribute must exist in the specified table.',
            '*.digits' => ':Attribute must be exactly :digits digits.',
            '*.regex' => ':Attribute must be a valid 10-digit number.',
            'mobile_1.regex' => 'Mobile no. 1 must be a valid 10-digit number.',
            'mobile_2.regex' => 'Mobile no. 2 must be a valid 10-digit number.',
            'landline.regex' => 'Landline no. must be a valid number.',
            'officerMobile.regex' => 'Officer mobile no. must be a valid 10-digit number.',
            'officerDesignation.required' => 'Officer designation is required.',
            'officerDesignation.string' => 'Officer designation must be a string.',
        ];
    }
}
