<?php

namespace App\Http\Requests;

use App\Models\ChallengeResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeSubmitRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'challenge_type' => [
                'required',
                'string',
                Rule::in(ChallengeResult::getAllTypes()),
            ],
            'score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'challenge_type.required' => 'Challenge type is required',
            'challenge_type.in' => 'Invalid challenge type',
            'score.required' => 'Score is required',
            'score.integer' => 'Score must be a number',
            'score.min' => 'Score must be 0 or higher',
            'score.max' => 'Score cannot exceed 100',
        ];
    }
}
