
<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('usuário com permissão pode gerenciar papéis', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'Administrador',
        'slug' => 'admin',
    ]);

    $permission = Permission::create([
        'slug' => 'roles.manage',
        'description' => 'Gerenciar papéis',
    ]);

    $role->permissions()->attach($permission->id);
    $user->roles()->attach($role->id);

    expect(Gate::forUser($user)->allows('roles.manage'))->toBeTrue();
});

test('usuário sem permissão não pode gerenciar papéis', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $permission = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    $role->permissions()->attach($permission->id);
    $user->roles()->attach($role->id);

    expect(Gate::forUser($user)->allows('roles.manage'))->toBeFalse();
});

test('usuário sem papéis não pode gerenciar papéis', function () {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('roles.manage'))->toBeFalse();
});
