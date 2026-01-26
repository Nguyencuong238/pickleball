<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SkillQuizAnswerRequest extends FormRequest
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
             'attempt_id' => 'required|uuid|exists:skill_quiz_attempts,id',
             'question_id' => 'required|integer|exists:skill_questions,id',
             'answer_value' => 'required|integer|between:0,3',
             'time_spent_seconds' => 'nullable|integer|min:0|max:600',
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
             'attempt_id.required' => 'Thieu ID phien quiz',
             'attempt_id.uuid' => 'ID phien quiz khong hop le',
             'attempt_id.exists' => 'Phien quiz khong ton tai',
             'question_id.required' => 'Thieu ID cau hoi',
             'question_id.exists' => 'Cau hoi khong ton tai',
             'answer_value.required' => 'Thieu gia tri tra loi',
             'answer_value.between' => 'Gia tri tra loi phai tu 0 den 3',
         ];
     }

     /**
      * Handle a failed validation attempt.
      */
     protected function failedValidation(Validator $validator)
     {
         $errors = $validator->errors()->messages();
         $messages = [];
         
         foreach ($errors as $field => $fieldErrors) {
             $messages = array_merge($messages, $fieldErrors);
         }
         
         if (isset($errors['answer_value'])) {
             $errors['question_id'] = $this->input('question_id') ?? 'Unknown';
         }
         
         $message = implode('. ', $messages);
         
         throw new HttpResponseException(response()->json([
             'message' => $message,
             'errors' => $errors,
         ], 422));
     }
}
