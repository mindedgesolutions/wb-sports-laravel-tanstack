<?php

namespace App\Http\Requests\Sports;

use App\Models\SpWbsCouncilDesignation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpAdvisoryCommittRequest extends FormRequest
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
        if (!$this->hasFile('newImg')) {
            $this->merge([
                'newImg' => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);

                $check = DB::table('sp_advisory_committees')
                    ->join('sp_wbs_council_designations as swcd', 'sp_advisory_committees.designation_id', '=', 'swcd.id')
                    ->where('swcd.type', $this->type)
                    ->where('sp_advisory_committees.slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        return $query->where('sp_advisory_committees.id', '!=', $this->id);
                    })
                    ->exists();

                if ($check) {
                    return $fail('Member exists');
                }
            }],
            'designation' => ['required', function ($attribute, $value, $fail) {
                if (!SpWbsCouncilDesignation::where('type', $this->type)
                    ->where('id', $value)
                    ->exists()) {
                    return $fail('Designation does not exist');
                }
            }],
            'designationLabel' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|numeric|digits:10|regex:/^[0-9]{10}$/',
            'fax' => 'nullable|max:20',
            'newImg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'name',
            'designation' => 'designation',
            'designationLabel' => 'designation label',
            'email' => 'email',
            'mobile' => 'mobile no.',
            'fax' => 'fax no.',
            'newImg' => 'photo',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':Attribute is required.',
            '*.max' => ':Attribute must not exceed :max characters.',
            '*.email' => 'Invalid email',
            '*.numeric' => ':Attribute must be a number.',
            '*.digits' => ':Attribute must be exactly :digits digits.',
            '*.image' => ':Attribute must be an image file.',
            '*.mimes' => 'Invalid file type for :attribute. Allowed types: jpeg, png, jpg, webp.',
            '*.max' => 'File size for :attribute must not exceed :max KB.',
        ];
    }
}
