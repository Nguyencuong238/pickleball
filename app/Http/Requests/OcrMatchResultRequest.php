<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OcrMatchResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'challenger_score' => 'required|integer|min:0|max:99',
            'opponent_score' => 'required|integer|min:0|max:99|different:challenger_score',
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
            'challenger_score.required' => 'Challenger score is required',
            'challenger_score.integer' => 'Challenger score must be a number',
            'challenger_score.min' => 'Challenger score cannot be negative',
            'challenger_score.max' => 'Challenger score cannot exceed 99',
            'opponent_score.required' => 'Opponent score is required',
            'opponent_score.integer' => 'Opponent score must be a number',
            'opponent_score.min' => 'Opponent score cannot be negative',
            'opponent_score.max' => 'Opponent score cannot exceed 99',
            'opponent_score.different' => 'Cannot have a tie score - there must be a winner',
        ];
    }
}
