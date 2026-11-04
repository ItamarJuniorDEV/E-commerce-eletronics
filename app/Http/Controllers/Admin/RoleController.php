<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()
            ->with('permissions')
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->select(['id', 'slug', 'description'])
            ->orderBy('description')
            ->get();

        return Inertia::render('admin/roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Role::create($request->validated());

        return to_route('admin.roles.index');
    }

    public function update(
        UpdateRoleRequest $request,
        Role $role
    ): RedirectResponse {
        $data = $request->validated();

        $permissionIds = isset($data['permissions'])
            ? array_map('intval', $data['permissions'])
            : null;

        unset($data['permissions']);

        DB::transaction(function () use (
            $role,
            $data,
            $permissionIds
        ): void {
            if ($permissionIds !== null) {
                // Serializa as alterações de permissões
                // administrativas feitas por este fluxo.
                $managementPermission = Permission::query()
                    ->where('slug', 'roles.manage')
                    ->lockForUpdate()
                    ->first();

                if ($managementPermission !== null) {
                    $roleHasManagementPermission = $role
                        ->permissions()
                        ->where(
                            'permissions.id',
                            $managementPermission->getKey()
                        )
                        ->exists();

                    $isRemovingManagementPermission =
                        $roleHasManagementPermission
                        && ! in_array(
                            $managementPermission->getKey(),
                            $permissionIds,
                            true
                        );

                    if ($isRemovingManagementPermission) {
                        $hasOtherManager = User::query()
                            ->whereNotNull('email_verified_at')
                            ->whereHas(
                                'roles',
                                function ($query) use (
                                    $role,
                                    $managementPermission
                                ) {
                                    $query
                                        ->where(
                                            'roles.id',
                                            '!=',
                                            $role->getKey()
                                        )
                                        ->whereHas(
                                            'permissions',
                                            function ($permissionQuery) use (
                                                $managementPermission
                                            ) {
                                                $permissionQuery->where(
                                                    'permissions.id',
                                                    $managementPermission
                                                        ->getKey()
                                                );
                                            }
                                        );
                                }
                            )
                            ->exists();

                        if (! $hasOtherManager) {
                            throw ValidationException::withMessages([
                                'permissions' => 'Não é possível remover o último acesso administrativo.',
                            ]);
                        }
                    }
                }
            }

            $role->update($data);

            if ($permissionIds !== null) {
                $role->permissions()->sync($permissionIds);
            }
        }, 3);

        return to_route('admin.roles.index');
    }
}
