<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role as RoleModel;

test('seeder creates admin and user roles', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    expect(RoleModel::query()->pluck('name')->all())->toBe([
        Role::Admin->value,
        Role::User->value,
    ]);
});

test('seeder creates admin user with correct credentials', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = User::query()->where('email', 'filipmilewski@gmail.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Filip')
        ->and($admin->surname)->toBe('Milewski')
        ->and($admin->hasRole(Role::Admin))->toBeTrue()
        ->and($admin->hasRole(Role::User))->toBeFalse();
});

test('admin user can authenticate', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $response = $this->post(route('login.store'), [
        'email' => 'filipmilewski@gmail.com',
        'password' => 'Widzew13!#',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('user factory can assign roles', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    expect($admin->hasRole(Role::Admin))->toBeTrue()
        ->and($user->hasRole(Role::User))->toBeTrue()
        ->and($user->hasRole(Role::Admin))->toBeFalse();
});
