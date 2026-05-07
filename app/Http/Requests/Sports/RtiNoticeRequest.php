<?php

namespace App\Http\Requests\Sports;

use App\Models\SpRtiNotice;
use Illuminate\Foundation\Http\FormRequest;
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
            'newFile' => ['nullable', Rule::requiredIf(!$this->id), 'max:10240'],
        ];
    }

    public function attributes()
    {
        return [
            'noticeNo' => 'RTI notice no.',
            'subject' => 'Subject',
            'startDate' => 'Start date',
            'endDate' => 'End date',
            'newFile' => 'Attachment',
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
