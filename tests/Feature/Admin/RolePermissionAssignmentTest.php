<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

/**
 * @return array{User, Role, Permission}
 */
function createRolePermissionEditor(): array
{
    $user = User::factory()->create([
        'is_staff' => true,
    ]);

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

    return [$user, $role, $permission];
}

test('administrador pode atualizar permissões de um papel', function () {
    [$user] = createRolePermissionEditor();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $oldPermission = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);

    $newPermission = Permission::create([
        'slug' => 'products.create',
        'description' => 'Cadastrar produtos',
    ]);

    $role->permissions()->attach($oldPermission->id);

    $response = $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Supervisor',
        'slug' => 'supervisor',
        'permissions' => [$newPermission->id],
    ]);

    $response->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Supervisor',
        'slug' => 'supervisor',
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $newPermission->id,
    ]);

    $this->assertDatabaseMissing('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $oldPermission->id,
    ]);
});

test('permissões não enviadas permanecem vinculadas', function () {
    [$user, $role, $permission] = createRolePermissionEditor();

    $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Administrador Geral',
        'slug' => 'admin',
    ])->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);
});

test('não permite remover o último acesso administrativo', function () {
    [$user, $role, $permission] = createRolePermissionEditor();

    $response = $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Nome Alterado',
        'slug' => 'admin',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['permissions']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Administrador',
        'slug' => 'admin',
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);
});

test('rejeita permissões inexistentes', function () {
    [$user, $role, $permission] = createRolePermissionEditor();

    $invalidId = (int) Permission::query()->max('id') + 1;

    $response = $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Nome Alterado',
        'slug' => 'admin',
        'permissions' => [$invalidId],
    ]);

    $response->assertSessionHasErrors(['permissions.0']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Administrador',
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);
});

test('rejeita IDs de permissões duplicados', function () {
    [$user, $role, $permission] = createRolePermissionEditor();

    $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Administrador',
        'slug' => 'admin',
        'permissions' => [$permission->id, $permission->id],
    ])->assertSessionHasErrors(['permissions.0', 'permissions.1']);

    $this->assertDatabaseCount('permission_role', 1);
});

test('permite limpar todas as permissões de um papel comum', function () {
    [$user] = createRolePermissionEditor();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $permission = Permission::create([
        'slug' => 'products.view',
        'description' => 'Visualizar produtos',
    ]);
    $role->permissions()->attach($permission->id);

    $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Vendedor',
        'slug' => 'seller',
        'permissions' => [],
    ])->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseMissing('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);
});

test('um administrador com email não verificado não conta como reserva', function () {
    [$user, $role, $permission] = createRolePermissionEditor();

    $backupRole = Role::create([
        'name' => 'Administrador Reserva',
        'slug' => 'backup-admin',
    ]);
    $backupRole->permissions()->attach($permission->id);

    $backupUser = User::factory()->create([
        'is_staff' => true,
    ]);
    $backupUser->forceFill(['email_verified_at' => null])->save();
    $backupUser->roles()->attach($backupRole->id);

    $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Administrador',
        'slug' => 'admin',
        'permissions' => [],
    ])->assertSessionHasErrors(['permissions']);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);
});

test('usuário sem permissão não pode alterar vínculos de papéis', function () {
    [, $role, $managementPermission] = createRolePermissionEditor();

    $outsider = User::factory()->create([
        'is_staff' => true,
    ]);

    $response = $this->actingAs($outsider)->patch("/admin/roles/{$role->id}", [
        'name' => 'Administrador',
        'slug' => 'admin',
        'permissions' => [],
    ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $managementPermission->id,
    ]);
});

test('permite remover acesso quando outro administrador permanece', function () {
    [$user, $role, $permission] = createRolePermissionEditor();

    $backupRole = Role::create([
        'name' => 'Administrador Reserva',
        'slug' => 'backup-admin',
    ]);

    $backupRole->permissions()->attach($permission->id);

    $backupUser = User::factory()->create([
        'is_staff' => true,
    ]);
    $backupUser->roles()->attach($backupRole->id);

    $response = $this->actingAs($user)->patch("/admin/roles/{$role->id}", [
        'name' => 'Administrador',
        'slug' => 'admin',
        'permissions' => [],
    ]);

    $response->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseMissing('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $backupRole->id,
        'permission_id' => $permission->id,
    ]);

    expect($backupUser->hasPermission('roles.manage'))->toBeTrue();
});
