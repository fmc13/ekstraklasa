<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('guests cannot access users index', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));
});

test('non-admin users cannot access users index', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('admin can view users index', function () {
    $admin = User::query()->where('email', 'filipmilewski@gmail.com')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/Index')
            ->has('users'));
});

test('admin can create a user', function () {
    $admin = User::query()->where('email', 'filipmilewski@gmail.com')->firstOrFail();

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Jan',
        'surname' => 'Kowalski',
        'email' => 'jan.kowalski@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'role' => Role::User->value,
    ]);

    $response->assertRedirect(route('users.index'));

    $user = User::query()->where('email', 'jan.kowalski@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Jan')
        ->and($user->surname)->toBe('Kowalski')
        ->and($user->hasRole(Role::User))->toBeTrue()
        ->and(Hash::check('Password1!', $user->password))->toBeTrue();
});

test('admin can update a user', function () {
    $admin = User::query()->where('email', 'filipmilewski@gmail.com')->firstOrFail();
    $user = User::factory()->user()->create([
        'name' => 'Anna',
        'surname' => 'Nowak',
        'email' => 'anna.nowak@example.com',
    ]);

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'name' => 'Anna',
        'surname' => 'Wiśniewska',
        'email' => 'anna.wisniewska@example.com',
        'role' => Role::Admin->value,
    ]);

    $response->assertRedirect(route('users.index'));

    $user->refresh();

    expect($user->surname)->toBe('Wiśniewska')
        ->and($user->email)->toBe('anna.wisniewska@example.com')
        ->and($user->hasRole(Role::Admin))->toBeTrue();
});

test('admin can delete a user', function () {
    $admin = User::query()->where('email', 'filipmilewski@gmail.com')->firstOrFail();
    $user = User::factory()->user()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    expect(User::query()->find($user->id))->toBeNull();
});

test('admin cannot delete themselves', function () {
    $admin = User::query()->where('email', 'filipmilewski@gmail.com')->firstOrFail();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertForbidden();

    expect(User::query()->find($admin->id))->not->toBeNull();
});

test('profile update accepts name and surname', function () {
    $user = User::factory()->create([
        'name' => 'Old',
        'surname' => 'Name',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'New',
            'surname' => 'Surname',
            'email' => $user->email,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('New')
        ->and($user->surname)->toBe('Surname')
        ->and($user->full_name)->toBe('New Surname');
});
