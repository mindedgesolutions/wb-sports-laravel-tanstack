<?php

namespace App\Http\Requests;

use App\Models\MountainTraining;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class MountainTrainingRequest extends FormRequest
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
                if (MountainTraining::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        $query->where('id', '!=', $this->id);
                    })->exists()
                ) {
                    $fail('Course exists');
                }
            }],
            'courseNo' => ['required', 'integer', 'min:1'],
            'duration' => ['required', 'integer', 'min:1'],
            'groupStart' => ['required', 'integer'],
            'groupEnd' => ['required', 'integer', 'after_or_equal:groupStart'],
            'courseFee' => ['nullable', 'integer'],
            'remarks' => ['nullable', 'max:255'],
            'file' => ['nullable', 'file', 'array'],
            'file.*' => ['nullable', 'file', 'max:2048']
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'course name',
            'courseNo' => 'no. of courses',
            'duration' => 'duration (days)',
            'groupStart' => 'startng age group',
            'groupEnd' => 'ending age group',
            'courseFee' => 'course fee',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            '*.integer' => ':Attribute must be a no.',
            '*.after_or_equal' => ':Attribute must be after or equal to the starting age group',
            '*.max' => ':Attribute must not be greater than 2MB',
            '*.file' => 'Invalid file type',
        ];
    }
}
