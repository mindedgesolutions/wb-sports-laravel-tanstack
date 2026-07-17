<?php

namespace App\Http\Requests;

use App\Models\ServicesHomepageScroller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Override;

class ServicesHomepageScrollerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (ServicesHomepageScroller::where('slug', $slug)
                    ->when($this->id, function ($query) {
                        $query->where('id', '!=', $this->id);
                    })->exists()
                ) {
                    $fail('News exists');
                }
            }],
            'eventDate' => ['nullable'],
            'type' => ['required', 'in:attachment,link'],
            'newFile' => ['nullable', 'required_if:type,attachment', 'file', 'max:5120'],
            'link' => ['nullable', 'required_if:type,link', 'max:255']
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'news title',
            'eventDate' => 'event date',
            'newFile' => 'attachment'
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
        ];
    }
}
