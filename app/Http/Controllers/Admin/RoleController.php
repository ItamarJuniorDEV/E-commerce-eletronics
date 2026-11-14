<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Support\LastAdministratorGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        Role $role,
        LastAdministratorGuard $guard,
    ): RedirectResponse {
        $data = $request->validated();

        $permissionIds = isset($data['permissions'])
            ? array_map('intval', $data['permissions'])
            : null;

        unset($data['permissions']);

        DB::transaction(function () use (
            $role,
            $data,
            $permissionIds,
            $guard
        ): void {

            $guard->lock();

            $before = $guard->snapshot();

            $role->update($data);

            if ($permissionIds !== null) {
                $role->permissions()->sync($permissionIds);
            }

            $guard->ensureRetained($before, 'permissions');
        }, 3);

        return to_route('admin.roles.index');
    }
}
