<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompCentreRequest extends FormRequest
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
            'yctcName' => 'required|max:255',
            'yctcCode' => 'nullable|max:255',
            'address1' => 'required|min:3|max:255',
            'address2' => 'nullable|min:3|max:255',
            'address3' => 'nullable|min:3|max:255',
            'pincode' => 'nullable|digits:6',
            'inchargeMobile' => 'nullable|digits:10',
            'inchargeEmail' => 'nullable|email',
            'ownerMobile' => 'nullable|digits:10',
        ];
    }

    public function attributes()
    {
        return [
            'district' => 'District',
            'yctcName' => 'YCTC name',
            'yctcCode' => 'YCTC code',
            'address1' => 'Address line 1',
            'address2' => 'Address line 2',
            'address3' => 'Address line 3',
            'pincode' => 'PIN code',
            'inchargeMobile' => 'Incharge mobile',
            'inchargeEmail' => 'Incharge email',
            'ownerMobile' => 'Owner mobile',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            '*.min' => ':Attribute must be at least :min characters',
            '*.max' => ':Attribute may not be greater than :max characters',
            '*.digits' => ':Attribute must be :digits digits',
            '*.email' => 'Invalid email ID',
            '*.exists' => ':Attribute is invalid',
        ];
    }
}
