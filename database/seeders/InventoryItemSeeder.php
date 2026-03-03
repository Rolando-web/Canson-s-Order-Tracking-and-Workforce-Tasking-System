<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryItem;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Data Filer Box (Blue)', 'item_code' => 'INV-001', 'category' => 'Finished Goods', 'stock' => 250, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/data-filer-box-blue.jpg'],
            ['name' => 'Whiteboard (Standard)', 'item_code' => 'INV-002', 'category' => 'Finished Goods', 'stock' => 45, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-standard.jpg'],
            ['name' => 'Glue (Industrial)', 'item_code' => 'INV-004', 'category' => 'Raw Materials', 'stock' => 15, 'unit' => 'liters', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/glue-industrial.jpg'],
            ['name' => 'Storage Box (Large)', 'item_code' => 'INV-005', 'category' => 'Finished Goods', 'stock' => 80, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/storage-box-large.jpg'],
            ['name' => 'Single Data Filer', 'item_code' => 'INV-006', 'category' => 'Finished Goods', 'stock' => 350, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => true, 'image_path' => 'inventory/single-data-filer.jpg'],
            ['name' => 'Storage Box (Small)', 'item_code' => 'INV-007', 'category' => 'Finished Goods', 'stock' => 150, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/storage-box-small.jpg'],
            ['name' => 'Storage Box (Big)', 'item_code' => 'INV-008', 'category' => 'Finished Goods', 'stock' => 95, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/storage-box-big.jpg'],
            ['name' => 'A4 Bond Paper', 'item_code' => 'INV-009', 'category' => 'Finished Goods', 'stock' => 500, 'unit' => 'reams', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/a4-bond-paper.jpg'],
            ['name' => 'Double Data Filer', 'item_code' => 'INV-010', 'category' => 'Finished Goods', 'stock' => 180, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/double-data-filer.jpg'],
            ['name' => 'Whiteboard (Small)', 'item_code' => 'INV-011', 'category' => 'Finished Goods', 'stock' => 60, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-small.jpg'],
            ['name' => 'Whiteboard (Medium)', 'item_code' => 'INV-012', 'category' => 'Finished Goods', 'stock' => 40, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-medium.jpg'],
            ['name' => 'Whiteboard (Large)', 'item_code' => 'INV-013', 'category' => 'Finished Goods', 'stock' => 25, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/whiteboard-large.jpg'],
            ['name' => 'Chip Board', 'item_code' => 'INV-014', 'category' => 'Raw Materials', 'stock' => 800, 'unit' => 'sheets', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/chip-board.jpg'],
            ['name' => 'Big Single Data Filer', 'item_code' => 'INV-015', 'category' => 'Finished Goods', 'stock' => 120, 'unit' => 'pcs', 'status' => 'In Stock', 'is_best_seller' => false, 'image_path' => 'inventory/big-single-data-filer.jpg'],
        ];

        foreach ($items as $item) {
            InventoryItem::firstOrCreate(
                ['item_code' => $item['item_code']],
                collect($item)->except('item_code')->toArray()
            );
        }
    }
}