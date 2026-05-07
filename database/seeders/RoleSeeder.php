<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Menjalankan seeder role pengguna.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Pengguna dengan hak akses penuh terhadap sistem.',
            ],
            [
                'name' => 'admin_gudang',
                'display_name' => 'Admin Gudang',
                'description' => 'Pengguna yang bertugas mencatat transaksi barang masuk.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}