
<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\UniqueConstraintViolationException;

test('um papel pode ter várias permissões', function () {
    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $viewProducts = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    $createProposals = Permission::create([
        'slug' => 'proposals.create',
        'description' => 'Criar propostas',
    ]);

    $role->permissions()->attach([
        $viewProducts->id,
        $createProposals->id,
    ]);

    expect($role->permissions()->count())->toBe(2);

    expect($role->permissions()->pluck('permissions.id')->all())
        ->toEqualCanonicalizing([
            $viewProducts->id,
            $createProposals->id,
        ]);
});

test('uma permissão pode pertencer a vários papéis', function () {
    $salesperson = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $manager = Role::create([
        'name' => 'Gerente',
        'slug' => 'manager',
    ]);

    $permission = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    $permission->roles()->attach([
        $salesperson->id,
        $manager->id,
    ]);

    expect($permission->roles()->count())->toBe(2);

    expect($permission->roles()->pluck('roles.id')->all())
        ->toEqualCanonicalizing([
            $salesperson->id,
            $manager->id,
        ]);
});

test('um papel não pode receber a mesma permissão duas vezes', function () {
    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $permission = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    $role->permissions()->attach($permission->id);

    expect(fn () => $role->permissions()->attach($permission->id))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('duas permissões não podem ter o mesmo slug', function () {
    Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    expect(fn () => Permission::create([
        'slug' => 'products.view',
        'description' => 'Outra descrição',
    ]))->toThrow(UniqueConstraintViolationException::class);
});
