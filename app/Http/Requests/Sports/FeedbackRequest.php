<?php

namespace App\Http\Requests\Sports;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
            'feedbackType' => 'required|max:255',
            'name' => 'required|max:255',
            'mobile' => 'required|max:255',
            'email' => 'required|email|max:255',
            'address' => 'nullable|max:255',
            'subject' => 'required|max:100',
            'message' => 'required|max:200',
        ];
    }

    public function attributes()
    {
        return [
            'feedbackType' => 'Feedback type',
            'name' => 'Name',
            'mobile' => 'Mobile no.',
            'email' => 'Email',
            'address' => 'Address',
            'subject' => 'Subject',
            'message' => 'Message',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':attribute is required',
            '*.max' => ':attribute may not be more than :max characters',
            'email.email' => 'Invalid email',
            '*.numeric' => ':attribute must be a number',
        ];
    }
}
