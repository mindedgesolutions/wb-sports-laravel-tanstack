<?php

namespace App\Http\Requests;

use App\Models\YouthHostel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class YouthHostelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                $check = YouthHostel::where('slug', $slug)
                    ->when($this->id, function ($query) {
                        return $query->where('id', '!=', $this->id);
                    })
                    ->first();
                if ($check) {
                    return $fail('Hostel name exists');
                }
            }],
            'district' => 'required|exists:districts,id',
            'address' => 'required',
            'phone1' => 'nullable|digits:10',
            'phone2' => 'nullable|digits:10',
            'email' => 'nullable|email',
            'accommodation' => 'nullable|max:255',
            'railwayStation' => 'required|max:255',
            'busStop' => 'nullable|max:255',
            'airport' => 'nullable|max:255',
            'network' => 'nullable|max:255',
            'remarks' => 'nullable|max:255',
            'hostelImg' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:100',
        ];
    }

    public function attributes()
    {
        return [
            'phone1' => 'phone no. 1',
            'phone2' => 'phone no. 2',
            'accommodation' => 'accommodation type',
            'railwayStation' => 'railway station',
            'busStop' => 'bus stop',
            'network' => 'road transportation network',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':Attribute is required',
            'phone1.digits' => ':Attribute must be 10 digits',
            'phone2.digits' => ':Attribute must be 10 digits',
            'email.email' => 'Invalid email address',
            'accommodation.required' => 'Accommodation type is required',
            'railwayStation.required' => 'Railway station is required',
        ];
    }
}
