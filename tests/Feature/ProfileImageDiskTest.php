<?php

use Illuminate\Support\Facades\Storage;

it('uses the local driver for profile images when no S3 endpoint is configured', function () {
    expect(config('filesystems.disks.profile_images.driver'))->toBe('local')
        ->and(config('filesystems.disks.profile_images.root'))->toBe(storage_path('app/public'));
});

it('switches the profile images disk to s3 when an endpoint is configured', function () {
    // Re-evaluate the real config file with AWS_ENDPOINT present so the
    // env('AWS_ENDPOINT') conditional inside it is genuinely exercised.
    putenv('AWS_ENDPOINT=https://ref.storage.supabase.co/storage/v1/s3');

    try {
        $disk = (require config_path('filesystems.php'))['disks']['profile_images'];

        expect($disk['driver'])->toBe('s3')
            ->and($disk['endpoint'])->toBe('https://ref.storage.supabase.co/storage/v1/s3')
            ->and($disk['bucket'])->toBe(env('AWS_BUCKET'));
    } finally {
        putenv('AWS_ENDPOINT');
    }
});

it('falls back to the local driver when the endpoint is absent', function () {
    putenv('AWS_ENDPOINT');

    try {
        $disk = (require config_path('filesystems.php'))['disks']['profile_images'];

        expect($disk['driver'])->toBe('local')
            ->and($disk['root'])->toBe(storage_path('app/public'))
            ->and($disk['url'])->toBe(rtrim(config('app.url'), '/').'/storage');
    } finally {
        putenv('AWS_ENDPOINT');
    }
});

it('produces byte-identical urls to the previous asset() helper in local mode', function () {
    $path = 'Profile/test.jpg';

    expect(Storage::disk('profile_images')->url($path))
        ->toBe(asset('storage/'.$path))
        ->toBe(rtrim(config('app.url'), '/').'/storage/'.$path);
});

it('stores and deletes profile images through the profile_images disk', function () {
    Storage::disk('profile_images')->put('Profile/upload.txt', 'hello');

    expect(Storage::disk('profile_images')->exists('Profile/upload.txt'))->toBeTrue();

    Storage::disk('profile_images')->delete('Profile/upload.txt');

    expect(Storage::disk('profile_images')->exists('Profile/upload.txt'))->toBeFalse();

    Storage::disk('profile_images')->deleteDirectory('Profile');
});

it('no longer builds profile image urls from the local storage path in blade', function () {
    $bladePaths = collect(app('view')->getFinder()->getPaths())
        ->map(fn (string $path) => $path.DIRECTORY_SEPARATOR.'**')
        ->all();

    $offenders = collect($bladePaths)
        ->flatMap(fn (string $glob) => glob($glob.GLOB_BRACE) ?: [])
        ->filter(fn (string $file) => str_ends_with($file, '.blade.php'))
        ->filter(fn (string $file) => str_contains(
            (string) file_get_contents($file),
            "asset('storage/"
        ));

    expect($offenders->all())->toBe([]);
});
