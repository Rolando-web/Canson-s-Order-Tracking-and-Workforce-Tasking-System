<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Super Admin (Boss)
        User::factory()->create([
            'name' => 'boss',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Create Admin Manager
        User::factory()->create([
            'name' => 'admin',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Create Employee
        User::factory()->create([
            'name' => 'employee',
            'password' => bcrypt('password'),
            'role' => User::ROLE_EMPLOYEE,
        ]);

        // Create additional test employee
        User::factory()->create([
            'name' => 'test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $this->call([
            InventoryItemSeeder::class,
        ]);
    }
}
