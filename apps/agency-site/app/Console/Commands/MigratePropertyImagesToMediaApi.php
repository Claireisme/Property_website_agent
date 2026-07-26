<?php

namespace App\Console\Commands;

use App\Models\PropertyImage;
use App\Services\MediaApiClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('media:migrate-property-images {--property-id= : Limit migration to one property id} {--dry-run : Show what would be migrated without uploading or updating records}')]
#[Description('Upload existing local property images to the configured Media API and update image URLs')]
class MigratePropertyImagesToMediaApi extends Command
{
    public function handle(MediaApiClient $mediaApi): int
    {
        if (! $mediaApi->enabled()) {
            $this->error('Media API is not configured. Set MEDIA_API_URL, MEDIA_API_TOKEN, and MEDIA_TENANT first.');

            return self::FAILURE;
        }

        $isDryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $failures = 0;
        $migrated = 0;
        $skipped = 0;

        $query = PropertyImage::query()
            ->when($this->option('property-id'), fn ($query, $propertyId) => $query->where('property_id', $propertyId))
            ->orderBy('id');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No property images found.');

            return self::SUCCESS;
        }

        $this->info(($isDryRun ? 'Dry-run scanning' : 'Migrating').' '.$total.' property image records...');

        $query->chunkById(100, function ($images) use ($mediaApi, $disk, $isDryRun, &$failures, &$migrated, &$skipped): void {
            foreach ($images as $image) {
                $path = $image->original_url;

                if (blank($path) || $this->isAbsoluteUrl($path)) {
                    $skipped++;
                    $this->line("Skipped image {$image->id}: already remote or empty.");
                    continue;
                }

                if (! $disk->exists($path)) {
                    $failures++;
                    $this->error("Missing local file for image {$image->id}: {$path}");
                    continue;
                }

                if ($isDryRun) {
                    $migrated++;
                    $this->line("Would migrate image {$image->id}: {$path}");
                    continue;
                }

                try {
                    $upload = $mediaApi->uploadPublicDiskPath($path);

                    $image->forceFill([
                        'original_url' => $upload['brand_url'],
                        'thumbnail_url' => null,
                        'card_url' => null,
                        'detail_url' => null,
                        'large_url' => null,
                    ])->saveQuietly();

                    $migrated++;
                    $this->info("Migrated image {$image->id}: {$upload['brand_url']}");
                } catch (Throwable $exception) {
                    $failures++;
                    $this->error("Failed image {$image->id}: {$exception->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->line("Migratable: {$migrated}");
        $this->line("Skipped: {$skipped}");
        $this->line("Failures: {$failures}");

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function isAbsoluteUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
