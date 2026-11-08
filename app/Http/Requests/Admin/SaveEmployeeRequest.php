<?php

namespace App\Http\Requests\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEmployeeRequest extends FormRequest
{
    use PasswordValidationRules, ProfileValidationRules;

    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $this->user()?->can('users.manage') === true
            && (! ($employee instanceof User) || $employee->is_staff);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $employee = $this->route('employee');

        $employeeId = $employee instanceof User
            ? (int) $employee->getKey()
            : null;

        return [
            ...$this->profileRules($employeeId),

            'password' => $employeeId === null
                ? $this->passwordRules()
                : ['prohibited'],

            'roles' => ['present', 'array', 'min:1'],

            'roles.*' => [
                'integer',
                'distinct',
                Rule::exists('roles', 'id'),
            ],
        ];
    }
}
