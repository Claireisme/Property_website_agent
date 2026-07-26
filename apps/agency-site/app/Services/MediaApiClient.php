<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaApiClient
{
    public function enabled(): bool
    {
        return filled(config('services.media_api.url'))
            && filled(config('services.media_api.token'))
            && filled(config('services.media_api.tenant'));
    }

    public function uploadPublicDiskPath(string $path): array
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Media API is not configured.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw new RuntimeException("Unable to upload missing media file [{$path}].");
        }

        $absolutePath = $disk->path($path);
        $handle = fopen($absolutePath, 'r');

        if (! $handle) {
            throw new RuntimeException("Unable to read media file [{$path}].");
        }

        try {
            $response = Http::timeout(60)
                ->withToken((string) config('services.media_api.token'))
                ->attach('file', $handle, basename($path))
                ->post((string) config('services.media_api.url'), [
                    'tenant' => (string) config('services.media_api.tenant'),
                ]);
        } finally {
            fclose($handle);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Media API upload failed with status '.$response->status().': '.$response->body());
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['ok'] ?? false) !== true || blank($payload['brand_url'] ?? null)) {
            throw new RuntimeException('Media API upload returned an invalid response: '.$response->body());
        }

        return $payload;
    }
}
