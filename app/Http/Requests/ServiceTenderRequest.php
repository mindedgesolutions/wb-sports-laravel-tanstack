<?php

namespace App\Http\Requests;

use App\Models\ServiceTender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceTenderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        if ($this->tenderDate) {
            $this->merge([
                'tenderDate' => Date::createFromFormat('d/m/Y', $this->tenderDate)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (ServiceTender::where('slug', $inputSlug)->when($this->id, function ($query) {
                    $query->where('id', '!=', $this->id);
                })->exists()) {
                    $fail('Tender title exists');
                }
            }],
            'tenderDate' => ['nullable'],
            'file' => [Rule::requiredIf(!$this->id), 'file', 'array'],
            'file.*' => [Rule::requiredIf(!$this->id), 'file', 'max:5120'],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Tender title',
            'tenderDate' => 'Tender date',
            'file' => 'File',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            'name.max' => 'Tender title must not exceed 255 characters',
            'tenderDate.date' => 'Tender date must be a valid date',
            'file.*.max' => 'File size must not exceed 2MB',
        ];
    }
}
