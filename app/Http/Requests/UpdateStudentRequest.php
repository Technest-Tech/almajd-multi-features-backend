<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'in:USD,GBP,EUR,EGP,SAR,AED,CAD'],
            'hour_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
