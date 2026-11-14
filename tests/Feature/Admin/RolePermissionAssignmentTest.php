<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\LastAdministratorGuard;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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

test('não permite remover o último acesso de gerenciar funcionários', function () {
    [$admin, $role, $rolesPermission] = createRolePermissionEditor();

    $usersPermission = Permission::create([
        'slug' => 'users.manage',
        'description' => 'Gerenciar funcionários',
    ]);

    $role->permissions()->attach($usersPermission->id);

    $this->actingAs($admin)
        ->patch(route('admin.roles.update', $role), [
            'name' => 'Nome Alterado',
            'slug' => 'admin',
            'permissions' => [$rolesPermission->id],
        ])
        ->assertSessionHasErrors(['permissions']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Administrador',
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $rolesPermission->id,
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $usersPermission->id,
    ]);
});

test('administrador desativado não conta como reserva', function () {
    [$admin, $role, $permission] = createRolePermissionEditor();

    $backupRole = Role::create([
        'name' => 'Administrador Reserva',
        'slug' => 'backup-admin',
    ]);

    $backupRole->permissions()->attach($permission->id);

    $backupUser = User::factory()->create([
        'is_staff' => true,
        'is_active' => false,
    ]);

    $backupUser->roles()->attach($backupRole->id);

    $this->actingAs($admin)
        ->patch(route('admin.roles.update', $role), [
            'name' => 'Nome Alterado',
            'slug' => 'admin',
            'permissions' => [],
        ])
        ->assertSessionHasErrors(['permissions']);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Administrador',
    ]);

    $this->assertDatabaseHas('permission_role', [
        'role_id' => $role->id,
        'permission_id' => $permission->id,
    ]);

    expect($backupUser->fresh()->is_active)->toBeFalse();
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
test('bloqueia alteração administrativa concorrente no PostgreSQL', function () {
    if (
        DB::connection()->getDriverName() !== 'pgsql'
        || DB::connection()->getDatabaseName() !== 'ecommerce_ci'
    ) {
        $this->markTestSkipped('Exige o PostgreSQL isolado do CI.');
    }

    $original = DB::getDefaultConnection();
    $config = config("database.connections.{$original}");

    config()->set('database.connections.lock_test_a', $config);
    config()->set('database.connections.lock_test_b', $config);

    $a = DB::connection('lock_test_a');
    $b = DB::connection('lock_test_b');

    $permissionId = null;
    $created = false;

    try {
        $permissionId = $a->table('permissions')
            ->where('slug', 'roles.manage')
            ->value('id');

        if ($permissionId === null) {
            $permissionId = $a->table('permissions')->insertGetId([
                'slug' => 'roles.manage',
                'description' => 'Gerenciar papéis',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created = true;
        }

        // Transação A bloqueia a permissão.
        $a->beginTransaction();

        DB::setDefaultConnection('lock_test_a');

        app(LastAdministratorGuard::class)->lock();

        DB::setDefaultConnection($original);

        // Transação B tenta bloquear a mesma permissão.
        $b->beginTransaction();

        $blocked = false;

        try {
            $b->select(
                'SELECT id FROM permissions WHERE id = ? FOR UPDATE NOWAIT',
                [$permissionId]
            );
        } catch (QueryException $exception) {
            $blocked = ($exception->errorInfo[0] ?? null) === '55P03';
        } finally {
            $b->rollBack();
        }

        $this->assertTrue(
            $blocked,
            'A segunda transação deveria ser bloqueada.'
        );

        $a->commit();

        $b->beginTransaction();

        $rows = $b->select(
            'SELECT id FROM permissions WHERE id = ? FOR UPDATE NOWAIT',
            [$permissionId]
        );

        $this->assertCount(1, $rows);

        $b->rollBack();
    } finally {
        DB::setDefaultConnection($original);

        if ($b->transactionLevel() > 0) {
            $b->rollBack();
        }

        if ($a->transactionLevel() > 0) {
            $a->rollBack();
        }

        if ($created && $permissionId !== null) {
            $a->table('permissions')
                ->where('id', $permissionId)
                ->delete();
        }

        DB::purge('lock_test_a');
        DB::purge('lock_test_b');

        $connections = config('database.connections');

        unset($connections['lock_test_a'], $connections['lock_test_b']);

        config()->set('database.connections', $connections);
    }
});
