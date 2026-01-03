<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
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
        $user = $this->user();
        $isAdmin = $user && $user->isAdmin();
        
        $dateRules = ['sometimes', 'required', 'date'];
        // Only enforce "after_or_equal:today" if user is not an admin
        if (!$isAdmin) {
            $dateRules[] = 'after_or_equal:today';
        }
        
        return [
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'date' => $dateRules,
            'duration' => ['sometimes', 'required', 'integer', 'min:1'],
            // Status is always 'present' by default, not user-selectable
            'notes' => ['nullable', 'string'],
            'duty' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
