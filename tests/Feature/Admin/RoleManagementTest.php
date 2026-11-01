
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

test('usuário com permissão pode editar um papel', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $rolesCount = Role::query()->count();

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => 'internal-salesperson',
        ]);

    $response->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor Interno',
        'slug' => 'internal-salesperson',
    ]);

    $this->assertDatabaseCount('roles', $rolesCount);
});

test('usuário pode editar papel mantendo o mesmo slug', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => 'salesperson',
        ]);

    $response->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor Interno',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar papel com slug duplicado', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Outro Administrador',
            'slug' => 'admin',
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
        'name' => 'Outro Administrador',
    ]);
});

test('usuário sem permissão não pode editar um papel', function () {
    $user = User::factory()->create();

    $userRole = Role::create([
        'name' => 'Atendente',
        'slug' => 'support-agent',
    ]);

    $permission = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    $userRole->permissions()->attach($permission->id);
    $user->roles()->attach($userRole->id);

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => 'internal-salesperson',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('usuário sem papéis não pode editar um papel', function () {
    $user = User::factory()->create();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => 'internal-salesperson',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('visitante não autenticado não pode editar um papel', function () {
    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this->patch("/admin/roles/{$role->id}", [
        'name' => 'Vendedor Interno',
        'slug' => 'internal-salesperson',
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('usuário com email não verificado não pode editar um papel', function () {
    $user = createUserWithRoleManagementPermission();

    $user->forceFill([
        'email_verified_at' => null,
    ])->save();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => 'internal-salesperson',
        ]);

    $response->assertRedirect(route('verification.notice'));

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('nome é obrigatório para editar papel', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'slug' => 'internal-salesperson',
        ]);

    $response->assertSessionHasErrors(['name']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('slug é obrigatório para editar papel', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar papel com slug em formato inválido', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => 'Internal Salesperson',
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar papel com nome acima do limite', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => str_repeat('A', 256),
            'slug' => 'salesperson',
        ]);

    $response->assertSessionHasErrors(['name']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar papel com slug acima do limite', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => str_repeat('a', 256),
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar papel com nome que não seja texto', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => ['nome-invalido'],
            'slug' => 'salesperson',
        ]);

    $response->assertSessionHasErrors(['name']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar papel com slug que não seja texto', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Vendedor Interno',
            'slug' => ['slug-invalido'],
        ]);

    $response->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});

test('não permite editar um papel inexistente', function () {
    $user = createUserWithRoleManagementPermission();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);

    $missingRoleId = $role->id + 1;

    $response = $this
        ->actingAs($user)
        ->patch("/admin/roles/{$missingRoleId}", [
            'name' => 'Vendedor Interno',
            'slug' => 'internal-salesperson',
        ]);

    $response->assertNotFound();

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Vendedor',
        'slug' => 'salesperson',
    ]);
});
