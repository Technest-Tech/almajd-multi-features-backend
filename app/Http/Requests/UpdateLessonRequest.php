<?php

namespace App\Http\Requests;

use App\Models\PaymentSettings;
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
        $isTeacher = $user && $user->isTeacher();
        
        // Check if teachers can add past lessons (if user is a teacher)
        $canAddPastLessons = false;
        if ($isAdmin) {
            $canAddPastLessons = true; // Admins can always add past lessons
        } elseif ($isTeacher) {
            // Check if admin has enabled this setting for teachers
            $teachersCanAddPastLessons = PaymentSettings::getSetting('teachers_can_add_past_lessons', '0');
            $canAddPastLessons = $teachersCanAddPastLessons === '1';
        }
        
        $dateRules = ['sometimes', 'required', 'date'];
        // Only enforce "after_or_equal:today" if user cannot add past lessons
        if (!$canAddPastLessons) {
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
