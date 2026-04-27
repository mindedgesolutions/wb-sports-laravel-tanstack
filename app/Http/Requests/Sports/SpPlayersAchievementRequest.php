<?php

namespace App\Http\Requests\Sports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class SpPlayersAchievementRequest extends FormRequest
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
        $allowed = ['archery', 'paralympic-athletics', 'boxing', 'cricket', 'kabaddi-weightlifting', 'cycling', 'hockey', 'football', 'athletics', 'table-tennis', 'posthumous-award', 'lawn-tennis', 'weight-lifting', 'waterpolo', 'shooting', 'badminton', 'billiards-snooker', 'chess'];

        return [
            'sport' => ['required', Rule::in($allowed)],
            'name' => ['required', 'max:255', 'string'],
            'description' => ['required'],
            'achievementDate' => 'nullable|date|before_or_equal:today',
        ];
    }

    public function attributes()
    {
        return [
            'sport' => 'sport',
            'name' => 'name',
            'description' => 'description',
            'achievementDate' => 'achievement date',
        ];
    }
    public function messages(): array
    {
        return [
            '*.required' => ':Attribute is required.',
            '*.date' => ':Attribute must be a valid date.',
            '*.before_or_equal' => ':Attribute must be before or equal to today.',
            'sport.in' => 'Selected sport is invalid.',
        ];
    }
}
