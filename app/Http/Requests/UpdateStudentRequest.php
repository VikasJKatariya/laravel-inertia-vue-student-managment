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
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email,' . $this->student->id],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ];
    }

    protected function prepareForValidation()
    {
        // If no new image is uploaded, don't validate the image field
        if (!$this->hasFile('image')) {
            $this->request->remove('image');
        }
    }

    public function attributes()
    {
        return [
            'name' => 'name',
            'email' => 'email',
            'class_id' => 'class',
            'section_id' => 'section',
        ];

    }
}
