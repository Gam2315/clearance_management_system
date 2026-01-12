<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
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
            'middlename' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\.]+$/',
            'lastname' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\.]+$/',
            'suffix_name' => 'nullable|string|max:10|regex:/^[a-zA-Z\s\-\.]+$/',
            'employee_id' => 'required|string|max:50|unique:users,employee_id|regex:/^[a-zA-Z0-9\-]+$/',
            'role' => 'required|in:student,officer,employee,adviser,admin,dean',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'position_id' => 'nullable|exists:positions,id',
            'course_id' => 'nullable|exists:courses,id',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
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
            'employee_id.regex' => 'Employee ID can only contain letters, numbers, and hyphens.',
            'employee_id.unique' => 'This employee ID is already taken.',
            'picture.max' => 'Profile picture must not exceed 5MB.',
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
            'employee_id' => $this->sanitizeInput($this->employee_id),
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
