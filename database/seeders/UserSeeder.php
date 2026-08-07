<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@factuos.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456'),
                'is_active' => true,
                'empresa_id' => null,
            ]
        );

        $adminRole = Role::where('jerarquia', 1)->first();
        if ($adminRole && ! $admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }
    }
}
