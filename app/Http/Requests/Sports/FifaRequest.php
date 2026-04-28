<?php

namespace App\Http\Requests\Sports;

use App\Models\SpFifa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FifaRequest extends FormRequest
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
                $inputSlug = Str::slug($value);
                if (SpFifa::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        return $query->where('id', '!=', $this->id);
                    })
                    ->exists()
                ) {
                    $fail('Gallery exists');
                }
            }],
            'description' => ['nullable'],
            'eventDate' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}
