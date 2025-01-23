<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $webRoles = [
            Role::ROLE_SUPER_ADMIN,
            Role::ROLE_ADMIN,
        ];

        foreach ($webRoles as $role) {
            Role::updateOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );
        }
    }
}
