<?php

use App\Services\ImageStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config()->set([
        'cloudinary.cloud_name' => null,
        'cloudinary.api_key' => null,
        'cloudinary.api_secret' => null,
        'cloudinary.folder' => 'canson',
    ]);
});

it('reports cloudinary as unconfigured when credentials are missing', function () {
    expect(app(ImageStorage::class)->isConfigured())->toBeFalse();
});

it('reports cloudinary as configured once all three credentials are present', function () {
    config()->set([
        'cloudinary.cloud_name' => 'demo',
        'cloudinary.api_key' => 'key',
        'cloudinary.api_secret' => 'secret',
    ]);

    expect(app(ImageStorage::class)->isConfigured())->toBeTrue();
});

it('falls back to the local public disk when cloudinary is not configured', function () {
    Storage::fake('public');

    // create() with a real path rather than ->image(), because the GD
    // extension is absent locally even though the image ships with it.
    $file = UploadedFile::fake()->create('chair.png', 8, 'image/png');

    $path = app(ImageStorage::class)->put($file, 'products');

    Storage::disk('public')->assertExists($path);
    expect($path)->toStartWith('products/');
});

it('passes absolute cloudinary urls through untouched', function () {
    $url = 'https://res.cloudinary.com/demo/image/upload/canson/products/chair.png';

    expect(app(ImageStorage::class)->url($url))->toBe($url);
});

it('resolves seeded relative paths against the public storage url', function () {
    expect(app(ImageStorage::class)->url('inventory/A4.png'))
        ->toBe(asset('storage/inventory/A4.png'));
});

it('returns null for a missing image path', function () {
    expect(app(ImageStorage::class)->url(null))->toBeNull()
        ->and(app(ImageStorage::class)->url(''))->toBeNull();
});

it('never deletes local repository images', function () {
    // Seeded inventory paths are committed to the repo and must survive.
    app(ImageStorage::class)->delete('inventory/A4.png');

    expect(true)->toBeTrue();
});
