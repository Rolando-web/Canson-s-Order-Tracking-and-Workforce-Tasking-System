<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "Updating product image paths to PNG files...\n";
echo "============================================\n\n";

$updates = [
    'INV-006' => 'inventory/SingleDF.png',
    'INV-010' => 'inventory/DoubleDF.png',
    'INV-015' => 'inventory/BigSDF.png',
    'INV-004' => 'inventory/Glue.png',
    'INV-005' => 'inventory/StorageB(L).png',
    'INV-007' => 'inventory/StorageB(S).png',
    'INV-008' => 'inventory/StorageB(M).png',
    'INV-009' => 'inventory/A4.png',
    'INV-013' => 'inventory/WhiteB(L).png',
    'INV-014' => 'inventory/ChipB.png'
];

foreach ($updates as $itemCode => $imagePath) {
    $product = Product::where('item_code', $itemCode)->first();
    if ($product) {
        $product->update(['image_path' => $imagePath]);
        echo "✓ Updated {$itemCode}: {$product->name} -> {$imagePath}\n";
    } else {
        echo "✗ Product {$itemCode} not found\n";
    }
}

echo "\nFixing product names:\n";
// Also fix the Storage Box (Big) name to match the medium image
$product = Product::where('item_code', 'INV-008')->first();
if ($product) {
    $product->update(['name' => 'Storage Box (Medium)']);
    echo "✓ Updated INV-008 name to 'Storage Box (Medium)'\n";
}

echo "\nAll done! All products should now show images correctly.\n";