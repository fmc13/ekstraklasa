<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        RoleModel::findOrCreate(Role::Admin->value, 'web');
        RoleModel::findOrCreate(Role::User->value, 'web');

        $admin = User::query()->updateOrCreate(
            ['email' => 'filipmilewski@gmail.com'],
            [
                'name' => 'Filip',
                'surname' => 'Milewski',
                'password' => 'Widzew13!#',
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles([Role::Admin->value]);
    }
}
