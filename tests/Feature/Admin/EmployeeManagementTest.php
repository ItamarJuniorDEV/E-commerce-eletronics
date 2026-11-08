<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

function createEmployeeTestUser(): User
{
    $user = User::factory()->create();

    $user->forceFill([
        'is_staff' => true,
        'is_active' => true,
    ])->save();

    return $user;
}

function createEmployeeTestAdministrator(): User
{
    $user = createEmployeeTestUser();

    $role = Role::create([
        'name' => 'Administrador',
        'slug' => 'admin',
    ]);

    $permissions = [
        'roles.manage' => 'Gerenciar papéis',
        'users.manage' => 'Gerenciar funcionários internos',
    ];

    foreach ($permissions as $slug => $description) {
        $permission = Permission::create([
            'slug' => $slug,
            'description' => $description,
        ]);

        $role->permissions()->attach($permission->id);
    }

    $user->roles()->attach($role->id);

    return $user;
}

test('lista somente funcionários internos com seus acessos', function () {
    $admin = createEmployeeTestAdministrator();

    User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.employees.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/employees')
                ->has('employees', 1)
                ->where('employees.0.id', $admin->id)
                ->has('employees.0.permissions', 2)
                ->has('roles', 1)
        );
});

test('cliente não acessa a gestão mesmo possuindo papel administrativo', function () {
    $admin = createEmployeeTestAdministrator();

    $customer = User::factory()->create();

    $customer->roles()->attach(
        $admin->roles()->firstOrFail()->id
    );

    $this->actingAs($customer)
        ->get(route('admin.employees.index'))
        ->assertForbidden();
});

test('funcionário sem permissão não acessa a gestão', function () {
    $employee = createEmployeeTestUser();

    $this->actingAs($employee)
        ->get(route('admin.employees.index'))
        ->assertForbidden();
});

test('visitante não acessa a gestão de funcionários', function () {
    $this->get(route('admin.employees.index'))
        ->assertRedirect(route('login'));
});

test('funcionário com email não verificado não acessa a gestão', function () {
    $admin = createEmployeeTestAdministrator();

    $admin->forceFill([
        'email_verified_at' => null,
    ])->save();

    $this->actingAs($admin)
        ->get(route('admin.employees.index'))
        ->assertRedirect(route('verification.notice'));
});

test('administrador cadastra funcionário com papel', function () {
    Notification::fake();

    $admin = createEmployeeTestAdministrator();

    $role = Role::create([
        'name' => 'Gerente Comercial',
        'slug' => 'sales-manager',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.employees.store'), [
            'name' => 'Funcionário de Teste',
            'email' => 'funcionario@example.test',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'roles' => [$role->id],
            'is_staff' => false,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.employees.index'));

    $employee = User::query()
        ->where('email', 'funcionario@example.test')
        ->firstOrFail();

    expect($employee->is_staff)->toBeTrue()
        ->and($employee->is_active)->toBeTrue()
        ->and($employee->email_verified_at)->toBeNull();

    $this->assertDatabaseHas('role_user', [
        'user_id' => $employee->id,
        'role_id' => $role->id,
    ]);

    Notification::assertSentTo(
        $employee,
        VerifyEmail::class
    );
});

test('cadastro exige nome email senha e papéis', function () {
    $admin = createEmployeeTestAdministrator();

    $this->actingAs($admin)
        ->post(route('admin.employees.store'), [])
        ->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'roles',
        ]);

    $this->assertDatabaseCount('users', 1);
});

test('cadastro não permite email duplicado', function () {
    $admin = createEmployeeTestAdministrator();

    $existing = User::factory()->create();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.employees.store'), [
            'name' => 'Novo Funcionário',
            'email' => $existing->email,
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'roles' => [$role->id],
        ])
        ->assertSessionHasErrors(['email']);

    $this->assertDatabaseCount('users', 2);
});

test('cadastro rejeita papel inexistente', function () {
    $admin = createEmployeeTestAdministrator();

    $this->actingAs($admin)
        ->post(route('admin.employees.store'), [
            'name' => 'Novo Funcionário',
            'email' => 'novo@example.test',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'roles' => [999999],
        ])
        ->assertSessionHasErrors(['roles.0']);

    $this->assertDatabaseMissing('users', [
        'email' => 'novo@example.test',
    ]);
});

