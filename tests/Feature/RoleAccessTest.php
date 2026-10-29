
<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('usuário com permissão pode acessar a gestão de papéis', function () {
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

    $response = $this
        ->actingAs($user)
        ->get(route('admin.roles.index'));

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/roles')
                ->has('roles', 1)
                ->where('roles.0.id', $role->id)
                ->where('roles.0.name', 'Administrador')
                ->where('roles.0.slug', 'admin')
        );
});

test('usuário sem permissão não pode acessar a gestão de papéis', function () {
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

    $response = $this
        ->actingAs($user)
        ->get(route('admin.roles.index'));

    $response->assertForbidden();
});

test('usuário sem papéis não pode acessar a gestão de papéis', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.roles.index'));

    $response->assertForbidden();
});

test('visitante não autenticado é redirecionado para o login', function () {
    $response = $this->get(route('admin.roles.index'));

    $response->assertRedirect(route('login'));
});

test('papéis são listados em ordem alfabética', function () {
    $user = User::factory()->create();

    $salesperson = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $permission = Permission::create([
        'slug' => 'roles.manage',
        'description' => 'Gerenciar papéis',
    ]);

    $salesperson->permissions()->attach($permission->id);
    $user->roles()->attach($salesperson->id);

    Role::create([
        'name' => 'Administrador',
        'slug' => 'admin',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.roles.index'));

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/roles')
                ->has('roles', 2)
                ->where('roles.0.name', 'Administrador')
                ->where('roles.1.name', 'Vendedor')
        );
});
