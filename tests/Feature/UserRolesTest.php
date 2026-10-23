<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('um usuário pode ter duas funções', function () {
    $user = User::factory()->create();

    $salesperson = new Role;
    $salesperson->name = 'Vendedor';
    $salesperson->slug = 'salesperson';
    $salesperson->save();

    $manager = new Role;
    $manager->name = 'Gerente';
    $manager->slug = 'manager';
    $manager->save();

    $user->roles()->attach([$salesperson->id, $manager->id]);

    expect($user->roles()->count())->toBe(2);

    expect($user->roles()->pluck('roles.id')->all())
        ->toEqualCanonicalizing([$salesperson->id, $manager->id]);

    expect($salesperson->users()->whereKey($user->id)->exists())
        ->toBeTrue();

    expect($manager->users()->whereKey($user->id)->exists())
        ->toBeTrue();
});

test('uma função pode pertencer a dois usuários', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $salesperson = new Role;
    $salesperson->name = 'Vendedor';
    $salesperson->slug = 'salesperson';
    $salesperson->save();

    $firstUser->roles()->attach($salesperson->id);
    $secondUser->roles()->attach($salesperson->id);

    expect($salesperson->users()->count())->toBe(2);

    expect($salesperson->users()->pluck('users.id')->all())
        ->toEqualCanonicalizing([
            $firstUser->id,
            $secondUser->id,
        ]);
});

test('um usuário não pode receber a mesma função duas vezes', function () {
    $user = User::factory()->create();

    $salesperson = new Role;
    $salesperson->name = 'Vendedor';
    $salesperson->slug = 'salesperson';
    $salesperson->save();

    $user->roles()->attach($salesperson->id);

    expect(fn () => $user->roles()->attach($salesperson->id))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('duas funções não podem ter o mesmo slug', function () {
    $salesperson = new Role;
    $salesperson->name = 'Vendedor';
    $salesperson->slug = 'salesperson';
    $salesperson->save();

    $anotherRole = new Role;
    $anotherRole->name = 'Outra função';
    $anotherRole->slug = 'salesperson';

    expect(fn () => $anotherRole->save())
        ->toThrow(UniqueConstraintViolationException::class);
});
