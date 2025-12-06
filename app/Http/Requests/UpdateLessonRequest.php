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
        return [
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'date' => ['sometimes', 'required', 'date', 'after_or_equal:today'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:planned,completed,missed,cancelled'],
            'notes' => ['nullable', 'string'],
            'duty' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
