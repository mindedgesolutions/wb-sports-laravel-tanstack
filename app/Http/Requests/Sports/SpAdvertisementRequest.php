<?php

namespace App\Http\Requests\Sports;

use App\Models\SpAdvertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SpAdvertisementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (!$this->hasFile('newFile')) {
            $this->merge([
                'newFile' => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                SpAdvertisement::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        $query->where('id', '!=', $this->id);
                    })
                    ->exists() ? $fail('Advertisement exists') : null;
            }],
            'description' => 'nullable',
            'adDate' => 'nullable|before_or_equal:today',
            'newFile' => [
                Rule::requiredIf(!$this->id),
                'nullable',
                'file',
                'max:102400',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'title',
            'description' => 'description',
            'adDate' => 'advertisement date',
            'newFile' => 'file',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required.',
            'newFile.max' => 'File must not exceed 100MB',
            'adDate.before_or_equal' => 'Advertisement date must be today or earlier',
        ];
    }
}
