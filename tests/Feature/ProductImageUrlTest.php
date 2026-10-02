<?php

use App\Models\Product;
use App\Services\ImageStorage;

it('exposes the image url accessor on the product model', function () {
    $product = new Product(['image_path' => 'https://res.cloudinary.com/demo/image/upload/canson/products/a.png']);

    expect($product->image_url)->toBe('https://res.cloudinary.com/demo/image/upload/canson/products/a.png');
});

it('resolves seeded relative image paths through the accessor', function () {
    $product = new Product(['image_path' => 'inventory/A4.png']);

    expect($product->image_url)->toBe(asset('storage/inventory/A4.png'));
});

it('returns null from the accessor when no image is set', function () {
    expect((new Product)->image_url)->toBeNull();
});

it('no longer builds product image urls from asset() in blade', function () {
    $offenders = collect(app('view')->getFinder()->getPaths())
        ->flatMap(fn (string $path) => glob($path.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR.'*.blade.php') ?: [])
        ->filter(fn (string $file) => str_contains(
            (string) file_get_contents($file),
            "asset('storage/' . \$item->image_path"
        ));

    expect($offenders->all())->toBe([]);
});

it('uses the image storage service for product uploads', function () {
    $controller = file_get_contents(app_path('Http/Controllers/InventoryController.php'));

    expect($controller)->toContain('ImageStorage')
        ->and($controller)->not->toContain("->store('products', 'public')");
});

it('points every product listing view at the resolved accessor', function () {
    $views = [
        'resources/views/pages/products.blade.php',
        'resources/views/pages/inventory.blade.php',
        'resources/views/pages/stock-in.blade.php',
    ];

    foreach ($views as $view) {
        expect(file_get_contents(base_path($view)))
            ->toContain('$item->image_url');
    }
});

it('keeps the cloudinary url detection narrow to the delivery host', function () {
    $storage = app(ImageStorage::class);

    expect($storage->isCloudinaryUrl('https://res.cloudinary.com/demo/image/upload/a.png'))->toBeTrue()
        ->and($storage->isCloudinaryUrl('https://example.com/a.png'))->toBeFalse()
        ->and($storage->isCloudinaryUrl('inventory/A4.png'))->toBeFalse();
});
