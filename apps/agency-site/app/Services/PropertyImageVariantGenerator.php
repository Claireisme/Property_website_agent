<?php

namespace App\Services;

use App\Models\PropertyImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PropertyImageVariantGenerator
{
    private const VARIANTS = [
        'thumbnail_url' => 320,
        'card_url' => 640,
        'detail_url' => 1280,
        'large_url' => 1920,
    ];

    public function generate(PropertyImage $image): void
    {
        if (blank($image->original_url)) {
            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($image->original_url)) {
            return;
        }

        if (app(MediaApiClient::class)->enabled()) {
            $upload = app(MediaApiClient::class)->uploadPublicDiskPath($image->original_url);

            $image->forceFill([
                'original_url' => $upload['brand_url'],
                'thumbnail_url' => null,
                'card_url' => null,
                'detail_url' => null,
                'large_url' => null,
            ])->saveQuietly();

            return;
        }

        $sourcePath = $disk->path($image->original_url);
        $source = @imagecreatefromstring(file_get_contents($sourcePath));

        if (! $source) {
            throw new RuntimeException("Unable to read image [{$image->original_url}].");
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $directory = 'properties/'.$image->property_id.'/variants';
        $disk->makeDirectory($directory);

        $updates = [];

        foreach (self::VARIANTS as $column => $maxWidth) {
            $targetWidth = min($sourceWidth, $maxWidth);
            $targetHeight = (int) round($sourceHeight * ($targetWidth / $sourceWidth));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagecopyresampled(
                $target,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight,
            );

            $filename = Str::slug(pathinfo($image->original_url, PATHINFO_FILENAME)) ?: 'property-image';
            $relativePath = $directory.'/'.$filename.'-'.Str::before($column, '_url').'.webp';

            imagewebp($target, $disk->path($relativePath), 82);
            imagedestroy($target);

            $updates[$column] = $relativePath;
        }

        imagedestroy($source);

        $image->forceFill($updates)->saveQuietly();
    }
}
