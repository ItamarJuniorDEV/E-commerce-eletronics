<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        $slugRule = Rule::unique('roles', 'slug');

        if ($role instanceof Role) {
            $slugRule->ignore($role->getKey());
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slugRule,
            ],
            'permissions' => [
                'sometimes',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id'),
            ],
        ];
    }
}
