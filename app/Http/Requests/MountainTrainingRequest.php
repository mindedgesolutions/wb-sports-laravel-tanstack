<?php

namespace App\Http\Requests;

use App\Models\MountainTraining;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class MountainTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'count' => ['required', 'integer', 'min:1'],
            'duration' => ['required', 'integer', 'min:1'],
            'start' => ['required', 'integer'],
            'end' => ['required', 'integer', 'after_or_equal:start'],
            'fee' => ['nullable', 'integer'],
            'remarks' => ['nullable', 'max:255'],
            'newFile' => ['nullable', 'file', 'max:5120']
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'course name',
            'count' => 'no. of courses',
            'duration' => 'duration (days)',
            'start' => 'startng age group',
            'end' => 'ending age group',
            'fee' => 'course fee',
            'newFile' => 'attchment'
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            '*.integer' => ':Attribute must be a no.',
            '*.after_or_equal' => ':Attribute must be after or equal to the starting age group',
            '*.max' => ':Attribute must not be greater than 5MB',
            '*.file' => 'Invalid file type',
        ];
    }
}