test('cadastro rejeita papéis duplicados', function () {
    $admin = createEmployeeTestAdministrator();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.employees.store'), [
            'name' => 'Novo Funcionário',
            'email' => 'novo@example.test',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'roles' => [$role->id, $role->id],
        ])
        ->assertSessionHasErrors(['roles.0']);

    $this->assertDatabaseMissing('users', [
        'email' => 'novo@example.test',
    ]);
});

test('administrador edita funcionário e substitui seus papéis', function () {
    $admin = createEmployeeTestAdministrator();

    $employee = createEmployeeTestUser();

    $oldRole = Role::create([
        'name' => 'Atendente',
        'slug' => 'support',
    ]);

    $newRole = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $employee->roles()->attach($oldRole->id);

    $this->actingAs($admin)
        ->patch(route('admin.employees.update', $employee), [
            'name' => 'Nome Atualizado',
            'email' => $employee->email,
            'roles' => [$newRole->id],
        ])
        ->assertRedirect(route('admin.employees.index'));

    $this->assertDatabaseHas('users', [
        'id' => $employee->id,
        'name' => 'Nome Atualizado',
    ]);

    $this->assertDatabaseHas('role_user', [
        'user_id' => $employee->id,
        'role_id' => $newRole->id,
    ]);

    $this->assertDatabaseMissing('role_user', [
        'user_id' => $employee->id,
        'role_id' => $oldRole->id,
    ]);
});

test('alterar email exige nova verificação', function () {
    Notification::fake();

    $admin = createEmployeeTestAdministrator();

    $employee = createEmployeeTestUser();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $employee->roles()->attach($role->id);

    $this->actingAs($admin)
        ->patch(route('admin.employees.update', $employee), [
            'name' => $employee->name,
            'email' => 'alterado@example.test',
            'roles' => [$role->id],
        ])
        ->assertRedirect(route('admin.employees.index'));

    $employee->refresh();

    expect($employee->email)->toBe('alterado@example.test')
        ->and($employee->email_verified_at)->toBeNull();

    Notification::assertSentTo(
        $employee,
        VerifyEmail::class
    );
});

test('não permite editar cliente como funcionário', function () {
    $admin = createEmployeeTestAdministrator();

    $customer = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.employees.update', $customer), [
            'name' => 'Alteração Indevida',
            'email' => $customer->email,
            'roles' => [],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', [
        'id' => $customer->id,
        'name' => 'Alteração Indevida',
    ]);
});

test('edição não permite modificar senha pelo formulário administrativo', function () {
    $admin = createEmployeeTestAdministrator();

    $employee = createEmployeeTestUser();

    $role = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $employee->roles()->attach($role->id);

    $oldPassword = $employee->password;

    $this->actingAs($admin)
        ->patch(route('admin.employees.update', $employee), [
            'name' => $employee->name,
            'email' => $employee->email,
            'roles' => [$role->id],
            'password' => 'OutraSenha123!',
        ])
        ->assertSessionHasErrors(['password']);

    expect($employee->fresh()->password)
        ->toBe($oldPassword);
});

test('funcionário sem permissão não cadastra outro funcionário', function () {
    createEmployeeTestAdministrator();

    $outsider = createEmployeeTestUser();

    $this->actingAs($outsider)
        ->post(route('admin.employees.store'), [
            'name' => 'Novo Funcionário',
            'email' => 'novo@example.test',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'roles' => [],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', [
        'email' => 'novo@example.test',
    ]);
});

