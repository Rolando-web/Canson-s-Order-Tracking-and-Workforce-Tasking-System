<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Single Data Filer', 'item_code' => 'INV-006', 'category' => 'Finished Goods', 'unit_price' => 180.00, 'stock' => 3500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => true, 'image_path' => 'inventory/SingleDF.png'],
            ['name' => 'Double Data Filer', 'item_code' => 'INV-010', 'category' => 'Finished Goods', 'unit_price' => 280.00, 'stock' => 1800, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/DoubleDF.png'],
            ['name' => 'Big Single Data Filer', 'item_code' => 'INV-015', 'category' => 'Finished Goods', 'unit_price' => 380.00, 'stock' => 1200, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/BigSDF.png'],
            ['name' => 'Glue (Industrial)', 'item_code' => 'INV-004', 'category' => 'Raw Materials', 'unit_price' => 85.00, 'stock' => 1500, 'unit' => 'liters', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/Glue.png'],
            ['name' => 'Storage Box (Large)', 'item_code' => 'INV-005', 'category' => 'Finished Goods', 'unit_price' => 480.00, 'stock' => 8000, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/StorageB(L).png'],
            ['name' => 'Storage Box (Small)', 'item_code' => 'INV-007', 'category' => 'Finished Goods', 'unit_price' => 380.00, 'stock' => 1500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/StorageB(S).png'],
            ['name' => 'Storage Box (Medium)', 'item_code' => 'INV-008', 'category' => 'Finished Goods', 'unit_price' => 430.00, 'stock' => 9500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/StorageB(M).png'],
            ['name' => 'A4 Bond Paper', 'item_code' => 'INV-009', 'category' => 'Finished Goods', 'unit_price' => 220.00, 'stock' => 5000, 'unit' => 'reams', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/A4.png'],
            ['name' => 'Whiteboard (Large)', 'item_code' => 'INV-013', 'category' => 'Finished Goods', 'unit_price' => 290.00, 'stock' => 2000, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/WhiteB(L).png'],
            ['name' => 'Chip Board', 'item_code' => 'INV-014', 'category' => 'Raw Materials', 'unit_price' => 1000.00, 'stock' => 800, 'unit' => 'sheets', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/ChipB.png'],
            ['name' => 'Corrugated Board Sheets', 'item_code' => 'INV-016', 'category' => 'Raw Materials', 'unit_price' => 500.00, 'stock' => 1200, 'unit' => 'sheets', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/CorrugatedB.png'],
            ['name' => 'Whiteboard (Medium)', 'item_code' => 'INV-017', 'category' => 'Finished Goods', 'unit_price' => 220.00, 'stock' => 1500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteB(M).png'],
            ['name' => 'Whiteboard (Small)', 'item_code' => 'INV-018', 'category' => 'Finished Goods', 'unit_price' => 150.00, 'stock' => 2500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteB(S).png'],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                ['item_code' => $item['item_code']],
                collect($item)->except('item_code')->toArray()
            );
        }
    }
}