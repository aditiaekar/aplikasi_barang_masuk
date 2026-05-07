<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Menjalankan seeder user default.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminGudangRole = Role::where('name', 'admin_gudang')->first();

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'role_id' => $superAdminRole?->id,
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'phone' => null,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'admingudang'],
            [
                'role_id' => $adminGudangRole?->id,
                'name' => 'Admin Gudang',
                'email' => 'admingudang@example.com',
                'phone' => null,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
    }
}