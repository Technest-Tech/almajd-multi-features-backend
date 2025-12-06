<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertificateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Fully public access
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $certificateId = $this->route('certificate')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'student_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'subject' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'manager_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'teacher_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'certificate_number' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', 'unique:certificates,certificate_number,' . $certificateId],
            'issue_date' => [$isUpdate ? 'sometimes' : 'required', 'date'],
        ];
    }
}
