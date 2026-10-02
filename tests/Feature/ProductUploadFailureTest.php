<?php

use App\Models\Product;
use App\Models\User;
use App\Services\ImageStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.local',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]));

    Product::create([
        'item_code' => 'A4',
        'name' => 'Paper A4',
        'category' => 'Paper',
        'unit' => 'ream',
        'stock' => 10,
        'unit_price' => 500,
        'image_path' => 'inventory/A4.png',
    ]);
});

it('surfaces an upload failure on create instead of a bare 500', function () {

    app()->bind(ImageStorage::class, fn () => new class extends ImageStorage
    {
        public function put(UploadedFile $file, string $folder = 'products'): string
        {
            throw new RuntimeException('cloudinary exploded');
        }
    });

    $response = $this->from(route('products.store'))->post(route('products.store'), [
        'name' => 'Broken Upload Item',
        'category' => 'Test',
        'unit' => 'pcs',
        'stock' => 5,
        'unit_price' => 10,
        'image' => UploadedFile::fake()->create('broken.png', 8, 'image/png'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Product::where('name', 'Broken Upload Item')->exists())->toBeFalse();
});

it('surfaces an upload failure on update instead of a bare 500', function () {
    $item = Product::where('item_code', 'A4')->firstOrFail();
    $original = $item->image_path;

    app()->bind(ImageStorage::class, fn () => new class extends ImageStorage
    {
        public function put(UploadedFile $file, string $folder = 'products'): string
        {
            throw new RuntimeException('cloudinary exploded');
        }
    });

    $response = $this->from(route('products'))->put(route('products.update', $item), [
        'name' => $item->name,
        'unit_price' => $item->unit_price,
        'image' => UploadedFile::fake()->create('broken.png', 8, 'image/png'),
    ]);

    $response->assertRedirect();

    // The previous image must survive a failed replacement.
    expect($item->fresh()->image_path)->toBe($original);
});

it('leaves both upload paths free of unguarded storage calls', function () {
    $controller = (string) file_get_contents(app_path('Http/Controllers/InventoryController.php'));

    expect(substr_count($controller, 'app(ImageStorage::class)->put('))->toBe(1)
        ->and(substr_count($controller, '$storage->put('))->toBe(1)
        ->and($controller)->not->toContain('$file->store(');
});

it('sanitizes undefined or invalid status on create without failing', function () {
    $response = $this->postJson(route('products.store'), [
        'name' => 'Safe Status Product',
        'category' => 'Finished Goods',
        'unit' => 'pcs',
        'stock' => 15,
        'unit_price' => 100,
        'reorder_point' => 5,
        'status' => 'undefined',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $product = Product::where('name', 'Safe Status Product')->first();
    expect($product)->not->toBeNull()
        ->and($product->status)->toBe('In Stock');
});
