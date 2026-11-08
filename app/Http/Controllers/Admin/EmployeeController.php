<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveEmployeeRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\LastAdministratorGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(): Response
    {
        $employees = User::query()
            ->select(['id', 'name', 'email', 'is_active'])
            ->where('is_staff', true)
            ->with('roles.permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,

                'roles' => $user->roles
                    ->map(fn (Role $role): array => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                    ])
                    ->values()
                    ->all(),

                'permissions' => $user->roles
                    ->flatMap(
                        fn (Role $role) => $role->permissions
                            ->pluck('description')
                    )
                    ->unique()
                    ->values()
                    ->all(),
            ]);

        $roles = Role::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/employees', [
            'employees' => $employees,
            'roles' => $roles,
        ]);
    }

    public function store(
        SaveEmployeeRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $actor = $request->user();

        abort_unless($actor instanceof User, 403);

        $this->assertAssignableRoles($actor, $data['roles']);

        $employee = DB::transaction(function () use ($data): User {
            $employee = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $employee->forceFill([
                'is_staff' => true,
                'is_active' => true,
            ])->save();

            $employee->roles()->sync($data['roles']);

            return $employee;
        }, 3);

        $employee->sendEmailVerificationNotification();

        return to_route('admin.employees.index');
    }

    public function update(
        SaveEmployeeRequest $request,
        User $employee,
        LastAdministratorGuard $guard,
    ): RedirectResponse {
        abort_unless($employee->is_staff, 404);

        $data = $request->validated();

        $actor = $request->user();

        abort_unless($actor instanceof User, 403);

        $this->assertAssignableRoles($actor, $data['roles']);

        $emailChanged = $employee->email !== $data['email'];

        DB::transaction(
            function () use (
                $employee,
                $data,
                $emailChanged,
                $guard
            ): void {
                $guard->lock();
                $before = $guard->snapshot();

                $employee->fill([
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);

                if ($emailChanged) {
                    $employee->forceFill([
                        'email_verified_at' => null,
                    ]);
                }

                $employee->save();

                $employee->roles()->sync($data['roles']);

                $guard->ensureRetained($before, 'roles');
            },
            3
        );

        if ($emailChanged) {
            $employee->sendEmailVerificationNotification();
        }

        return to_route('admin.employees.index');
    }

    public function changeStatus(
        Request $request,
        User $employee,
        LastAdministratorGuard $guard,
    ): RedirectResponse {
        abort_unless($employee->is_staff, 404);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(
            function () use ($employee, $data, $guard): void {
                $guard->lock();
                $before = $guard->snapshot();

                $employee->forceFill([
                    'is_active' => $data['is_active'],
                ])->save();

                $guard->ensureRetained($before, 'is_active');
            },
            3
        );

        return to_route('admin.employees.index');
    }

    /** @param array<int, int> $roleIds */
    private function assertAssignableRoles(
        User $actor,
        array $roleIds
    ): void {
        $permissions = Permission::query()
            ->whereHas(
                'roles',
                fn ($query) => $query->whereIn(
                    'roles.id',
                    $roleIds
                )
            )
            ->pluck('slug');

        foreach ($permissions as $permission) {
            if (! $actor->hasPermission($permission)) {
                throw ValidationException::withMessages([
                    'roles' => 'Você não pode atribuir papéis com permissões que não possui.',
                ]);
            }
        }
    }
}
