<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => env('ADMIN_EMAIL'),
            ],

            [
                'name' => env('ADMIN_NAME'),
                'password' => bcrypt(env('ADMIN_PASSWORD')),
            ]
        )->assignRole(Role::ROLE_SUPER_ADMIN);
    }
}
