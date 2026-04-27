<?php

namespace App\Http\Requests\Sports;

use App\Models\SpRtiNotice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class RtiNoticeRequest extends FormRequest
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
            'startDate' => $this->startDate ? Date::createFromFormat('d/m/Y', $this->startDate) : null,
            'endDate' => $this->endDate ? Date::createFromFormat('d/m/Y', $this->endDate) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'noticeNo' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $check = SpRtiNotice::where('notice_no', trim($value))
                    ->when($this->id, function ($query) {
                        return $query->where('id', '!=', $this->id);
                    })
                    ->exists();
                if ($check) {
                    return $fail('Information exists');
                }
            }],
            'subject' => 'required',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'file' => ['nullable', 'array', Rule::requiredIf(!$this->id)],
            'file.*' => ['nullable', Rule::requiredIf(!$this->id), 'max:2048'],
        ];
    }

    public function attributes()
    {
        return [
            'noticeNo' => 'RTI notice no.',
            'subject' => 'Subject',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'file' => 'File',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':attribute is required.',
            '*.max' => ':attribute must not exceed :max characters.',
            '*.date' => 'Invalid date',
            '*.after_or_equal' => 'End date must be after or equal to start date',
            'file.*.max' => 'File size must not exceed 2MB.',
        ];
    }
}
