<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }
    
    public function rules(): array
    {
        return [
            'emails' => 'required|array|min:1',
            'emails.*' => 'email',
            'questions' => 'required|array|min:1',
            'questions.*' => 'exists:questions,id'
        ];
    }
    
    public function messages(): array
    {
        return [
            'emails.required' => 'At least one email address is required.',
            'emails.*.email' => 'All email addresses must be valid.',
            'questions.required' => 'At least one question must be selected.',
            'questions.*.exists' => 'Selected questions must be valid.'
        ];
    }
}