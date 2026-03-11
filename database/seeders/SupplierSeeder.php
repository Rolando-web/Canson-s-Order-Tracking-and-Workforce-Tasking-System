<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name'    => 'ABC Office Supplies Co.',
                'address' => '123 Rizal Avenue, Quezon City, Metro Manila',
                'email'   => 'orders@abcoffice.com',
                'phone'   => '09171234567',
            ],
            [
                'name'    => 'Nacional Paper & Printing',
                'address' => '45 Printing St., Sampaloc, Manila',
                'email'   => 'supply@nacional.com',
                'phone'   => '09281234567',
            ],
            [
                'name'    => 'DataFile Industries Inc.',
                'address' => '78 Industrial Road, Caloocan City, Metro Manila',
                'email'   => 'sales@datafileindustries.com',
                'phone'   => '09391234567',
            ],
            [
                'name'    => 'Prestige School Supplies',
                'address' => '9 Education Lane, Marikina City, Metro Manila',
                'email'   => 'info@prestigeschool.com',
                'phone'   => '09501234567',
            ],
            [
                'name'    => 'Canson Raw Materials Depot',
                'address' => '200 Warehouse Blvd., Valenzuela City, Metro Manila',
                'email'   => 'depot@cansonraw.com',
                'phone'   => '09171239999',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['name' => $supplier['name']],
                array_merge($supplier, ['archived' => false])
            );
        }
    }
}
