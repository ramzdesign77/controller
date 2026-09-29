<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@minimarket.test'],
            [
                'name' => 'Admin Minimarket',
                'password' => 'password',
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'kasir@minimarket.test'],
            [
                'name' => 'Kasir Minimarket',
                'password' => 'password',
                'role' => 'kasir',
            ],
        );
    }
}
