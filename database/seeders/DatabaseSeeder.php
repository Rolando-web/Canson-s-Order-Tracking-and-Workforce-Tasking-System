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
        User::firstOrCreate(
            ['name' => 'boss'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_SUPER_ADMIN]
        );

        // Create Admin Manager
        User::firstOrCreate(
            ['name' => 'admin'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_ADMIN]
        );

        // Create Employees (Workers)
        User::firstOrCreate(
            ['name' => 'employee1'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_EMPLOYEE]
        );

            User::firstOrCreate(
            ['name' => 'employee2'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_EMPLOYEE]
        );

            User::firstOrCreate(
            ['name' => 'employee3'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_EMPLOYEE]
        );

            User::firstOrCreate(
            ['name' => 'employee4'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_EMPLOYEE]
        );

            User::firstOrCreate(
            ['name' => 'employee5'],
            ['password' => bcrypt('password'), 'role' => User::ROLE_EMPLOYEE]
        );


        $this->call([
            InventoryItemSeeder::class,
            SupplierSeeder::class,
        ]);
    }
}
