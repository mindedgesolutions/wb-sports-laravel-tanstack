<?php

namespace App\Http\Requests;

use App\Models\NewsEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required'],
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                $check = NewsEvent::where('slug', $slug)
                    ->when($this->id, function ($query) {
                        return $query->where('id', '!=', $this->id);
                    })
                    ->first();
                if ($check) {
                    return $fail('Title already exists');
                }
            }],
            'eventDate' => ['nullable', 'date', 'before_or_equal:today'],
            'newFile' => [
                'nullable',
                Rule::requiredIf(!$this->id),
                'file',
                'max:5120'
            ],
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'Title',
            'eventDate' => 'Event date',
            'newFile' => 'Attachment',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            'title.max' => 'Title must not exceed 255 characters',
            'eventDate.date' => 'Event date must be a valid date',
            'eventDate.before' => 'Event date must be before today',
            'file.max' => 'File size must be less than 200 KB',
        ];
    }
}
