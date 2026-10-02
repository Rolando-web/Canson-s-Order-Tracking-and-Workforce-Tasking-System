<?php

namespace App\Services;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Uploads product images to Cloudinary and falls back to the local public disk
 * when no credentials are configured.
 *
 * Why a service rather than a filesystem disk: Cloudinary has no first-party
 * Flysystem driver, so a custom disk would need an adapter from a third party.
 * Uploads here go through the official SDK instead, which keeps the dependency
 * surface smaller.
 */
class ImageStorage
{
    /**
     * Upload an image and return the value to persist in image_path.
     *
     * Returns an absolute Cloudinary URL when configured, otherwise a relative
     * path on the public disk, matching what the seeded rows already contain.
     */
    public function put(UploadedFile $file, string $folder = 'products'): string
    {
        if (! $this->isConfigured()) {
            return $file->store($folder, 'public');
        }

        $response = $this->client()->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => trim(config('cloudinary.folder', 'canson').'/'.$folder, '/'),
                'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'overwrite' => false,
                'resource_type' => 'image',
                'transformation' => [
                    ['width' => config('cloudinary.width', 1600), 'crop' => 'limit'],
                    ['quality' => 'auto', 'fetch_format' => 'auto'],
                ],
            ]
        );

        $url = $this->secureUrl($response);

        Log::info('Uploaded product image to Cloudinary', [
            'folder' => $folder,
            'cloud' => config('cloudinary.cloud_name'),
            'url' => $url,
        ]);

        return $url;
    }

    /**
     * Delete a previously uploaded image.
     *
     * Absolute URLs are Cloudinary assets; anything else is a local path and is
     * left alone, since local files in the repository are not ours to remove.
     */
    public function delete(?string $imagePath): void
    {
        if (! $imagePath || ! $this->isCloudinaryUrl($imagePath)) {
            return;
        }

        if (! $this->isConfigured()) {
            return;
        }

        try {
            // destroy() lives on UploadApi, reached via EditTrait, not on the
            // Cloudinary wrapper itself.
            $this->client()->uploadApi()->destroy(
                $this->publicIdFromUrl($imagePath),
                ['resource_type' => 'image', 'invalidate' => true]
            );
        } catch (\Throwable $e) {
            // A stale asset is not worth failing a request over.
            Log::warning('Could not delete Cloudinary asset', [
                'image_path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function isConfigured(): bool
    {
        return filled(config('cloudinary.cloud_name'))
            && filled(config('cloudinary.api_key'))
            && filled(config('cloudinary.api_secret'));
    }

    public function isCloudinaryUrl(string $imagePath): bool
    {
        return str_starts_with($imagePath, 'https://res.cloudinary.com/');
    }

    /**
     * Resolve image_path to something an <img src> can load.
     *
     * Seeded rows hold relative paths like "inventory/A4.png" and point at the
     * public disk. Cloudinary rows hold absolute URLs. Both are still in the
     * column, so callers resolve through here rather than assuming either.
     */
    public function url(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        if ($this->isCloudinaryUrl($imagePath) || str_starts_with($imagePath, 'http://')) {
            return $imagePath;
        }

        return asset('storage/'.$imagePath);
    }

    private function client(): Cloudinary
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
        ]);
    }

    private function secureUrl(ApiResponse $response): string
    {
        $url = (string) ($response['secure_url'] ?? '');

        if ($url === '') {
            throw new \RuntimeException('Cloudinary upload returned no secure_url.');
        }

        return $url;
    }

    /**
     * Derive the public_id from a delivery URL.
     *
     * Delivery URLs carry the folder, the public id with its extension, and any
     * transformation suffix, so the id is reconstructed from the path between
     * the upload prefix and the file extension.
     */
    private function publicIdFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $path = preg_replace('#^/[^/]+/#', '', $path) ?? $path;

        return preg_replace('/\.[a-z0-9]+$/i', '', $path) ?? $path;
    }
}
