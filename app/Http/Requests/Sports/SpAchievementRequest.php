<?php

namespace App\Http\Requests\Sports;

use App\Models\SpAchievement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SpAchievementRequest extends FormRequest
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
            'title' => ['required', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpAchievement::where('slug', $inputSlug)
                    ->when($this->id, function ($query) {
                        $query->where('id', '!=', $this->id);
                    })
                    ->exists()
                ) {
                    $fail('Achievement exists');
                }
            }],
            'achievementDate' => 'nullable|date|before_or_equal:today',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'title',
            'achievementDate' => 'achievement date',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':Attribute is required.',
            '*.date' => ':Attribute must be a valid date.',
            '*.before_or_equal' => ':Attribute must be before or equal to today.',
        ];
    }
}
