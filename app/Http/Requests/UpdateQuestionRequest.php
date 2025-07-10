<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }
    
    public function rules(): array
    {
        return [
            'question' => 'required|string|max:1000',
            'current_score' => 'required|integer|min:0|max:5'
        ];
    }
    
    public function messages(): array
    {
        return [
            'question.required' => 'The question field is required.',
            'question.max' => 'The question cannot exceed 1000 characters.',
            'current_score.required' => 'The current score is required.',
            'current_score.integer' => 'The current score must be a number.',
            'current_score.min' => 'The current score must be at least 0.',
            'current_score.max' => 'The current score cannot exceed 5.'
        ];
    }
}