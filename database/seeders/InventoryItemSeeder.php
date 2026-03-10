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
        
            ['name' => 'Data Filer Box (Blue)', 'item_code' => 'INV-001', 'category' => 'Finished Goods', 'unit_price' => 180.00, 'stock' => 2500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/data-filer-box-blue.jpg'],
            ['name' => 'Whiteboard (Standard)', 'item_code' => 'INV-002', 'category' => 'Finished Goods', 'unit_price' => 245.00, 'stock' => 4500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-standard.jpg'],
            ['name' => 'Glue (Industrial)', 'item_code' => 'INV-004', 'category' => 'Raw Materials', 'unit_price' => 85.00, 'stock' => 1500, 'unit' => 'liters', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/glue-industrial.jpg'],
            ['name' => 'Storage Box (Large)', 'item_code' => 'INV-005', 'category' => 'Finished Goods', 'unit_price' => 480.00, 'stock' => 8000, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/storage-box-large.jpg'],
            ['name' => 'Single Data Filer', 'item_code' => 'INV-006', 'category' => 'Finished Goods', 'unit_price' => 180.00, 'stock' => 3500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => true, 'image_path' => 'inventory/single-data-filer.jpg'],
            ['name' => 'Storage Box (Small)', 'item_code' => 'INV-007', 'category' => 'Finished Goods', 'unit_price' => 380.00, 'stock' => 1500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/storage-box-small.jpg'],
            ['name' => 'Storage Box (Big)', 'item_code' => 'INV-008', 'category' => 'Finished Goods', 'unit_price' => 430.00, 'stock' => 9500, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/storage-box-big.jpg'],
            ['name' => 'A4 Bond Paper', 'item_code' => 'INV-009', 'category' => 'Finished Goods', 'unit_price' => 220.00, 'stock' => 5000, 'unit' => 'reams', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/a4-bond-paper.jpg'],
            ['name' => 'Double Data Filer', 'item_code' => 'INV-010', 'category' => 'Finished Goods', 'unit_price' => 280.00, 'stock' => 1800, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/double-data-filer.jpg'],
            ['name' => 'Whiteboard (Small)', 'item_code' => 'INV-011', 'category' => 'Finished Goods', 'unit_price' => 130.00, 'stock' => 6000, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-small.jpg'],
            ['name' => 'Whiteboard (Medium)', 'item_code' => 'INV-012', 'category' => 'Finished Goods', 'unit_price' => 200.00, 'stock' => 4000, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-medium.jpg'],
            ['name' => 'Whiteboard (Large)', 'item_code' => 'INV-013', 'category' => 'Finished Goods', 'unit_price' => 290.00, 'stock' => 2000, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-large.jpg'],
            ['name' => 'Chip Board', 'item_code' => 'INV-014', 'category' => 'Raw Materials', 'unit_price' => 1000.00, 'stock' => 800, 'unit' => 'sheets', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/chip-board.jpg'],
            ['name' => 'Big Single Data Filer', 'item_code' => 'INV-015', 'category' => 'Finished Goods', 'unit_price' => 380.00, 'stock' => 1200, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/big-single-data-filer.jpg'],
        ];

        foreach ($items as $item) {
            Product::firstOrCreate(
                ['item_code' => $item['item_code']],
                collect($item)->except('item_code')->toArray()
            );
        }
    }
}