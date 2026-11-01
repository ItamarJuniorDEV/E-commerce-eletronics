
<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

function createUserWithRoleManagementPermission(): User
{
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

    return $user;
}

test('usuário com permissão pode cadastrar um papel', function () {
    $user = createUserWithRoleManagementPermission();

    $response = $this
        ->actingAs($user)
        ->post('/admin/roles', [
            'name' => 'Gerente de Vendas',
            'slug' => 'sales-manager',
        ]);

    $response->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseHas('roles', [
        'name' => 'Gerente de Vendas',
        'slug' => 'sales-manager',
    ]);
});

test('usuário sem permissão não pode cadastrar um papel', function () {
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
        ->post('/admin/roles', [
            'name' => 'Gerente de Vendas',
            'slug' => 'sales-manager',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('roles', [
        'slug' => 'sales-manager',
    ]);
});

test('usuário sem papéis não pode cadastrar um papel', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/admin/roles', [
            'name' => 'Gerente de Vendas',
            'slug' => 'sales-manager',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('roles', [
        'slug' => 'sales-manager',
    ]);
});

test('visitante não autenticado não pode cadastrar um papel', function () {
    $response = $this->post('/admin/roles', [
        'name' => 'Gerente de Vendas',
        'slug' => 'sales-manager',
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseMissing('roles', [
        'slug' => 'sales-manager',
    ]);
});

test('nome é obrigatório para cadastrar papel', function () {
    $user = createUserWithRoleManagementPermission();

    $response = $this
        ->actingAs($user)
        ->post('/admin/roles', [
            'slug' => 'sales-manager',
        ]);

    $response->assertSessionHasErrors(['name']);

    $this->assertDatabaseMissing('roles', [
        'slug' => 'sales-manager',
    ]);
});

test('slug é obrigatório para cadastrar papel', function () {
    $user = createUserWithRoleManagementPermission();

    $response = $this
        ->actingAs($user)
        ->post('/admin/roles', [
            'name' => 'Gerente de Vendas',
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseMissing('roles', [
        'name' => 'Gerente de Vendas',
    ]);
});

test('não permite cadastrar papel com slug duplicado', function () {
    $user = createUserWithRoleManagementPermission();

    $response = $this
        ->actingAs($user)
        ->post('/admin/roles', [
            'name' => 'Outro Administrador',
            'slug' => 'admin',
        ]);
    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseMissing('roles', [
        'name' => 'Outro Administrador',
    ]);
});

test('não permite cadastrar papel com slug em formato inválido', function () {
    $user = createUserWithRoleManagementPermission();

    $response = $this
        ->actingAs($user)
        ->post('/admin/roles', [
            'name' => 'Gerente de Marketing',
            'slug' => 'Sales Manager',
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseMissing('roles', [
        'name' => 'Gerente de Marketing',
        'slug' => 'Sales Manager',
    ]);
});
