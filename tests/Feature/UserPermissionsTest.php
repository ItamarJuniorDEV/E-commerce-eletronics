
<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('usuário com permissão pode utilizá-la', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $permission = Permission::create([
        'slug' => 'products.edit',
        'description' => 'Editar produtos',
    ]);

    $role->permissions()->attach($permission->id);
    $user->roles()->attach($role->id);

    expect($user->hasPermission('products.edit'))->toBeTrue();
});

test('usuário sem a permissão retorna falso', function () {
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

    expect($user->hasPermission('products.edit'))->toBeFalse();
});

test('usuário sem papéis não possui permissões', function () {
    $user = User::factory()->create();

    expect($user->hasPermission('products.edit'))->toBeFalse();
});

test('usuário recebe permissão por qualquer um dos seus papéis', function () {
    $user = User::factory()->create();

    $salesperson = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $manager = Role::create([
        'name' => 'Gerente',
        'slug' => 'manager',
    ]);

    $permission = Permission::create([
        'slug' => 'products.edit',
        'description' => 'Editar produtos',
    ]);

    $manager->permissions()->attach($permission->id);
    $user->roles()->attach([$salesperson->id, $manager->id]);

    expect($user->hasPermission('products.edit'))->toBeTrue();
});
