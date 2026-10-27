
<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

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
        ->assertJson([
            'message' => 'Acesso autorizado à gestão de papéis',
        ]);
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
