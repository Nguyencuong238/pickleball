<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OcrMatchStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by route middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'match_type' => 'required|in:singles,doubles',
            'opponent_id' => 'required|exists:users,id',
            'challenger_partner_id' => 'nullable|required_if:match_type,doubles|exists:users,id',
            'opponent_partner_id' => 'nullable|required_if:match_type,doubles|exists:users,id',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
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
            'match_type.required' => 'Match type is required',
            'match_type.in' => 'Match type must be singles or doubles',
            'opponent_id.required' => 'Opponent is required',
            'opponent_id.exists' => 'Opponent not found',
            'challenger_partner_id.required_if' => 'Partner required for doubles match',
            'challenger_partner_id.exists' => 'Challenger partner not found',
            'opponent_partner_id.required_if' => 'Opponent partner required for doubles match',
            'opponent_partner_id.exists' => 'Opponent partner not found',
            'scheduled_date.after_or_equal' => 'Scheduled date cannot be in the past',
            'scheduled_time.date_format' => 'Time must be in HH:MM format',
        ];
    }
}
