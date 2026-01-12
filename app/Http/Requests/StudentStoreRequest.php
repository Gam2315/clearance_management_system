<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'dean']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\.]+$/',
            'middlename' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\.]+$/',
            'lastname' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\.]+$/',
            'suffix_name' => 'nullable|string|max:10|regex:/^[a-zA-Z\s\-\.]+$/',
            'student_id' => 'required|string|max:50|unique:students,student_number|regex:/^[a-zA-Z0-9\-]+$/',
            'department_id' => 'required|exists:departments,id',
            'program' => 'required|exists:courses,id',
            'year' => 'required|in:1st,2nd,3rd,4th,5th',
            'academic_id' => 'required|exists:academic_years,id',
            'is_uniwide' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'firstname.regex' => 'First name can only contain letters, spaces, hyphens, and periods.',
            'middlename.regex' => 'Middle name can only contain letters, spaces, hyphens, and periods.',
            'lastname.regex' => 'Last name can only contain letters, spaces, hyphens, and periods.',
            'suffix_name.regex' => 'Suffix can only contain letters, spaces, hyphens, and periods.',
            'student_id.regex' => 'Student ID can only contain letters, numbers, and hyphens.',
            'student_id.unique' => 'This student ID is already taken.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'firstname' => $this->sanitizeInput($this->firstname),
            'middlename' => $this->sanitizeInput($this->middlename),
            'lastname' => $this->sanitizeInput($this->lastname),
            'suffix_name' => $this->sanitizeInput($this->suffix_name),
            'student_id' => $this->sanitizeInput($this->student_id),
        ]);
    }

    /**
     * Sanitize input to prevent XSS
     */
    private function sanitizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return trim(strip_tags($input));
    }
}
