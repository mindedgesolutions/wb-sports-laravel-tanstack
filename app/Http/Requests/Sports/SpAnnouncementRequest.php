<?php

namespace App\Http\Requests\Sports;

use App\Models\SpAnnouncement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpAnnouncementRequest extends FormRequest
{
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
            'annNo' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $check = SpAnnouncement::where('ann_no', $value)
                    ->where('type', $this->type)
                    ->when($this->id, function ($query) {
                        return $query->where('id', '!=', $this->id);
                    })
                    ->exists();
                if ($check) {
                    return $fail('Information already exists');
                }
            }],
            'subject' => 'required',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'newFile' => [
                Rule::requiredIf(!$this->id),
                'nullable',
                'file',
                'max:5120',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'annNo' => $this->label . ' no.',
            'startDate' => 'start date',
            'endDate' => 'end date',
            'newFile' => 'file',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            'newFile.max' => ':Attribute must be less than 2 MB',
            'endDate.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
