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
        User::updateOrCreate(
            ['name' => 'boss'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'contact_number' => '09526874522',
            ]
        );

        // Create Admin Manager
        User::updateOrCreate(
            ['name' => 'admin'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'contact_number' => '09171234567',
            ]
        );

        // Create Employees (Workers)
        User::updateOrCreate(
            ['name' => 'employee1'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_EMPLOYEE,
                'contact_number' => '09981234567',
            ]
        );

            User::updateOrCreate(
            ['name' => 'employee2'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_EMPLOYEE,
                'contact_number' => '09771234567',
            ]
        );

            User::updateOrCreate(
            ['name' => 'employee3'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_EMPLOYEE,
                'contact_number' => '09351234567',
            ]
        );

            User::updateOrCreate(
            ['name' => 'employee4'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_EMPLOYEE,
                'contact_number' => '09261234567',
            ]
        );

            User::updateOrCreate(
            ['name' => 'employee5'],
            [
                'password' => bcrypt('password'),
                'role' => User::ROLE_EMPLOYEE,
                'contact_number' => '09651234567',
            ]
        );


        $this->call([
            InventoryItemSeeder::class,
            SupplierSeeder::class,
        ]);
    }
}