test('gestor não pode atribuir permissões superiores às suas', function () {
    $admin = createEmployeeTestAdministrator();

    $hrRole = Role::create([
        'name' => 'Gestor de RH',
        'slug' => 'hr',
    ]);

    $usersManage = Permission::query()
        ->where('slug', 'users.manage')
        ->firstOrFail();

    $hrRole->permissions()->attach($usersManage->id);

    $manager = createEmployeeTestUser();

    $manager->roles()->attach($hrRole->id);

    $adminRole = $admin->roles()->firstOrFail();

    $this->actingAs($manager)
        ->post(route('admin.employees.store'), [
            'name' => 'Novo Funcionário',
            'email' => 'novo@example.test',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'roles' => [$adminRole->id],
        ])
        ->assertSessionHasErrors(['roles']);

    $this->assertDatabaseMissing('users', [
        'email' => 'novo@example.test',
    ]);
});

test('administrador pode desativar e reativar funcionário comum', function () {
    $admin = createEmployeeTestAdministrator();

    $employee = createEmployeeTestUser();

    $this->actingAs($admin)
        ->patch(route('admin.employees.status', $employee), [
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.employees.index'));

    expect($employee->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('admin.employees.status', $employee), [
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.employees.index'));

    expect($employee->fresh()->is_active)->toBeTrue();
});

test('situação inválida é rejeitada', function () {
    $admin = createEmployeeTestAdministrator();

    $employee = createEmployeeTestUser();

    $this->actingAs($admin)
        ->patch(route('admin.employees.status', $employee), [
            'is_active' => 'valor-invalido',
        ])
        ->assertSessionHasErrors(['is_active']);

    expect($employee->fresh()->is_active)->toBeTrue();
});

test('não permite desativar o último administrador ativo', function () {
    $admin = createEmployeeTestAdministrator();

    $this->actingAs($admin)
        ->patch(route('admin.employees.status', $admin), [
            'is_active' => false,
        ])
        ->assertSessionHasErrors(['is_active']);

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('administrador reserva não verificado não evita o bloqueio', function () {
    $admin = createEmployeeTestAdministrator();

    $backup = createEmployeeTestUser();

    $backup->roles()->attach(
        $admin->roles()->firstOrFail()->id
    );

    $backup->forceFill([
        'email_verified_at' => null,
    ])->save();

    $this->actingAs($admin)
        ->patch(route('admin.employees.status', $admin), [
            'is_active' => false,
        ])
        ->assertSessionHasErrors(['is_active']);

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('permite desativação quando existe outro administrador ativo', function () {
    $admin = createEmployeeTestAdministrator();

    $backup = createEmployeeTestUser();

    $backup->roles()->attach(
        $admin->roles()->firstOrFail()->id
    );

    $this->actingAs($admin)
        ->patch(route('admin.employees.status', $backup), [
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.employees.index'));

    expect($backup->fresh()->is_active)->toBeFalse()
        ->and($admin->fresh()->is_active)->toBeTrue();
});

test('não permite trocar o último papel administrativo por papel comum', function () {
    $admin = createEmployeeTestAdministrator();

    $ordinaryRole = Role::create([
        'name' => 'Vendedor',
        'slug' => 'seller',
    ]);

    $adminRole = $admin->roles()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.employees.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => [$ordinaryRole->id],
        ])
        ->assertSessionHasErrors(['roles']);

    $this->assertDatabaseHas('role_user', [
        'user_id' => $admin->id,
        'role_id' => $adminRole->id,
    ]);

    $this->assertDatabaseMissing('role_user', [
        'user_id' => $admin->id,
        'role_id' => $ordinaryRole->id,
    ]);
});

test('não permite retirar permissões do último papel administrativo', function () {
    $admin = createEmployeeTestAdministrator();

    $role = $admin->roles()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.roles.update', $role), [
            'name' => $role->name,
            'slug' => $role->slug,
            'permissions' => [],
        ])
        ->assertSessionHasErrors(['permissions']);

    expect($role->permissions()->count())->toBe(2);
});

test('conta desativada perde acesso mesmo com sessão antiga', function () {
    $admin = createEmployeeTestAdministrator();

    $admin->forceFill([
        'is_active' => false,
    ])->save();

    $this->actingAs($admin)
        ->get(route('admin.employees.index'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('conta desativada não consegue autenticar com senha', function () {
    $admin = createEmployeeTestAdministrator();

    $admin->forceFill([
        'is_active' => false,
    ])->save();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});
