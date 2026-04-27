<?php

namespace App\Http\Requests\Sports;

use App\Models\SpNewsScroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsScrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'news_date' => $this->newsDate ? Date::createFromFormat('d/m/Y', $this->newsDate) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpNewsScroll::where('slug', $inputSlug)->when($this->id, function ($query) {
                    $query->where('id', '!=', $this->id);
                })->exists()) {
                    $fail('News scroll exists.');
                }
            }],
            'news_date' => 'nullable|date|before_or_equal:today',
            'file' => [Rule::requiredIf(!$this->id), 'nullable', 'array'],
            'file.*' => 'nullable|max:1024',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'title',
            'news_date' => 'news date',
            'file' => 'file',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required.',
            'title.max' => ':Attribute must not exceed :max characters.',
            'file.*.max' => 'File must not exceed 1MB.',
        ];
    }
}
